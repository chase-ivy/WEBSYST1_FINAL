<?php
require_once __DIR__ . '/../../config/config.php';

function getActiveSections(PDO $pdo): array {
    $stmt = $pdo->prepare('SELECT section_id, school_year, grade_level, name, is_active FROM sections WHERE is_active = 1 ORDER BY school_year DESC, grade_level, name');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStaffList(PDO $pdo): array {
    $stmt = $pdo->query("SELECT user_id, username, email, COALESCE(NULLIF(role, ''), 'Unassigned') AS role, is_active, created_at FROM users WHERE role = 'staff' ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function getStaffById(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ? AND role = \'staff\' LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $stmt->execute([$table, $column]);
    return intval($stmt->fetchColumn()) > 0;
}

function createStudentAccount(PDO $pdo, array $data): array {
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');
    $role = strtolower(trim($data['role'] ?? 'student'));

    $errors = [];
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }
    if ($role !== 'student') {
        $errors[] = 'Role must be student.';
    }
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $check->execute([$username, $email]);
    if ($check->fetchColumn() > 0) {
        return ['success' => false, 'errors' => ['Username or email already exists.']];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $email, $hash, 'student']);

    return ['success' => true, 'message' => 'Student account created successfully.'];
}

function createStaff(PDO $pdo, string $username, string $email, string $password, string $role): array {
    $role = strtolower(trim($role));
    $errors = [];
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }
    if ($role !== 'staff') {
        $errors[] = 'Role must be staff.';
    }
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $check->execute([$username, $email]);
    if ($check->fetchColumn() > 0) {
        return ['success' => false, 'errors' => ['Username or email already exists.']];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $email, $hash, 'staff']);

    return ['success' => true, 'message' => 'Staff account created successfully.'];
}

function updateStaff(PDO $pdo, int $userId, string $username, string $email, string $role, ?string $password = null, ?string $gradeLevel = null, int $sectionId = 0, ?int $isActive = null): array {
    $role = strtolower(trim($role));
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
    if ($role === '' || !in_array($role, ['staff'], true)) {
        $errors[] = 'Role must be staff.';
    }

    if ($sectionId > 0 && columnExists($pdo, 'users', 'section_id')) {
        $sectionStmt = $pdo->prepare('SELECT section_id FROM sections WHERE section_id = ? AND is_active = 1 LIMIT 1');
        $sectionStmt->execute([$sectionId]);
        if (!$sectionStmt->fetch()) {
            $errors[] = 'Selected section is invalid.';
        }
    }

    if ($isActive !== null && columnExists($pdo, 'users', 'is_active')) {
        if (!in_array($isActive, [0, 1], true)) {
            $errors[] = 'Invalid active status.';
        }
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
    if ($gradeLevel !== null && columnExists($pdo, 'users', 'grade_level')) {
        $sql .= ', grade_level = ?';
        $params[] = trim($gradeLevel);
    }
    if ($sectionId > 0 && columnExists($pdo, 'users', 'section_id')) {
        $sql .= ', section_id = ?';
        $params[] = $sectionId;
    }
    if ($isActive !== null && columnExists($pdo, 'users', 'is_active')) {
        $sql .= ', is_active = ?';
        $params[] = $isActive;
    }
    $sql .= ' WHERE user_id = ? AND role = \'staff\'';
    $params[] = $userId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return ['success' => true, 'message' => 'Staff member updated successfully.'];
}

function deleteStaff(PDO $pdo, int $userId): array {
    if ($userId <= 0) {
        return ['success' => false, 'errors' => ['Invalid staff ID.']];
    }

    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ? AND role = \'staff\'');
    $stmt->execute([$userId]);

    return ['success' => true, 'message' => 'Staff member deleted successfully.'];
}
