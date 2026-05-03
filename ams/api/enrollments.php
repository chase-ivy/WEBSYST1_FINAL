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
    if ($action === 'list' && $role === 'admin') {
        $stmt = $pdo->query('SELECT e.enrollment_id, s.first_name, s.last_name, s.lrn, c.class_id, 
                            subj.subject_name, u.username as teacher_name, c.grade_level, e.enrollment_date
                            FROM enrollments e
                            JOIN students s ON e.student_id = s.student_id
                            JOIN classes c ON e.class_id = c.class_id
                            JOIN subjects subj ON c.subject_id = subj.subject_id
                            JOIN users u ON c.teacher_id = u.user_id
                            ORDER BY e.enrollment_date DESC');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'create' && $role === 'admin') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $class_id = intval($_POST['class_id'] ?? 0);
        $enrollment_date = $_POST['enrollment_date'] ?? date('Y-m-d');

        // Check if already enrolled
        $check = $pdo->prepare('SELECT enrollment_id FROM enrollments WHERE student_id = ? AND class_id = ?');
        $check->execute([$student_id, $class_id]);
        if ($check->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Student already enrolled in this class']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, class_id, enrollment_date, grade_level) 
                              SELECT ?, ?, ?, grade_level FROM students WHERE student_id = ?');
        $stmt->execute([$student_id, $class_id, $enrollment_date, $student_id]);
        echo json_encode(['success' => true, 'message' => 'Student enrolled', 'enrollment_id' => $pdo->lastInsertId()]);
    } elseif ($action === 'student_enrollments' && ($role === 'parent' || $role === 'student')) {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT e.enrollment_id, s.subject_name, u.username as teacher_name, c.grade_level, c.section, e.enrollment_date
                              FROM enrollments e
                              JOIN classes c ON e.class_id = c.class_id
                              JOIN subjects s ON c.subject_id = s.subject_id
                              JOIN users u ON c.teacher_id = u.user_id
                              WHERE e.student_id = ?
                              ORDER BY s.subject_name ASC');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'delete' && $role === 'admin') {
        $enrollment_id = intval($_POST['enrollment_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE enrollment_id = ?');
        $stmt->execute([$enrollment_id]);
        echo json_encode(['success' => true, 'message' => 'Enrollment deleted']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
