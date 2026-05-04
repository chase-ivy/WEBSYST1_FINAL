<?php
require_once __DIR__ . '/teacher_config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grade Management - Teacher Dashboard</title>
    <link rel="stylesheet" href="../../style/style.css">

    <style>
        .grade-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .grade-table th, .grade-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .grade-table th { background: #f5f5f5; }

        .grade-table input {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-save-grade {
            background: #4CAF50;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-save-grade:hover { background: #45a049; }

        .form-group { margin-bottom: 12px; }

        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

        .loading { color: #888; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Grade Management</h2>
</header>

<div class="container">
<?php renderTeacherSidebar('grades'); ?>

<div class="content">

<div class="card">
    <h3>Grade Management</h3>

    <div class="form-group">
        <label>Select Class:</label>
        <select id="classSelect">
            <option value="">-- Choose Class --</option>
        </select>
    </div>

    <div class="form-group">
        <label>Grading Period:</label>
        <select id="gradingPeriod">
            <option value="Q1">1st Quarter</option>
            <option value="Q2">2nd Quarter</option>
            <option value="Q3">3rd Quarter</option>
            <option value="Q4">4th Quarter</option>
            <option value="Midterm">Midterm</option>
            <option value="Final">Final</option>
        </select>
    </div>

    <div id="statusMessage"></div>

    <div id="gradeContainer" style="display:none;">
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="gradeTable">
                <tr><td colspan="4" class="loading">Loading...</td></tr>
            </tbody>
        </table>
    </div>

</div>

</div>
</div>

<script>
let currentClassId = null;
let gradeData = [];


   //LOAD CLASSES (teacher)
async function loadClasses() {
    const res = await fetch('../../api/classes.php?action=teacher_classes');
    const json = await res.json();

    if (!json.success) return;

    const select = document.getElementById('classSelect');
    select.innerHTML = '<option value="">-- Choose Class --</option>' +
        json.data.map(c =>
            `<option value="${c.class_id}">
                ${c.grade_level} ${c.section} - ${c.subject}
            </option>`
        ).join('');
}


   //LOAD GRADES BY CLASS
async function loadGrades() {
    if (!currentClassId) return;

    const res = await fetch(`../../api/grades.php?action=class&class_id=${currentClassId}`);
    const json = await res.json();

    if (!json.success) return;

    gradeData = json.data;
    renderGrades();
}


   //RENDER TABLE
function renderGrades() {
    const tbody = document.getElementById('gradeTable');

    if (gradeData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4">No grades found</td></tr>`;
        return;
    }

    const period = document.getElementById('gradingPeriod').value;

    tbody.innerHTML = gradeData
        .filter(g => g.grading_period === period)
        .map(g => `
        <tr>
            <td>${escapeHtml(g.first_name + ' ' + g.last_name)}</td>
            <td>${escapeHtml(g.subject)}</td>
            <td>
                <input type="number" 
                    id="grade-${g.class_student_id}-${g.grade_id ?? 0}"
                    value="${g.grade ?? ''}"
                    min="0" max="100" step="0.5">
            </td>
            <td>
                <button class="btn-save-grade"
                    onclick="saveGrade(${g.class_student_id}, ${g.grade_id ?? 0})">
                    Save
                </button>
            </td>
        </tr>
    `).join('');
}


   //SAVE GRADE (API MATCHED)
async function saveGrade(class_student_id, class_subject_id) {

    const input = document.querySelector(
        `input[id^="grade-${class_student_id}-"]`
    );

    const grade = parseFloat(input.value);
    const grading_period = document.getElementById('gradingPeriod').value;

    if (isNaN(grade)) {
        showMessage('error', 'Invalid grade');
        return;
    }

    const res = await fetch('../../api/grades.php?action=save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            class_student_id,
            class_subject_id,
            grading_period,
            grade
        })
    });

    const json = await res.json();

    if (json.success) {
        showMessage('success', 'Grade saved');
        loadGrades();
    } else {
        showMessage('error', json.error || 'Failed to save');
    }
}


   //UI HELPERS
function showMessage(type, text) {
    document.getElementById('statusMessage').innerHTML =
        `<div class="alert alert-${type}">${escapeHtml(text)}</div>`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


   //EVENTS
document.getElementById('classSelect').addEventListener('change', function () {
    currentClassId = this.value;

    if (!currentClassId) {
        document.getElementById('gradeContainer').style.display = 'none';
        return;
    }

    document.getElementById('gradeContainer').style.display = 'block';
    loadGrades();
});

document.getElementById('gradingPeriod').addEventListener('change', renderGrades);

/* INIT */
document.addEventListener('DOMContentLoaded', loadClasses);

</script>

</body>
</html>