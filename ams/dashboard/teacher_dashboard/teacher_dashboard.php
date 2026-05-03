<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$teacher_id]);
$staff = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../../style/style.css">
    <style>
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin: 16px 0; }
        .summary-card { background: #f5f5f5; padding: 16px; border-radius: 4px; text-align: center; }
        .summary-card h4 { margin: 0 0 8px 0; font-size: 14px; color: #666; }
        .summary-card p { margin: 0; font-size: 32px; font-weight: bold; color: #333; }
        .loading { color: #999; font-style: italic; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="container">
<?php renderTeacherSidebar('dashboard'); ?>

    <div class="content">

        <div class="card">
            <h3>Welcome, <?= htmlspecialchars($staff['username']) ?> 👋</h3>
            <p>Loading your dashboard information...</p>
        </div>

        <!-- SUMMARY -->
        <div class="card">
            <h3>Dashboard Summary</h3>
            <div class="summary-grid" id="summaryCards">
                <div class="loading">Loading statistics...</div>
            </div>
        </div>

        <!-- SUBJECTS -->
        <div class="card">
            <h3>Subjects Handled</h3>
            <ul id="subjectsList">
                <li class="loading">Loading subjects...</li>
            </ul>
        </div>

        <!-- CLASSES -->
        <div class="card">
            <h3>Your Classes</h3>
            <table id="classesTable">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>Students</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" class="loading">Loading classes...</td></tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="../../api/client.js"></script>
<script>
    async function loadDashboard() {
        try {
            // Load classes
            const classesResponse = await api.getClasses();
            if (classesResponse.success) {
                renderClasses(classesResponse.data);
                renderSubjects(classesResponse.data);
                renderSummary(classesResponse.data);
            }
        } catch (error) {
            console.error('Dashboard load error:', error);
            document.getElementById('summaryCards').innerHTML = '<div class="alert alert-error">Failed to load dashboard</div>';
        }
    }

    function renderClasses(classes) {
        const tbody = document.querySelector('#classesTable tbody');
        if (classes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4">No classes assigned</td></tr>';
            return;
        }

        tbody.innerHTML = classes.map(c => `
            <tr>
                <td>${escapeHtml(c.subject_name)}</td>
                <td>${c.grade_level}</td>
                <td>${c.section}</td>
                <td><a href="manage_students.php?class_id=${c.class_id}">Manage</a></td>
            </tr>
        `).join('');
    }

    function renderSubjects(classes) {
        const subjects = [...new Set(classes.map(c => c.subject_name))];
        const subjectsList = document.getElementById('subjectsList');
        
        if (subjects.length === 0) {
            subjectsList.innerHTML = '<li>No subjects assigned</li>';
            return;
        }

        subjectsList.innerHTML = subjects.map(s => `<li>${escapeHtml(s)}</li>`).join('');
    }

    function renderSummary(classes) {
        const totalClasses = classes.length;
        const totalStudents = classes.reduce((sum, c) => sum + (parseInt(c.students) || 0), 0);
        
        const summaryCards = document.getElementById('summaryCards');
        summaryCards.innerHTML = `
            <div class="summary-card">
                <h4>Classes</h4>
                <p>${totalClasses}</p>
            </div>
            <div class="summary-card">
                <h4>Students</h4>
                <p>${totalStudents}</p>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Load dashboard on page load
    document.addEventListener('DOMContentLoaded', loadDashboard);
</script>

</body>
</html>