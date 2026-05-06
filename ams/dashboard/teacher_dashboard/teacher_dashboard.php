<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';
require_once __DIR__ . '/../../config/config.php';

require_role(['staff']);

$teacher_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$teacher_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Teacher Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="teacher.css">
</head>

<body>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<!-- LAYOUT -->
<div class="shell">

<?php renderTeacherSidebar('dashboard'); ?>

<!-- MAIN -->
<main class="main">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($staff['username']) ?>. Here's your overview.</p>
    </div>

    <!-- STAT GRID (MATCH ADMIN STYLE) -->
    <div class="stat-grid">

        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <div class="stat-value" id="student-count">0</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
            <div>
                <div class="stat-value" id="class-count">0</div>
                <div class="stat-label">My Classes</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
            </div>
            <div>
                <div class="stat-value" id="subject-count">0</div>
                <div class="stat-label">Subjects</div>
            </div>
        </div>

    </div>

    <!-- ACTION GRID (ALIGNED) -->
    <div class="action-grid">

        <div class="action-card">
            <div class="action-card-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M3 7h18"/>
                    <path d="M7 11l5 5 5-5"/>
                </svg>
            </div>
            <h3>My Classes</h3>
            <p>View and manage your assigned classes.</p>
            <a class="btn-action" href="teacher_classes.php">Open</a>
        </div>

        <div class="action-card">
            <div class="action-card-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>
            </div>
            <h3>Scores</h3>
            <p>Update student grades and assessments.</p>
            <a class="btn-action" href="teacher_scores.php">Open</a>
        </div>

        <div class="action-card">
            <div class="action-card-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M4 7h16"/>
                    <path d="M4 12h16"/>
                    <path d="M4 17h16"/>
                </svg>
            </div>
            <h3>Attendance</h3>
            <p>Track student attendance records.</p>
            <a class="btn-action" href="teacher_attendance.php">Open</a>
        </div>

    </div>

    <!-- SECTION TABLE -->
    <section class="section">

        <div class="section-header">
            <h2>My Classes</h2>
            <p>Classes assigned to you</p>
        </div>

        <div id="dashboard-error" class="alert-error" style="display:none; margin-bottom:1rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span id="dashboard-error-msg"></span>
        </div>

        <div class="section-body">

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Students</th>
                            <th>School Year</th>
                        </tr>
                    </thead>

                    <tbody id="classes-tbody">
                        <tr class="empty-row">
                            <td colspan="5">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </section>

</main>
</div>

<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>

<script>
function showDashboardError(message) {
    const container = document.getElementById('dashboard-error');
    const msg = document.getElementById('dashboard-error-msg');
    if (container && msg) {
        msg.textContent = message;
        container.style.display = 'flex';
    }
}

async function loadDashboard() {
    try {
        const res = await API.teacher.dashboard();

        if (!res.success || !res.data) {
            throw new Error(res.error || 'Unable to load dashboard data');
        }

        const data = res.data;

        document.getElementById('student-count').textContent = data.total_students ?? 0;
        document.getElementById('class-count').textContent = data.class_count ?? (data.classes ? data.classes.length : 0);
        document.getElementById('subject-count').textContent = data.subject_count ?? (data.subjects ? data.subjects.length : 0);

        const tbody = document.getElementById('classes-tbody');

        if (!data.classes || data.classes.length === 0) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="5">No classes found</td></tr>`;
            return;
        }

        tbody.innerHTML = data.classes.map(c => `
            <tr>
                <td class="td-primary">${c.subject_name}</td>
                <td>${c.grade_level}</td>
                <td>${c.section}</td>
                <td>${c.student_count}</td>
                <td>${c.school_year}</td>
            </tr>
        `).join('');

    } catch (err) {
        showDashboardError(err.message || 'Unable to load dashboard');
        console.error('Teacher dashboard error:', err);
    }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
setInterval(loadDashboard, 30000);
</script>

</body>
</html>