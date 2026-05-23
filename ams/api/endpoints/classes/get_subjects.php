<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../login/auth.php';
require_once __DIR__ . '/../config/config.php';

try {
    if (!is_logged_in()) throw new Exception('Unauthorized');

    $section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : null;
    if (!$section_id) throw new Exception('Missing section_id');

    $stmt = $pdo->prepare("SELECT ss.section_subject_id AS class_subject_id, ss.section_id, ss.subject_id, sub.name AS subject_name
        FROM section_subjects ss
        JOIN subjects sub ON ss.subject_id = sub.subject_id
        WHERE ss.section_id = ?
        ORDER BY sub.name ASC");

    $stmt->execute([$section_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

