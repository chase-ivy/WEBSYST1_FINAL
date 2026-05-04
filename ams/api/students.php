<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'list') {
        // Get all students
        $stmt = $pdo->query('SELECT s.student_id, s.lrn, s.first_name, s.last_name, s.birth_date, s.sex 
                            FROM students s 
                            ORDER BY s.last_name, s.first_name');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } 
    elseif ($action === 'search') {
        $q = '%' . ($_GET['q'] ?? '') . '%';
        $stmt = $pdo->prepare('SELECT s.student_id, s.lrn, s.first_name, s.last_name, s.birth_date, s.sex 
                              FROM students s 
                              WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR s.lrn LIKE ?
                              ORDER BY s.last_name, s.first_name');
        $stmt->execute([$q, $q, $q]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'get') {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO students (lrn, first_name, last_name, middle_name, birth_date, sex, place_of_birth) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['lrn'] ?? null,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['middle_name'] ?? null,
            $data['birth_date'] ?? null,
            $data['sex'] ?? null,
            $data['place_of_birth'] ?? null
        ]);
        echo json_encode(['success' => true, 'student_id' => $pdo->lastInsertId()]);
    }
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = intval($data['student_id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE students 
                              SET lrn = ?, first_name = ?, last_name = ?, middle_name = ?, birth_date = ?, sex = ?, place_of_birth = ?
                              WHERE student_id = ?');
        $stmt->execute([
            $data['lrn'] ?? null,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['middle_name'] ?? null,
            $data['birth_date'] ?? null,
            $data['sex'] ?? null,
            $data['place_of_birth'] ?? null,
            $student_id
        ]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = intval($data['student_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM students WHERE student_id = ?');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true]);
    }
    else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
