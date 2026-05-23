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
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $old = $_POST;
        $result = createStaff(
            $pdo,
            trim($_POST['username'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['password'] ?? ''),
            'staff'
        );
        if ($result['success']) {
            $success = $result['message'];
            $old = [];
        } else {
            $errors = $result['errors'];
        }
    } elseif ($action === 'update') {
        $result = updateStaff(
            $pdo,
            intval($_POST['user_id'] ?? 0),
            trim($_POST['username'] ?? ''),
            trim($_POST['email'] ?? ''),
            'staff',
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
    } elseif ($action === 'delete') {
        $result = deleteStaff($pdo, intval($_POST['user_id'] ?? 0));
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $errors = $result['errors'];
        }
    } elseif ($action === 'reset_password') {
        $userId = intval($_POST['user_id'] ?? 0);
        $password = trim($_POST['new_password'] ?? '');

        if ($userId <= 0) {
            $errors[] = 'Invalid user ID.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
            $stmt->execute([$hash, $userId]);
            $success = 'Password updated successfully.';
        }
    } elseif ($action === 'toggle_active') {
        $userId = intval($_POST['user_id'] ?? 0);
        $newState = intval($_POST['is_active'] ?? 0);

        if ($userId <= 0) {
            $errors[] = 'Invalid user ID.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET is_active = ? WHERE user_id = ?');
            $stmt->execute([$newState ? 1 : 0, $userId]);
            $success = 'Account status updated.';
        }
    }
}

$roleFilter = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');
$where = ["role != 'admin'"];
$params = [];

