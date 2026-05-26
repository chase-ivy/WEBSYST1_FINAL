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
                        <label>Select Subject</label>
                        <select id="subjectSelect">
                            <option value="">-- Choose Subject --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grading Period</label>
                        <select id="gradingPeriod">
                            <option value="Q1">1st Quarter</option>
                            <option value="Q2">2nd Quarter</option>
                            <option value="Q3">3rd Quarter</option>
                            <option value="Q4">4th Quarter</option>
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
                                <th>Grade</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="gradeTable">
                            <tr><td colspan="3" class="empty-row">Loading grades...</td></tr>
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
let currentClassSubjectId = null;
let gradeData = [];
let classSubjectsMap = {};

async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Choose Class --</option>' +
                response.data.map(c =>
                    `<option value="${c.class_id}">${c.grade_level} ${c.section} (${c.school_year})</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
        showMessage('error', 'Failed to load classes');
    }
}

function onClassChange() {
    currentClassId = document.getElementById('classSelect').value;
    const subjectSelect = document.getElementById('subjectSelect');

    subjectSelect.innerHTML = '<option value="">-- Choose Subject --</option>';
    currentClassSubjectId = null;
    document.getElementById('gradeSection').style.display = 'none';

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
            select.innerHTML = '<option value="">-- Choose Subject --</option>';
            classSubjectsMap = {};

            response.data.forEach(cs => {
                classSubjectsMap[cs.class_subject_id] = { ...cs, class_id: classId };
                const option = document.createElement('option');
                option.value = cs.class_subject_id;
                option.textContent = cs.subject_name;
                select.appendChild(option);
            });

            if (response.data.length === 0) {
                select.innerHTML = '<option value="" disabled>No subjects available</option>';
            }
        }
    } catch (error) {
        console.error('Failed to load subjects:', error);
        showMessage('error', 'Failed to load subjects');
    }
}

function onSubjectChange() {
    currentClassSubjectId = document.getElementById('subjectSelect').value;

    document.getElementById('gradeSection').style.display = 'none';

    if (!currentClassSubjectId) {
        return;
    }

    document.getElementById('gradeSection').style.display = 'block';
    loadGrades();
}

async function loadGrades() {
    if (!currentClassSubjectId) return;

    document.getElementById('gradeTable').innerHTML = '<tr><td colspan="4" class="empty-row">Loading grades...</td></tr>';

    try {
        const response = await API.grades.getClassGrades(currentClassSubjectId);
        if (response.success) {
            const studentsMap = {};
            response.data.forEach(item => {
                const key = item.class_student_id;
                if (!studentsMap[key]) {
                    studentsMap[key] = {
                        class_student_id: item.class_student_id,
                        class_subject_id: item.class_subject_id,
                        first_name: item.first_name,
                        last_name: item.last_name,
                        grades: {}
                    };
                }
                if (item.grading_period !== null && item.grading_period !== '') {
                    studentsMap[key].grades[item.grading_period] = {
                        grade_id: item.grade_id,
                        grade: item.grade
                    };
                }
            });
            gradeData = Object.values(studentsMap);
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
        tbody.innerHTML = '<tr><td colspan="3" class="empty-row">No students found</td></tr>';
        return;
    }

    const period = document.getElementById('gradingPeriod').value;

    tbody.innerHTML = gradeData.map(student => {
        const gradeInfo = student.grades[period] || {};
        const gradeValue = gradeInfo.grade ?? '';
        return `
            <tr>
                <td class="td-primary">${escapeHtml(student.first_name + ' ' + student.last_name)}</td>
                <td>
                    <input type="number"
                        id="grade-${student.class_student_id}-${gradeInfo.grade_id ?? 0}"
                        value="${gradeValue}"
                        min="0" max="100" step="0.5">
                </td>
                <td>
                    <button type="button" class="btn-primary" onclick="saveGrade(${student.class_student_id}, ${gradeInfo.grade_id ?? 0}, ${student.class_subject_id})">Save</button>
                </td>
            </tr>
        `;
    }).join('');
}

async function saveGrade(class_student_id, grade_id, class_subject_id) {
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
            class_subject_id,
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

document.getElementById('classSelect').addEventListener('change', onClassChange);

document.getElementById('subjectSelect').addEventListener('change', onSubjectChange);

document.getElementById('gradingPeriod').addEventListener('change', renderGrades);

window.addEventListener('DOMContentLoaded', loadClasses);
</script>
<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>

</body>
</html>
