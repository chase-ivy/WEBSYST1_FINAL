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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="admin.css">
    <style>
        body {
        font-family: 'DM Sans', sans-serif;
        background-image: url('hallway.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #2a1a1a; /* fallback if image fails to load */
        color: var(--text);
        min-height: 100vh;
        font-size: 14px;
        line-height: 1.5;
        }
    </style>
    <title>Delete Staff | Admin Dashboard</title>
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
    
</header>

<div class="shell">

        <?php renderAdminSidebar('delete'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Remove Staff</h1>
            <p>Permanently delete staff accounts from the system</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Account Directory</h2>
                <p>Use caution: deletion cannot be undone.</p>
            </div>
            
            <div class="section-body">
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

                <div class="table-wrap">
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
                                <tr class="empty-row"><td colspan="4">No staff accounts found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($staffList as $staff): ?>
                                    <tr>
                                        <td class="td-primary"><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge badge-default"><?php echo htmlspecialchars($staff['role'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="td-actions">
                                            <button type="button" class="btn-danger delete-user" data-user-id="<?php echo htmlspecialchars($staff['user_id'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>
<script>
    const deleteAlert = document.getElementById('delete-alert');

    function showDeleteMessage(message, isError = false) {
        deleteAlert.innerHTML = `<div class="alert ${isError ? 'alert-error' : 'alert-success'}">${message}</div>`;
    }

    // Attach listeners to buttons (works for both initial PHP load and JS refresh)
    function attachDeleteListeners() {
        document.querySelectorAll('.delete-user').forEach(button => {
            // Remove old listener to prevent double-firing if called multiple times
            button.onclick = null; 
            button.onclick = async () => {
                if (!confirm('Are you sure you want to delete this staff member? This action is permanent.')) {
                    return;
                }
                try {
                    const userId = parseInt(button.dataset.userId, 10);
                    const response = await API.users.delete(userId);
                    
                    if (response.success) {
                        showDeleteMessage(response.message || 'Staff deleted successfully.');
                        loadDeleteUsers(); // Refresh list via AJAX
                    } else {
                        showDeleteMessage((response.errors || []).join('<br>') || 'Delete failed.', true);
                    }
                } catch (error) {
                    showDeleteMessage(error.message || 'Delete request failed.', true);
                }
            };
        });
    }

    async function loadDeleteUsers() {
        try {
            const response = await API.users.list();
            const rows = response.data || [];
            const tbody = document.getElementById('delete-staff-tbody');
            if (!tbody) return;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr class="empty-row"><td colspan="4">No staff accounts found.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map(staff => `
                <tr>
                    <td class="td-primary">${staff.username}</td>
                    <td>${staff.email}</td>
                    <td><span class="badge badge-default">${staff.role}</span></td>
                    <td class="td-actions">
                        <button type="button" class="btn-danger delete-user" data-user-id="${staff.user_id}">Delete</button>
                    </td>
                </tr>
            `).join('');

            attachDeleteListeners();
        } catch (error) {
            console.error('Unable to load delete list', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        attachDeleteListeners();
    });
</script>
</body>
</html>