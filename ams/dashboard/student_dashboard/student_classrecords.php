<?php
require_once __DIR__ . '/../../login/auth.php';
require_role(['student']);
require_once __DIR__ . '/student_nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Class Records · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="../mobile-nav.css">
</head>
<body>

<header class="topbar">
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
    <span class="topbar-label">Student Portal</span>
</header>

<div class="shell">
    <?php renderStudentSidebar('classrecords'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Class Records</h1>
            <p>View your attendance summary and detailed records.</p>
        </div>

        <div id="error" class="alert alert-error" style="display:none;"></div>

        <section class="section">
            <div class="section-header">
                <h2>Attendance Summary</h2>
                <p>Your overall attendance statistics for the school year.</p>
            </div>
            <div class="section-body">
                <div id="attendance-summary">
                    <div class="empty-row">Loading attendance summary...</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Attendance Records</h2>
                <p>Detailed attendance records by subject and date.</p>
            </div>
            <div class="section-body">
                <div id="attendance-records">
                    <div class="empty-row">Loading attendance records...</div>
                </div>
            </div>
        </section>
    </main>
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
    const summaryDiv = document.getElementById('attendance-summary');
    const recordsDiv = document.getElementById('attendance-records');

    try {
        const response = await API.studentDashboard.get();
        const attendance = response.data.attendance || {};
        const records = response.data.attendance_records || [];

        summaryDiv.innerHTML = `
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-value">${attendance.present || 0}</div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${attendance.absent || 0}</div>
                    <div class="stat-label">Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${attendance.late_count || 0}</div>
                    <div class="stat-label">Late</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${attendance.excused || 0}</div>
                    <div class="stat-label">Excused</div>
                </div>
            </div>
        `;

        if (records.length === 0) {
            recordsDiv.innerHTML = '<div class="empty-row">No attendance records found.</div>';
            return;
        }

        recordsDiv.innerHTML = `
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${records.map(r => `
                            <tr>
                                <td class="td-primary">${escapeHtml(r.subject_name)}</td>
                                <td>${formatDate(r.attendance_date)}</td>
                                <td class="${statusClass(r.status)}">${escapeHtml(r.status)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        console.error(error);
        errorBox.style.display = 'block';
        errorBox.textContent = 'Unable to load attendance records. Please refresh the page.';
        summaryDiv.innerHTML = '<div class="empty-row">Unable to load attendance at this time.</div>';
        recordsDiv.innerHTML = '<div class="empty-row">Unable to load attendance records at this time.</div>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', loadAttendance);
</script>
<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>

</body>
</html>
