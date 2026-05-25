<?php
require_once __DIR__ . '/admin_config.php';
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();

$staffList = getStaffList($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard · Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
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

<!-- ── TOPBAR ──────────────────────────────────────────────── -->
<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMES</span></div>
    <div class="topbar-right">
        <div class="topbar-welcome" aria-live="polite">
            <div class="welcome-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path>
                    <path d="M6 20v-1a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v1"></path>
                </svg>
            </div>
            <div class="welcome-copy">
                <div class="welcome-title">Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="welcome-subtitle">Academic Management system is ready!</div>
            </div>
        </div>
        <span class="topbar-label">Admin Panel</span>
    </div>
</header>

<!-- ── LAYOUT SHELL ────────────────────────────────────────── -->
<div class="shell">

    <!-- SIDEBAR — rendered by renderAdminSidebar() -->
    <?php renderAdminSidebar('dashboard'); ?>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- Page Header -->
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back. Here's an overview of your system.</p>
        </div>

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="stat-value" id="staff-count"><?php echo count($staffList); ?></div>
                    <div class="stat-label">Active Staff Accounts</div>
                </div>
            </div>
            <?php 
                $studentStmt = $pdo->query("SELECT COUNT(*) as total FROM students");
                $studentCount = $studentStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            ?>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M18 21H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2z"/><path d="M10 13.5h4"/><path d="M9 9h0"/><path d="M15 9h0"/></svg>
                </div>
                <div>
                    <div class="stat-value" id="student-count"><?php echo $studentCount; ?></div>
                    <div class="stat-label">Total Student Accounts</div>
                </div>
            </div>
            <?php 
                $pendingStmt = $pdo->query("SELECT COUNT(*) as total FROM enrollments WHERE enrollment_status = 'pending'");
                $pendingCount = $pendingStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            ?>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M3 7h18M5 7v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7"/></svg>
                </div>
                <div>
                    <div class="stat-value" id="pending-count"><?php echo $pendingCount; ?></div>
                    <div class="stat-label">Pending Enrollments</div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="action-grid">
            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <h3>Create Staff</h3>
                <p>Add new staff accounts to the system.</p>
                <a class="btn-action" href="admin_users.php">
                    Go to Create
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <h3>Update Staff</h3>
                <p>Review and edit existing staff records and permissions.</p>
                <a class="btn-action" href="admin_users.php">
                    Go to Update
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <h3>Delete Staff</h3>
                <p>Remove staff accounts that should no longer have access.</p>
                <a class="btn-action" href="admin_users.php">
                    Go to Delete
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>

        <div class="action-grid">
            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M3 7h18M5 7v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7"/></svg>
                </div>
                <h3>Enrollment Queue</h3>
                <p>View pending enrollment submissions and take action.</p>
                <a class="btn-action" href="admin_enrollment_queue.php">
                    Open Queue
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <h3>Lookup Tables</h3>
                <p>Edit lookup data used by enrollment and student records.</p>
                <a class="btn-action" href="admin_lookups.php">
                    Manage Lookups
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
                <h3>Subject Master List</h3>
                <p>Maintain canonical subjects available for class assignment.</p>
                <a class="btn-action" href="admin_subjects.php">
                    Manage Subjects
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>

        <!-- Staff Table -->
        <div class="section">
            <div class="section-header">
                <h2>Recent Staff Accounts</h2>
                <p>All registered staff and their roles</p>
            </div>

            <div id="staff-error" class="alert-error" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="staff-error-msg"></span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody id="staff-tbody">
                        <?php if (empty($staffList)): ?>
                            <tr class="empty-row">
                                <td colspan="4">No staff accounts found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staffList as $staff): ?>
                                <?php
                                    $role = htmlspecialchars($staff['role'] ?? 'unassigned', ENT_QUOTES, 'UTF-8');
                                    $badgeClass = match(strtolower($role)) {
                                        'admin'   => 'badge-admin',
                                        'teacher' => 'badge-teacher',
                                        'staff'   => 'badge-staff',
                                        default   => 'badge-default',
                                    };
                                ?>
                                <tr>
                                    <td class="td-primary"><?php echo htmlspecialchars($staff['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $role; ?></span></td>
                                    <td><?php echo htmlspecialchars($staff['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof adminDashboardInit === 'function') {
            adminDashboardInit();
        }

        const alert = document.querySelector('.topbar-welcome');
        if (!alert) {
            return;
        }

        requestAnimationFrame(() => alert.classList.add('show'));
        alert.addEventListener('click', () => alert.classList.add('dismissed'));

        window.setTimeout(() => {
            alert.classList.add('dismissed');
            window.setTimeout(() => {
                alert.style.display = 'none';
            }, 260);
        }, 3000);
    });
</script>

</body>
</html>