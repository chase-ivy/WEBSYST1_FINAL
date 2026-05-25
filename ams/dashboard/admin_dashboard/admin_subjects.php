<?php
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_special_admin();

$errors = [];
$success = '';
$editSubject = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($action === 'create') {
        if ($name === '') {
            $errors[] = 'Subject name is required.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (?)');
                $stmt->execute([$name]);
                $success = 'Subject created successfully.';
            } catch (PDOException $e) {
                $errors[] = 'Failed to create subject: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'update') {
        $subjectId = intval($_POST['subject_id'] ?? 0);
        if ($subjectId <= 0) {
            $errors[] = 'Valid subject ID is required.';
        }
        if ($name === '') {
            $errors[] = 'Subject name is required.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('UPDATE subjects SET name = ? WHERE subject_id = ?');
                $stmt->execute([$name, $subjectId]);
                $success = 'Subject updated successfully.';
            } catch (PDOException $e) {
                $errors[] = 'Failed to update subject: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $subjectId = intval($_POST['subject_id'] ?? 0);
        if ($subjectId <= 0) {
            $errors[] = 'Valid subject ID is required.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('DELETE FROM subjects WHERE subject_id = ?');
                $stmt->execute([$subjectId]);
                $success = 'Subject deleted successfully.';
            } catch (PDOException $e) {
                $errors[] = 'Failed to delete subject: ' . $e->getMessage();
            }
        }
    }
}

if (!empty($_GET['edit_id'])) {
    $subjectId = intval($_GET['edit_id']);
    if ($subjectId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM subjects WHERE subject_id = ? LIMIT 1');
        $stmt->execute([$subjectId]);
        $editSubject = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if (!$editSubject) {
            $errors[] = 'Subject not found.';
        }
    }
}

$subjectsStmt = $pdo->query('SELECT * FROM subjects ORDER BY name ASC');
$subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subject Master List | Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background-image: url('hallway.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: #2a1a1a;
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
<header class="topbar"><div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div></header>
<div class="shell">
    <?php renderAdminSidebar('subjects'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Subject Master List</h1>
            <p>Create, edit, and remove canonical subjects used by the school.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2><?php echo $editSubject ? 'Edit Subject' : 'Add New Subject'; ?></h2>
            </div>
            <div class="section-body">
                <?php if ($success !== ''): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error"><ul><?php foreach ($errors as $error) echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>'; ?></ul></div>
                <?php endif; ?>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="<?php echo $editSubject ? 'update' : 'create'; ?>">
                    <?php if ($editSubject): ?>
                        <input type="hidden" name="subject_id" value="<?php echo intval($editSubject['subject_id']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="subject_name">Subject Name</label>
                        <input id="subject_name" name="name" type="text" value="<?php echo $editSubject ? htmlspecialchars($editSubject['name'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                    </div>

                    <div class="form-actions full">
                        <button type="submit" class="btn-primary"><?php echo $editSubject ? 'Save Subject' : 'Create Subject'; ?></button>
                        <?php if ($editSubject): ?>
                            <a href="admin_subjects.php" class="btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Subjects</h2>
                <p>All subjects available for staff assignment.</p>
            </div>
            <div class="section-body">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Subject Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subjects)): ?>
                                <tr class="empty-row"><td colspan="2">No subjects found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td class="td-primary"><?php echo htmlspecialchars($subject['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="td-actions">
                                            <a class="btn-small btn-primary" href="admin_subjects.php?edit_id=<?php echo intval($subject['subject_id']); ?>">Edit</a>
                                            <form method="post" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Delete this subject?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="subject_id" value="<?php echo intval($subject['subject_id']); ?>">
                                                <button type="submit" class="btn-small btn-danger">Delete</button>
                                            </form>
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
<script>
document.addEventListener('DOMContentLoaded', adminSubjectsInit);
</script>
</body>
</html>
