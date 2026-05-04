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
        .grade-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: bold; margin-bottom: 4px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-family: inherit;
        }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .grade-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .grade-table th, .grade-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .grade-table th { background: #f5f5f5; font-weight: bold; }
        .grade-table input { width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; }
        .grade-table .action-cell { text-align: center; }
        .btn-save-grade { background: #4CAF50; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-save-grade:hover { background: #45a049; }
        .alert { padding: 12px; margin: 12px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .loading { color: #999; font-style: italic; }
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
                <label for="classSelect">Select Class:</label>
                <select id="classSelect">
                    <option value="">-- Choose a class --</option>
                </select>
            </div>

            <div id="gradeContainer" style="display: none;">
                <div class="form-group">
                    <label for="gradingPeriod">Grading Period:</label>
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

                <table class="grade-table" id="gradeTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Current Grade</th>
                            <th>New Grade</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="5" class="loading">Loading students...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="../../api/client.js"></script>
<script>
    let currentClassId = null;
    let enrollments = [];

    document.getElementById('classSelect').addEventListener('change', async function() {
        currentClassId = this.value;
        if (!currentClassId) {
            document.getElementById('gradeContainer').style.display = 'none';
            return;
        }
        
        document.getElementById('gradeContainer').style.display = 'block';
        await loadGrades();
    });

    async function loadClasses() {
        try {
            const response = await api.getClasses();
            if (response.success) {
                const select = document.getElementById('classSelect');
                select.innerHTML = '<option value="">-- Choose a class --</option>' +
                    response.data.map(c => 
                        `<option value="${c.class_id}">${escapeHtml(c.subject_name)} - ${c.section}</option>`
                    ).join('');
            }
        } catch (error) {
            console.error('Error loading classes:', error);
            showMessage('error', 'Failed to load classes');
        }
    }

    async function loadGrades() {
        try {
            const response = await api.getClassEnrollments(currentClassId);
            if (response.success) {
                enrollments = response.data;
                renderGradeTable();
            }
        } catch (error) {
            console.error('Error loading grades:', error);
            showMessage('error', 'Failed to load grades');
        }
    }

    function renderGradeTable() {
        const tbody = document.querySelector('#gradeTable tbody');
        if (enrollments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5">No students in this class</td></tr>';
            return;
        }

        tbody.innerHTML = enrollments.map(e => `
            <tr>
                <td>${escapeHtml(e.first_name + ' ' + e.last_name)}</td>
                <td>-</td>
                <td><input type="number" class="grade-input" data-enrollment-id="${e.enrollment_id}" min="0" max="100" step="0.5" placeholder="Grade"></td>
                <td><input type="text" class="remark-input" data-enrollment-id="${e.enrollment_id}" placeholder="Remarks"></td>
                <td class="action-cell"><button class="btn-save-grade" onclick="saveGrade(${e.enrollment_id})">Save</button></td>
            </tr>
        `).join('');
    }

    async function saveGrade(enrollmentId) {
        const gradeInput = document.querySelector(`input.grade-input[data-enrollment-id="${enrollmentId}"]`);
        const remarkInput = document.querySelector(`input.remark-input[data-enrollment-id="${enrollmentId}"]`);
        
        const grade = parseFloat(gradeInput.value);
        const remarks = remarkInput.value;

        if (isNaN(grade)) {
            showMessage('error', 'Please enter a valid grade');
            return;
        }

        try {
            const response = await api.saveGrade(
                enrollmentId,
                document.getElementById('gradingPeriod').value,
                grade,
                remarks
            );

            if (response.success) {
                showMessage('success', 'Grade saved successfully');
                gradeInput.value = '';
                remarkInput.value = '';
            } else {
                showMessage('error', response.error || 'Failed to save grade');
            }
        } catch (error) {
            console.error('Error saving grade:', error);
            showMessage('error', 'Error saving grade');
        }
    }

    function showMessage(type, text) {
        const container = document.getElementById('statusMessage');
        container.innerHTML = `<div class="alert alert-${type}">${escapeHtml(text)}</div>`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Load classes on page load
    document.addEventListener('DOMContentLoaded', loadClasses);
</script>

</body>
</html>
