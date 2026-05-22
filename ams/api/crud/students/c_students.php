<?php
require_once __DIR__ . "/../../crud_base.php";

function generateStudentUsername(PDO $pdo, string $candidate): string {
    $base = preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($candidate)));
    if ($base === '') {
        $base = 'student';
    }

    $username = $base;
    $suffix = 0;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');

    while (true) {
        $stmt->execute([$username]);
        if (intval($stmt->fetchColumn()) === 0) {
            break;
        }
        $suffix++;
        $username = $base . $suffix;
    }

    return $username;
}

function createStudentUser(PDO $pdo, array $data): int {
    $email = trim($data['user_email'] ?? '');
    $password = trim($data['user_password'] ?? '');
    $lrn = trim($data['lrn'] ?? '');
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJson(['success' => false, 'error' => 'Invalid student email address'], 400);
    }

    if ($password === '') {
        $password = bin2hex(random_bytes(5));
    }

    $candidate = $lrn ?: ($firstName && $lastName ? $firstName[0] . $lastName : 'student');
    $username = generateStudentUsername($pdo, $candidate);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $username,
        $email === '' ? null : $email,
        $passwordHash,
        'student'
    ]);

    return intval($pdo->lastInsertId());
}

function createStudentRecord(PDO $pdo): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
    }

    $data = getJsonInput();
    if (empty($data['user_id'])) {
        $data['user_id'] = createStudentUser($pdo, $data);
    }

    $columns = getInsertableColumns($pdo, 'students');
    $payload = array_intersect_key($data, array_flip($columns));

    if (empty($payload)) {
        sendJson(['success' => false, 'error' => 'No valid fields provided for insertion'], 400);
    }

    try {
        $stmt = $pdo->prepare(buildInsertQuery('students', array_keys($payload)));
        $stmt->execute(array_values($payload));
        sendJson(['success' => true, 'id' => intval($pdo->lastInsertId())]);
    } catch (PDOException $e) {
        sendJson(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

createStudentRecord($pdo);

