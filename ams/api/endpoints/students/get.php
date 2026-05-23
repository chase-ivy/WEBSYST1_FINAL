<?php
// ============================================================
// endpoints/students/get.php
// Fetches student profile data from the students table.
// Does NOT return school/medical records — use records/get.php
// for those. This is for the student profile layer only.
//
// GET ?id=<student_id>
//     Single student with their addresses and parent/guardians.
//
// GET ?user_id=<user_id>
//     Look up a student by their user account.
//
// GET ?lrn=<lrn>
//     Look up a student by LRN — useful for duplicate checks
//     before enrollment submission.
//
// GET (no params)
//     Full list of students — admin/staff only, no pagination
//     needed at this scale.
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$studentId = isset($_GET['id'])      ? intval($_GET['id'])      : null;
$userId    = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$lrn       = trim($_GET['lrn']      ?? '');

// ── Helper: load addresses + guardians for one student ────────

function hydrateStudent(PDO $pdo, array $student): array {
    $addrStmt = $pdo->prepare('
        SELECT * FROM student_addresses WHERE student_id = ? ORDER BY created_at ASC
    ');
    $addrStmt->execute([$student['student_id']]);
    $student['addresses'] = $addrStmt->fetchAll();

    $guardianStmt = $pdo->prepare('
        SELECT spg.*, pgt.name AS guardian_type_name
        FROM student_parent_guardians spg
        JOIN parent_guardian_types pgt ON spg.parent_guardian_type_id = pgt.parent_guardian_type_id
        WHERE spg.student_id = ?
        ORDER BY spg.contact_priority ASC
    ');
    $guardianStmt->execute([$student['student_id']]);
    $student['guardians'] = $guardianStmt->fetchAll();

    return $student;
}

// ── Mode 1: by student_id ─────────────────────────────────────

if ($studentId !== null) {
    $stmt = $pdo->prepare('
        SELECT s.*, u.username, u.email, u.role, u.is_active
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.student_id = ?
        LIMIT 1
    ');
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        sendJson(['success' => false, 'error' => 'Student not found'], 404);
    }

    $student = hydrateStudent($pdo, $student);

    $enrollmentStmt = $pdo->prepare('SELECT * FROM enrollments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1');
    $enrollmentStmt->execute([$studentId]);
    $latestEnrollment = $enrollmentStmt->fetch() ?: null;

    $currentAddress = null;
    $permanentAddress = null;
    if ($latestEnrollment) {
        $addressStmt = $pdo->prepare('SELECT * FROM student_addresses WHERE student_id = ? AND address_type = ? AND enrollment_id = ? LIMIT 1');
        $addressStmt->execute([$studentId, 'current', $latestEnrollment['enrollment_id']]);
        $currentAddress = $addressStmt->fetch() ?: null;

        $addressStmt->execute([$studentId, 'permanent', $latestEnrollment['enrollment_id']]);
        $permanentAddress = $addressStmt->fetch() ?: null;
    }

    $parents = [];
    $guardianStmt = $pdo->prepare('SELECT spg.*, pgt.name AS guardian_type_name FROM student_parent_guardians spg JOIN parent_guardian_types pgt ON spg.parent_guardian_type_id = pgt.parent_guardian_type_id WHERE spg.student_id = ? ORDER BY spg.contact_priority ASC');
    $guardianStmt->execute([$studentId]);
    foreach ($guardianStmt->fetchAll() as $guard) {
        $key = strtolower($guard['guardian_type_name'] ?? '') ?: 'guardian';
        if (!in_array($key, ['father', 'mother', 'guardian'], true)) {
            $key = 'guardian';
        }
        $parents[$key] = $guard;
    }

    $returning = null;
    $disabilities = [];
    $medical = null;
    if ($latestEnrollment) {
        $returningStmt = $pdo->prepare('SELECT * FROM enrollment_returning_learners WHERE enrollment_id = ? LIMIT 1');
        $returningStmt->execute([$latestEnrollment['enrollment_id']]);
        $returning = $returningStmt->fetch() ?: null;

        $disabilityStmt = $pdo->prepare('SELECT * FROM enrollment_disabilities WHERE enrollment_id = ?');
        $disabilityStmt->execute([$latestEnrollment['enrollment_id']]);
        $disabilities = $disabilityStmt->fetchAll();

        $medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
        $medStmt->execute([$latestEnrollment['enrollment_id']]);
        $medical = $medStmt->fetch() ?: null;
    }

    sendJson(['success' => true, 'data' => [
        'student' => $student,
        'latest_enrollment' => $latestEnrollment,
        'current_address' => $currentAddress,
        'permanent_address' => $permanentAddress,
        'parents' => $parents,
        'returning' => $returning,
        'disabilities' => $disabilities,
        'medical' => $medical,
    ]]);
}

// ── Mode 2: by user_id ───────────────────────────────────────

if ($userId !== null) {
    $stmt = $pdo->prepare('
        SELECT s.*, u.username, u.email, u.role, u.is_active
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.user_id = ?
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $student = $stmt->fetch();

    if (!$student) {
        sendJson(['success' => false, 'error' => 'Student not found'], 404);
    }

    sendJson(['success' => true, 'data' => hydrateStudent($pdo, $student)]);
}

// ── Mode 3: by LRN (duplicate check / lookup) ────────────────

if ($lrn !== '') {
    $stmt = $pdo->prepare('
        SELECT s.*, u.username, u.email, u.role, u.is_active
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.lrn = ?
        LIMIT 1
    ');
    $stmt->execute([$lrn]);
    $student = $stmt->fetch();

    if (!$student) {
        sendJson(['success' => false, 'error' => 'No student found with that LRN'], 404);
    }

    sendJson(['success' => true, 'data' => hydrateStudent($pdo, $student)]);
}

// ── Mode 4: full list ─────────────────────────────────────────

$stmt = $pdo->prepare('
    SELECT s.*, u.username, u.email, u.role, u.is_active,
           ssr.grade_level,
           COALESCE(sec.school_year, ssr.school_year) AS school_year,
           sec.name AS section
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN student_school_records ssr ON ssr.school_record_id = (
        SELECT school_record_id
        FROM student_school_records
        WHERE student_id = s.student_id
          AND academic_status = "active"
        ORDER BY school_year DESC, created_at DESC
        LIMIT 1
    )
    LEFT JOIN student_sections stsec ON stsec.student_section_id = (
        SELECT student_section_id
        FROM student_sections
        WHERE school_record_id = ssr.school_record_id
        ORDER BY assigned_at DESC
        LIMIT 1
    )
    LEFT JOIN sections sec ON sec.section_id = stsec.section_id
    ORDER BY s.last_name ASC, s.first_name ASC
');
$stmt->execute();
$students = $stmt->fetchAll();

// List mode — no address/guardian hydration to keep payload lean
sendJson(['success' => true, 'data' => $students]);