<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

function is_logged_in() {
    return !empty($_SESSION['logged_in'])
        && !empty($_SESSION['user_id'])
        && !empty($_SESSION['role']);
}

function redirect_to_dashboard($role) {
    // Get the base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname($_SERVER['SCRIPT_NAME'], 2); // Get /WEBSYST1_FINAL/ams
    
    switch ($role) {
        case 'admin':
            return $protocol . '://' . $host . $basePath . '/dashboard/admin_dashboard/admin_dashboard.php';
        case 'teacher':
            return $protocol . '://' . $host . $basePath . '/dashboard/teacher_dashboard/teacher_dashboard.php';
        case 'parent':
            return $protocol . '://' . $host . $basePath . '/dashboard/student_dashboard/student_dashboard.php';
        default:
            return $protocol . '://' . $host . $basePath . '/login/login.php';
    }
}

function get_login_url() {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/dashboard/') !== false) {
        return dirname($script, 3) . '/login/index.php';
    }
    return dirname($script) . '/login/index.php';
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
        header('Location: ' . get_login_url());
        exit;
    }
}

function require_role(array $allowed_roles) {
    if (!is_logged_in() || !in_array($_SESSION['role'], $allowed_roles, true)) {
        header('Location: ' . get_login_url());
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
