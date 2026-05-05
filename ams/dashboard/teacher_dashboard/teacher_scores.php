<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scores · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">
    <?php renderTeacherSidebar('scores'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Scores</h1>
            <p>Enter and update scores for your students.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Select Activity</h2>
                <p>Choose an activity to review and update scores.</p>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Activity</label>
                    <select id="activitySelect" onchange="loadActivityScores()">
                        <option value="">-- Select Activity --</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="section" id="scoresSection" style="display: none;">
            <div class="section-header">
                <h2>Score Entry</h2>
                <p>Update student scores for the selected activity.</p>
            </div>
            <div class="section-body">
                <p><strong>Max Score:</strong> <span id="maxScore">-</span></p>

                <form id="scoresForm">
                    <input type="hidden" id="activityId" name="activity_id">
                    <input type="hidden" id="maxScoreInput" name="max_score">

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Current / Max</th>
                                    <th>New Score</th>
                                </tr>
                            </thead>
                            <tbody id="scoresTable">
                                <tr><td colspan="3" class="empty-row">Loading students...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Scores</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
let currentActivity = null;
let maxScore = 0;

async function loadActivities() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('activitySelect');
            select.innerHTML = '<option value="">-- Select Activity --</option>';

            if (response.data.length > 0) {
                const firstClass = response.data[0];
                const activitiesResponse = await API.activities.listByClass(firstClass.class_id);

                if (activitiesResponse.success) {
                    activitiesResponse.data.forEach(activity => {
                        const option = document.createElement('option');
                        option.value = activity.activity_id;
                        option.textContent = activity.title;
                        option.dataset.maxScore = activity.max_score;
                        select.appendChild(option);
                    });
                }
            }
        }
    } catch (error) {
        console.error('Failed to load activities:', error);
    }
}

async function loadActivityScores() {
    const activityId = document.getElementById('activitySelect').value;
    if (!activityId) {
        document.getElementById('scoresSection').style.display = 'none';
        return;
    }

    currentActivity = activityId;
    const select = document.getElementById('activitySelect');
    const option = select.querySelector(`option[value="${activityId}"]`);
    maxScore = parseInt(option.dataset.maxScore) || 0;

    document.getElementById('activityId').value = activityId;
    document.getElementById('maxScoreInput').value = maxScore;
    document.getElementById('maxScore').textContent = maxScore;

    try {
        const scoresResponse = await API.activities.getScores(activityId);
        const studentsResponse = await API.teacher.students();

        if (scoresResponse.success && studentsResponse.success) {
            renderScoresTable(studentsResponse.data, scoresResponse.data);
            document.getElementById('scoresSection').style.display = 'block';
        }
    } catch (error) {
        console.error('Failed to load scores:', error);
        document.getElementById('scoresTable').innerHTML = '<tr><td colspan="3" class="empty-row">Failed to load scores</td></tr>';
    }
}

function renderScoresTable(students, scores) {
    const tbody = document.getElementById('scoresTable');
    const scoresMap = {};
    scores.forEach(score => {
        scoresMap[score.class_student_id] = score.score;
    });

    tbody.innerHTML = students.map(student => {
        const currentScore = scoresMap[student.class_student_id] || 0;
        return `
            <tr>
                <td>${escapeHtml(student.first_name + ' ' + student.last_name)}</td>
                <td>${currentScore} / ${maxScore}</td>
                <td>
                    <input
                        type="number"
                        name="score[${student.class_student_id}]"
                        value="${currentScore || ''}"
                        max="${maxScore}"
                        min="0"
                    >
                </td>
            </tr>
        `;
    }).join('');
}

document.getElementById('scoresForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        activity_id: formData.get('activity_id'),
        scores: {}
    };

    for (let [key, value] of formData.entries()) {
        if (key.startsWith('score[')) {
            const classStudentId = key.match(/score\[(\d+)\]/)[1];
            if (value && parseFloat(value) <= maxScore) {
                data.scores[classStudentId] = parseFloat(value);
            }
        }
    }

    try {
        await API.activities.saveScore(data);
        alert('Scores saved successfully!');
        loadActivityScores();
    } catch (error) {
        alert('Failed to save scores: ' + error.message);
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.addEventListener('DOMContentLoaded', loadActivities);
</script>

</body>
</html>
