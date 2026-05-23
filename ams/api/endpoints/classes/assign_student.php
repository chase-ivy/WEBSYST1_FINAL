<?php
// ============================================================
// endpoints/classes/assign_student.php
// Assigns a verified student to a section using student_id.
// POST body: student_id, class_id
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();
$studentId = intval($data['student_id'] ?? 0);
$sectionId = intval($data['class_id'] ?? 0);

if ($studentId <= 0) {
    sendJson(['success' => false, 'error' => 'student_id is required'], 400);
}
if ($sectionId <= 0) {
    sendJson(['success' => false, 'error' => 'class_id is required'], 400);
}

$stmt = $pdo->prepare('SELECT ssr.school_record_id, ssr.academic_status, ssr.grade_level, s.user_id
    FROM student_school_records ssr
    JOIN students s ON ssr.student_id = s.student_id
    WHERE ssr.student_id = ?
    ORDER BY ssr.school_year DESC, ssr.created_at DESC
    LIMIT 1');
$stmt->execute([$studentId]);
$record = $stmt->fetch();

if (!$record) {
    sendJson(['success' => false, 'error' => 'Verified student record not found'], 404);
}
if ($record['academic_status'] !== 'active') {
    sendJson(['success' => false, 'error' => 'Student record is not active'], 400);
}

$sectionCheck = $pdo->prepare('SELECT section_id, grade_level FROM sections WHERE section_id = ? AND is_active = 1 LIMIT 1');
$sectionCheck->execute([$sectionId]);
$section = $sectionCheck->fetch();
if (!$section) {
    sendJson(['success' => false, 'error' => 'Class section not found or inactive'], 404);
}

// Validate grade level match
if ($section['grade_level'] !== $record['grade_level']) {
    sendJson(['success' => false, 'error' => 'Cannot assign: section grade level does not match student grade level'], 400);
}

try {
    $pdo->beginTransaction();
    $insert = $pdo->prepare('INSERT INTO student_sections (school_record_id, section_id, assigned_by, assigned_at)
        VALUES (?, ?, ?, NOW())');
    $insert->execute([$record['school_record_id'], $sectionId, intval($_SESSION['user_id'])]);
    $pdo->prepare('UPDATE users SET is_active = 1 WHERE user_id = ? AND is_active = 0')->execute([$record['user_id']]);
    $pdo->commit();
    sendJson(['success' => true, 'student_section_id' => intval($pdo->lastInsertId())]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        sendJson(['success' => false, 'error' => 'Student is already assigned to a class for this school year'], 409);
    }
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
