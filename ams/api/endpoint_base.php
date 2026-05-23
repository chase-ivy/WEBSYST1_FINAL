<?php
// ============================================================
// endpoint_base.php
// Shared bootstrap for all business-logic endpoints.
// Handles output buffering, JSON response, JSON input parsing,
// and DB connection. Does NOT enforce auth — each endpoint
// calls require_role() itself so it can specify which roles
// are allowed.
// ============================================================

ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

function sendJson(array $payload, int $status = 200): void {
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

function requireMethod(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        sendJson(['success' => false, 'error' => "Method not allowed. Use $method."], 405);
    }
}