<?php
require_once __DIR__ . '/../../login/auth.php';
require_role(['student']);
require_once __DIR__ . '/student_nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
</head>

<body>
<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../../style/logo.png" alt="Logo" class="logo">
</header>

<div class="container">
    <?php renderStudentSidebar('report'); ?>

    <div class="content">
        <div id="error" class="error-message" style="display:none;"></div>
        <div class="card" id="report-card">
            <h3>Report Card</h3>
            <p>Loading report card...</p>
        </div>
    </div>
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
        const reportCard = document.getElementById('report-card');

        try {
            const response = await API.studentDashboard.get();
            const data = response.data;
            const grades = normalizeGrades(data.grades || []);
            const report = data.report_card || [];

            if (grades.length === 0 && report.length === 0) {
                reportCard.innerHTML = '<h3>Report Card</h3><p>No report card data found.</p>';
                return;
            }

            reportCard.innerHTML = `
                <h3>Report Card</h3>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>
                        <th>Remarks</th>
                    </tr>
                    ${grades.map(g => `
                        <tr>
                            <td>${g.subject_name}</td>
                            <td>${g.q1}</td>
                            <td>${g.q2}</td>
                            <td>${g.q3}</td>
                            <td>${g.q4}</td>
                            <td>${g.remarks}</td>
                        </tr>
                    `).join('')}
                    <tr class="average-row">
                        <td>General Average</td>
                        <td>${report.find(r => r.grading_period.toLowerCase().includes('1'))?.general_average || '-'}</td>
                        <td>${report.find(r => r.grading_period.toLowerCase().includes('2'))?.general_average || '-'}</td>
                        <td>${report.find(r => r.grading_period.toLowerCase().includes('3'))?.general_average || '-'}</td>
                        <td>${report.find(r => r.grading_period.toLowerCase().includes('4'))?.general_average || '-'}</td>
                        <td>-</td>
                    </tr>
                </table>
            `;
        } catch (error) {
            console.error(error);
            errorBox.style.display = 'block';
            errorBox.textContent = 'Unable to load report card. Please refresh the page.';
            reportCard.innerHTML = '<h3>Report Card</h3><p>Unable to load report card at this time.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', loadReportCard);
</script>
</body>
</html>
