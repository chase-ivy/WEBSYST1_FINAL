<?php
// ============================================================
// endpoints/classes/unassign_subject.php
// Unassigns a subject from a class section.
// POST body: class_subject_id
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();
$classSubjectId = intval($data['class_subject_id'] ?? 0);

if ($classSubjectId <= 0) {
    sendJson(['success' => false, 'error' => 'class_subject_id is required'], 400);
}

$stmt = $pdo->prepare('SELECT teacher_id FROM section_subjects WHERE section_subject_id = ? LIMIT 1');
$stmt->execute([$classSubjectId]);
$assignment = $stmt->fetch();
if (!$assignment) {
    sendJson(['success' => false, 'error' => 'Assigned subject not found'], 404);
}

if ($_SESSION['role'] === 'staff' && intval($assignment['teacher_id']) !== intval($_SESSION['user_id'])) {
    sendJson(['success' => false, 'error' => 'Not authorized to remove this assignment'], 403);
}

try {
    $pdo->prepare('DELETE FROM section_subjects WHERE section_subject_id = ?')->execute([$classSubjectId]);
    sendJson(['success' => true]);
} catch (Exception $e) {
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
