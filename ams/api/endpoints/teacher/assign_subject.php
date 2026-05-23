<?php
// ============================================================
// endpoints/teacher/assign_subject.php
// Assigns a subject to a teacher's class section.
// POST body: class_id, subject_id
// Accessible by: staff
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff']);
requireMethod('POST');

$data = getJsonInput();
$sectionId = intval($data['class_id'] ?? $data['section_id'] ?? 0);
$subjectId = intval($data['subject_id'] ?? 0);
$teacherId = intval($_SESSION['user_id'] ?? 0);

if ($sectionId <= 0) {
    sendJson(['success' => false, 'error' => 'class_id is required'], 400);
}
if ($subjectId <= 0) {
    sendJson(['success' => false, 'error' => 'subject_id is required'], 400);
}

$section = $pdo->prepare('SELECT section_id FROM sections WHERE section_id = ? AND is_active = 1 LIMIT 1');
$section->execute([$sectionId]);
if (!$section->fetch()) {
    sendJson(['success' => false, 'error' => 'Class section not found or inactive'], 404);
}

$subject = $pdo->prepare('SELECT subject_id FROM subjects WHERE subject_id = ? AND is_active = 1 LIMIT 1');
$subject->execute([$subjectId]);
if (!$subject->fetch()) {
    sendJson(['success' => false, 'error' => 'Subject not found or inactive'], 404);
}

try {
    $stmt = $pdo->prepare('INSERT INTO section_subjects (section_id, subject_id, teacher_id) VALUES (?, ?, ?)');
    $stmt->execute([$sectionId, $subjectId, $teacherId]);
    sendJson(['success' => true, 'section_subject_id' => intval($pdo->lastInsertId())]);
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        sendJson(['success' => false, 'error' => 'This subject is already assigned to the selected class'], 409);
    }
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
