<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$staffList = getStaffList($pdo);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Admin Dashboard</title>
</head>
<body>
<header>
    <h2>Gibraltar AMS Admin</h2>
    <a class="action-link" href="../../login/logout.php">Logout</a>
</header>
<div class="container">
    <?php renderAdminSidebar('dashboard'); ?>
    <main class="content">
        <div class="card">
            <div class="card-header">
                <h3>Overview</h3>
            </div>
            <div class="grid">
                <div class="card">
                    <h3>Total Staff</h3>
                    <p><span id="staff-count"><?php echo count($staffList); ?></span> active accounts</p>
                </div>
                <div class="card">
                    <h3>Create Staff</h3>
                    <p>Open the create page to add new teacher or parent accounts.</p>
                    <a class="btn" href="admin_create.php">Go to Create</a>
                </div>
                <div class="card">
                    <h3>Update Staff</h3>
                    <p>Review and edit existing staff records.</p>
                    <a class="btn" href="admin_update.php">Go to Update</a>
                </div>
                <div class="card">
                    <h3>Delete Staff</h3>
                    <p>Remove accounts that should no longer have access.</p>
                    <a class="btn" href="admin_delete.php">Go to Delete</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Recent Staff Accounts</h3>
            </div>
            <div id="staff-error" class="alert alert-error" style="display:none;"></div>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody id="staff-tbody">
                    <?php if (empty($staffList)): ?>
                        <tr><td colspan="4">No staff accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($staffList as $staff): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($staff['role'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($staff['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>
<script>
    async function loadAdminStaff() {
        try {
            const response = await API.users.list();
            const rows = response.data || [];
            const tbody = document.getElementById('staff-tbody');
            const count = document.getElementById('staff-count');

            count.textContent = rows.length;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">No staff accounts found.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map(staff => `
                <tr>
                    <td>${staff.username}</td>
                    <td>${staff.email}</td>
                    <td>${staff.role || 'Unassigned'}</td>
                    <td>${staff.created_at}</td>
                </tr>
            `).join('');
        } catch (error) {
            const errorContainer = document.getElementById('staff-error');
            if (errorContainer) {
                errorContainer.textContent = error.message || 'Unable to load staff list.';
                errorContainer.style.display = 'block';
            }
            console.error('Unable to load staff list', error);
        }
    }

    document.addEventListener('DOMContentLoaded', loadAdminStaff);
</script>
</body>
</html>
