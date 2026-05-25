<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../api/endpoints/sections/sections_helper.php';
require_special_admin();

$errors = [];
$success = '';
$editSection = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $school_year = trim($_POST['school_year'] ?? '');
        $grade_level = trim($_POST['grade_level'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;

        if ($school_year === '') $errors[] = 'School year is required.';
        if ($grade_level === '') $errors[] = 'Grade level is required.';
        if ($name === '') $errors[] = 'Section name is required.';

        if (empty($errors)) {
            $result = createSection($pdo, $school_year, $grade_level, $name, $is_active);
            if ($result['success']) {
                $success = 'Section created successfully.';
            } else {
                $errors[] = $result['error'];
            }
        }
    }

    if ($action === 'update') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $school_year = trim($_POST['school_year'] ?? '');
        $grade_level = trim($_POST['grade_level'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;

        if ($id <= 0) $errors[] = 'Valid section ID is required.';
        if ($school_year === '') $errors[] = 'School year is required.';
        if ($grade_level === '') $errors[] = 'Grade level is required.';
        if ($name === '') $errors[] = 'Section name is required.';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('UPDATE sections SET school_year = ?, grade_level = ?, name = ?, is_active = ? WHERE section_id = ?');
                $stmt->execute([$school_year, $grade_level, $name, $is_active, $id]);
                $success = 'Section updated successfully.';
            } catch (PDOException $e) {
                $errors[] = 'Failed to update section: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            $errors[] = 'Valid section ID is required.';
        } else {
            try {
                // Check for orphaned records in section_subjects or student_sections
                $check = $pdo->prepare('SELECT COUNT(*) as count FROM student_sections WHERE section_id = ?');
                $check->execute([$id]);
                $hasStudents = $check->fetch(PDO::FETCH_ASSOC)['count'] > 0;
                
                if ($hasStudents) {
                    $errors[] = 'Cannot delete section with enrolled students. Remove students first.';
                } else {
                    $stmt = $pdo->prepare('DELETE FROM sections WHERE section_id = ?');
                    $stmt->execute([$id]);
                    if ($stmt->rowCount() > 0) {
                        $success = 'Section deleted successfully.';
                    } else {
                        $errors[] = 'Section not found.';
                    }
                }
            } catch (PDOException $e) {
                $errors[] = 'Failed to delete section: ' . $e->getMessage();
            }
        }
    }

}

if (!empty($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM sections WHERE section_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $editSection = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if (!$editSection) $errors[] = 'Section not found.';
    }
}

// Load all sections and derive dropdown options for school years and grade levels
$stmt = $pdo->query('SELECT * FROM sections ORDER BY school_year DESC, grade_level, name');
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

$schoolYears = array_values(array_unique(array_map(fn($s) => $s['school_year'], $sections)));
rsort($schoolYears);

// Always show all grade levels, not just existing ones
$gradeLevels = ['Kinder', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];

if (empty($schoolYears)) {
    $cy = (int)date('Y');
    $schoolYears = [($cy) . '-' . ($cy + 1)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <title>Manage Sections | Admin Dashboard</title>
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
</head>
<body>
<header class="topbar"><div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div></header>
<div class="shell">
    <?php renderAdminSidebar('sections'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Sections</h1>
            <p>Manage sections (create, edit, delete)</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2><?php echo $editSection ? 'Edit Section' : 'All Sections'; ?></h2>
                <?php if (!$editSection): ?>
                    <form method="post" style="display:inline-block;">
                        <input type="hidden" name="action" value="create">
                        <div style="display:flex; gap:8px; align-items:center;">
                            <div>
                                <select name="school_year" required>
                                    <option value="" disabled selected>Select school year...</option>
                                    <?php foreach ($schoolYears as $sy): ?>
                                        <option value="<?php echo htmlspecialchars($sy, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($sy, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <select name="grade_level" required>
                                    <option value="" disabled selected>Select grade level...</option>
                                    <?php foreach ($gradeLevels as $gl): ?>
                                        <option value="<?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <input name="name" placeholder="Section Name" required>
                            </div>

                            <div>
                                <select name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <button type="submit" class="btn-primary">Add Section</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="section-body">
                <?php if ($success !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>'; ?></ul></div><?php endif; ?>

                <?php if ($editSection): ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo intval($editSection['section_id']); ?>">
                        <div class="form-group">
                            <label>School Year</label>
                            <div class="select-wrap">
                                <select name="school_year" required>
                                    <option value="" disabled>Select school year...</option>
                                    <?php foreach ($schoolYears as $sy): ?>
                                        <option value="<?php echo htmlspecialchars($sy, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($editSection['school_year'] === $sy) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sy, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Grade Level</label>
                            <div class="select-wrap">
                                <select name="grade_level" required>
                                    <option value="" disabled>Select grade level...</option>
                                    <?php foreach ($gradeLevels as $gl): ?>
                                        <option value="<?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($editSection['grade_level'] === $gl) ? 'selected' : ''; ?>><?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group"><label>Section Name</label><input name="name" value="<?php echo htmlspecialchars($editSection['name'], ENT_QUOTES, 'UTF-8'); ?>" required></div>
                        <div class="form-group"><label>Status</label><select name="is_active"><option value="1" <?php echo $editSection['is_active'] ? 'selected' : ''; ?>>Active</option><option value="0" <?php echo !$editSection['is_active'] ? 'selected' : ''; ?>>Inactive</option></select></div>
                        <div class="form-actions full"><button class="btn-primary" type="submit">Save</button> <a href="admin_sections.php" class="btn-secondary">Cancel</a></div>
                    </form>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>School Year</th><th>Grade Level</th><th>Section Name</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php if (empty($sections)): ?>
                                    <tr class="empty-row"><td colspan="5">No sections found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($sections as $s): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($s['school_year'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($s['grade_level'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo $s['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'; ?></td>
                                            <td class="td-actions">
                                                <a class="btn-small btn-primary" href="admin_sections.php?edit_id=<?php echo intval($s['section_id']); ?>">Edit</a>
                                                <form method="post" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Delete this section?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo intval($s['section_id']); ?>">
                                                    <button type="submit" class="btn-small btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', adminSectionsInit);
</script>
</body>
</html>
