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
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="container">

<?php renderTeacherSidebar('activities'); ?>

<div class="content">

<!-- ================= CLASS SELECT ================= -->
<div class="card">
    <h3>My Classes</h3>

    <select id="classSelect" onchange="loadStudents()">
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

    <input id="activity_name" placeholder="Activity Name">
    <input type="number" id="max_score" placeholder="Max Score">

    <button onclick="createActivity()">Create Activity</button>
</div>

<!-- ================= ACTIVITY LIST ================= -->
<div class="card">
    <h3>Activities</h3>
    <div id="activityList">No data yet</div>
</div>

</div>
</div>

<script>

const API = '../../api/teacher_classes.php';

/* ================= LOAD CLASSES ================= */
async function loadClasses() {

    const res = await fetch(API + '?action=teacher_classes');
    const json = await res.json();

    if (!json.success) return;

    let html = '<option value="">-- Select Class --</option>';

    json.data.forEach(c => {
        html += `
            <option value="${c.class_id}">
                ${c.school_year} - Grade ${c.grade_level} ${c.section} (${c.subject})
            </option>
        `;
    });

    document.getElementById('classSelect').innerHTML = html;
}

/* ================= LOAD STUDENTS ================= */
async function loadStudents() {

    const class_id = document.getElementById('classSelect').value;
    if (!class_id) return;

    const res = await fetch(API + '?action=students&class_id=' + class_id);
    const json = await res.json();

    if (!json.success) return;

    let html = '<table>';
    html += '<tr><th>Name</th><th>LRN</th></tr>';

    json.data.forEach(s => {
        html += `
            <tr>
                <td>${s.first_name} ${s.last_name}</td>
                <td>${s.lrn}</td>
            </tr>
        `;
    });

    html += '</table>';

    document.getElementById('studentList').innerHTML = html;
}

/* ================= CREATE ACTIVITY (UI ONLY PLACEHOLDER) ================= */
async function createActivity() {

    const class_id = document.getElementById('classSelect').value;

    if (!class_id) {
        alert("Select a class first");
        return;
    }

    const data = {
        class_id: class_id,
        activity_name: document.getElementById('activity_name').value,
        max_score: document.getElementById('max_score').value
    };

    alert("You still need an activities API endpoint to save this.");
}

/* ================= INIT ================= */
loadClasses();

</script>

</body>
</html>