<?php
// ============================================================
// endpoints/sections/unassign_student.php
// Unassigns a student from their section.
// Blocked if the student already has grades, attendance, or
// activity scores — contact admin to resolve those first.
// POST body: { school_record_id: int }
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data           = getJsonInput();
$schoolRecordId = intval($data['school_record_id'] ?? 0);

if ($schoolRecordId <= 0) {
    sendJson(['success' => false, 'error' => 'school_record_id is required'], 400);
}

// Resolve to student_section_id
$stmt = $pdo->prepare('SELECT student_section_id FROM student_sections WHERE school_record_id = ? LIMIT 1');
$stmt->execute([$schoolRecordId]);
$row = $stmt->fetch();

if (!$row) {
    sendJson(['success' => false, 'error' => 'Student is not assigned to any section'], 404);
}

$studentSectionId = intval($row['student_section_id']);

// Block unassign if any academic records already exist
$checks = [
    'attendance'      => 'SELECT COUNT(*) FROM attendance      WHERE class_student_id = ?',
    'grades'          => 'SELECT COUNT(*) FROM grades           WHERE class_student_id = ?',
    'activity scores' => 'SELECT COUNT(*) FROM activity_scores  WHERE class_student_id = ?',
];

foreach ($checks as $label => $sql) {
    $count = $pdo->prepare($sql);
    $count->execute([$studentSectionId]);
    if (intval($count->fetchColumn()) > 0) {
        sendJson([
            'success' => false,
            'error'   => "Cannot unassign: student already has $label recorded. Contact an admin to resolve.",
        ], 409);
    }
}

// Safe to remove — no academic records exist yet
$pdo->prepare('DELETE FROM student_sections WHERE student_section_id = ?')->execute([$studentSectionId]);

sendJson(['success' => true, 'message' => 'Student successfully unassigned from section']);