<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createStaff(
        $pdo,
        trim($_POST['username'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['password'] ?? ''),
        trim($_POST['role'] ?? '')
    );

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Create Staff | Admin Dashboard</title>
</head>
<body>
<header>
    <h2>Gibraltar AMS Admin</h2>
    <a class="action-link" href="../../login/logout.php">Logout</a>
</header>
<div class="container">
    <?php renderAdminSidebar('create'); ?>
    <main class="content">
        <div class="card">
            <div class="card-header">
                <h3>Create New Staff Member</h3>
            </div>
            <?php if ($success !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" required>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required>
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select role</option>
                    <option value="teacher">Teacher</option>
                    <option value="parent">Parent</option>
                </select>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
                <button type="submit" class="btn">Create Staff</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
