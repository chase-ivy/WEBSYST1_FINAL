<?php
// endpoints/sections/update.php
// Update section metadata (admin only)

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['admin']);
requireMethod('POST');

$data = getJsonInput();

$id = intval($data['id'] ?? 0);
$schoolYear = trim($data['school_year'] ?? '');
$gradeLevel = trim($data['grade_level'] ?? '');
$name = trim($data['name'] ?? '');
$isActive = isset($data['is_active']) ? intval($data['is_active']) : 0;

if ($id <= 0) sendJson(['success' => false, 'error' => 'Invalid section id'], 400);
if ($schoolYear === '') sendJson(['success' => false, 'error' => 'school_year is required'], 400);
if ($gradeLevel === '') sendJson(['success' => false, 'error' => 'grade_level is required'], 400);
if ($name === '') sendJson(['success' => false, 'error' => 'name is required'], 400);

try {
    $stmt = $pdo->prepare('UPDATE sections SET school_year = ?, grade_level = ?, name = ?, is_active = ? WHERE section_id = ?');
    $stmt->execute([$schoolYear, $gradeLevel, $name, $isActive, $id]);
    if ($stmt->rowCount() === 0) {
        // still return success if no change
        sendJson(['success' => true, 'message' => 'No changes made']);
    }
    sendJson(['success' => true, 'message' => 'Section updated']);
} catch (Exception $e) {
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}







