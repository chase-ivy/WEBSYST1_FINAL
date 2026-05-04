<?php
require_once __DIR__ . '/../../login/auth.php';
require_role(['student']);
require_once __DIR__ . '/student_nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal - Gibraltar AMS</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
</head>

<body>
<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../../style/logo.png" alt="Logo" class="logo">
</header>

<div class="container">
    <?php renderStudentSidebar('dashboard'); ?>

    <div class="content">
        <div id="error" class="error-message" style="display:none;"></div>

        <div id="student-overview" class="card">
            <h3>Student Dashboard</h3>
            <p>Loading your dashboard...</p>
        </div>

        <div id="grades-summary" class="card"></div>
        <div id="activities-summary" class="card"></div>
        <div id="attendance-summary" class="card"></div>
        <div id="report-summary" class="card"></div>
    </div>
</div>

<script src="../../api/client.js"></script>
<script>
    function formatDate(value) {
        if (!value) return '-';
        const date = new Date(value);
        return date.toLocaleDateString();
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
                <h3>Welcome, ${student.first_name} ${student.last_name}</h3>
                <p><strong>LRN:</strong> ${student.lrn || '-'}</p>
                <p><strong>Grade Level:</strong> ${student.grade_level || '-'}</p>
                <p><strong>Sex:</strong> ${student.sex || '-'}</p>
                <p><strong>Birth Date:</strong> ${formatDate(student.birth_date)}</p>
                <p><strong>Overall Average:</strong> ${overallAverage}</p>
                <p><strong>Remarks:</strong> ${overallRemarks}</p>
            `;

            if (data.grades.length === 0) {
                gradesSummary.innerHTML = '<h3>Recorded Grades</h3><p>No grades are available yet.</p>';
            } else {
                gradesSummary.innerHTML = `
                    <h3>Recorded Grades</h3>
                    <table>
                        <tr>
                            <th>Subject</th>
                            <th>Quarter</th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                        ${data.grades.map(g => `
                            <tr>
                                <td>${g.subject_name}</td>
                                <td>${g.grading_period}</td>
                                <td>${g.final_grade}</td>
                                <td>${g.remarks}</td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            }

            if (data.activities.length === 0) {
                activitiesSummary.innerHTML = '<h3>Activities</h3><p>No activities have been recorded yet.</p>';
            } else {
                activitiesSummary.innerHTML = `
                    <h3>Activities</h3>
                    <table>
                        <tr>
                            <th>Subject</th>
                            <th>Activity</th>
                            <th>Date</th>
                            <th>Score</th>
                        </tr>
                        ${data.activities.map(a => `
                            <tr>
                                <td>${a.subject_name}</td>
                                <td>${a.activity_name}</td>
                                <td>${formatDate(a.activity_date)}</td>
                                <td>${a.score} / ${a.max_score}</td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            }

            attendanceSummary.innerHTML = `
                <h3>Attendance Summary</h3>
                <p>Present: ${data.attendance.present || 0}</p>
                <p>Absent: ${data.attendance.absent || 0}</p>
                <p>Late: ${data.attendance.late_count || 0}</p>
                <p>Excused: ${data.attendance.excused || 0}</p>
            `;

            if (data.report_card.length === 0) {
                reportSummary.innerHTML = '<h3>Report Card</h3><p>No report card data found.</p>';
            } else {
                reportSummary.innerHTML = `
                    <h3>Report Card</h3>
                    <table>
                        <tr>
                            <th>Quarter</th>
                            <th>Average</th>
                            <th>Remarks</th>
                        </tr>
                        ${data.report_card.map(r => `
                            <tr>
                                <td>${r.grading_period}</td>
                                <td>${r.general_average}</td>
                                <td>${r.remarks}</td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            }
        } catch (error) {
            console.error(error);
            errorBox.style.display = 'block';
            errorBox.textContent = 'Unable to load student dashboard. Please refresh the page.';
        }
    }

    document.addEventListener('DOMContentLoaded', loadStudentDashboard);
</script>
</body>
</html>
