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
            <div id="delete-alert"></div>
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
                <tbody id="delete-staff-tbody">
                    <?php if (empty($staffList)): ?>
                        <tr><td colspan="4">No staff accounts found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($staffList as $staff): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($staff['role'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <button type="button" class="action-link delete-user" data-user-id="<?php echo htmlspecialchars($staff['user_id'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>
<script>
    const deleteAlert = document.getElementById('delete-alert');

    function showDeleteMessage(message, isError = false) {
        deleteAlert.innerHTML = `<div class="alert ${isError ? 'alert-error' : 'alert-success'}">${message}</div>`;
    }

    async function loadDeleteUsers() {
        try {
            const response = await API.users.list();
            const rows = response.data || [];
            const tbody = document.getElementById('delete-staff-tbody');
            if (!tbody) return;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">No staff accounts found.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map(staff => `
                <tr>
                    <td>${staff.username}</td>
                    <td>${staff.email}</td>
                    <td>${staff.role}</td>
                    <td><button type="button" class="action-link delete-user" data-user-id="${staff.user_id}">Delete</button></td>
                </tr>
            `).join('');

            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', async () => {
                    if (!confirm('Delete this staff member?')) {
                        return;
                    }
                    try {
                        const response = await API.users.delete(parseInt(button.dataset.userId, 10));
                        if (response.success) {
                            showDeleteMessage(response.message || 'Staff deleted successfully.');
                            loadDeleteUsers();
                        } else {
                            showDeleteMessage((response.errors || []).join('<br>') || 'Delete failed.', true);
                        }
                    } catch (error) {
                        showDeleteMessage(error.message || 'Delete request failed.', true);
                    }
                });
            });
        } catch (error) {
            console.error('Unable to load delete list', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.querySelector('tbody');
        if (tbody) {
            tbody.id = 'delete-staff-tbody';
        }
        loadDeleteUsers();
    });
</script>
</body>
</html>
