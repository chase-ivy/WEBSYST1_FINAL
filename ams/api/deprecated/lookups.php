<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'mother_tongues':
        $stmt = $pdo->prepare('SELECT name FROM mother_tongues WHERE is_active = 1 ORDER BY name ASC');
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
        break;

    case 'indigenous_groups':
        $stmt = $pdo->prepare('SELECT name FROM indigenous_groups WHERE is_active = 1 ORDER BY name ASC');
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
        break;

    case 'all':
        $stmt = $pdo->prepare('SELECT name FROM mother_tongues WHERE is_active = 1 ORDER BY name ASC');
        $stmt->execute();
        $motherTongues = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare('SELECT name FROM indigenous_groups WHERE is_active = 1 ORDER BY name ASC');
        $stmt->execute();
        $indigenousGroups = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'data' => [
                'motherTongues' => $motherTongues,
                'indigenousGroups' => $indigenousGroups,
            ]
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid lookup action']);
        break;
}
