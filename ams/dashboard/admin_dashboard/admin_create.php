<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$errors = [];
$success = '';
$old = [];
$sections = getActiveSections($pdo);
$gradeLevels = array_values(array_unique(array_filter(array_column($sections, 'grade_level'))));
sort($gradeLevels);
if (empty($gradeLevels)) {
    $gradeLevels = ['Kinder', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $role = strtolower(trim($_POST['role'] ?? ''));

    if ($role === 'student') {
        $result = createStudentAccount($pdo, $_POST);
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

                            <div class="form-group">
                        <label for="role">Assigned Role</label>
                        <div class="select-wrap">
                            <select id="role" name="role" required>
                                <option value="" disabled selected>Select a role...</option>
                                <option value="staff" <?php echo (isset($old['role']) && $old['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                                <option value="student" <?php echo (isset($old['role']) && $old['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="grade_level">Grade Level</label>
                        <div class="select-wrap">
                            <select id="grade_level" name="grade_level" required>
                                <option value="" disabled selected>Select grade level...</option>
                                <?php foreach ($gradeLevels as $gradeLevelOption): ?>
                                    <option value="<?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($old['grade_level']) && $old['grade_level'] === $gradeLevelOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="section_id">Section</label>
                        <div class="select-wrap">
                            <select id="section_id" name="section_id" required>
                                <option value="" disabled selected>Select section...</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?php echo intval($section['section_id']); ?>" data-grade-level="<?php echo htmlspecialchars($section['grade_level'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($old['section_id']) && intval($old['section_id']) === intval($section['section_id'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim($section['school_year'] . ' · ' . $section['grade_level'] . ' · ' . $section['name']), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
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

<script src="../../api/client.js?v=3"></script>
<script>
    const createForm = document.getElementById('create-account-form');
    const createAlert = document.getElementById('create-alert');
    const roleSelect = document.getElementById('role');
    const sectionSelect = document.getElementById('section_id');
    const gradeLevelSelect = document.getElementById('grade_level');

    function showCreateMessage(message, isError = false) {
        createAlert.innerHTML = `<div class="alert ${isError ? 'alert-error' : 'alert-success'}">${message}</div>`;
    }

    // Grade level and section are always required for all roles

    sectionSelect.addEventListener('change', () => {
        const selected = sectionSelect.selectedOptions[0];
        if (selected && selected.dataset.gradeLevel) {
            gradeLevelSelect.value = selected.dataset.gradeLevel;
        }
    });

    createForm.addEventListener('submit', async event => {
        event.preventDefault();
        createAlert.innerHTML = '';

        const data = {
            username: document.getElementById('username').value.trim(),
            email: document.getElementById('email').value.trim(),
            role: roleSelect.value.trim(),
            password: document.getElementById('password').value.trim()
        };

        if (!data.role) {
            showCreateMessage('Please select a valid role.', true);
            return;
        }

        // Add grade level and section for all roles
        data.grade_level = gradeLevelSelect.value;
        data.section_id = sectionSelect.value ? Number(sectionSelect.value) : null;

        if (!data.section_id) {
            showCreateMessage('Please select a section.', true);
            return;
        }

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