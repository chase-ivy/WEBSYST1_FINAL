<?php
require_once __DIR__ . "/../../crud_base.php";
require_once __DIR__ . "/../../../dashboard/admin_dashboard/admin_config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
}

$data = getJsonInput();
$role = strtolower(trim($data['role'] ?? ''));

if ($role === 'student') {
    $result = createStudentAccount($pdo, $data);
} else {
    $result = createStaff(
        $pdo,
        trim($data['username'] ?? ''),
        trim($data['email'] ?? ''),
        trim($data['password'] ?? ''),
        'staff'
    );
}

if ($result['success']) {
    sendJson(['success' => true, 'message' => $result['message']]);
}
$errors = $result['errors'] ?? [];
$errorMessage = implode(' ', $errors);
sendJson(['success' => false, 'error' => $errorMessage, 'errors' => $errors], 400);

