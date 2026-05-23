<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$errors = [];
$success = '';
$editStaff = null;
$sections = getActiveSections($pdo);
$gradeLevels = array_values(array_unique(array_filter(array_column($sections, 'grade_level'))));
sort($gradeLevels);
if (empty($gradeLevels)) {
    $gradeLevels = ['Kinder', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $result = updateStaff(
        $pdo,
        intval($_POST['user_id'] ?? 0),
        trim($_POST['username'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['role'] ?? ''),
        trim($_POST['password'] ?? ''),
        trim($_POST['grade_level'] ?? '') ?: null,
        intval($_POST['section_id'] ?? 0),
        isset($_POST['is_active']) ? intval($_POST['is_active']) : null
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
                                    <option value="staff" <?php echo $editStaff['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="grade_level">Grade Level</label>
                            <div class="select-wrap">
                                <select id="grade_level" name="grade_level">
                                    <option value="">Select grade level...</option>
                                    <?php foreach ($gradeLevels as $gradeLevelOption): ?>
                                        <option value="<?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($editStaff['grade_level']) && $editStaff['grade_level'] === $gradeLevelOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="section_id">Section</label>
                            <div class="select-wrap">
                                <select id="section_id" name="section_id">
                                    <option value="">Select section...</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo intval($section['section_id']); ?>" data-grade-level="<?php echo htmlspecialchars($section['grade_level'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($editStaff['section_id']) && intval($editStaff['section_id']) === intval($section['section_id'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim($section['school_year'] . ' · ' . $section['grade_level'] . ' · ' . $section['name']), ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="is_active">Account Status</label>
                            <div class="select-wrap">
                                <select id="is_active" name="is_active">
                                    <option value="1" <?php echo isset($editStaff['is_active']) && $editStaff['is_active'] ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo isset($editStaff['is_active']) && !$editStaff['is_active'] ? 'selected' : ''; ?>>Inactive</option>
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
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="update-staff-tbody">
                                <?php if (empty($staffList)): ?>
                                    <tr class="empty-row"><td colspan="5">No staff accounts found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($staffList as $staff): ?>
                                        <tr>
                                            <td class="td-primary"><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="badge badge-staff"><?php echo htmlspecialchars($staff['role'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?php echo isset($staff['is_active']) && $staff['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'; ?></td>
                                            <td class="td-actions">
                                                <a href="admin_update.php?edit_id=<?php echo htmlspecialchars($staff['user_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-edit edit-user" data-user-id="<?php echo htmlspecialchars($staff['user_id'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden Edit Form for AJAX Updates -->
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
                                <label>Grade Level</label>
                                <div class="select-wrap">
                                    <select id="js-grade_level">
                                        <option value="">Select grade level...</option>
                                        <?php foreach ($gradeLevels as $gradeLevelOption): ?>
                                            <option value="<?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Section</label>
                                <div class="select-wrap">
                                    <select id="js-section_id">
                                        <option value="">Select section...</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?php echo intval($section['section_id']); ?>" data-grade-level="<?php echo htmlspecialchars($section['grade_level'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(trim($section['school_year'] . ' · ' . $section['grade_level'] . ' · ' . $section['name']), ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <div class="select-wrap">
                                    <select id="js-is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
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

<script src="../../api/client.js?v=3"></script>
<script>
    const alertContainer = document.getElementById('js-alert-container');

    function showUpdateMessage(message, isError = false) {
        alertContainer.innerHTML = `<div class="alert ${isError ? 'alert-error' : 'alert-success'}">${message}</div>`;
    }

    function attachEditListeners() {
        document.querySelectorAll('.edit-user').forEach(button => {
            button.onclick = null;
            button.onclick = event => {
                event.preventDefault();
                fillUpdateForm(button.dataset.userId);
            };
        });
    }

    async function fillUpdateForm(userId) {
        try {
            const response = await API.crud.read('users', parseInt(userId));
            const staff = response.data;
            if (!staff) return;

            const jsGradeSelect = document.getElementById('js-grade_level');
            const jsSectionSelect = document.getElementById('js-section_id');

            document.querySelector('.table-wrap').style.display = 'none';
            document.getElementById('user_id').value = staff.user_id;
            document.getElementById('js-username').value = staff.username;
            document.getElementById('js-email').value = staff.email;
            document.getElementById('js-role').value = staff.role;
            if (jsGradeSelect) {
                jsGradeSelect.value = staff.grade_level || '';
            }
            if (jsSectionSelect) {
                jsSectionSelect.value = staff.section_id || '';
            }
            const jsIsActive = document.getElementById('js-is_active');
            if (jsIsActive) {
                jsIsActive.value = staff.is_active ? '1' : '0';
            }

            document.getElementById('update-form-wrapper').style.display = 'block';
        } catch (error) {
            showUpdateMessage('Failed to load staff data.', true);
        }
    }

    const updateForm = document.getElementById('update-staff-form');
    if (updateForm) {
        const jsGradeSelect = document.getElementById('js-grade_level');
        const jsSectionSelect = document.getElementById('js-section_id');

        if (jsSectionSelect && jsGradeSelect) {
            jsSectionSelect.addEventListener('change', () => {
                const selected = jsSectionSelect.selectedOptions[0];
                if (selected && selected.dataset.gradeLevel) {
                    jsGradeSelect.value = selected.dataset.gradeLevel;
                }
            });
        }

        updateForm.addEventListener('submit', async event => {
            event.preventDefault();
            const data = {
                user_id: parseInt(document.getElementById('user_id').value),
                username: document.getElementById('js-username').value.trim(),
                email: document.getElementById('js-email').value.trim(),
                role: document.getElementById('js-role').value,
                password: document.getElementById('js-password').value.trim(),
                grade_level: jsGradeSelect ? jsGradeSelect.value || null : null,
                section_id: jsSectionSelect && jsSectionSelect.value ? Number(jsSectionSelect.value) : null,
                is_active: document.getElementById('js-is_active') ? parseInt(document.getElementById('js-is_active').value, 10) : 1
            };
            try {
                const response = await API.crud.update('users', data.user_id, data);
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

    const sectionSelect = document.getElementById('section_id');
    const gradeLevelSelect = document.getElementById('grade_level');
    if (sectionSelect && gradeLevelSelect) {
        sectionSelect.addEventListener('change', () => {
            const selected = sectionSelect.selectedOptions[0];
            if (selected && selected.dataset.gradeLevel) {
                gradeLevelSelect.value = selected.dataset.gradeLevel;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        attachEditListeners();
    });
</script>
</body>
</html>