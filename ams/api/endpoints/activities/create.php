<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';

try {
    require_role(['staff']);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $class_subject_id = isset($input['class_subject_id']) ? (int)$input['class_subject_id'] : null;
    $title = trim($input['title'] ?? '');
    $max_score = isset($input['max_score']) ? $input['max_score'] : null;
    $due_date = $input['due_date'] ?? null;

    if (!$class_subject_id || $title === '') throw new Exception('Missing parameters');

    $stmt = $pdo->prepare("INSERT INTO activities (class_subject_id, title, max_score, due_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$class_subject_id, $title, $max_score, $due_date]);
    $id = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'activity_id' => (int)$id]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
