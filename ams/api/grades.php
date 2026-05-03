<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

try {
    if ($action === 'get' && ($role === 'teacher' || $role === 'admin')) {
        $class_id = intval($_GET['class_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT g.grade_id, g.enrollment_id, s.first_name, s.last_name, 
                              g.grading_period, g.final_grade, g.remarks
                              FROM grades g
                              JOIN enrollments e ON g.enrollment_id = e.enrollment_id
                              JOIN students s ON e.student_id = s.student_id
                              WHERE e.class_id = ?
                              ORDER BY s.last_name, s.first_name ASC');
        $stmt->execute([$class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'save' && $role === 'teacher') {
        $enrollment_id = intval($_POST['enrollment_id'] ?? 0);
        $grading_period = $_POST['grading_period'] ?? '';
        $final_grade = floatval($_POST['final_grade'] ?? 0);
        $remarks = $_POST['remarks'] ?? '';

        $check = $pdo->prepare('SELECT grade_id FROM grades WHERE enrollment_id = ? AND grading_period = ?');
        $check->execute([$enrollment_id, $grading_period]);
        $existing = $check->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE grades SET final_grade = ?, remarks = ? WHERE grade_id = ?');
            $stmt->execute([$final_grade, $remarks, $existing['grade_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO grades (enrollment_id, grading_period, final_grade, remarks) VALUES (?, ?, ?, ?)');
            $stmt->execute([$enrollment_id, $grading_period, $final_grade, $remarks]);
        }
        echo json_encode(['success' => true, 'message' => 'Grade saved']);
    } elseif ($action === 'get_student' && $role === 'parent') {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT s.subject_name, g.grading_period, g.final_grade, g.remarks
                              FROM grades g
                              JOIN enrollments e ON g.enrollment_id = e.enrollment_id
                              JOIN classes c ON e.class_id = c.class_id
                              JOIN subjects s ON c.subject_id = s.subject_id
                              WHERE e.student_id = ?
                              ORDER BY s.subject_name, g.grading_period ASC');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
