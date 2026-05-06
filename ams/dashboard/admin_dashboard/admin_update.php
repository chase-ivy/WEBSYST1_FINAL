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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
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
    <title>Update Staff | Admin Dashboard</title>
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
    
</header>

<div class="shell">
   
        <?php renderAdminSidebar('update'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Staff Records</h1>
            <p><?php echo $editStaff ? 'Update details for ' . htmlspecialchars($editStaff['username']) : 'Manage and update system users'; ?></p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2><?php echo $editStaff ? 'Edit Member' : 'Staff List'; ?></h2>
            </div>
            
            <div class="section-body">
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

                <div id="js-alert-container"></div>

                <?php if ($editStaff): ?>
                    <!-- Form for Direct PHP POST Update -->
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($editStaff['user_id']); ?>">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" name="username" value="<?php echo htmlspecialchars($editStaff['username']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($editStaff['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="role">User Role</label>
                            <div class="select-wrap">
                                <select id="role" name="role" required>
                                    <option value="teacher" <?php echo $editStaff['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                    <option value="staff" <?php echo $editStaff['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">New Password <small>(leave blank to keep current)</small></label>
                            <input id="password" type="password" name="password">
                        </div>

                        <div class="form-actions full">
                            <button type="submit" class="btn-primary">Update Member</button>
                            <a href="admin_update.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Default List View -->
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
                            <tbody id="update-staff-tbody">
                                <!-- Data handled by client.js -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden Edit Panel for AJAX Updates -->
                    <div id="update-form-wrapper" class="edit-panel" style="display:none;">
                        <div class="edit-panel-header">
                            <h3>Edit Staff Member</h3>
                        </div>
                        <form id="update-staff-form" class="form-grid">
                            <input type="hidden" id="user_id" name="user_id">
                            <div class="form-group">
                                <label>Username</label>
                                <input id="js-username" type="text" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input id="js-email" type="email" required>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <div class="select-wrap">
                                    <select id="js-role">
                                        <option value="teacher">Teacher</option>
                                        <option value="staff">Staff</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input id="js-password" type="password">
                            </div>
                            <div class="form-actions full">
                                <button type="submit" class="btn-primary">Save Changes</button>
                                <button type="button" class="btn-secondary" onclick="location.reload()">Back to List</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>
<script>
    const alertContainer = document.getElementById('js-alert-container');

    function showUpdateMessage(message, isError = false) {
        alertContainer.innerHTML = `<div class="alert ${isError ? 'alert-error' : 'alert-success'}">${message}</div>`;
    }

    async function loadStaffForUpdate() {
        try {
            const response = await API.users.list();
            const rows = response.data || [];
            const tbody = document.getElementById('update-staff-tbody');
            if (!tbody) return;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr class="empty-row"><td colspan="4">No staff accounts found.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map(staff => `
                <tr>
                    <td class="td-primary">${staff.username}</td>
                    <td>${staff.email}</td>
                    <td><span class="badge badge-staff">${staff.role}</span></td>
                    <td class="td-actions">
                        <button type="button" class="btn-edit edit-user" data-user-id="${staff.user_id}">Edit</button>
                    </td>
                </tr>
            `).join('');

            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', () => fillUpdateForm(button.dataset.userId));
            });
        } catch (error) {
            console.error('Unable to load staff list', error);
        }
    }

    async function fillUpdateForm(userId) {
        try {
            const response = await API.users.get(userId);
            const staff = response.data;
            if (!staff) return;

            document.querySelector('.table-wrap').style.display = 'none';
            document.getElementById('user_id').value = staff.user_id;
            document.getElementById('js-username').value = staff.username;
            document.getElementById('js-email').value = staff.email;
            document.getElementById('js-role').value = staff.role;
            
            document.getElementById('update-form-wrapper').style.display = 'block';
        } catch (error) {
            showUpdateMessage('Failed to load staff data.', true);
        }
    }

    const updateForm = document.getElementById('update-staff-form');
    if (updateForm) {
        updateForm.addEventListener('submit', async event => {
            event.preventDefault();
            const data = {
                user_id: parseInt(document.getElementById('user_id').value),
                username: document.getElementById('js-username').value.trim(),
                email: document.getElementById('js-email').value.trim(),
                role: document.getElementById('js-role').value,
                password: document.getElementById('js-password').value.trim()
            };
            try {
                const response = await API.users.update(data.user_id, data);
                if (response.success) {
                    showUpdateMessage('Staff updated successfully.');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showUpdateMessage(response.errors?.join(', ') || 'Update failed.', true);
                }
            } catch (error) {
                showUpdateMessage('Update request failed.', true);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadStaffForUpdate);
</script>
</body>
</html>