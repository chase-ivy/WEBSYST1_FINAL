<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $result = deleteStaff($pdo, intval($_POST['user_id'] ?? 0));
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $errors = $result['errors'];
    }
}

$staffList = getStaffList($pdo);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Delete Staff | Admin Dashboard</title>
</head>
<body>
<header>
    <h2>Gibraltar AMS Admin</h2>
    <a class="action-link" href="../../login/logout.php">Logout</a>
</header>
<div class="container">
    <?php renderAdminSidebar('delete'); ?>
    <main class="content">
        <div class="card">
            <div class="card-header">
                <h3>Delete Staff Accounts</h3>
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
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staffList)): ?>
                        <tr><td colspan="4">No staff accounts found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($staffList as $staff): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($staff['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this staff member?');" style="display:inline; margin:0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($staff['user_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="action-link" style="background:none; border:none; padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
