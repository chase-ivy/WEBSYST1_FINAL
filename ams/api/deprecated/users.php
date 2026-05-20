<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT user_id, username, email, COALESCE(NULLIF(role, ''), 'Unassigned') AS role, created_at FROM users WHERE role IN ('teacher', 'staff') ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } elseif ($action === 'get') {
        $user_id = intval($_GET['user_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT user_id, username, email, COALESCE(NULLIF(role, ''), 'Unassigned') AS role FROM users WHERE user_id = ? AND role IN ('teacher', 'staff') LIMIT 1");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    } elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $role = strtolower(trim($data['role'] ?? ''));

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
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'errors' => ['Username or email already exists.']]);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $insert->execute([$username, $email, $hash, $role]);

        echo json_encode(['success' => true, 'message' => 'Staff member created successfully.']);
    } elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = intval($data['user_id'] ?? 0);
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $role = strtolower(trim($data['role'] ?? ''));
        $password = trim($data['password'] ?? '');

        $errors = [];
        if ($user_id <= 0) {
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
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND user_id <> ?');
        $stmt->execute([$username, $email, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'errors' => ['Username or email already belongs to another account.']]);
            exit;
        }

        $sql = 'UPDATE users SET username = ?, email = ?, role = ?';
        $params = [$username, $email, $role];
        if ($password !== '') {
            $sql .= ', password_hash = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE user_id = ? AND role IN (\'teacher\', \'staff\')';
        $params[] = $user_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Staff member updated successfully.']);
    } elseif ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = intval($data['user_id'] ?? 0);

        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'errors' => ['Invalid staff ID.']]);
            exit;
        }

        $stmt = $pdo->prepare('SELECT role FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'errors' => ['Staff member not found.']]);
            exit;
        }

        if (!in_array($user['role'], ['teacher', 'staff'], true)) {
            echo json_encode(['success' => false, 'errors' => ['Only teacher or staff accounts can be deleted from this screen.']]);
            exit;
        }

        $dependencyErrors = [];

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM classes WHERE adviser_id = ?');
        $stmt->execute([$user_id]);
        if ((int) $stmt->fetchColumn() > 0) {
            $dependencyErrors[] = 'This staff member is assigned as a class adviser. Remove or reassign their classes before deleting.';
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM class_subjects WHERE teacher_id = ?');
        $stmt->execute([$user_id]);
        if ((int) $stmt->fetchColumn() > 0) {
            $dependencyErrors[] = 'This staff member is assigned to one or more subjects. Unassign their subjects before deleting.';
        }

        if (!empty($dependencyErrors)) {
            echo json_encode(['success' => false, 'errors' => $dependencyErrors]);
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ? AND role IN (\'teacher\', \'staff\')');
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'errors' => ['Unable to delete staff member.']]);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Staff member deleted successfully.']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>