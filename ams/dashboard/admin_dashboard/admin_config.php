<?php
require_once __DIR__ . '/../../config/config.php';

function getActiveSections(PDO $pdo): array {
    $stmt = $pdo->prepare('SELECT s.section_id, s.school_year, s.grade_level, s.name, s.is_active, s.adviser_id, u.username AS adviser_name FROM sections s LEFT JOIN users u ON u.user_id = s.adviser_id WHERE s.is_active = 1 ORDER BY s.school_year DESC, s.grade_level, s.name');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStaffList(PDO $pdo): array {
    $stmt = $pdo->query("SELECT user_id, username, email, COALESCE(NULLIF(role, ''), 'Unassigned') AS role, is_active, created_at FROM users WHERE role = 'staff' ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function getStaffById(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare("SELECT u.* FROM users u WHERE u.user_id = ? AND u.role = 'staff' LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return null;

    // Look up the section this teacher is currently assigned as adviser
    $secStmt = $pdo->prepare('SELECT section_id, grade_level FROM sections WHERE adviser_id = ? AND is_active = 1 ORDER BY school_year DESC LIMIT 1');
    $secStmt->execute([$userId]);
    $section = $secStmt->fetch(PDO::FETCH_ASSOC);
    $user['section_id']  = $section ? $section['section_id']  : null;
    $user['grade_level'] = $section ? $section['grade_level'] : null;

    return $user;
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $stmt->execute([$table, $column]);
    return intval($stmt->fetchColumn()) > 0;
}

function createStudentAccount(PDO $pdo, array $data): array {
    $username = trim($data['username'] ?? '');
    $email    = trim($data['email']    ?? '');
    $password = trim($data['password'] ?? '');
    $role     = strtolower(trim($data['role'] ?? 'student'));

    $errors = [];
    if ($username === '') $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if ($password === '') $errors[] = 'Password is required.';
    if ($role !== 'student') $errors[] = 'Role must be student.';
    if (!empty($errors)) return ['success' => false, 'errors' => $errors];

    $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $check->execute([$username, $email]);
    if ($check->fetchColumn() > 0) return ['success' => false, 'errors' => ['Username or email already exists.']];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)')->execute([$username, $email, $hash, 'student']);
    return ['success' => true, 'message' => 'Student account created successfully.'];
}

function createStaff(PDO $pdo, string $username, string $email, string $password, string $role): array {
    $role = strtolower(trim($role));
    $errors = [];
    if ($username === '') $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if ($password === '') $errors[] = 'Password is required.';
    if ($role !== 'staff') $errors[] = 'Role must be staff.';
    if (!empty($errors)) return ['success' => false, 'errors' => $errors];

    $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $check->execute([$username, $email]);
    if ($check->fetchColumn() > 0) return ['success' => false, 'errors' => ['Username or email already exists.']];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)')->execute([$username, $email, $hash, 'staff']);
    return ['success' => true, 'message' => 'Staff account created successfully.'];
}

function updateStaff(PDO $pdo, int $userId, string $username, string $email, string $role, ?string $password = null, ?string $gradeLevel = null, int $sectionId = 0, ?int $isActive = null): array {
    $role = strtolower(trim($role));
    $errors = [];

    if ($userId <= 0)    $errors[] = 'Invalid staff ID.';
    if ($username === '') $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if ($role === '' || !in_array($role, ['staff'], true)) $errors[] = 'Role must be staff.';

    if ($sectionId > 0) {
        $sectionStmt = $pdo->prepare('SELECT section_id FROM sections WHERE section_id = ? AND is_active = 1 LIMIT 1');
        $sectionStmt->execute([$sectionId]);
        if (!$sectionStmt->fetch()) $errors[] = 'Selected section is invalid.';
    }

    if (!empty($errors)) return ['success' => false, 'errors' => $errors];

    $dup = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND user_id <> ?');
    $dup->execute([$username, $email, $userId]);
    if ($dup->fetchColumn() > 0) return ['success' => false, 'errors' => ['Username or email already belongs to another account.']];

    // Update users table (only columns that actually exist)
    $sql    = 'UPDATE users SET username = ?, email = ?, role = ?';
    $params = [$username, $email, $role];

    if ($password !== null && $password !== '') {
        $sql     .= ', password_hash = ?';
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    if ($isActive !== null) {
        $sql     .= ', is_active = ?';
        $params[] = $isActive ? 1 : 0;
    }
    $sql     .= " WHERE user_id = ? AND role = 'staff'";
    $params[] = $userId;

    $pdo->prepare($sql)->execute($params);

    // Handle section adviser assignment:
    // 1. Remove this teacher as adviser from any section they currently advise
    $pdo->prepare('UPDATE sections SET adviser_id = NULL WHERE adviser_id = ?')->execute([$userId]);

    // 2. If a section was chosen, set them as adviser of that section
    if ($sectionId > 0) {
        $pdo->prepare('UPDATE sections SET adviser_id = ? WHERE section_id = ?')->execute([$userId, $sectionId]);
    }

    return ['success' => true, 'message' => 'Staff member updated successfully.'];
}

function deleteStaff(PDO $pdo, int $userId): array {
    if ($userId <= 0) return ['success' => false, 'errors' => ['Invalid staff ID.']];

    // Unset adviser before deleting
    $pdo->prepare('UPDATE sections SET adviser_id = NULL WHERE adviser_id = ?')->execute([$userId]);
    $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'staff'")->execute([$userId]);

    return ['success' => true, 'message' => 'Staff member deleted successfully.'];
}
