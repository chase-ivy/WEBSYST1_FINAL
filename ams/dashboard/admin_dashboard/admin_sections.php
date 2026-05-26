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
        $name        = trim($_POST['name'] ?? '');
        $is_active   = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
        $adviser_id  = isset($_POST['adviser_id']) && $_POST['adviser_id'] !== '' ? intval($_POST['adviser_id']) : null;

        if ($school_year === '') $errors[] = 'School year is required.';
        if ($grade_level === '') $errors[] = 'Grade level is required.';
        if ($name === '')        $errors[] = 'Section name is required.';

        if (empty($errors)) {
            $result = createSection($pdo, $school_year, $grade_level, $name, $is_active, [], $adviser_id);
            if ($result['success']) {
                $success = 'Section created successfully.';
            } else {
                $errors[] = $result['error'];
            }
        }
    }

    if ($action === 'update') {
        $id          = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $school_year = trim($_POST['school_year'] ?? '');
        $grade_level = trim($_POST['grade_level'] ?? '');
        $name        = trim($_POST['name'] ?? '');
        $is_active   = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
        $adviser_id  = isset($_POST['adviser_id']) && $_POST['adviser_id'] !== '' ? intval($_POST['adviser_id']) : null;

        if ($id <= 0)            $errors[] = 'Valid section ID is required.';
        if ($school_year === '') $errors[] = 'School year is required.';
        if ($grade_level === '') $errors[] = 'Grade level is required.';
        if ($name === '')        $errors[] = 'Section name is required.';

        if (empty($errors)) {
            $result = updateSection($pdo, $id, [
                'school_year' => $school_year,
                'grade_level' => $grade_level,
                'name'        => $name,
                'is_active'   => $is_active,
                'adviser_id'  => $adviser_id,
            ]);
            if ($result['success']) {
                $success = 'Section updated successfully.';
            } else {
                $errors[] = $result['error'];
            }
        }
    }

    if ($action === 'assign_subject') {
        $section_id = intval($_POST['section_id'] ?? 0);
        $subject_id = intval($_POST['subject_id'] ?? 0);
        $teacher_id = intval($_POST['teacher_id'] ?? 0);

        if ($section_id <= 0) $errors[] = 'Invalid section.';
        if ($subject_id <= 0) $errors[] = 'Subject is required.';
        if ($teacher_id <= 0) $errors[] = 'Teacher is required.';

        if (empty($errors)) {
            try {
                // Prevent duplicate subject assignment in same section
                $dup = $pdo->prepare('SELECT COUNT(*) FROM section_subjects WHERE section_id = ? AND subject_id = ?');
                $dup->execute([$section_id, $subject_id]);
                if ($dup->fetchColumn() > 0) {
                    $errors[] = 'This subject is already assigned to this section.';
                } else {
                    $pdo->prepare('INSERT INTO section_subjects (section_id, subject_id, teacher_id) VALUES (?, ?, ?)')->execute([$section_id, $subject_id, $teacher_id]);
                    $success = 'Subject assigned successfully.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Failed to assign subject: ' . $e->getMessage();
            }
        }
        // Stay on edit view
        if (!isset($_GET['edit_id'])) {
            header('Location: admin_sections.php?edit_id=' . $section_id . ($success ? '&msg=assigned' : ''));
            exit;
        }
    }

    if ($action === 'remove_subject') {
        $section_subject_id = intval($_POST['section_subject_id'] ?? 0);
        $section_id         = intval($_POST['section_id'] ?? 0);
        if ($section_subject_id > 0) {
            $pdo->prepare('DELETE FROM section_subjects WHERE section_subject_id = ?')->execute([$section_subject_id]);
            $success = 'Subject removed.';
        }
        header('Location: admin_sections.php?edit_id=' . $section_id);
        exit;
    }

    if ($action === 'delete') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            $errors[] = 'Valid section ID is required.';
        } else {
            try {
                $check = $pdo->prepare('SELECT COUNT(*) FROM student_sections WHERE section_id = ?');
                $check->execute([$id]);
                if ($check->fetchColumn() > 0) {
                    $errors[] = 'Cannot delete section with enrolled students. Remove students first.';
                } else {
                    $pdo->prepare('DELETE FROM section_subjects WHERE section_id = ?')->execute([$id]);
                    $stmt = $pdo->prepare('DELETE FROM sections WHERE section_id = ?');
                    $stmt->execute([$id]);
                    $success = $stmt->rowCount() > 0 ? 'Section deleted successfully.' : 'Section not found.';
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

// Load staff list for adviser/teacher dropdowns
$staffStmt = $pdo->query("SELECT user_id, username FROM users WHERE role = 'staff' AND is_active = 1 ORDER BY username");
$staffList = $staffStmt->fetchAll(PDO::FETCH_ASSOC);

$supportsAdviser = supportsSectionAdviser($pdo);

// Load subjects master list
$subjectsStmt = $pdo->query("SELECT subject_id, name FROM subjects WHERE is_active = 1 ORDER BY name ASC");
$subjectsList = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Load all sections including adviser name when supported
$sectionQuery = $supportsAdviser
    ? 'SELECT s.*, u.username AS adviser_name FROM sections s LEFT JOIN users u ON u.user_id = s.adviser_id ORDER BY s.school_year DESC, s.grade_level, s.name'
    : 'SELECT s.*, NULL AS adviser_name FROM sections s ORDER BY s.school_year DESC, s.grade_level, s.name';
$stmt = $pdo->query($sectionQuery);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If editing, load current subject assignments for that section
$sectionSubjects = [];
if ($editSection) {
    $ssStmt = $pdo->prepare('SELECT ss.section_subject_id, ss.subject_id, sub.name AS subject_name, ss.teacher_id, u.username AS teacher_name FROM section_subjects ss JOIN subjects sub ON sub.subject_id = ss.subject_id LEFT JOIN users u ON u.user_id = ss.teacher_id WHERE ss.section_id = ? ORDER BY sub.name ASC');
    $ssStmt->execute([$editSection['section_id']]);
    $sectionSubjects = $ssStmt->fetchAll(PDO::FETCH_ASSOC);
}

$schoolYears = array_values(array_unique(array_map(fn($s) => $s['school_year'], $sections)));
rsort($schoolYears);
$gradeLevels = ['Kinder', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
if (empty($schoolYears)) {
    $cy = (int)date('Y');
    $schoolYears = [$cy . '-' . ($cy + 1)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../mobile-nav.css">
    <title>Manage Sections | Admin Dashboard</title>
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
<header class="topbar">
    <button class="mob-menu-btn"
            aria-label="Open menu"
            aria-expanded="false"
            aria-controls="main-sidebar">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
    <span class="topbar-label">Admin Panel</span>
</header>
<div class="shell">
    <?php renderAdminSidebar('sections'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Sections</h1>
            <p>Manage sections (create, edit, delete, assign subjects &amp; teachers)</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2><?php echo $editSection ? 'Edit Section: ' . htmlspecialchars($editSection['name'], ENT_QUOTES, 'UTF-8') : 'All Sections'; ?></h2>
                <?php if (!$editSection): ?>
                    <form method="post" style="display:inline-block;">
                        <input type="hidden" name="action" value="create">
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
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
                            <div><input name="name" placeholder="Section Name" required></div>
                            <div>
                                <select name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <?php if ($supportsAdviser): ?>
                            <div>
                                <select name="adviser_id">
                                    <option value="">No adviser</option>
                                    <?php foreach ($staffList as $staff): ?>
                                        <option value="<?php echo intval($staff['user_id']); ?>"><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <button type="submit" class="btn-primary">Add Section</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="section-body">
                <?php if ($success !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>'; ?></ul></div><?php endif; ?>

                <?php if ($editSection): ?>
                    <!-- Section details form -->
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
                        <?php if ($supportsAdviser): ?>
                        <div class="form-group">
                            <label>Adviser</label>
                            <div class="select-wrap">
                                <select name="adviser_id">
                                    <option value="">No adviser</option>
                                    <?php foreach ($staffList as $staff): ?>
                                        <option value="<?php echo intval($staff['user_id']); ?>" <?php echo (isset($editSection['adviser_id']) && intval($editSection['adviser_id']) === intval($staff['user_id'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Status</label>
                            <div class="select-wrap">
                                <select name="is_active">
                                    <option value="1" <?php echo $editSection['is_active'] ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo !$editSection['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions full">
                            <button class="btn-primary" type="submit">Save Changes</button>
                            <a href="admin_sections.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <!-- Subject & Teacher assignments -->
                    <div style="margin-top: 32px;">
                        <h3 style="margin-bottom: 12px; font-size: 15px;">Subjects &amp; Teachers Assigned to This Section</h3>
                        <p style="opacity:0.6; font-size:13px; margin-bottom:16px;">
                            Teachers only see classes listed here. Students must also be assigned to this section to appear in the teacher's class list.
                        </p>

                        <?php if (empty($sectionSubjects)): ?>
                            <div class="alert" style="background:rgba(255,180,0,0.1); border:1px solid rgba(255,180,0,0.3); border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px;">
                                ⚠️ No subjects assigned yet. Teachers cannot see any students until at least one subject+teacher is assigned below.
                            </div>
                        <?php else: ?>
                            <div class="table-wrap" style="margin-bottom: 20px;">
                                <table class="data-table">
                                    <thead><tr><th>Subject</th><th>Teacher</th><th>Action</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($sectionSubjects as $ss): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ss['subject_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo $ss['teacher_name'] ? htmlspecialchars($ss['teacher_name'], ENT_QUOTES, 'UTF-8') : '<span style="opacity:0.4">—</span>'; ?></td>
                                                <td>
                                                    <form method="post" style="display:inline;" onsubmit="return confirm('Remove this subject from section?');">
                                                        <input type="hidden" name="action" value="remove_subject">
                                                        <input type="hidden" name="section_subject_id" value="<?php echo intval($ss['section_subject_id']); ?>">
                                                        <input type="hidden" name="section_id" value="<?php echo intval($editSection['section_id']); ?>">
                                                        <button type="submit" class="btn-small btn-danger">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <!-- Assign new subject+teacher -->
                        <?php if (!empty($subjectsList) && !empty($staffList)): ?>
                            <form method="post" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                <input type="hidden" name="action" value="assign_subject">
                                <input type="hidden" name="section_id" value="<?php echo intval($editSection['section_id']); ?>">
                                <div>
                                    <select name="subject_id" required>
                                        <option value="" disabled selected>Select subject...</option>
                                        <?php foreach ($subjectsList as $sub): ?>
                                            <option value="<?php echo intval($sub['subject_id']); ?>"><?php echo htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <select name="teacher_id" required>
                                        <option value="" disabled selected>Select teacher...</option>
                                        <?php foreach ($staffList as $staff): ?>
                                            <option value="<?php echo intval($staff['user_id']); ?>"><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-primary">Assign Subject</button>
                            </form>
                        <?php elseif (empty($subjectsList)): ?>
                            <p style="opacity:0.6; font-size:13px;">No subjects in the master list yet. <a href="admin_subjects.php">Add subjects first</a>.</p>
                        <?php elseif (empty($staffList)): ?>
                            <p style="opacity:0.6; font-size:13px;">No active staff accounts. <a href="admin_users.php">Add staff first</a>.</p>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>School Year</th><th>Grade Level</th><th>Section Name</th><th>Adviser</th><th>Subjects</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php if (empty($sections)): ?>
                                    <tr class="empty-row"><td colspan="7">No sections found.</td></tr>
                                <?php else: ?>
                                    <?php
                                    // Pre-load subject counts per section
                                    $subjectCounts = [];
                                    $scStmt = $pdo->query('SELECT section_id, COUNT(*) as cnt FROM section_subjects GROUP BY section_id');
                                    foreach ($scStmt->fetchAll(PDO::FETCH_ASSOC) as $sc) {
                                        $subjectCounts[$sc['section_id']] = $sc['cnt'];
                                    }
                                    ?>
                                    <?php foreach ($sections as $s): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($s['school_year'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($s['grade_level'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo $s['adviser_name'] ? htmlspecialchars($s['adviser_name'], ENT_QUOTES, 'UTF-8') : '<span style="opacity:0.4">—</span>'; ?></td>
                                            <td>
                                                <?php $cnt = $subjectCounts[$s['section_id']] ?? 0; ?>
                                                <?php if ($cnt === 0): ?>
                                                    <span style="opacity:0.4; font-size:12px;">None</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success"><?php echo $cnt; ?> subject<?php echo $cnt !== 1 ? 's' : ''; ?></span>
                                                <?php endif; ?>
                                            </td>
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
<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>
</body>
</html>
