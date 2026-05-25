<?php
// ============================================================
// endpoints/sections/assign_student.php
// Assigns a verified student to a section.
// Can only be called after a student_school_records row exists
// (i.e. enrollment must be verified first).
// UNIQUE constraint on school_record_id prevents double assignment.
//
// Also activates the student's user account (is_active = 1)
// if it was created as a guest (is_active = 0).
//
// POST body:
//   school_record_id, section_id
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();

$schoolRecordId = intval($data['school_record_id'] ?? 0);
$sectionId      = intval($data['section_id']       ?? 0);

if ($schoolRecordId <= 0) sendJson(['success' => false, 'error' => 'school_record_id is required'], 400);
if ($sectionId <= 0)      sendJson(['success' => false, 'error' => 'section_id is required'], 400);

// Verify school record exists and get student info (including grade_level)
$stmt = $pdo->prepare('
    SELECT ssr.school_record_id, ssr.student_id, ssr.academic_status, ssr.grade_level,
           s.user_id
    FROM student_school_records ssr
    JOIN students s ON ssr.student_id = s.student_id
    WHERE ssr.school_record_id = ?
    LIMIT 1
');
$stmt->execute([$schoolRecordId]);
$record = $stmt->fetch();

if (!$record) {
    sendJson(['success' => false, 'error' => 'School record not found'], 404);
}

if ($record['academic_status'] !== 'active') {
    sendJson(['success' => false, 'error' => 'Student record is not active'], 400);
}

if (empty(trim((string)($record['grade_level'] ?? '')))) {
    sendJson(['success' => false, 'error' => 'Student school record has no grade level — cannot match to a section'], 400);
}

// Verify section exists and matches grade_level
$sectionCheck = $pdo->prepare('SELECT section_id, grade_level FROM sections WHERE section_id = ? AND is_active = 1 LIMIT 1');
$sectionCheck->execute([$sectionId]);
$section = $sectionCheck->fetch();
if (!$section) {
    sendJson(['success' => false, 'error' => 'Section not found or inactive'], 404);
}

// Validate grade_level match
$sectionGrade = mb_strtolower(trim((string)$section['grade_level']));
$studentGrade = mb_strtolower(trim((string)$record['grade_level']));
if ($sectionGrade !== $studentGrade) {
    sendJson(['success' => false, 'error' => 'Cannot assign: section grade level does not match student grade level'], 400);
}

$assignedBy = intval($_SESSION['user_id']);

try {
    $pdo->beginTransaction();

    // Assign to section
    $pdo->prepare('
        INSERT INTO student_sections (school_record_id, section_id, assigned_by, assigned_at)
        VALUES (?, ?, ?, NOW())
    ')->execute([$schoolRecordId, $sectionId, $assignedBy]);

    // Activate guest account if still inactive
    $pdo->prepare('
        UPDATE users SET is_active = 1 WHERE user_id = ? AND is_active = 0
    ')->execute([$record['user_id']]);

    $pdo->commit();

    sendJson([
        'success'          => true,
        'school_record_id' => $schoolRecordId,
        'section_id'       => $sectionId,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // Catch duplicate assignment
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        sendJson(['success' => false, 'error' => 'Student is already assigned to a section for this school year'], 409);
    }
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}