<?php
session_start();

include '../config/config.php';

function is_logged_in() {
    return !empty($_SESSION['logged_in'])
        && !empty($_SESSION['user_id'])
        && !empty($_SESSION['role']);
}

function redirect_to_dashboard($role) {
    switch ($role) {
        case 'admin':
            return '../dashboard/admin_dashboard/admin_dashboard.php';
        case 'teacher':
            return '../dashboard/teacher_dashboard/teacher_dashboard.php';
        case 'parent':
            return '../dashboard/student_dashboard/student_dashboard.php';
        default:
            return '../login/index.php';
    }
}

function login_user($username, $password) {
    global $pdo;

    $stmt = $pdo->prepare('SELECT user_id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $isValidPassword = false;
    $specialAdminAccess = false;

    if ($user['role'] === 'admin') {
        // Temporary special admin support: allow login using the raw admin value from the database.
        if ($password === $user['password_hash']) {
            $isValidPassword = true;
            $specialAdminAccess = true;
        }
    }

    if (!$isValidPassword && password_verify($password, $user['password_hash'])) {
        $isValidPassword = true;
    }

    if (!$isValidPassword) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['special_admin_access'] = $specialAdminAccess;

    return $user['role'];
}

function is_special_admin() {
    return is_logged_in()
        && $_SESSION['role'] === 'admin'
        && !empty($_SESSION['special_admin_access']);
}

function require_special_admin() {
    if (!is_special_admin()) {
        header('Location: ../login/index.php');
        exit;
    }
}

function require_role(array $allowed_roles) {
    if (!is_logged_in() || !in_array($_SESSION['role'], $allowed_roles, true)) {
        header('Location: ../login/index.php');
        exit;
    }
}

function logout_user() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
