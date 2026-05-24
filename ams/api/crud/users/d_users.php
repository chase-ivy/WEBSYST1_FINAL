<?php
require_once __DIR__ . "/../../crud_base.php";

// Soft-delete only: set is_active = 0 instead of hard DELETE.
// A hard DELETE would be blocked once the FK constraints on verified_by /
// rejected_by / status_changed_by / regenerated_by are added, and would
// also silently destroy the audit trail even before those FKs exist.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
}

$data = getJsonInput();
$id   = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    sendJson(['success' => false, 'error' => 'Valid id is required'], 400);
}

// Prevent deactivating your own account
if (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) === $id) {
    sendJson(['success' => false, 'error' => 'You cannot deactivate your own account'], 403);
}

$stmt = $pdo->prepare('UPDATE users SET is_active = 0 WHERE user_id = ?');
$stmt->execute([$id]);

sendJson(['success' => true, 'rowsAffected' => $stmt->rowCount()]);