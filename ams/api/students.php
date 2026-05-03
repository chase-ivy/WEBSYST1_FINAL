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
        $stmt = $pdo->query('SELECT student_id, first_name, last_name, lrn, date_of_birth, grade_level, status 
                            FROM students 
                            ORDER BY last_name, first_name ASC');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'search' && ($role === 'admin' || $role === 'teacher')) {
        $search = '%' . ($_GET['q'] ?? '') . '%';
        $stmt = $pdo->prepare('SELECT student_id, first_name, last_name, lrn, grade_level, status
                              FROM students 
                              WHERE first_name LIKE ? OR last_name LIKE ? OR lrn LIKE ?
                              ORDER BY last_name, first_name ASC');
        $stmt->execute([$search, $search, $search]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'get' && $role === 'admin') {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
    } elseif ($action === 'create' && $role === 'admin') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $lrn = $_POST['lrn'] ?? '';
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        $grade_level = $_POST['grade_level'] ?? '';
        $status = $_POST['status'] ?? 'Active';

        $stmt = $pdo->prepare('INSERT INTO students (first_name, last_name, lrn, date_of_birth, grade_level, status) 
                              VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$first_name, $last_name, $lrn, $date_of_birth, $grade_level, $status]);
        echo json_encode(['success' => true, 'message' => 'Student created', 'student_id' => $pdo->lastInsertId()]);
    } elseif ($action === 'update' && $role === 'admin') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $lrn = $_POST['lrn'] ?? '';
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        $grade_level = $_POST['grade_level'] ?? '';
        $status = $_POST['status'] ?? 'Active';

        $stmt = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, lrn = ?, date_of_birth = ?, grade_level = ?, status = ? 
                              WHERE student_id = ?');
        $stmt->execute([$first_name, $last_name, $lrn, $date_of_birth, $grade_level, $status, $student_id]);
        echo json_encode(['success' => true, 'message' => 'Student updated']);
    } elseif ($action === 'delete' && $role === 'admin') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM students WHERE student_id = ?');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'message' => 'Student deleted']);
    } elseif ($action === 'dashboard' && $role === 'student') {
        $stmt = $pdo->prepare('SELECT first_name, last_name, lrn, grade_level, status FROM students WHERE student_id = (SELECT student_id FROM enrollments WHERE enrollment_id IN (SELECT enrollment_id FROM grades LIMIT 1)) OR student_id = ?');
        $stmt->execute([$_SESSION['student_id'] ?? 0]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
