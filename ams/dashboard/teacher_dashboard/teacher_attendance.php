<?php
require_once __DIR__ . '/teacher_config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Recording - Teacher Dashboard</title>
    <link rel="stylesheet" href="../../style/style.css">
    <style>
        .attendance-controls { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin: 16px 0; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: bold; margin-bottom: 4px; font-size: 14px; }
        .form-group input, .form-group select { 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-family: inherit;
        }
        .attendance-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .attendance-table th, .attendance-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .attendance-table th { background: #f5f5f5; font-weight: bold; }
        .status-select { width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; }
        .status-select option[value=""] { color: #999; }
        .btn-mark-all { background: #2196F3; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin: 8px 4px 8px 0; }
        .btn-mark-all:hover { background: #0b7dda; }
        .alert { padding: 12px; margin: 12px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .loading { color: #999; font-style: italic; }
        .action-link { color: #2196F3; cursor: pointer; text-decoration: underline; }
        .action-link:hover { color: #0b7dda; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Attendance Recording</h2>
</header>

<div class="container">
<?php renderTeacherSidebar('attendance'); ?>

    <div class="content">

        <div class="card">
            <h3>Record Attendance</h3>

            <div class="attendance-controls">
                <div class="form-group">
                    <label for="classSelect">Select Class:</label>
                    <select id="classSelect">
                        <option value="">-- Choose a class --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="attendanceDate">Date:</label>
                    <input type="date" id="attendanceDate" value="">
                </div>

                <div style="display: flex; gap: 8px; align-items: flex-end;">
                    <button class="btn-mark-all" onclick="markAllPresent()">Mark All Present</button>
                    <button class="btn-mark-all" onclick="markAllAbsent()" style="background: #f44336;">Mark All Absent</button>
                </div>
            </div>

            <div id="statusMessage"></div>

            <div id="attendanceContainer" style="display: none;">
                <table class="attendance-table" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="loading">Loading students...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="../../api/client.js"></script>
<script>
    let currentClassId = null;
    let attendanceRecords = [];

    // Set today's date by default
    document.getElementById('attendanceDate').valueAsDate = new Date();

    document.getElementById('classSelect').addEventListener('change', async function() {
        currentClassId = this.value;
        if (!currentClassId) {
            document.getElementById('attendanceContainer').style.display = 'none';
            return;
        }
        
        document.getElementById('attendanceContainer').style.display = 'block';
        await loadAttendance();
    });

    document.getElementById('attendanceDate').addEventListener('change', async function() {
        if (currentClassId) {
            await loadAttendance();
        }
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

    async function loadAttendance() {
        try {
            const date = document.getElementById('attendanceDate').value;
            const response = await api.getAttendance(currentClassId, date);
            if (response.success) {
                attendanceRecords = response.data;
                renderAttendanceTable();
            }
        } catch (error) {
            console.error('Error loading attendance:', error);
            showMessage('error', 'Failed to load attendance');
        }
    }

    function renderAttendanceTable() {
        const tbody = document.querySelector('#attendanceTable tbody');
        if (attendanceRecords.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3">No students in this class</td></tr>';
            return;
        }

        tbody.innerHTML = attendanceRecords.map(record => `
            <tr>
                <td>${escapeHtml(record.first_name + ' ' + record.last_name)}</td>
                <td>
                    <select class="status-select" id="status-${record.enrollment_id}">
                        <option value="">-- Select --</option>
                        <option value="Present" ${record.status === 'Present' ? 'selected' : ''}>Present</option>
                        <option value="Absent" ${record.status === 'Absent' ? 'selected' : ''}>Absent</option>
                        <option value="Late" ${record.status === 'Late' ? 'selected' : ''}>Late</option>
                        <option value="Excused" ${record.status === 'Excused' ? 'selected' : ''}>Excused</option>
                    </select>
                </td>
                <td>
                    <span class="action-link" onclick="recordAttendance(${record.enrollment_id})">Save</span>
                </td>
            </tr>
        `).join('');
    }

    async function recordAttendance(enrollmentId) {
        const statusSelect = document.getElementById(`status-${enrollmentId}`);
        const status = statusSelect.value;

        if (!status) {
            showMessage('error', 'Please select a status');
            return;
        }

        try {
            const date = document.getElementById('attendanceDate').value;
            const response = await api.recordAttendance(enrollmentId, date, status);

            if (response.success) {
                showMessage('success', 'Attendance recorded successfully');
            } else {
                showMessage('error', response.error || 'Failed to record attendance');
            }
        } catch (error) {
            console.error('Error recording attendance:', error);
            showMessage('error', 'Error recording attendance');
        }
    }

    async function markAllPresent() {
        for (let record of attendanceRecords) {
            const date = document.getElementById('attendanceDate').value;
            await api.recordAttendance(record.enrollment_id, date, 'Present');
        }
        showMessage('success', 'All students marked present');
        await loadAttendance();
    }

    async function markAllAbsent() {
        for (let record of attendanceRecords) {
            const date = document.getElementById('attendanceDate').value;
            await api.recordAttendance(record.enrollment_id, date, 'Absent');
        }
        showMessage('success', 'All students marked absent');
        await loadAttendance();
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
