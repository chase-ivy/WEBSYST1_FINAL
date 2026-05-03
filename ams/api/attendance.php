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
    if ($action === 'record' && $role === 'teacher') {
        $enrollment_id = intval($_POST['enrollment_id'] ?? 0);
        $attendance_date = $_POST['attendance_date'] ?? '';
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }

        $check = $pdo->prepare('SELECT attendance_id FROM attendance WHERE enrollment_id = ? AND attendance_date = ?');
        $check->execute([$enrollment_id, $attendance_date]);
        $existing = $check->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE attendance SET status = ? WHERE attendance_id = ?');
            $stmt->execute([$status, $existing['attendance_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO attendance (enrollment_id, attendance_date, status) VALUES (?, ?, ?)');
            $stmt->execute([$enrollment_id, $attendance_date, $status]);
        }
        echo json_encode(['success' => true, 'message' => 'Attendance recorded']);
    } elseif ($action === 'get' && $role === 'teacher') {
        $class_id = intval($_GET['class_id'] ?? 0);
        $attendance_date = $_GET['date'] ?? date('Y-m-d');

        $stmt = $pdo->prepare('SELECT a.attendance_id, e.enrollment_id, s.first_name, s.last_name, a.status
                              FROM enrollments e
                              JOIN students s ON e.student_id = s.student_id
                              LEFT JOIN attendance a ON e.enrollment_id = a.enrollment_id AND a.attendance_date = ?
                              WHERE e.class_id = ?
                              ORDER BY s.last_name, s.first_name ASC');
        $stmt->execute([$attendance_date, $class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'summary' && ($role === 'parent' || $role === 'student')) {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT 
                              SUM(CASE WHEN a.status = \'Present\' THEN 1 ELSE 0 END) AS present,
                              SUM(CASE WHEN a.status = \'Absent\' THEN 1 ELSE 0 END) AS absent,
                              SUM(CASE WHEN a.status = \'Late\' THEN 1 ELSE 0 END) AS late_count,
                              SUM(CASE WHEN a.status = \'Excused\' THEN 1 ELSE 0 END) AS excused
                              FROM attendance a
                              JOIN enrollments e ON a.enrollment_id = e.enrollment_id
                              WHERE e.student_id = ?');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
