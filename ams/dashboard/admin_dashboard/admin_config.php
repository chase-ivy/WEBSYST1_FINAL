<?php
include '../../config/config.php';

function getStaffList(PDO $pdo): array {
    $stmt = $pdo->query("SELECT user_id, username, email, COALESCE(NULLIF(role, ''), 'Unassigned') AS role, created_at FROM users WHERE role IN ('teacher', 'staff') ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function getStaffById(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ? AND role IN (\'teacher\', \'staff\') LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $stmt->execute([$table, $column]);
    return intval($stmt->fetchColumn()) > 0;
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
    if ($role === '' || !in_array($role, ['teacher', 'staff'], true)) {
        $errors[] = 'Role must be teacher or staff.';
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

function getActiveSections(PDO $pdo): array {
    $stmt = $pdo->prepare('SELECT section_id, school_year, grade_level, name FROM sections WHERE is_active = 1 ORDER BY school_year DESC, grade_level, name');
    $stmt->execute();
    return $stmt->fetchAll();
}

function createStudentAccount(PDO $pdo, array $data): array {
    $username   = trim($data['username'] ?? '');
    $email      = trim($data['email'] ?? '');
    $password   = trim($data['password'] ?? '');
    $firstName  = trim($data['first_name'] ?? '');
    $lastName   = trim($data['last_name'] ?? '');
    $birthDate  = trim($data['birth_date'] ?? '');
    $sex        = trim($data['sex'] ?? '');
    $gradeLevel = trim($data['grade_level'] ?? '');
    $sectionId  = intval($data['section_id'] ?? 0);

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
    if ($firstName === '') {
        $errors[] = 'First name is required for student accounts.';
    }
    if ($lastName === '') {
        $errors[] = 'Last name is required for student accounts.';
    }
    if ($birthDate === '') {
        $errors[] = 'Birth date is required for student accounts.';
    }
    if ($sex !== 'Male' && $sex !== 'Female') {
        $errors[] = 'Sex must be Male or Female.';
    }
    if ($sectionId <= 0) {
        $errors[] = 'Section selection is required.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'errors' => ['Username or email already exists.']];
    }

    $sectionStmt = $pdo->prepare('SELECT section_id, grade_level, school_year FROM sections WHERE section_id = ? AND is_active = 1 LIMIT 1');
    $sectionStmt->execute([$sectionId]);
    $section = $sectionStmt->fetch();
    if (!$section) {
        return ['success' => false, 'errors' => ['Selected section is invalid.']];
    }

    $gradeLevel = $section['grade_level'] ?: $gradeLevel;
    if ($gradeLevel === '') {
        return ['success' => false, 'errors' => ['Grade level is required.']];
    }

    try {
        $pdo->beginTransaction();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userInsert = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $userInsert->execute([$username, $email, $hash, 'student']);
        $userId = intval($pdo->lastInsertId());

        $studentInsert = $pdo->prepare('INSERT INTO students (user_id, last_name, first_name, middle_name, extension_name, birth_date, sex) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $studentInsert->execute([$userId, $lastName, $firstName, null, null, $birthDate, $sex]);
        $studentId = intval($pdo->lastInsertId());

        $schoolYear = $section['school_year'] ?: null;
        $verifiedBy = $_SESSION['user_id'] ?? null;
        $verifiedAt = date('Y-m-d H:i:s');

        $enrollmentInsert = $pdo->prepare('INSERT INTO enrollments (student_id, school_year, grade_level, enrollment_status, verified_by, verified_at) VALUES (?, ?, ?, ?, ?, ?)');
        $enrollmentInsert->execute([$studentId, $schoolYear, $gradeLevel, 'verified', $verifiedBy, $verifiedAt]);
        $enrollmentId = intval($pdo->lastInsertId());

        $schoolRecordInsert = $pdo->prepare(
            'INSERT INTO student_school_records (enrollment_id, student_id, school_year, grade_level, lrn, last_name, first_name, middle_name, extension_name, birth_date, sex, place_of_birth, verified_by, verified_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $schoolRecordInsert->execute([
            $enrollmentId,
            $studentId,
            $schoolYear,
            $gradeLevel,
            null,
            $lastName,
            $firstName,
            null,
            null,
            $birthDate,
            $sex,
            null,
            $verifiedBy,
            $verifiedAt,
        ]);
        $schoolRecordId = intval($pdo->lastInsertId());

        $sectionAssign = $pdo->prepare('INSERT INTO student_sections (school_record_id, section_id) VALUES (?, ?)');
        $sectionAssign->execute([$schoolRecordId, $sectionId]);

        $pdo->commit();

        return ['success' => true, 'message' => 'Student account created successfully.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'errors' => ['Failed to create student account. Please try again.']];
    }
}

function updateStaff(PDO $pdo, int $userId, string $username, string $email, string $role, ?string $password = null, ?string $gradeLevel = null, int $sectionId = 0): array {
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
    if ($role === '' || !in_array($role, ['teacher', 'staff'], true)) {
        $errors[] = 'Role must be teacher or staff.';
    }

    if ($sectionId > 0 && columnExists($pdo, 'users', 'section_id')) {
        $sectionStmt = $pdo->prepare('SELECT section_id FROM sections WHERE section_id = ? AND is_active = 1 LIMIT 1');
        $sectionStmt->execute([$sectionId]);
        if (!$sectionStmt->fetch()) {
            $errors[] = 'Selected section is invalid.';
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
    $sql .= ' WHERE user_id = ? AND role IN (\'teacher\', \'staff\')';
    $params[] = $userId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return ['success' => true, 'message' => 'Staff member updated successfully.'];
}

function deleteStaff(PDO $pdo, int $userId): array {
    if ($userId <= 0) {
        return ['success' => false, 'errors' => ['Invalid staff ID.']];
    }

    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ? AND role IN (\'teacher\', \'staff\')');
    $stmt->execute([$userId]);

    return ['success' => true, 'message' => 'Staff member deleted successfully.'];
}
