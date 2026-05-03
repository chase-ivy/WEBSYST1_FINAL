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

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT subject_id, subject_name, description FROM subjects ORDER BY subject_name ASC');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'create' && $role === 'admin') {
        $subject_name = $_POST['subject_name'] ?? '';
        $description = $_POST['description'] ?? '';

        $stmt = $pdo->prepare('INSERT INTO subjects (subject_name, description) VALUES (?, ?)');
        $stmt->execute([$subject_name, $description]);
        echo json_encode(['success' => true, 'message' => 'Subject created', 'subject_id' => $pdo->lastInsertId()]);
    } elseif ($action === 'update' && $role === 'admin') {
        $subject_id = intval($_POST['subject_id'] ?? 0);
        $subject_name = $_POST['subject_name'] ?? '';
        $description = $_POST['description'] ?? '';

        $stmt = $pdo->prepare('UPDATE subjects SET subject_name = ?, description = ? WHERE subject_id = ?');
        $stmt->execute([$subject_name, $description, $subject_id]);
        echo json_encode(['success' => true, 'message' => 'Subject updated']);
    } elseif ($action === 'delete' && $role === 'admin') {
        $subject_id = intval($_POST['subject_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM subjects WHERE subject_id = ?');
        $stmt->execute([$subject_id]);
        echo json_encode(['success' => true, 'message' => 'Subject deleted']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
