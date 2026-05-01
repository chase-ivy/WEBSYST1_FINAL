<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$errors = [];
$success = '';
$editStaff = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $result = updateStaff(
        $pdo,
        intval($_POST['user_id'] ?? 0),
        trim($_POST['username'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['role'] ?? ''),
        trim($_POST['password'] ?? '')
    );

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $errors = $result['errors'];
    }
}

if (!empty($_GET['edit_id'])) {
    $editStaff = getStaffById($pdo, intval($_GET['edit_id']));
}

$staffList = getStaffList($pdo);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Update Staff | Admin Dashboard</title>
</head>
<body>
<header>
    <h2>Gibraltar AMS Admin</h2>
    <a class="action-link" href="../../login/logout.php">Logout</a>
</header>
<div class="container">
    <?php renderAdminSidebar('update'); ?>
    <main class="content">
        <div class="card">
            <div class="card-header">
                <h3><?php echo $editStaff ? 'Update Staff Member' : 'Select a Staff Record'; ?></h3>
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
            <?php if ($editStaff): ?>
                <form method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($editStaff['user_id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" value="<?php echo htmlspecialchars($editStaff['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($editStaff['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="teacher" <?php echo $editStaff['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                        <option value="parent" <?php echo $editStaff['role'] === 'parent' ? 'selected' : ''; ?>>Parent</option>
                    </select>
                    <label for="password">New Password <small>(leave blank to keep current)</small></label>
                    <input id="password" type="password" name="password">
                    <button type="submit" class="btn">Save Changes</button>
                </form>
            <?php else: ?>
                <p>Select a staff member below to edit their details.</p>
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
                                <td><a class="action-link" href="admin_update.php?edit_id=<?php echo htmlspecialchars($staff['user_id'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
