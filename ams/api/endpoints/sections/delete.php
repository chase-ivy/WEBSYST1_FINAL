<?php
// endpoints/sections/delete.php
// Delete a section if no enrolled students exist (admin only)

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['admin']);
requireMethod('POST');

$data = getJsonInput();
$id = intval($data['id'] ?? 0);

if ($id <= 0) sendJson(['success' => false, 'error' => 'Invalid section id'], 400);

try {
    $check = $pdo->prepare('SELECT COUNT(*) as count FROM student_sections WHERE section_id = ?');
    $check->execute([$id]);
    $hasStudents = intval($check->fetchColumn() ?? 0) > 0;
    if ($hasStudents) {
        sendJson(['success' => false, 'error' => 'Cannot delete section with enrolled students. Remove students first.'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM sections WHERE section_id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        sendJson(['success' => false, 'error' => 'Section not found'], 404);
    }
    sendJson(['success' => true, 'message' => 'Section deleted']);
} catch (Exception $e) {
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
