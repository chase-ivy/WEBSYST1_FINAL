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
        $stmt = $pdo->query('SELECT subject_id, name FROM subjects ORDER BY name');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (?)');
        $stmt->execute([$data['name'] ?? '']);
        echo json_encode(['success' => true, 'subject_id' => $pdo->lastInsertId()]);
    }
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $subject_id = intval($data['subject_id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE subjects SET name = ? WHERE subject_id = ?');
        $stmt->execute([$data['name'] ?? '', $subject_id]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $subject_id = intval($data['subject_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM subjects WHERE subject_id = ?');
        $stmt->execute([$subject_id]);
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
