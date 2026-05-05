<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Activities</title>
    <link rel="stylesheet" href="../../style/style.css">

    <style>
        .card { background:#fff; padding:15px; margin-bottom:15px; border-radius:6px; }
        select, input, button { padding:6px; margin:5px 0; width:100%; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border-bottom:1px solid #ddd; }
        button { cursor:pointer; }
        .loading { color: #999; font-style: italic; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="dashboard-layout">

<?php renderTeacherSidebar('activities'); ?>

<div class="content">

<!-- ================= CLASS SELECT ================= -->
<div class="card">
    <h3>My Classes</h3>

    <select id="classSelect" onchange="onClassChange()">
        <option value="">-- Select Class --</option>
    </select>
</div>

<!-- ================= STUDENTS ================= -->
<div class="card">
    <h3>Students in Class</h3>
    <div id="studentList">Select a class first</div>
</div>

<!-- ================= CREATE ACTIVITY ================= -->
<div class="card">
    <h3>Create Activity</h3>

    <input id="activity_title" placeholder="Activity Title" required>
    <input type="number" id="max_score" placeholder="Max Score" min="1" required>

    <button onclick="createActivity()">Create Activity</button>
</div>

<!-- ================= ACTIVITY LIST ================= -->
<div class="card">
    <h3>Activities</h3>
    <div id="activityList">Select a class to view activities</div>
</div>

</div>
</div>

<script src="../../api/client.js"></script>
<script>
let currentClassId = null;

/* ================= LOAD CLASSES ================= */
async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Select Class --</option>';

            response.data.forEach(c => {
                const option = document.createElement('option');
                option.value = c.class_id;
                option.textContent = `${c.subject_name} - Grade ${c.grade_level} ${c.section} (${c.school_year})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
    }
}

/* ================= ON CLASS CHANGE ================= */
function onClassChange() {
    currentClassId = document.getElementById('classSelect').value;
    if (currentClassId) {
        loadStudents();
        loadActivities();
    } else {
        document.getElementById('studentList').innerHTML = 'Select a class first';
        document.getElementById('activityList').innerHTML = 'Select a class to view activities';
    }
}

/* ================= LOAD STUDENTS ================= */
async function loadStudents() {
    if (!currentClassId) return;

    document.getElementById('studentList').innerHTML = '<div class="loading">Loading students...</div>';

    try {
        const response = await API.classes.getClassStudents(currentClassId);
        if (response.success) {
            let html = '<table>';
            html += '<thead><tr><th>Name</th><th>LRN</th></tr></thead><tbody>';

            if (response.data.length === 0) {
                html += '<tr><td colspan="2">No students in this class</td></tr>';
            } else {
                response.data.forEach(s => {
                    html += `
                        <tr>
                            <td>${escapeHtml(s.first_name + ' ' + s.last_name)}</td>
                            <td>${escapeHtml(s.lrn || 'N/A')}</td>
                        </tr>
                    `;
                });
            }

            html += '</tbody></table>';
            document.getElementById('studentList').innerHTML = html;
        }
    } catch (error) {
        console.error('Failed to load students:', error);
        document.getElementById('studentList').innerHTML = 'Failed to load students';
    }
}

/* ================= LOAD ACTIVITIES ================= */
async function loadActivities() {
    if (!currentClassId) return;

    document.getElementById('activityList').innerHTML = '<div class="loading">Loading activities...</div>';

    try {
        const response = await API.activities.listByClass(currentClassId);
        if (response.success) {
            let html = '<table>';
            html += '<thead><tr><th>Title</th><th>Max Score</th><th>Actions</th></tr></thead><tbody>';

            if (response.data.length === 0) {
                html += '<tr><td colspan="3">No activities created yet</td></tr>';
            } else {
                response.data.forEach(activity => {
                    html += `
                        <tr>
                            <td>${escapeHtml(activity.title)}</td>
                            <td>${activity.max_score}</td>
                            <td>
                                <button onclick="viewScores(${activity.activity_id})">View Scores</button>
                            </td>
                        </tr>
                    `;
                });
            }

            html += '</tbody></table>';
            document.getElementById('activityList').innerHTML = html;
        }
    } catch (error) {
        console.error('Failed to load activities:', error);
        document.getElementById('activityList').innerHTML = 'Failed to load activities';
    }
}

/* ================= CREATE ACTIVITY ================= */
async function createActivity() {
    if (!currentClassId) {
        alert("Select a class first");
        return;
    }

    const title = document.getElementById('activity_title').value.trim();
    const maxScore = parseInt(document.getElementById('max_score').value);

    if (!title || !maxScore || maxScore < 1) {
        alert("Please enter valid activity title and max score");
        return;
    }

    try {
        const response = await API.activities.create({
            class_subject_id: currentClassId, // This might need adjustment based on your API
            title: title,
            max_score: maxScore
        });

        if (response.success) {
            alert('Activity created successfully!');
            document.getElementById('activity_title').value = '';
            document.getElementById('max_score').value = '';
            loadActivities();
        } else {
            alert('Failed to create activity');
        }
    } catch (error) {
        alert('Error creating activity: ' + error.message);
    }
}

/* ================= VIEW SCORES ================= */
function viewScores(activityId) {
    // Redirect to scores page with activity selected
    window.location.href = 'teacher_scores.php?activity_id=' + activityId;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

/* ================= INIT ================= */
loadClasses();
</script>

</body>
</html>
