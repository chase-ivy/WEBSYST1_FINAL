<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../login/auth.php';
require_once __DIR__ . '/../config/config.php';

try {
    require_role(['staff']);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $class_student_id = isset($input['class_student_id']) ? (int)$input['class_student_id'] : null;
    $date = $input['date'] ?? null;
    $status = $input['status'] ?? null;

    if (!$class_student_id || !$date || !$status) throw new Exception('Missing parameters');

    // Upsert attendance
    $check = $pdo->prepare("SELECT 1 FROM attendance WHERE class_student_id = ? AND date = ? LIMIT 1");
    $check->execute([$class_student_id, $date]);

    if ($check->fetch()) {
        $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE class_student_id = ? AND date = ?");
        $stmt->execute([$status, $class_student_id, $date]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance (class_student_id, date, status) VALUES (?, ?, ?)");
        $stmt->execute([$class_student_id, $date, $status]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

