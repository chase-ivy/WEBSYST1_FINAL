<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';

try {
    require_role(['staff']);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $activity_id = isset($input['activity_id']) ? (int)$input['activity_id'] : null;
    $scores = $input['scores'] ?? [];

    if (!$activity_id || !is_array($scores)) throw new Exception('Missing parameters');

    $checkStmt = $pdo->prepare("SELECT 1 FROM activity_scores WHERE activity_id = ? AND class_student_id = ? LIMIT 1");
    $updateStmt = $pdo->prepare("UPDATE activity_scores SET score = ? WHERE activity_id = ? AND class_student_id = ?");
    $insertStmt = $pdo->prepare("INSERT INTO activity_scores (activity_id, class_student_id, score) VALUES (?, ?, ?)");

    if (array_values($scores) === $scores) {
        // Numeric array of objects
        $rows = $scores;
    } else {
        $rows = [];
        foreach ($scores as $class_student_id => $score) {
            $rows[] = [
                'class_student_id' => (int)$class_student_id,
                'score' => $score
            ];
        }
    }

    foreach ($rows as $row) {
        $class_student_id = isset($row['class_student_id']) ? (int)$row['class_student_id'] : null;
        $score = isset($row['score']) ? $row['score'] : null;
        if (!$class_student_id) continue;

        $checkStmt->execute([$activity_id, $class_student_id]);
        if ($checkStmt->fetch()) {
            $updateStmt->execute([$score, $activity_id, $class_student_id]);
        } else {
            $insertStmt->execute([$activity_id, $class_student_id, $score]);
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
