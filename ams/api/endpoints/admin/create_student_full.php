<?php
// ============================================================
// endpoints/admin/create_student_full.php
// Creates a full student record (user + student + enrollment +
// student_school_records + student_sections) in one transaction.
// Accessible by: staff, admin
// POST body expected:
//   username, password, email (optional), last_name, first_name,
//   birth_date (YYYY-MM-DD), sex (Male|Female), section_id
//   grade_level (optional)
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff']);
requireMethod('POST');

$data = getJsonInput();

$username   = trim($data['username'] ?? '');
$password   = trim($data['password'] ?? '');
$email      = trim($data['email'] ?? '') ?: null;
$lastName   = trim($data['last_name'] ?? '');
$firstName  = trim($data['first_name'] ?? '');
$birthDate  = trim($data['birth_date'] ?? '');
$sex        = trim($data['sex'] ?? '');
$lrn        = trim($data['lrn'] ?? '');
$sectionId  = intval($data['section_id'] ?? 0);

if ($username === '' || $lastName === '' || $firstName === '' || $birthDate === '' || !in_array($sex, ['Male', 'Female'], true) || $sectionId <= 0) {
    sendJson(['success' => false, 'error' => 'Missing required fields'], 400);
}

// Prefer LRN as password if available, otherwise require explicit password
if ($password === '') {
    if ($lrn !== '') {
        $password = $lrn;
    } else {
        sendJson(['success' => false, 'error' => 'password or lrn is required'], 400);
    }
}

// Auto-generate email if not provided
if ($email === null || $email === '') {
    $email = $username . '@ges.edu';
}

// Ensure section exists and derive school_year/grade_level
$secStmt = $pdo->prepare('SELECT section_id, school_year, grade_level FROM sections WHERE section_id = ? LIMIT 1');
$secStmt->execute([$sectionId]);
$section = $secStmt->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    sendJson(['success' => false, 'error' => 'Invalid section_id'], 400);
}

$schoolYear = $section['school_year'] ?? null;
$gradeLevel = $data['grade_level'] ?? ($section['grade_level'] ?? null);

// Check username/email uniqueness
$check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
$check->execute([$username, $email]);
if (intval($check->fetchColumn()) > 0) {
    sendJson(['success' => false, 'error' => 'Username or email already exists'], 409);
}

$verifiedBy = intval($_SESSION['user_id'] ?? 0);

try {
    $pdo->beginTransaction();

    // 1. Create user (active)
    $pdo->prepare('INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)')
        ->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT), 'student']);
    $userId = intval($pdo->lastInsertId());

    // 2. Create student profile
    $pdo->prepare('INSERT INTO students (user_id, last_name, first_name, middle_name, extension_name, birth_date, sex) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$userId, $lastName, $firstName, null, null, $birthDate, $sex]);
    $studentId = intval($pdo->lastInsertId());

    // 3. Create enrollment (verified)
    $queueNumber = 0;
    $pdo->prepare('INSERT INTO enrollments (student_id, school_year, grade_level, enrollment_status, queue_number, verified_by, verified_at) VALUES (?, ?, ?, ?, ?, ?, NOW())')
        ->execute([$studentId, $schoolYear, $gradeLevel, 'verified', $queueNumber, $verifiedBy]);
    $enrollmentId = intval($pdo->lastInsertId());

    // 4. Create student_school_records (snapshot)
    $pdo->prepare('INSERT INTO student_school_records (enrollment_id, student_id, school_year, grade_level, academic_status, lrn, last_name, first_name, middle_name, extension_name, birth_date, sex, verified_by, verified_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())')
        ->execute([$enrollmentId, $studentId, $schoolYear, $gradeLevel, 'active', null, $lastName, $firstName, null, null, $birthDate, $sex, $verifiedBy]);
    $schoolRecordId = intval($pdo->lastInsertId());

    // 5. Assign to section (record who assigned and when)
    $pdo->prepare('INSERT INTO student_sections (school_record_id, section_id, assigned_by, assigned_at) VALUES (?, ?, ?, NOW())')
        ->execute([$schoolRecordId, $sectionId, $verifiedBy]);

    $pdo->commit();

    sendJson([
        'success' => true,
        'user_id' => $userId,
        'student_id' => $studentId,
        'enrollment_id' => $enrollmentId,
        'school_record_id' => $schoolRecordId,
        'message' => 'Student created and assigned to section',
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
