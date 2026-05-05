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
    <title>Grades · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">
    <?php renderTeacherSidebar('grades'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Grades</h1>
            <p>Manage student grades for your classes.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Grade Controls</h2>
                <p>Select a class and grading period to view grades.</p>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Select Class</label>
                        <select id="classSelect">
                            <option value="">-- Choose Class --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grading Period</label>
                        <select id="gradingPeriod">
                            <option value="Q1">1st Quarter</option>
                            <option value="Q2">2nd Quarter</option>
                            <option value="Q3">3rd Quarter</option>
                            <option value="Q4">4th Quarter</option>
                            <option value="Midterm">Midterm</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>
                </div>
                <div id="statusMessage"></div>
            </div>
        </section>

        <section class="section" id="gradeSection" style="display:none;">
            <div class="section-header">
                <h2>Grade Table</h2>
                <p>Update grades for each student and save individual entries.</p>
            </div>
            <div class="section-body">
                <div class="table-wrap">
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
                            <tr><td colspan="4" class="empty-row">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
let currentClassId = null;
let gradeData = [];

async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Choose Class --</option>' +
                response.data.map(c =>
                    `<option value="${c.class_id}">${c.grade_level} ${c.section} - ${c.subject_name}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
        showMessage('error', 'Failed to load classes');
    }
}

async function loadGrades() {
    if (!currentClassId) return;

    document.getElementById('gradeTable').innerHTML = '<tr><td colspan="4" class="empty-row">Loading grades...</td></tr>';

    try {
        const response = await API.grades.getClassGrades(currentClassId);
        if (response.success) {
            gradeData = response.data;
            renderGrades();
        } else {
            showMessage('error', 'Failed to load grades');
        }
    } catch (error) {
        console.error('Failed to load grades:', error);
        showMessage('error', 'Failed to load grades');
    }
}

function renderGrades() {
    const tbody = document.getElementById('gradeTable');

    if (gradeData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="empty-row">No grades found</td></tr>';
        return;
    }

    const period = document.getElementById('gradingPeriod').value;

    tbody.innerHTML = gradeData
        .filter(g => g.grading_period === period)
        .map(g => `
            <tr>
                <td class="td-primary">${escapeHtml(g.first_name + ' ' + g.last_name)}</td>
                <td>${escapeHtml(g.subject)}</td>
                <td>
                    <input type="number"
                        id="grade-${g.class_student_id}-${g.grade_id ?? 0}"
                        value="${g.grade ?? ''}"
                        min="0" max="100" step="0.5">
                </td>
                <td>
                    <button type="button" class="btn-primary" onclick="saveGrade(${g.class_student_id}, ${g.grade_id ?? 0})">Save</button>
                </td>
            </tr>
        `).join('');
}

async function saveGrade(class_student_id, grade_id) {
    const input = document.querySelector(`input[id^="grade-${class_student_id}-"]`);
    const grade = parseFloat(input.value);
    const grading_period = document.getElementById('gradingPeriod').value;

    if (isNaN(grade) || grade < 0 || grade > 100) {
        showMessage('error', 'Grade must be between 0 and 100');
        return;
    }

    try {
        const response = await API.grades.save({
            class_student_id,
            grade_id,
            grading_period,
            grade
        });

        if (response.success) {
            showMessage('success', 'Grade saved successfully');
            loadGrades();
        } else {
            showMessage('error', response.error || 'Failed to save grade');
        }
    } catch (error) {
        console.error('Failed to save grade:', error);
        showMessage('error', 'Failed to save grade');
    }
}

function showMessage(type, text) {
    document.getElementById('statusMessage').innerHTML = `<div class="alert alert-${type}">${escapeHtml(text)}</div>`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

document.getElementById('classSelect').addEventListener('change', function () {
    currentClassId = this.value;

    if (!currentClassId) {
        document.getElementById('gradeSection').style.display = 'none';
        return;
    }

    document.getElementById('gradeSection').style.display = 'block';
    loadGrades();
});

document.getElementById('gradingPeriod').addEventListener('change', renderGrades);

window.addEventListener('DOMContentLoaded', loadClasses);
</script>

</body>
</html>
