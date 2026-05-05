<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
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

        .btn-mark-all { background: #2196F3; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin: 8px 4px 8px 0; }
        .btn-mark-all:hover { background: #0b7dda; }

        .alert { padding: 12px; margin: 12px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

        .loading { color: #999; font-style: italic; }
        .action-link { color: #2196F3; cursor: pointer; text-decoration: underline; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Attendance Recording</h2>
</header>

<div class="dashboard-layout">

<?php renderTeacherSidebar('attendance'); ?>

<div class="content">

<div class="card">
    <h3>Record Attendance</h3>

    <div class="attendance-controls">

        <div class="form-group">
            <label>Select Class:</label>
            <select id="classSelect">
                <option value="">-- Choose a class --</option>
            </select>
        </div>

        <div class="form-group">
            <label>Date:</label>
            <input type="date" id="attendanceDate">
        </div>

        <div style="display:flex; gap:8px; align-items:flex-end;">
            <button class="btn-mark-all" onclick="markAllPresent()">Mark All Present</button>
            <button class="btn-mark-all" onclick="markAllAbsent()" style="background:#f44336;">Mark All Absent</button>
        </div>

    </div>

    <div id="statusMessage"></div>

    <div id="attendanceContainer" style="display:none;">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="attendanceBody">
                <tr><td colspan="3" class="loading">Loading...</td></tr>
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

/* DEFAULT DATE */
document.getElementById('attendanceDate').valueAsDate = new Date();

/* LOAD CLASSES */
async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Choose a class --</option>' +
                response.data.map(c =>
                    `<option value="${c.class_id}">
                        ${c.subject_name} - ${c.grade_level} ${c.section}
                    </option>`
                ).join('');
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
    }
}

/* CLASS CHANGE */
document.getElementById('classSelect').addEventListener('change', async function () {

    currentClassId = this.value;

    if (!currentClassId) {
        document.getElementById('attendanceContainer').style.display = 'none';
        return;
    }

    document.getElementById('attendanceContainer').style.display = 'block';
    await loadAttendance();
});

/* DATE CHANGE */
document.getElementById('attendanceDate').addEventListener('change', async function () {
    if (currentClassId) {
        await loadAttendance();
    }
});

/* LOAD ATTENDANCE */
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
    }
}

/* RENDER TABLE */
function renderTable() {

    const body = document.getElementById('attendanceBody');

    if (attendanceRecords.length === 0) {
        body.innerHTML = `<tr><td colspan="3">No students found</td></tr>`;
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

/* SAVE SINGLE */
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

/* MARK ALL PRESENT */
async function markAllPresent() {
    const date = document.getElementById('attendanceDate').value;

    for (let r of attendanceRecords) {
        await saveAuto(r.class_student_id, date, 'present');
    }

    showMessage('success', 'All marked present');
    await loadAttendance();
}

/* MARK ALL ABSENT */
async function markAllAbsent() {
    const date = document.getElementById('attendanceDate').value;

    for (let r of attendanceRecords) {
        await saveAuto(r.class_student_id, date, 'absent');
    }

    showMessage('success', 'All marked absent');
    await loadAttendance();
}

/* AUTO SAVE */
async function saveAuto(class_student_id, date, status) {
    try {
        await API.attendance.record({
            class_student_id,
            date,
            status
        });
    } catch (error) {
        console.error('Failed to record attendance:', error);
    }
}

/* MESSAGE */
function showMessage(type, text) {
    const div = document.createElement('div');
    div.textContent = text;
    const msg = div.innerHTML;
    document.getElementById('statusMessage').innerHTML =
        `<div class="alert alert-${type}">${msg}</div>`;
}

/* INIT */
loadClasses();

</script>

</body>
</html>