if ($roleFilter !== '' && in_array($roleFilter, ['staff', 'student'], true)) {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
}
if ($search !== '') {
    $where[] = '(username LIKE ? OR email LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql = 'SELECT user_id, username, email, role, is_active, created_at FROM users WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$editUserId = intval($_GET['edit_id'] ?? 0);
$editUser = null;
if ($editUserId > 0) {
    $editUser = getStaffById($pdo, $editUserId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users | Admin Dashboard</title>
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
        .filter-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 16px;
        }
        .filter-bar .form-group {
            margin-bottom: 0;
        }
        .filter-bar input[type="text"] {
            min-width: 200px;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open {
            display: flex;
        }
        .modal-box {
            background: var(--surface, #1e1e1e);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 28px 32px;
            width: 100%;
            max-width: 420px;
        }
        .modal-box h3 {
            margin: 0 0 6px;
            font-size: 16px;
        }
        .modal-box p {
            margin: 0 0 18px;
            opacity: 0.7;
            font-size: 13px;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
</header>
<div class="shell">
    <?php renderAdminSidebar('users'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Manage Users</h1>
            <p>One place for staff account creation, editing, activation, password resets, and user overview.</p>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <section class="section">
            <div class="section-header">
                <h2>Create Staff Account</h2>
                <p>Use this form to add new staff users. Student accounts are created through enrollment workflows.</p>
            </div>
            <div class="section-body">
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="create_username">Username</label>
                        <input id="create_username" type="text" name="username" value="<?php echo htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="create_email">Email Address</label>
                        <input id="create_email" type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="create_password">Password</label>
                        <input id="create_password" type="password" name="password" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="form-actions full">
                        <button type="submit" class="btn-primary">Register Staff</button>
                        <button type="reset" class="btn-secondary">Clear</button>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($editUser): ?>
        <section class="section">
            <div class="section-header">
                <h2>Edit Staff Member</h2>
                <p>Update username, email, grade level, section assignment, active status, or reset password.</p>
            </div>
            <div class="section-body">
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($editUser['user_id'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input id="edit_username" type="text" name="username" value="<?php echo htmlspecialchars($editUser['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email Address</label>
                        <input id="edit_email" type="email" name="email" value="<?php echo htmlspecialchars($editUser['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_grade_level">Grade Level</label>
                        <div class="select-wrap">
                            <select id="edit_grade_level" name="grade_level">
                                <option value="">Select grade level...</option>
                                <?php foreach ($gradeLevels as $gradeLevelOption): ?>
                                    <option value="<?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($editUser['grade_level']) && $editUser['grade_level'] === $gradeLevelOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($gradeLevelOption, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_section_id">Section</label>
                        <div class="select-wrap">
                            <select id="edit_section_id" name="section_id">
                                <option value="">Select section...</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?php echo intval($section['section_id']); ?>" <?php echo (isset($editUser['section_id']) && intval($editUser['section_id']) === intval($section['section_id'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim($section['school_year'] . ' · ' . $section['grade_level'] . ' · ' . $section['name']), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_is_active">Account Status</label>
                        <div class="select-wrap">
                            <select id="edit_is_active" name="is_active">
                                <option value="1" <?php echo isset($editUser['is_active']) && $editUser['is_active'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo isset($editUser['is_active']) && !$editUser['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">New Password <small>(leave blank to keep current)</small></label>
                        <input id="edit_password" type="password" name="password">
                    </div>
                    <div class="form-actions full">
                        <button type="submit" class="btn-primary">Save Changes</button>
                        <a href="admin_users.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <section class="section">
            <div class="section-header">
                <h2>Account Directory</h2>
                <p>Browse staff and student accounts with quick actions for each record.</p>
            </div>
            <div class="section-body">
                <form method="get" class="filter-bar">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input id="search" type="text" name="search" placeholder="Username or email" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <div class="select-wrap">
                            <select id="role" name="role">
                                <option value="">All roles</option>
                                <option value="staff" <?php echo $roleFilter === 'staff' ? 'selected' : ''; ?>>Staff</option>
                                <option value="student" <?php echo $roleFilter === 'student' ? 'selected' : ''; ?>>Student</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Filter</button>
                    <?php if ($search !== '' || $roleFilter !== ''): ?>
                        <a href="admin_users.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>

                <div id="page-message" class="alert" style="display:none;margin-bottom:12px;"></div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr class="empty-row"><td colspan="6">No accounts found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr id="row-<?php echo $user['user_id']; ?>">
                                        <td class="td-primary"><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge badge-<?php echo $user['role'] === 'staff' ? 'staff' : 'student'; ?>"><?php echo htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <span id="status-badge-<?php echo $user['user_id']; ?>" class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('M j, Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="td-actions">
                                            <a href="admin_users.php?reset_id=<?php echo $user['user_id']; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?><?php echo $roleFilter !== '' ? '&role=' . urlencode($roleFilter) : ''; ?>" class="btn-edit">Reset PW</a>
                                            <?php if ($user['role'] === 'staff'): ?>
                                                <a href="admin_users.php?edit_id=<?php echo $user['user_id']; ?>" class="btn-secondary">Edit</a>
                                                <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to delete this staff member? This action is permanent.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <button type="submit" class="btn-danger">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                            <button type="button" class="btn-secondary toggle-btn" data-user-id="<?php echo $user['user_id']; ?>" data-is-active="<?php echo $user['is_active'] ? '1' : '0'; ?>">
                                                <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top:10px;opacity:0.6;font-size:13px;">
                    <?php echo count($users); ?> account<?php echo count($users) !== 1 ? 's' : ''; ?> found. All admin accounts are hidden from this list.
                </p>
            </div>
        </section>
    </main>
</div>

<div class="modal-overlay" id="toggleModal">
    <div class="modal-box">
        <h3 id="modalTitle">Confirm status change</h3>
        <p id="modalDesc"></p>
        <div class="modal-actions">
            <button type="button" class="btn-primary" id="modalConfirm">Confirm</button>
            <button type="button" class="btn-secondary" id="modalCancel">Cancel</button>
        </div>
    </div>
</div>

<script>
const pageMessage = document.getElementById('page-message');
const modal = document.getElementById('toggleModal');
const modalTitle = document.getElementById('modalTitle');
const modalDesc = document.getElementById('modalDesc');
const modalConfirm = document.getElementById('modalConfirm');
const modalCancel = document.getElementById('modalCancel');
let pendingToggle = null;

function showMsg(text, isError = false) {
    pageMessage.className = 'alert ' + (isError ? 'alert-error' : 'alert-success');
    pageMessage.textContent = text;
    pageMessage.style.display = 'block';
    setTimeout(() => { pageMessage.style.display = 'none'; }, 4000);
}

function attachToggleButtons() {
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const userId = btn.dataset.userId;
            const isActive = btn.dataset.isActive === '1';
            const action = isActive ? 'deactivate' : 'activate';

            pendingToggle = { userId, newState: isActive ? 0 : 1, btn };
            modalTitle.textContent = (isActive ? 'Deactivate' : 'Activate') + ' account?';
            modalDesc.textContent = 'This will ' + action + ' the account. The user will ' + (isActive ? 'no longer be able to log in.' : 'regain access.');
            modal.classList.add('open');
        });
    });
}

modalCancel.addEventListener('click', () => {
    modal.classList.remove('open');
    pendingToggle = null;
});

modal.addEventListener('click', e => {
    if (e.target === modal) {
        modal.classList.remove('open');
        pendingToggle = null;
    }
});

modalConfirm.addEventListener('click', async () => {
    if (!pendingToggle) return;
    modal.classList.remove('open');

    const { userId, newState, btn } = pendingToggle;
    pendingToggle = null;

    try {
        const res = await fetch('admin_users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'toggle_active', user_id: userId, is_active: newState })
        });
        if (res.ok) {
            const badge = document.getElementById('status-badge-' + userId);
            if (newState === 1) {
                badge.className = 'badge badge-success';
                badge.textContent = 'Active';
                btn.textContent = 'Deactivate';
                btn.dataset.isActive = '1';
            } else {
                badge.className = 'badge badge-danger';
                badge.textContent = 'Inactive';
                btn.textContent = 'Activate';
                btn.dataset.isActive = '0';
            }
            showMsg('Account status updated.');
        } else {
            showMsg('Failed to update status.', true);
        }
    } catch (err) {
        showMsg('Request failed.', true);
    }
});

document.addEventListener('DOMContentLoaded', attachToggleButtons);
</script>
</body>
</html>
