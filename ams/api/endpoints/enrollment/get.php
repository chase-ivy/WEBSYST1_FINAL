<?php
// ============================================================
// endpoints/enrollment/get.php
// Fetches a full enrollment record including all child tables.
// Used to populate the verification form on the teacher side.
//
// GET ?id=<enrollment_id>
// GET ?student_id=<student_id>&school_year=<year>  (latest enrollment)
// GET ?school_year=<year>&status=pending            (queue list)
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$enrollmentId = isset($_GET['id']) ? intval($_GET['id']) : null;
$studentId    = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;
$schoolYear   = trim($_GET['school_year'] ?? '');
$status       = trim($_GET['status'] ?? '');

// ── Queue list mode ───────────────────────────────────────────

if ($enrollmentId === null && $studentId === null) {
    $sql    = 'SELECT e.*, s.last_name, s.first_name, s.lrn FROM enrollments e JOIN students s ON e.student_id = s.student_id WHERE 1=1';
    $params = [];
    if ($schoolYear !== '') { $sql .= ' AND e.school_year = ?'; $params[] = $schoolYear; }
    if ($status !== '')     { $sql .= ' AND e.enrollment_status = ?'; $params[] = $status; }
    $sql .= ' ORDER BY e.queue_number ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    sendJson(['success' => true, 'data' => $stmt->fetchAll()]);
}

// ── Single enrollment lookup ──────────────────────────────────

if ($enrollmentId === null && $studentId !== null) {
    $sql    = 'SELECT enrollment_id FROM enrollments WHERE student_id = ?';
    $params = [$studentId];
    if ($schoolYear !== '') { $sql .= ' AND school_year = ?'; $params[] = $schoolYear; }
    $sql .= ' ORDER BY created_at DESC LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    if (!$row) sendJson(['success' => false, 'error' => 'No enrollment found'], 404);
    $enrollmentId = intval($row['enrollment_id']);
}

// Fetch core enrollment
$stmt = $pdo->prepare('
    SELECT e.*, s.lrn, s.psa_bcn, s.last_name, s.first_name, s.middle_name,
           s.extension_name, s.birth_date, s.sex, s.place_of_birth
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    WHERE e.enrollment_id = ? LIMIT 1
');
$stmt->execute([$enrollmentId]);
$enrollment = $stmt->fetch();
if (!$enrollment) sendJson(['success' => false, 'error' => 'Enrollment not found'], 404);

// Fetch child records
$medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
$medStmt->execute([$enrollmentId]);
$medInfo   = $medStmt->fetch();
$medInfoId = $medInfo ? intval($medInfo['medical_information_id']) : null;

$allergies = $conditions = $surgeries = $treatments = $familyHistory = $disabilities = $returningLearner = $addresses = [];

if ($medInfoId) {
    $q = fn($sql) => $pdo->prepare($sql);

    $s = $q('SELECT * FROM enrollment_medical_allergies WHERE medical_information_id = ?'); $s->execute([$medInfoId]); $allergies = $s->fetchAll();
    $s = $q('SELECT * FROM enrollment_medical_conditions WHERE medical_information_id = ?'); $s->execute([$medInfoId]); $conditions = $s->fetchAll();
    $s = $q('SELECT * FROM enrollment_medical_surgeries WHERE medical_information_id = ?'); $s->execute([$medInfoId]); $surgeries = $s->fetchAll();
    $s = $q('SELECT * FROM enrollment_medical_treatments WHERE medical_information_id = ?'); $s->execute([$medInfoId]); $treatments = $s->fetchAll();
    $s = $q('SELECT * FROM enrollment_family_medical_history WHERE medical_information_id = ?'); $s->execute([$medInfoId]); $familyHistory = $s->fetchAll();
}

$s = $pdo->prepare('
    SELECT ed.*, dt.name AS type_name, ds.name AS subtype_name
    FROM enrollment_disabilities ed
    LEFT JOIN disability_types dt ON ed.disability_type_id = dt.disability_type_id
    LEFT JOIN disability_subtypes ds ON ed.disability_subtype_id = ds.disability_subtype_id
    WHERE ed.enrollment_id = ?
');
$s->execute([$enrollmentId]); $disabilities = $s->fetchAll();

$s = $pdo->prepare('SELECT * FROM enrollment_returning_learners WHERE enrollment_id = ? LIMIT 1');
$s->execute([$enrollmentId]); $returningLearner = $s->fetch() ?: null;

$s = $pdo->prepare('SELECT * FROM student_addresses WHERE student_id = ? AND enrollment_id = ?');
$s->execute([$enrollment['student_id'], $enrollmentId]); $addresses = $s->fetchAll();

$s = $pdo->prepare('SELECT * FROM student_parent_guardians WHERE student_id = ?');
$s->execute([$enrollment['student_id']]); $guardians = $s->fetchAll();

sendJson([
    'success'    => true,
    'data'       => [
        'enrollment'       => $enrollment,
        'medical_info'     => $medInfo,
        'allergies'        => $allergies,
        'conditions'       => $conditions,
        'surgeries'        => $surgeries,
        'treatments'       => $treatments,
        'family_history'   => $familyHistory,
        'disabilities'     => $disabilities,
        'returning_learner'=> $returningLearner,
        'addresses'        => $addresses,
        'guardians'        => $guardians,
    ],
]);