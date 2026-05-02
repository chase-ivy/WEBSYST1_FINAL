<?php
include '../../config/config.php';

function getStaffList(PDO $pdo): array {
    $stmt = $pdo->query("SELECT user_id, username, email, role, created_at FROM users WHERE role IN ('teacher', 'parent') ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function getStaffById(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare('SELECT user_id, username, email, role FROM users WHERE user_id = ? AND role IN ("teacher", "parent") LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function createStaff(PDO $pdo, string $username, string $email, string $password, string $role): array {
    $errors = [];
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (!in_array($role, ['teacher', 'parent'], true)) {
        $errors[] = 'Role must be teacher or parent.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'errors' => ['Username or email already exists.']];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $insert->execute([$username, $email, $hash, $role]);

    return ['success' => true, 'message' => 'Staff member created successfully.'];
}

function updateStaff(PDO $pdo, int $userId, string $username, string $email, string $role, ?string $password = null): array {
    $errors = [];
    if ($userId <= 0) {
        $errors[] = 'Invalid staff ID.';
    }
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (!in_array($role, ['teacher', 'parent'], true)) {
        $errors[] = 'Role must be teacher or parent.';
    }
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND user_id <> ?');
    $stmt->execute([$username, $email, $userId]);
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'errors' => ['Username or email already belongs to another account.']];
    }

    $sql = 'UPDATE users SET username = ?, email = ?, role = ?';
    $params = [$username, $email, $role];
    if ($password !== null && $password !== '') {
        $sql .= ', password_hash = ?';
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    $sql .= ' WHERE user_id = ? AND role IN ("teacher", "parent")';
    $params[] = $userId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return ['success' => true, 'message' => 'Staff member updated successfully.'];
}

function deleteStaff(PDO $pdo, int $userId): array {
    if ($userId <= 0) {
        return ['success' => false, 'errors' => ['Invalid staff ID.']];
    }

    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ? AND role IN ("teacher", "parent")');
    $stmt->execute([$userId]);

    return ['success' => true, 'message' => 'Staff member deleted successfully.'];
}
