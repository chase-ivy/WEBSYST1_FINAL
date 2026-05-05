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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="crud.css">
    <title>Create Staff | Admin Dashboard</title>
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
</header>

<div class="shell">
    <nav class="sidebar">
        <div class="sidebar-brand">
            <h3>Management</h3>
            <p>Admin Controls</p>
        </div>
        <?php renderAdminSidebar('create'); ?>
    </nav>

    <main class="main">
        <div class="page-header">
            <h1>Staff Registration</h1>
            <p>Add a new staff or student account to the system</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Account Details</h2>
            </div>
            
            <div class="section-body">
                <div id="create-alert"></div>
                
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

                <form id="create-staff-form" method="post" class="form-grid">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" placeholder="e.g. jdoe" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" placeholder="email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Assigned Role</label>
                        <div class="select-wrap">
                            <select id="role" name="role" required>
                                <option value="" disabled selected>Select a role...</option>
                                <option value="staff">Staff</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="Min. 6 characters" required>
                    </div>

                    <div class="form-actions full">
                        <button type="submit" class="btn-primary">Register Account</button>
                        <button type="reset" class="btn-secondary">Clear Form</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>
<script>
    const createForm = document.getElementById('create-staff-form');
    const createAlert = document.getElementById('create-alert');

    function showCreateMessage(message, isError = false) {
        createAlert.innerHTML = `<div class="alert ${isError ? 'alert-error' : 'alert-success'}">${message}</div>`;
    }

    createForm.addEventListener('submit', async event => {
        event.preventDefault();
        createAlert.innerHTML = ''; // Clear previous messages

        const data = {
            username: document.getElementById('username').value.trim(),
            email: document.getElementById('email').value.trim(),
            role: document.getElementById('role').value,
            password: document.getElementById('password').value.trim()
        };

        if (!data.role) {
            showCreateMessage('Please select a valid role.', true);
            return;
        }

        try {
            // Using the client.js API helper
            const response = await API.users.create(data);
            if (response.success) {
                showCreateMessage(response.message || 'Staff member successfully created.');
                createForm.reset();
                // Optional: redirect to update page to see the new entry
                // setTimeout(() => window.location.href = 'admin_update.php', 2000);
            } else {
                const errorText = Array.isArray(response.errors) ? response.errors.join('<br>') : 'Registration failed.';
                showCreateMessage(errorText, true);
            }
        } catch (error) {
            showCreateMessage(error.message || 'An unexpected error occurred during creation.', true);
        }
    });
</script>
</body>
</html>