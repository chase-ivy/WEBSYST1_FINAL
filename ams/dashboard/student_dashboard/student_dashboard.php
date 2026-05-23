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
    <title>Dashboard · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="student.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Student Portal</span>
</header>

<div class="shell">
    <?php renderStudentSidebar('dashboard'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome to your student portal.</p>
        </div>

        <div id="error" class="alert alert-error" style="display:none;"></div>

        <section class="section">
            <div class="section-header">
                <h2>Student Information</h2>
                <p>Your personal and academic details.</p>
            </div>
            <div class="section-body">
                <div id="student-overview">
                    <p>Loading your dashboard...</p>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Recorded Grades</h2>
                <p>Your latest grades across subjects.</p>
            </div>
            <div class="section-body">
                <div id="grades-summary">
                    <div class="empty-row">Loading grades...</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Activities</h2>
                <p>Your activity scores and participation.</p>
            </div>
            <div class="section-body">
                <div id="activities-summary">
                    <div class="empty-row">Loading activities...</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Attendance Summary</h2>
                <p>Your attendance record for the current period.</p>
            </div>
            <div class="section-body">
                <div id="attendance-summary">
                    <div class="empty-row">Loading attendance...</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Report Card</h2>
                <p>Your quarterly performance summary.</p>
            </div>
            <div class="section-body">
                <div id="report-summary">
                    <div class="empty-row">Loading report card...</div>
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

function formatStatus(value) {
    if (!value) return '-';
    return String(value)
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

async function loadStudentDashboard() {
    const errorBox = document.getElementById('error');
    const overview = document.getElementById('student-overview');
    const gradesSummary = document.getElementById('grades-summary');
    const activitiesSummary = document.getElementById('activities-summary');
    const attendanceSummary = document.getElementById('attendance-summary');
    const reportSummary = document.getElementById('report-summary');

    try {
        const response = await API.studentDashboard.get();
        const data = response.data;
        const student = data.student;

        const overallAverage = data.report_card.length > 0
            ? (data.report_card.reduce((sum, item) => sum + parseFloat(item.general_average || 0), 0) / data.report_card.length).toFixed(2)
            : '0.00';
        const overallRemarks = overallAverage >= 75 ? 'Passed' : 'Failed';

        overview.innerHTML = `
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="stat-value" style="font-size: 1.125rem; font-weight: bold;">${student.first_name} ${student.last_name}</div>
                    <div class="stat-label">Student Name</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4"/>
                            <path d="M21 12c.552 0 1-.448 1-1V5c0-.552-.448-1-1-1H3c-.552 0-1 .448-1 1v6c0 .552.448 1 1 1h18z"/>
                            <path d="M3 21h18"/>
                        </svg>
                    </div>
                    <div class="stat-value" style="font-size: 1.375rem; font-weight: bold;">${student.lrn || '-'}</div>
                    <div class="stat-label">LRN</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="stat-value" style="font-size: 1.125rem; font-weight: bold;">${overallAverage}</div>
                    <div class="stat-label">Overall Average</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4"/>
                            <path d="M21 12c.552 0 1-.448 1-1V5c0-.552-.448-1-1-1H3c-.552 0-1 .448-1 1v6c0 .552.448 1 1 1h18z"/>
                            <path d="M3 21h18"/>
                        </svg>
                    </div>
                    <div class="stat-value" style="font-size: 1.125rem; font-weight: bold;">${overallRemarks}</div>
                    <div class="stat-label">Remarks</div>
                </div>
            </div>
            <div class="form-grid" style="margin-top: 24px;">
                <div class="form-group">
                    <label>Grade Level</label>
                    <input type="text" value="${student.grade_level || '-'}" readonly>
                </div>
                <div class="form-group">
                    <label>Sex</label>
                    <input type="text" value="${student.sex || '-'}" readonly>
                </div>
                <div class="form-group">
                    <label>Enrollment Status</label>
                    <input type="text" value="${formatStatus(student.enrollment_status)}" readonly>
                </div>
                <div class="form-group">
                    <label>Birth Date</label>
                    <input type="text" value="${formatDate(student.birth_date)}" readonly>
                </div>
                <div class="form-group">
                    <label>Rejection Reason</label>
                    <input type="text" value="${escapeHtml(student.enrollment_rejection_reason || '-') }" readonly>
                </div>
            </div>
        `;

        if (data.grades.length === 0) {
            gradesSummary.innerHTML = '<div class="empty-row">No grades are available yet.</div>';
        } else {
            gradesSummary.innerHTML = `
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Quarter</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.grades.map(g => `
                                <tr>
                                    <td class="td-primary">${escapeHtml(g.subject_name)}</td>
                                    <td>${escapeHtml(g.grading_period)}</td>
                                    <td>${escapeHtml(g.final_grade)}</td>
                                    <td>${escapeHtml(g.remarks)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        if (data.activities.length === 0) {
            activitiesSummary.innerHTML = '<div class="empty-row">No activities have been recorded yet.</div>';
        } else {
            activitiesSummary.innerHTML = `
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Activity</th>
                                <th>Date</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.activities.map(a => `
                                <tr>
                                    <td class="td-primary">${escapeHtml(a.subject_name)}</td>
                                    <td>${escapeHtml(a.activity_name)}</td>
                                    <td>${formatDate(a.activity_date)}</td>
                                    <td>${escapeHtml(a.score)} / ${escapeHtml(a.max_score)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        attendanceSummary.innerHTML = `
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                    <div class="stat-value">${data.attendance.present || 0}</div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                    <div class="stat-value">${data.attendance.absent || 0}</div>
                    <div class="stat-label">Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 8v4l3 3"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                    <div class="stat-value">${data.attendance.late_count || 0}</div>
                    <div class="stat-label">Late</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4"/>
                            <path d="M21 12c.552 0 1-.448 1-1V5c0-.552-.448-1-1-1H3c-.552 0-1 .448-1 1v6c0 .552.448 1 1 1h18z"/>
                            <path d="M3 21h18"/>
                        </svg>
                    </div>
                    <div class="stat-value">${data.attendance.excused || 0}</div>
                    <div class="stat-label">Excused</div>
                </div>
            </div>
        `;

        if (data.report_card.length === 0) {
            reportSummary.innerHTML = '<div class="empty-row">No report card data found.</div>';
        } else {
            reportSummary.innerHTML = `
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Quarter</th>
                                <th>Average</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.report_card.map(r => `
                                <tr>
                                    <td class="td-primary">${escapeHtml(r.grading_period)}</td>
                                    <td>${escapeHtml(r.general_average)}</td>
                                    <td>${escapeHtml(r.remarks)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
    } catch (error) {
        console.error(error);
        errorBox.style.display = 'block';
        errorBox.textContent = 'Unable to load student dashboard. Please refresh the page.';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', loadStudentDashboard);
</script>

</body>
</html>
