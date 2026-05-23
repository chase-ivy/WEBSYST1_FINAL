<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';

try {
    require_role(['staff']);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $class_student_id = isset($input['class_student_id']) ? (int)$input['class_student_id'] : null;
    $class_subject_id = isset($input['class_subject_id']) ? (int)$input['class_subject_id'] : null;
    $grade_id = isset($input['grade_id']) ? (int)$input['grade_id'] : 0;
    $grading_period = $input['grading_period'] ?? null;
    $grade = isset($input['grade']) ? $input['grade'] : null;

    if (!$class_student_id || !$class_subject_id || $grading_period === null || $grade === null) throw new Exception('Missing parameters');

    if ($grade_id > 0) {
        $stmt = $pdo->prepare("UPDATE grades SET grading_period = ?, grade = ? WHERE grade_id = ? AND class_student_id = ? AND class_subject_id = ?");
        $stmt->execute([$grading_period, $grade, $grade_id, $class_student_id, $class_subject_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO grades (class_student_id, class_subject_id, grading_period, grade) VALUES (?, ?, ?, ?)");
        $stmt->execute([$class_student_id, $class_subject_id, $grading_period, $grade]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
