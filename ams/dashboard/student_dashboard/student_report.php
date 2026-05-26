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
    <title>Report Card · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="student.css">
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
    <span class="topbar-label">Student Portal</span>
</header>

<div class="shell">
    <?php renderStudentSidebar('report'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Report Card</h1>
            <p>Your complete academic report for the school year.</p>
        </div>

        <div id="error" class="alert alert-error" style="display:none;"></div>

        <section class="section">
            <div class="section-header">
                <h2>Academic Performance</h2>
                <p>Grades by subject and general averages by quarter.</p>
            </div>
            <div class="section-body">
                <div id="report-content">
                    <div class="empty-row">Loading report card...</div>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
function normalizeGrades(rows) {
    const subjects = {};
    rows.forEach(r => {
        const subject = r.subject_name || 'Unknown Subject';
        const period = (r.grading_period || '').toLowerCase();

        if (!subjects[subject]) {
            subjects[subject] = { subject_name: subject, q1: '-', q2: '-', q3: '-', q4: '-', remarks: r.remarks || '-' };
        }

        if (period === '1st grading' || period === 'q1') {
            subjects[subject].q1 = r.final_grade;
        } else if (period === '2nd grading' || period === 'q2') {
            subjects[subject].q2 = r.final_grade;
        } else if (period === '3rd grading' || period === 'q3') {
            subjects[subject].q3 = r.final_grade;
        } else if (period === '4th grading' || period === 'q4') {
            subjects[subject].q4 = r.final_grade;
        }

        if (r.remarks) {
            subjects[subject].remarks = r.remarks;
        }
    });
    return Object.values(subjects);
}

async function loadReportCard() {
    const errorBox = document.getElementById('error');
    const content = document.getElementById('report-content');

    try {
        const response = await API.studentDashboard.get();
        const data = response.data;
        const grades = normalizeGrades(data.grades || []);
        const report = data.report_card || [];

        if (grades.length === 0 && report.length === 0) {
            content.innerHTML = '<div class="empty-row">No report card data found.</div>';
            return;
        }

        content.innerHTML = `
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${grades.map(g => `
                            <tr>
                                <td class="td-primary">${escapeHtml(g.subject_name)}</td>
                                <td>${escapeHtml(g.q1)}</td>
                                <td>${escapeHtml(g.q2)}</td>
                                <td>${escapeHtml(g.q3)}</td>
                                <td>${escapeHtml(g.q4)}</td>
                                <td>${escapeHtml(g.remarks)}</td>
                            </tr>
                        `).join('')}
                        <tr class="average-row">
                            <td class="td-primary">General Average</td>
                            <td>${escapeHtml(report.find(r => r.grading_period.toLowerCase().includes('1'))?.general_average || '-')}</td>
                            <td>${escapeHtml(report.find(r => r.grading_period.toLowerCase().includes('2'))?.general_average || '-')}</td>
                            <td>${escapeHtml(report.find(r => r.grading_period.toLowerCase().includes('3'))?.general_average || '-')}</td>
                            <td>${escapeHtml(report.find(r => r.grading_period.toLowerCase().includes('4'))?.general_average || '-')}</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        console.error(error);
        errorBox.style.display = 'block';
        errorBox.textContent = 'Unable to load report card. Please refresh the page.';
        content.innerHTML = '<div class="empty-row">Unable to load report card at this time.</div>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', loadReportCard);
</script>
<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>

</body>
</html>
