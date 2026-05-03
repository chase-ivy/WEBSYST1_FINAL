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
    if ($action === 'list' && $role === 'teacher') {
        $stmt = $pdo->prepare('SELECT c.class_id, s.subject_name, c.grade_level, c.section, c.school_year 
                              FROM classes c
                              JOIN subjects s ON c.subject_id = s.subject_id
                              WHERE c.teacher_id = ?
                              ORDER BY s.subject_name ASC');
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'list' && $role === 'admin') {
        $stmt = $pdo->query('SELECT c.class_id, s.subject_name, u.username, c.grade_level, c.section, c.school_year 
                            FROM classes c
                            JOIN subjects s ON c.subject_id = s.subject_id
                            JOIN users u ON c.teacher_id = u.user_id
                            ORDER BY s.subject_name ASC');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'enrollments' && $role === 'teacher') {
        $class_id = intval($_GET['class_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT e.enrollment_id, s.student_id, s.first_name, s.last_name, s.grade_level, e.grade_level as enrolled_grade
                              FROM enrollments e
                              JOIN students s ON e.student_id = s.student_id
                              WHERE e.class_id = ?
                              ORDER BY s.last_name, s.first_name ASC');
        $stmt->execute([$class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
