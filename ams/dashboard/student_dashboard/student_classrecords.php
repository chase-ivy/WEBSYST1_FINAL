<?php
require_once __DIR__ . '/../../login/auth.php';
require_role(['student']);
require_once __DIR__ . '/student_nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Record</title>
    <link rel="stylesheet" href="../../style/style.css">
</head>

<body>
<header>
    <h2>Gibraltar AMS - Class Record</h2>
    <img src="../../style/logo.png" class="logo">
</header>

<div class="dashboard-layout">
    <?php renderStudentSidebar('classrecords'); ?>

    <div class="content">
        <div id="error" class="error-message" style="display:none;"></div>

        <div class="card" id="attendance-summary-card">
            <h3>Attendance Summary</h3>
            <p>Loading attendance summary...</p>
        </div>

        <div class="card" id="attendance-records-card">
            <h3>Attendance Records</h3>
            <p>Loading attendance records...</p>
        </div>
    </div>
</div>

<script src="../../api/client.js"></script>
<script>
    function formatDate(value) {
        if (!value) return '-';
        const date = new Date(value);
        return date.toLocaleDateString();
    }

    function statusClass(status) {
        if (!status) return '';
        switch (status.toLowerCase()) {
            case 'present': return 'text-success';
            case 'absent': return 'text-danger';
            case 'late': return 'text-warning';
            default: return '';
        }
    }

    async function loadAttendance() {
        const errorBox = document.getElementById('error');
        const summaryCard = document.getElementById('attendance-summary-card');
        const recordsCard = document.getElementById('attendance-records-card');

        try {
            const response = await API.studentDashboard.get();
            const attendance = response.data.attendance || {};
            const records = response.data.attendance_records || [];

            summaryCard.innerHTML = `
                <h3>Attendance Summary</h3>
                <p>Present: ${attendance.present || 0}</p>
                <p>Absent: ${attendance.absent || 0}</p>
                <p>Late: ${attendance.late_count || 0}</p>
                <p>Excused: ${attendance.excused || 0}</p>
            `;

            if (records.length === 0) {
                recordsCard.innerHTML = '<h3>Attendance Records</h3><p>No attendance records found.</p>';
                return;
            }

            recordsCard.innerHTML = `
                <h3>Attendance Records</h3>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                    ${records.map(r => `
                        <tr>
                            <td>${r.subject_name}</td>
                            <td>${formatDate(r.attendance_date)}</td>
                            <td class="${statusClass(r.status)}">${r.status}</td>
                        </tr>
                    `).join('')}
                </table>
            `;
        } catch (error) {
            console.error(error);
            errorBox.style.display = 'block';
            errorBox.textContent = 'Unable to load attendance records. Please refresh the page.';
            summaryCard.innerHTML = '<h3>Attendance Summary</h3><p>Unable to load attendance at this time.</p>';
            recordsCard.innerHTML = '<h3>Attendance Records</h3><p>Unable to load attendance records at this time.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', loadAttendance);
</script>
</body>
</html>
