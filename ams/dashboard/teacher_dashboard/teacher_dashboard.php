<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);

$teacher_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$teacher_id]);
$staff = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Dashboard · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">
    <?php renderTeacherSidebar('dashboard'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($staff['username']) ?>. Here's your overview.</p>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="stat-value" id="student-count">0</div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </div>
                <div>
                    <div class="stat-value" id="class-count">0</div>
                    <div class="stat-label">My Classes</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div>
                    <div class="stat-value" id="subject-count">0</div>
                    <div class="stat-label">Subjects</div>
                </div>
            </div>
        </div>

        <div class="action-grid">
            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M3 7h18"/><path d="M7 11l5 5 5-5"/></svg>
                </div>
                <h3>View My Classes</h3>
                <p>Browse the classes assigned to you and review student enrollment details.</p>
                <a class="btn-action" href="teacher_classes.php">
                    Open Classes
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <h3>Enter Scores</h3>
                <p>Update student scores quickly and keep grades aligned with your current lessons.</p>
                <a class="btn-action" href="teacher_scores.php">
                    Enter Scores
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <div class="action-card">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
                </div>
                <h3>Take Attendance</h3>
                <p>Log student attendance for your sessions and keep records current.</p>
                <a class="btn-action" href="teacher_attendance.php">
                    Open Attendance
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>My Classes</h2>
                <p>Classes you are assigned to teach</p>
            </div>
            <div class="section-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>Students</th>
                                <th>School Year</th>
                            </tr>
                        </thead>
                        <tbody id="classes-tbody">
                            <tr class="empty-row"><td colspan="5">Loading classes...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>
<script>
    async function loadDashboard() {
        try {
            const response = await API.call('teacher', 'dashboard');
            if (response.success) {
                const data = response.data;
                document.getElementById('student-count').textContent = data.total_students || 0;
                document.getElementById('class-count').textContent = (data.classes || []).length;
                document.getElementById('subject-count').textContent = (data.subjects || []).length;
                loadClasses(data.classes || []);
            }
        } catch (error) {
            console.error('Failed to load dashboard:', error);
        }
    }

    function loadClasses(classes) {
        const tbody = document.getElementById('classes-tbody');
        if (classes.length === 0) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="5">No classes assigned.</td></tr>';
            return;
        }

        tbody.innerHTML = classes.map(cls => `
            <tr>
                <td class="td-primary">${cls.subject_name || 'N/A'}</td>
                <td>${cls.grade_level || 'N/A'}</td>
                <td>${cls.section || 'N/A'}</td>
                <td>${cls.student_count || 0}</td>
                <td>${cls.school_year || 'N/A'}</td>
            </tr>
        `).join('');
    }

    document.addEventListener('DOMContentLoaded', loadDashboard);
</script>

</body>
</html>
