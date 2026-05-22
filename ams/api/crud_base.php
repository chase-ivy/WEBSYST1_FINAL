<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

function sendJson(array $payload, int $status = 200): void {
    // Ensure no accidental output (HTML, warnings) is sent before JSON
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($payload);
    exit;
}

function getJsonInput(): array {
    $body = file_get_contents('php://input');
    if ($body === false || trim($body) === '') {
        return [];
    }

    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        sendJson(['success' => false, 'error' => 'Invalid JSON body'], 400);
    }

    return $data;
}

function sanitizeIdentifier(string $value): string {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
        sendJson(['success' => false, 'error' => 'Invalid table or column name'], 400);
    }
    return $value;
}

function getTableMetadata(PDO $pdo, string $table): array {
    $table = sanitizeIdentifier($table);
    $sql = 'SELECT COLUMN_NAME, COLUMN_KEY, EXTRA FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? ORDER BY ORDINAL_POSITION';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$table]);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($columns)) {
        sendJson(['success' => false, 'error' => "Unknown table: $table"], 404);
    }

    return $columns;
}

function getPrimaryKey(PDO $pdo, string $table): string {
    foreach (getTableMetadata($pdo, $table) as $column) {
        if ($column['COLUMN_KEY'] === 'PRI') {
            return $column['COLUMN_NAME'];
        }
    }

    sendJson(['success' => false, 'error' => "Primary key not found for table: $table"], 500);
}

function getInsertableColumns(PDO $pdo, string $table): array {
    $columns = [];
    foreach (getTableMetadata($pdo, $table) as $column) {
        if (stripos($column['EXTRA'], 'auto_increment') === false) {
            $columns[] = $column['COLUMN_NAME'];
        }
    }
    return $columns;
}

function buildInsertQuery(string $table, array $columns): string {
    $table = sanitizeIdentifier($table);
    $columnList = implode(', ', array_map('sanitizeIdentifier', $columns));
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    return "INSERT INTO $table ($columnList) VALUES ($placeholders)";
}

function buildUpdateQuery(string $table, array $columns, string $primaryKey): string {
    $table = sanitizeIdentifier($table);
    $primaryKey = sanitizeIdentifier($primaryKey);
    $assignments = implode(', ', array_map(fn($column) => sanitizeIdentifier($column) . ' = ?', $columns));
    return "UPDATE $table SET $assignments WHERE $primaryKey = ?";
}

function getUrlId(): ?int {
    if (!isset($_GET['id'])) {
        return null;
    }
    $id = intval($_GET['id']);
    return $id > 0 ? $id : null;
}

function handleCreate(PDO $pdo, string $table): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
    }

    $data = getJsonInput();
    $columns = getInsertableColumns($pdo, $table);
    $payload = array_intersect_key($data, array_flip($columns));

    if (empty($payload)) {
        sendJson(['success' => false, 'error' => 'No valid fields provided for insertion'], 400);
    }

    try {
        $stmt = $pdo->prepare(buildInsertQuery($table, array_keys($payload)));
        $stmt->execute(array_values($payload));
    } catch (PDOException $e) {
        sendJson(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }

    sendJson(['success' => true, 'id' => intval($pdo->lastInsertId())]);
}

function handleRead(PDO $pdo, string $table): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use GET.'], 405);
    }

    $primaryKey = getPrimaryKey($pdo, $table);
    $id = getUrlId();

    if ($id !== null) {
        $stmt = $pdo->prepare('SELECT * FROM ' . sanitizeIdentifier($table) . ' WHERE ' . sanitizeIdentifier($primaryKey) . ' = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            sendJson(['success' => false, 'error' => 'Record not found'], 404);
        }
        sendJson(['success' => true, 'data' => $row]);
    }

    $stmt = $pdo->query('SELECT * FROM ' . sanitizeIdentifier($table));
    sendJson(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function handleUpdate(PDO $pdo, string $table): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
    }

    $data = getJsonInput();
    $primaryKey = getPrimaryKey($pdo, $table);
    $id = isset($data['id']) ? intval($data['id']) : 0;

    if ($id <= 0) {
        sendJson(['success' => false, 'error' => 'Valid id is required'], 400);
    }

    $columns = getInsertableColumns($pdo, $table);
    if (($key = array_search($primaryKey, $columns, true)) !== false) {
        unset($columns[$key]);
    }

    $payload = array_intersect_key($data, array_flip($columns));
    if (empty($payload)) {
        sendJson(['success' => false, 'error' => 'No valid fields provided for update'], 400);
    }

    $stmt = $pdo->prepare(buildUpdateQuery($table, array_keys($payload), $primaryKey));
    $stmt->execute([...array_values($payload), $id]);

    sendJson(['success' => true, 'rowsAffected' => $stmt->rowCount()]);
}

function handleDelete(PDO $pdo, string $table): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
    }

    $data = getJsonInput();
    $primaryKey = getPrimaryKey($pdo, $table);
    $id = isset($data['id']) ? intval($data['id']) : 0;

    if ($id <= 0) {
        sendJson(['success' => false, 'error' => 'Valid id is required'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM ' . sanitizeIdentifier($table) . ' WHERE ' . sanitizeIdentifier($primaryKey) . ' = ?');
    $stmt->execute([$id]);

    sendJson(['success' => true, 'rowsAffected' => $stmt->rowCount()]);
}
