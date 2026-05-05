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
    <title>Activities · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">
    <?php renderTeacherSidebar('activities'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Activities</h1>
            <p>Manage activities for your assigned classes.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Select Class</h2>
                <p>Choose a class to view its students and activity list.</p>
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
                <h2>Students in Class</h2>
                <p>See the roster for the selected class.</p>
            </div>
            <div class="section-body">
                <div id="studentList">Select a class first</div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Create Activity</h2>
                <p>Add an activity for the selected class.</p>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Activity Title</label>
                        <input id="activity_title" placeholder="Activity Title" required>
                    </div>
                    <div class="form-group">
                        <label>Max Score</label>
                        <input type="number" id="max_score" placeholder="Max Score" min="1" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-primary" onclick="createActivity()">Create Activity</button>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Activity List</h2>
                <p>View activities for the selected class.</p>
            </div>
            <div class="section-body">
                <div id="activityList">Select a class to view activities</div>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
let currentClassId = null;

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

async function loadStudents() {
    if (!currentClassId) return;

    document.getElementById('studentList').innerHTML = '<div class="empty-row">Loading students...</div>';

    try {
        const response = await API.classes.getClassStudents(currentClassId);
        if (response.success) {
            let html = '<div class="table-wrap"><table>';
            html += '<thead><tr><th>Name</th><th>LRN</th></tr></thead><tbody>';

            if (response.data.length === 0) {
                html += '<tr><td colspan="2">No students in this class</td></tr>';
            } else {
                response.data.forEach(s => {
                    html += `
                        <tr>
                            <td class="td-primary">${escapeHtml(s.first_name + ' ' + s.last_name)}</td>
                            <td>${escapeHtml(s.lrn || 'N/A')}</td>
                        </tr>
                    `;
                });
            }

            html += '</tbody></table></div>';
            document.getElementById('studentList').innerHTML = html;
        }
    } catch (error) {
        console.error('Failed to load students:', error);
        document.getElementById('studentList').innerHTML = '<div class="empty-row">Failed to load students</div>';
    }
}

async function loadActivities() {
    if (!currentClassId) return;

    document.getElementById('activityList').innerHTML = '<div class="empty-row">Loading activities...</div>';

    try {
        const response = await API.activities.listByClass(currentClassId);
        if (response.success) {
            let html = '<div class="table-wrap"><table>';
            html += '<thead><tr><th>Title</th><th>Max Score</th><th>Actions</th></tr></thead><tbody>';

            if (response.data.length === 0) {
                html += '<tr><td colspan="3">No activities created yet</td></tr>';
            } else {
                response.data.forEach(activity => {
                    html += `
                        <tr>
                            <td class="td-primary">${escapeHtml(activity.title)}</td>
                            <td>${activity.max_score}</td>
                            <td>
                                <button type="button" class="btn-secondary" onclick="viewScores(${activity.activity_id})">View Scores</button>
                            </td>
                        </tr>
                    `;
                });
            }

            html += '</tbody></table></div>';
            document.getElementById('activityList').innerHTML = html;
        }
    } catch (error) {
        console.error('Failed to load activities:', error);
        document.getElementById('activityList').innerHTML = '<div class="empty-row">Failed to load activities</div>';
    }
}

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
            class_subject_id: currentClassId,
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

function viewScores(activityId) {
    window.location.href = 'teacher_scores.php?activity_id=' + activityId;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

window.addEventListener('DOMContentLoaded', loadClasses);
</script>

</body>
</html>

    return div.innerHTML;
}

/* ================= INIT ================= */
loadClasses();
</script>

</body>
</html>
