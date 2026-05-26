<?php
require_once __DIR__ . '/../../login/auth.php';
require_role(['student']);
require_once __DIR__ . '/student_nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activities · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="../mobile-nav.css">
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
    <div class="topbar-brand">Gibraltar <span>AMES</span></div>
    <span class="topbar-label">Student Portal</span>
</header>

<div class="shell">
    <?php renderStudentSidebar('activities'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Activities</h1>
            <p>View your activity scores and participation.</p>
        </div>

        <div id="error" class="alert alert-error" style="display:none;"></div>

        <section class="section">
            <div class="section-header">
                <h2>Activity Scores</h2>
                <p>Your performance in class activities and assignments.</p>
            </div>
            <div class="section-body">
                <div id="activities-content">
                    <div class="empty-row">Loading activities...</div>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
function formatDate(value) {
    if (!value) return '-';
    const date = new Date(value);
    return date.toLocaleDateString();
}

async function loadActivities() {
    const errorBox = document.getElementById('error');
    const content = document.getElementById('activities-content');

    try {
        const response = await API.studentDashboard.get();
        const activities = response.data.activities || [];

        if (activities.length === 0) {
            content.innerHTML = '<div class="empty-row">No activities found.</div>';
            return;
        }

        content.innerHTML = `
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Activity</th>
                            <th>Date</th>
                            <th>Score</th>
                            <th>Max Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${activities.map(a => {
                            const score = Number(a.score || 0);
                            const maxScore = Number(a.max_score || 0);
                            const status = maxScore > 0 && score >= maxScore * 0.75 ? 'Good' : 'Needs Improvement';
                            return `
                                <tr>
                                    <td class="td-primary">${escapeHtml(a.subject_name)}</td>
                                    <td>${escapeHtml(a.activity_name)}</td>
                                    <td>${formatDate(a.activity_date)}</td>
                                    <td>${score}</td>
                                    <td>${maxScore}</td>
                                    <td>${status}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        console.error(error);
        errorBox.style.display = 'block';
        errorBox.textContent = 'Unable to load activities. Please refresh the page.';
        content.innerHTML = '<div class="empty-row">Unable to load activities at this time.</div>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', loadActivities);
</script>
<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>

</body>
</html>
