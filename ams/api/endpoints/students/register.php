<?php
// ============================================================
// endpoints/students/register.php
// Creates a user account + student profile in one transaction.
// Used for both manual registration (admin/staff) and
// guest account creation from the public enrollment form.
//
// Guest accounts: is_active = 0 until enrollment is verified.
// Regular accounts: is_active = 1 immediately.
//
// POST body:
//   username, password, email (optional),
//   lrn (optional), psa_bcn (optional),
//   last_name, first_name, middle_name, extension_name,
//   birth_date, sex, place_of_birth,
//   role (default: student), is_active (default: 0 for student, 1 for others)
//
// Accessible by: admin, staff (for manual), public (for guest)
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

// Allow unauthenticated access for public guest registration
// If logged in as admin/staff, can create any role
$isPublic = !is_logged_in();

requireMethod('POST');
$data = getJsonInput();

$username  = trim($data['username']  ?? '');
$password  = trim($data['password']  ?? '');
$email     = trim($data['email']     ?? '') ?: null;
$role      = trim($data['role']      ?? 'student');

// Allow missing username/password from public enrollment form — auto-generate when absent
// Username will be derived from email or name if possible; password will be randomly generated.
if ($username === '') {
    // Try to derive from email prefix or name
    if ($email) {
        $base = strtolower(preg_replace('/[^a-z0-9]+/', '', strstr($email, '@', true)));
    } else {
        $base = strtolower(preg_replace('/[^a-z0-9]+/', '', ($data['first_name'] ?? '') . ($data['last_name'] ?? '')));
    }
    if ($base === '') {
        $base = 'user';
    }
    // Ensure unique username
    $attempt = 0;
    do {
        $candidate = $base . ($attempt ? str_pad((string)rand(1, 9999), 3, '0', STR_PAD_LEFT) : '');
        $check = $pdo->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
        $check->execute([$candidate]);
        $exists = (bool)$check->fetch();
        $attempt++;
    } while ($exists && $attempt < 10);
    $username = $candidate;
}

if ($password === '') {
    // generate a random password for the user (returned in response)
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $pw = '';
    for ($i = 0; $i < 10; $i++) { $pw .= $chars[random_int(0, strlen($chars) - 1)]; }
    $password = $pw;
    $generatedPassword = $password;
} else {
    $generatedPassword = null;
}

// Public requests can only create student accounts
if ($isPublic && $role !== 'student') {
    sendJson(['success' => false, 'error' => 'Unauthorized role'], 403);
}

// Check username uniqueness
$check = $pdo->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
$check->execute([$username]);
if ($check->fetch()) {
    sendJson(['success' => false, 'error' => 'Username already taken'], 409);
}

// Guest accounts (public form) start inactive until verified
$isActive = ($role === 'student' && $isPublic) ? 0 : 1;

$lastName   = trim($data['last_name']   ?? '');
$firstName  = trim($data['first_name']  ?? '');
$birthDate  = trim($data['birth_date']  ?? '');
$sex        = trim($data['sex']         ?? '');

if ($lastName  === '') sendJson(['success' => false, 'error' => 'last_name is required'], 400);
if ($firstName === '') sendJson(['success' => false, 'error' => 'first_name is required'], 400);
if ($birthDate === '') sendJson(['success' => false, 'error' => 'birth_date is required'], 400);
if (!in_array($sex, ['Male', 'Female'], true)) sendJson(['success' => false, 'error' => 'sex must be Male or Female'], 400);

try {
    $pdo->beginTransaction();

    // 1. Create user account
    $pdo->prepare('
        INSERT INTO users (username, email, password_hash, role, is_active)
        VALUES (?, ?, ?, ?, ?)
    ')->execute([
        $username,
        $email,
        password_hash($password, PASSWORD_BCRYPT),
        $role,
        $isActive,
    ]);
    $userId = intval($pdo->lastInsertId());

    // 2. Create student profile
    $pdo->prepare('
        INSERT INTO students
            (user_id, lrn, psa_bcn, last_name, first_name, middle_name,
             extension_name, birth_date, sex, place_of_birth)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $userId,
        trim($data['lrn']            ?? '') ?: null,
        trim($data['psa_bcn']        ?? '') ?: null,
        $lastName,
        $firstName,
        trim($data['middle_name']    ?? '') ?: null,
        trim($data['extension_name'] ?? '') ?: null,
        $birthDate,
        $sex,
        trim($data['place_of_birth'] ?? '') ?: null,
    ]);
    $studentId = intval($pdo->lastInsertId());

    $pdo->commit();

    // If this was a public registration, create a session for the newly created user
    if ($isPublic) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['special_admin_access'] = false;
    }

    $resp = [
        'success'    => true,
        'user_id'    => $userId,
        'student_id' => $studentId,
        'username'   => $username,
        'is_active'  => $isActive,
    ];
    if (!empty($generatedPassword)) {
        $resp['generated_password'] = $generatedPassword;
    }

    sendJson($resp);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}