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
    <title>Attendance · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">
    <?php renderTeacherSidebar('attendance'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Attendance</h1>
            <p>Record attendance for your classes.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Attendance Controls</h2>
                <p>Select a class and date before saving attendance records.</p>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Select Class</label>
                        <select id="classSelect">
                            <option value="">-- Choose a class --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="attendanceDate">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-primary" onclick="markAllPresent()">Mark All Present</button>
                    <button type="button" class="btn-secondary" onclick="markAllAbsent()">Mark All Absent</button>
                </div>

                <div id="statusMessage"></div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Class Attendance</h2>
                <p>Student attendance records for the selected class.</p>
            </div>
            <div class="section-body">
                <div id="attendanceContainer" style="display:none;">
                    <div class="table-wrap">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceBody">
                                <tr><td colspan="3" class="empty-row">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
let currentClassId = null;
let attendanceRecords = [];

function showMessage(type, text) {
    document.getElementById('statusMessage').innerHTML = `<div class="alert alert-${type}">${text}</div>`;
}

async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Choose a class --</option>' +
                response.data.map(c =>
                    `<option value="${c.class_id}">${c.subject_name} - ${c.grade_level} ${c.section}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
    }
}

async function loadAttendance() {
    const date = document.getElementById('attendanceDate').value;

    try {
        const response = await API.attendance.getClassAttendance(currentClassId, date);
        if (response.success) {
            attendanceRecords = response.data;
            renderTable();
        }
    } catch (error) {
        console.error('Failed to load attendance:', error);
        showMessage('error', 'Failed to load attendance');
    }
}

function renderTable() {
    const body = document.getElementById('attendanceBody');

    if (attendanceRecords.length === 0) {
        body.innerHTML = '<tr><td colspan="3" class="empty-row">No students found</td></tr>';
        return;
    }

    body.innerHTML = attendanceRecords.map(r => `
        <tr>
            <td>${r.first_name} ${r.last_name}</td>
            <td>
                <select id="status-${r.class_student_id}" class="status-select">
                    <option value="">-- Select --</option>
                    <option value="present" ${r.status === 'present' ? 'selected' : ''}>Present</option>
                    <option value="absent" ${r.status === 'absent' ? 'selected' : ''}>Absent</option>
                    <option value="late" ${r.status === 'late' ? 'selected' : ''}>Late</option>
                    <option value="excused" ${r.status === 'excused' ? 'selected' : ''}>Excused</option>
                </select>
            </td>
            <td>
                <span class="action-link" onclick="save(${r.class_student_id})">Save</span>
            </td>
        </tr>
    `).join('');
}

async function save(class_student_id) {
    const status = document.getElementById(`status-${class_student_id}`).value;
    const date = document.getElementById('attendanceDate').value;

    if (!status) return showMessage('error', 'Select status first');

    try {
        const response = await API.attendance.record({
            class_student_id,
            date,
            status
        });
        if (response.success) {
            showMessage('success', 'Saved successfully');
        } else {
            showMessage('error', response.error || 'Error saving');
        }
    } catch (error) {
        console.error('Failed to save attendance:', error);
        showMessage('error', 'Error saving');
    }
}

async function markAllPresent() {
    const date = document.getElementById('attendanceDate').value;

    await Promise.all(attendanceRecords.map(async student => {
        try {
            await API.attendance.record({
                class_student_id: student.class_student_id,
                date,
                status: 'present'
            });
        } catch (error) {
            console.error('Failed to mark present:', error);
        }
    }));

    showMessage('success', 'All students marked present');
    await loadAttendance();
}

async function markAllAbsent() {
    const date = document.getElementById('attendanceDate').value;

    await Promise.all(attendanceRecords.map(async student => {
        try {
            await API.attendance.record({
                class_student_id: student.class_student_id,
                date,
                status: 'absent'
            });
        } catch (error) {
            console.error('Failed to mark absent:', error);
        }
    }));

    showMessage('success', 'All students marked absent');
    await loadAttendance();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function handleClassChange() {
    currentClassId = document.getElementById('classSelect').value;

    if (!currentClassId) {
        document.getElementById('attendanceContainer').style.display = 'none';
        return;
    }

    document.getElementById('attendanceContainer').style.display = 'block';
    loadAttendance();
}

window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('attendanceDate').valueAsDate = new Date();
    document.getElementById('classSelect').addEventListener('change', handleClassChange);
    document.getElementById('attendanceDate').addEventListener('change', async () => {
        if (currentClassId) {
            await loadAttendance();
        }
    });
    loadClasses();
});
</script>

</body>
</html>
