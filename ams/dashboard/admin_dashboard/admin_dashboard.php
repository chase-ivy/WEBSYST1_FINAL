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
    <link href="crud.css" rel="stylesheet">
    <!-- <style>
    /* ── DESIGN TOKENS ─────────────────────────────────────── */
    :root {
        --brand:          #4e0303;
        --brand-dark:     #ec3f3f;
        --brand-light:    #fdf2f2;
        --border:         #d1d5db;
        --border-strong:  #b0b7c3;
        --text:           #0f0f0f;
        --text-secondary: #374151;
        --muted:          #6b7280;
        --surface:        #ffffff;
        --canvas:         #f5f7fa;
        --overlay:        #f0f2f5;
        --shadow-sm:      0 2px 8px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md:      0 4px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04);
        --radius-sm:      6px;
        --radius-md:      10px;
        --radius-lg:      14px;
        --radius-xl:      20px;
        --radius-full:    9999px;
        --transition:     180ms ease;
        --sidebar-w:      220px;
        --topbar-h:       56px;
    }

    /* ── RESET ──────────────────────────────────────────────── */
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--canvas);
        color: var(--text);
        min-height: 100vh;
        font-size: 14px;
        line-height: 1.5;
    }

    /* ── TOPBAR ─────────────────────────────────────────────── */
    .topbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: var(--topbar-h);
        background: var(--brand);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 28px;
        z-index: 200;
        box-shadow: 0 2px 8px rgba(78,3,3,.3);
    }
    .topbar-brand {
        font-family: 'Syne', sans-serif;
        font-size: 17px;
        font-weight: 800;
        color: #fff;
        letter-spacing: .5px;
    }
    .topbar-brand span { color: var(--brand-dark); }
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .topbar-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: rgba(255,255,255,.5);
    }

    /* ── LAYOUT SHELL ───────────────────────────────────────── */
    .shell {
        display: flex;
        margin-top: var(--topbar-h);
        min-height: calc(100vh - var(--topbar-h));
    }

    /* ── SIDEBAR — targets renderAdminSidebar() output ─────── */
    /*
       renderAdminSidebar() outputs:
         <aside class="sidebar">
           <div class="sidebar-brand"><h3>…</h3><p>…</p></div>
           <a class="" href="…">Label</a>        ← inactive
           <a class="active" href="…">Label</a>  ← active
         </aside>
    */
    .sidebar {
        width: var(--sidebar-w);
        background: var(--surface);
        border-right: 1px solid var(--border);
        position: fixed;
        top: var(--topbar-h);
        left: 0;
        bottom: 0;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        z-index: 100;
        padding: 0 0 16px;
    }

    /* brand block inside sidebar */
    .sidebar .sidebar-brand {
        padding: 20px 18px 16px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 8px;
    }
    .sidebar .sidebar-brand h3 {
        font-family: 'Syne', sans-serif;
        font-size: 13px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: .3px;
    }
    .sidebar .sidebar-brand p {
        font-size: 11px;
        color: var(--muted);
        margin-top: 2px;
    }

    /* nav links */
    .sidebar a {
        display: flex;
        align-items: center;
        margin: 1px 10px;
        padding: 9px 12px;
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: background var(--transition), color var(--transition);
    }
    .sidebar a:hover {
        background: var(--overlay);
        color: var(--text);
    }
    .sidebar a.active {
        background: var(--brand-light);
        color: var(--brand);
        font-weight: 600;
    }

    /* push logout to bottom */
    .sidebar a[href*="logout"] {
        margin-top: auto;
        color: var(--muted);
    }
    .sidebar a[href*="logout"]:hover {
        background: #fff1f1;
        color: var(--brand);
    }

    /* ── MAIN ───────────────────────────────────────────────── */
    .main {
        margin-left: var(--sidebar-w);
        flex: 1;
        padding: 32px 36px;
        min-width: 0;
    }

    /* ── PAGE HEADER ────────────────────────────────────────── */
    .page-header {
        margin-bottom: 28px;
    }
    .page-header h1 {
        font-family: 'Syne', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 3px;
    }
    .page-header p { font-size: 13px; color: var(--muted); }

    /* ── STAT CARDS ─────────────────────────────────────────── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 20px 22px;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: box-shadow var(--transition), transform var(--transition);
    }
    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .stat-icon {
        width: 38px; height: 38px;
        border-radius: var(--radius-md);
        background: var(--brand-light);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stat-icon svg {
        width: 18px; height: 18px;
        stroke: var(--brand); fill: none;
        stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
    }
    .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 30px;
        font-weight: 800;
        color: var(--text);
        line-height: 1;
    }
    .stat-label { font-size: 12px; color: var(--muted); font-weight: 500; }

    /* ── ACTION CARDS ───────────────────────────────────────── */
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .action-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 22px;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: box-shadow var(--transition), transform var(--transition), border-color var(--transition);
    }
    .action-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
        border-color: var(--border-strong);
    }
    .action-card-icon {
        width: 40px; height: 40px;
        border-radius: var(--radius-md);
        background: var(--brand-light);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .action-card-icon svg {
        width: 20px; height: 20px;
        stroke: var(--brand); fill: none;
        stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
    }
    .action-card h3 {
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
    }
    .action-card p {
        font-size: 12px;
        color: var(--muted);
        line-height: 1.55;
        flex: 1;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 14px;
        border-radius: var(--radius-sm);
        background: var(--brand);
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        align-self: flex-start;
        margin-top: 4px;
        transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
    }
    .btn-action:hover {
        background: var(--brand-dark);
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(78,3,3,.22);
    }
    .btn-action svg { width: 12px; height: 12px; }

    /* ── TABLE SECTION ──────────────────────────────────────── */
    .section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .section-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
    }
    .section-header h2 {
        font-family: 'Syne', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
    }
    .section-header p { font-size: 12px; color: var(--muted); margin-top: 2px; }

    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th {
        padding: 11px 24px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        color: var(--muted);
        background: var(--overlay);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background var(--transition);
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--overlay); }
    tbody td {
        padding: 13px 24px;
        font-size: 13px;
        color: var(--text-secondary);
        vertical-align: middle;
    }
    .td-primary { font-weight: 600; color: var(--text); }

    /* role badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .2px;
        text-transform: capitalize;
    }
    .badge-admin   { background: var(--brand-light); color: var(--brand);   border: 1px solid rgba(78,3,3,.2); }
    .badge-teacher { background: #eff6ff;             color: #1d4ed8;        border: 1px solid #bfdbfe; }
    .badge-staff   { background: #f0fdf4;             color: #15803d;        border: 1px solid #bbf7d0; }
    .badge-default { background: var(--overlay);      color: var(--muted);   border: 1px solid var(--border); }

    /* alert */
    .alert-error {
        margin: 14px 24px;
        padding: 10px 14px;
        border-radius: var(--radius-md);
        background: var(--brand-light);
        border: 1px solid rgba(78,3,3,.2);
        color: var(--brand);
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .alert-error svg { width: 15px; height: 15px; flex-shrink: 0; }

    .empty-row td {
        text-align: center;
        padding: 40px 24px;
        color: var(--muted);
        font-size: 13px;
    }

    /* ── RESPONSIVE ─────────────────────────────────────────── */
    @media (max-width: 860px) {
        .sidebar { display: none; }
        .main { margin-left: 0; padding: 20px 16px; }
    }
    </style> -->
</head>
<body>

<!-- ── TOPBAR ──────────────────────────────────────────────── -->
<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMES</span></div>
    <span class="topbar-label">Admin Panel</span>
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
        </div>

        <!-- Action Cards -->
        <div class="action-grid">
            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <h3>Create Staff</h3>
                <p>Add new staff or student accounts to the system.</p>
                <a class="btn-action" href="admin_create.php">
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
                <a class="btn-action" href="admin_update.php">
                    Go to Update
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <h3>Delete Staff</h3>
                <p>Remove accounts that should no longer have access.</p>
                <a class="btn-action" href="admin_delete.php">
                    Go to Delete
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

<script src="../api/client.js"></script>
<script>
    function getRoleBadgeClass(role) {
        const map = { admin: 'badge-admin', teacher: 'badge-teacher', staff: 'badge-staff' };
        return map[(role || '').toLowerCase()] || 'badge-default';
    }

    async function loadAdminStaff() {
        try {
            const response = await API.users.list();
            const rows     = response.data || [];
            const tbody    = document.getElementById('staff-tbody');
            const count    = document.getElementById('staff-count');

            count.textContent = rows.length;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr class="empty-row"><td colspan="4">No staff accounts found.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map(staff => {
                const role       = staff.role || 'Unassigned';
                const badgeClass = getRoleBadgeClass(role);
                return `
                    <tr>
                        <td class="td-primary">${staff.username}</td>
                        <td>${staff.email}</td>
                        <td><span class="badge ${badgeClass}">${role}</span></td>
                        <td>${staff.created_at}</td>
                    </tr>
                `;
            }).join('');

        } catch (error) {
            const container = document.getElementById('staff-error');
            const msg       = document.getElementById('staff-error-msg');
            if (container && msg) {
                msg.textContent         = error.message || 'Unable to load staff list.';
                container.style.display = 'flex';
            }
            console.error('Unable to load staff list', error);
        }
    }

    document.addEventListener('DOMContentLoaded', loadAdminStaff);
</script>

</body>
</html>