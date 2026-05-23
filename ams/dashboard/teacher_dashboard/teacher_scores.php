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
        <div id="pageMessage" class="message" style="display:block; margin-top:12px;"></div>

        <section class="section">
            <div class="section-header">
                <h2>Select Class</h2>
                <p>Choose a class to view activities and scores.</p>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Class</label>
                    <select id="classSelect" onchange="onClassChange()">
                        <option value="">-- Select Class --</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Select Subject</h2>
                <p>Choose a subject to view its activities.</p>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Subject</label>
                    <select id="subjectSelect" onchange="onSubjectChange()">
                        <option value="">-- Select Subject --</option>
                    </select>
                </div>
            </div>
        </section>

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
function showMessage(type, message) {
    const container = document.getElementById('pageMessage');
    if (!container) return;
    container.className = 'message';
    if (type === 'success') container.classList.add('success');
    if (type === 'error') container.classList.add('error');
    container.textContent = message;
}

let currentActivity = null;
let maxScore = 0;
let currentClassId = null;
let currentClassSubjectId = null;
let classesMap = {};
let classSubjectsMap = {};

async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Select Class --</option>';
            classesMap = {};

            response.data.forEach(c => {
                classesMap[c.class_id] = c;
                const option = document.createElement('option');
                option.value = c.class_id;
                option.textContent = `${c.subject_name || 'Unassigned'} - Grade ${c.grade_level} ${c.section} (${c.school_year})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
    }
}

function onClassChange() {
    currentClassId = document.getElementById('classSelect').value;
    const subjectSelect = document.getElementById('subjectSelect');
    const activitySelect = document.getElementById('activitySelect');
    
    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
    activitySelect.innerHTML = '<option value="">-- Select Activity --</option>';
    document.getElementById('scoresSection').style.display = 'none';

    if (!currentClassId) {
        return;
    }

    loadSubjectsForClass(currentClassId);
}

async function loadSubjectsForClass(classId) {
    try {
        const response = await API.classes.getSubjects(classId);
        if (response.success) {
            const select = document.getElementById('subjectSelect');
            select.innerHTML = '<option value="">-- Select Subject --</option>';
            classSubjectsMap = {};

            response.data.forEach(cs => {
                classSubjectsMap[cs.class_subject_id] = { ...cs, class_id: currentClassId };
                const option = document.createElement('option');
                option.value = cs.class_subject_id;
                option.textContent = cs.subject_name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load subjects:', error);
    }
}

function onSubjectChange() {
    currentClassSubjectId = document.getElementById('subjectSelect').value;
    const activitySelect = document.getElementById('activitySelect');
    
    activitySelect.innerHTML = '<option value="">-- Select Activity --</option>';
    document.getElementById('scoresSection').style.display = 'none';

    if (!currentClassSubjectId) {
        return;
    }

    loadActivitiesForSubject(currentClassSubjectId);
}

async function loadActivitiesForSubject(classSubjectId) {
    try {
        const response = await API.activities.listByClassSubject(classSubjectId);
        if (response.success) {
            const select = document.getElementById('activitySelect');
            select.innerHTML = '<option value="">-- Select Activity --</option>';

            if (response.data.length === 0) {
                select.innerHTML += '<option value="" disabled>No activities</option>';
                return;
            }

            response.data.forEach(activity => {
                const option = document.createElement('option');
                option.value = activity.activity_id;
                option.textContent = activity.title;
                option.dataset.maxScore = activity.max_score;
                select.appendChild(option);
            });

            const urlParams = new URLSearchParams(window.location.search);
            const requestedActivityId = urlParams.get('activity_id');
            if (requestedActivityId) {
                select.value = requestedActivityId;
                if (select.value) {
                    loadActivityScores();
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
        const classId = classSubjectsMap[currentClassSubjectId].class_id;
        const studentsResponse = await API.classes.getClassStudents(classId);

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
                        step="1"
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
            const numeric = parseInt(value);
            if (value !== '' && !isNaN(numeric) && numeric >= 0 && numeric <= maxScore) {
                data.scores[classStudentId] = numeric;
            }
        }
    }

    try {
        await API.activities.saveScore(data);
        showMessage('success', 'Scores saved successfully!');
        loadActivityScores();
    } catch (error) {
        showMessage('error', 'Failed to save scores: ' + (error.message || 'Please try again'));
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.addEventListener('DOMContentLoaded', loadClasses);
</script>

</body>
</html>
