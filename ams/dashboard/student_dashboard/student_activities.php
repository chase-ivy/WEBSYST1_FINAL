<?php
require_once __DIR__ . '/../../login/auth.php';
require_role(['student']);
require_once __DIR__ . '/student_nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activities</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
</head>

<body>
<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../../style/logo.png" alt="Logo" class="logo">
</header>

<div class="container">
    <?php renderStudentSidebar('activities'); ?>

    <div class="content">
        <div id="error" class="error-message" style="display:none;"></div>
        <div class="card" id="activities-card">
            <h3>Activities</h3>
            <p>Loading activities...</p>
        </div>
    </div>
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
        const card = document.getElementById('activities-card');

        try {
            const response = await API.studentDashboard.get();
            const activities = response.data.activities || [];

            if (activities.length === 0) {
                card.innerHTML = '<h3>Activities</h3><p>No activities found.</p>';
                return;
            }

            card.innerHTML = `
                <h3>Activities</h3>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Max Score</th>
                        <th>Status</th>
                    </tr>
                    ${activities.map(a => {
                        const score = Number(a.score || 0);
                        const maxScore = Number(a.max_score || 0);
                        const status = maxScore > 0 && score >= maxScore * 0.75 ? 'Good' : 'Needs Improvement';
                        return `
                            <tr>
                                <td>${a.subject_name}</td>
                                <td>${a.activity_name}</td>
                                <td>${formatDate(a.activity_date)}</td>
                                <td>${score}</td>
                                <td>${maxScore}</td>
                                <td>${status}</td>
                            </tr>
                        `;
                    }).join('')}
                </table>
            `;
        } catch (error) {
            console.error(error);
            errorBox.style.display = 'block';
            errorBox.textContent = 'Unable to load activities. Please refresh the page.';
            card.innerHTML = '<h3>Activities</h3><p>Unable to load activities at this time.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', loadActivities);
</script>
</body>
</html>
