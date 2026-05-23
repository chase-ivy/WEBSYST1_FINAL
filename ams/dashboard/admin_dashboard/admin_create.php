<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$errors = [];
$success = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $role = strtolower(trim($_POST['role'] ?? ''));

    if ($role === 'student') {
        // JS-driven path should call the admin API endpoint to create full student records.
        // Prevent fallback server-side incomplete creation to avoid orphaned user accounts.
        $result = ['success' => false, 'errors' => ['Please use the admin UI with JavaScript enabled to create students.']];
    } else {
        $result = createStaff(
            $pdo,
            trim($_POST['username'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['password'] ?? ''),
            'staff'
        );
    }

    if ($result['success']) {
        $success = $result['message'];
        $old = [];
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
    <title>Create Account | Admin Dashboard</title>
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
</header>

<div class="shell">
        <?php renderAdminSidebar('create'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Create Account</h1>
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

                <form id="create-account-form" method="post" class="form-grid">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" placeholder="e.g. jdoe" value="<?php echo htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" placeholder="email@example.com" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    
                    <input type="hidden" name="role" value="staff">

                    <!-- Grade level and section removed — admin creates pre-registered accounts only -->
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

<script src="../../api/client.js?v=3"></script>
<script>
    const createForm = document.getElementById('create-account-form');
    const createAlert = document.getElementById('create-alert');
    // role is fixed to 'staff' for admin-created accounts

    function showCreateMessage(message, isError = false) {
        createAlert.innerHTML = `<div class="alert ${isError ? 'alert-error' : 'alert-success'}">${message}</div>`;
    }

    // Admin creates pre-registered accounts only; use staff UI for enrollments.
    createForm.addEventListener('submit', async event => {
        event.preventDefault();
        createAlert.innerHTML = '';

        const data = {
            username: document.getElementById('username').value.trim(),
            email: document.getElementById('email').value.trim(),
            role: 'staff',
            password: document.getElementById('password').value.trim()
        };

        try {
            const response = await API.crud.create('users', data);
            if (response.success) {
                showCreateMessage(response.message || 'Account successfully created.');
                createForm.reset();
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