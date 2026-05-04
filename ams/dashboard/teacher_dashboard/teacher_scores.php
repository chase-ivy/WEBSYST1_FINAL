<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../../style/style.css">
    <title>Scores</title>
    <style>
        .card { background: #fff; padding: 15px; margin-bottom: 15px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
        input { padding: 6px; width: 80px; }
        button { padding: 6px 10px; cursor: pointer; }
        .loading { color: #999; font-style: italic; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="container">

<?php renderTeacherSidebar('scores'); ?>

<div class="content">

<div class="card">
    <h3>Select Activity</h3>
    <select id="activitySelect" onchange="loadActivityScores()">
        <option value="">-- Select Activity --</option>
    </select>
</div>

<div id="scoresContainer" style="display: none;">
<div class="card">
    <h3 id="activityTitle">Score Entry</h3>
    <p><strong>Max Score:</strong> <span id="maxScore">-</span></p>

    <form id="scoresForm">
        <input type="hidden" id="activityId" name="activity_id">
        <input type="hidden" id="maxScoreInput" name="max_score">

        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Current / Max</th>
                    <th>New Score</th>
                </tr>
            </thead>
            <tbody id="scoresTable">
                <tr><td colspan="3" class="loading">Loading students...</td></tr>
            </tbody>
        </table>

        <button type="submit" class="btn">Save Scores</button>
    </form>
</div>
</div>

</div>
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

            // For now, we'll load activities for the first class
            // In a full implementation, you'd select class first, then activities
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
        document.getElementById('scoresContainer').style.display = 'none';
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
        // Load students and their current scores
        const scoresResponse = await API.activities.getScores(activityId);
        const studentsResponse = await API.teacher.students();

        if (scoresResponse.success && studentsResponse.success) {
            renderScoresTable(studentsResponse.data, scoresResponse.data);
            document.getElementById('scoresContainer').style.display = 'block';
        }
    } catch (error) {
        console.error('Failed to load scores:', error);
        document.getElementById('scoresTable').innerHTML = '<tr><td colspan="3">Failed to load scores</td></tr>';
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
        loadActivityScores(); // Reload to show updated scores
    } catch (error) {
        alert('Failed to save scores: ' + error.message);
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load activities on page load
loadActivities();
</script>

</body>
</html>