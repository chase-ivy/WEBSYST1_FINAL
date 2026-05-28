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

// ── Lookup Validation Helpers ────────────────────────────

/**
 * Validates that an ID exists in a lookup table.
 * Returns the ID if valid, null if invalid, or throws exception.
 *
 * @param PDO $pdo Database connection
 * @param string $table Lookup table name (e.g., 'medical_allergy_types')
 * @param int|null $id ID to validate
 * @param string $idColumn Column name (default 'primary_key_name_id')
 * @return int|null The validated ID, or null if ID was null/0
 * @throws Exception if ID doesn't exist in table
 */
function validateLookupId(PDO $pdo, string $table, ?int $id, string $idColumn = null): ?int {
    if ($id === null || $id === 0) {
        return null;
    }

    // Auto-detect primary key column if not provided
    if ($idColumn === null) {
        $idColumn = rtrim($table, 's') . '_id'; // Simple convention: remove trailing 's' and add '_id'
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM $table WHERE $idColumn = ? AND is_active = 1");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['cnt'] == 0) {
        throw new Exception("Invalid ID: $id does not exist in $table or is inactive");
    }

    return $id;
}

/**
 * Validates multiple IDs exist in a lookup table.
 * Filters out null/0 values and validates remaining IDs.
 *
 * @param PDO $pdo Database connection
 * @param string $table Lookup table name
 * @param array $ids Array of IDs to validate
 * @param string $idColumn Column name for the ID field
 * @param string $friendlyName Friendly name for error messages (e.g., 'allergy type')
 * @return array Validated IDs (filtered)
 * @throws Exception if any ID doesn't exist
 */
function validateLookupIds(PDO $pdo, string $table, array $ids, string $idColumn, string $friendlyName): array {
    if (empty($ids)) {
        return [];
    }

    // Filter to valid integers only
    $validIds = array_values(array_filter(
        array_map('intval', $ids),
        fn($v) => $v > 0
    ));

    if (empty($validIds)) {
        return [];
    }

    // Check if all IDs exist
    $placeholders = implode(',', array_fill(0, count($validIds), '?'));
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as cnt
        FROM $table
        WHERE $idColumn IN ($placeholders) AND is_active = 1
    ");
    $stmt->execute($validIds);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['cnt'] != count($validIds)) {
        throw new Exception("One or more invalid $friendlyName IDs provided");
    }

    return $validIds;
}

/**
 * Special validator for disability types and subtypes.
 * Validates that disability_type_id exists and optionally validates disability_subtype_id.
 *
 * @param PDO $pdo Database connection
 * @param int $typeId Disability type ID
 * @param int|null $subtypeId Disability subtype ID (optional)
 * @throws Exception if type doesn't exist or subtype doesn't match type
 */
function validateDisabilityIds(PDO $pdo, int $typeId, ?int $subtypeId = null): void {
    if ($typeId <= 0) {
        throw new Exception("Invalid disability type ID");
    }

    // Check type exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM disability_types WHERE disability_type_id = ? AND is_active = 1");
    $stmt->execute([$typeId]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] == 0) {
        throw new Exception("Disability type ID $typeId does not exist or is inactive");
    }

    // Check subtype if provided
    if ($subtypeId !== null && $subtypeId > 0) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as cnt
            FROM disability_subtypes
            WHERE disability_subtype_id = ? AND disability_type_id = ? AND is_active = 1
        ");
        $stmt->execute([$subtypeId, $typeId]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] == 0) {
            throw new Exception("Disability subtype ID $subtypeId does not exist for type $typeId or is inactive");
        }
    }
}