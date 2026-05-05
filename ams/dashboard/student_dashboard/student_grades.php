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
    <title>Student Portal - Grades</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
</head>

<body>
<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../../style/logo.png" alt="Logo" class="logo">
</header>

<div class="dashboard-layout">
    <?php renderStudentSidebar('grades'); ?>

    <div class="content">
        <div id="error" class="error-message" style="display:none;"></div>
        <div class="card" id="grades-card">
            <h3>Grades</h3>
            <p>Loading grades...</p>
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

    async function loadGrades() {
        const errorBox = document.getElementById('error');
        const gradesCard = document.getElementById('grades-card');

        try {
            const response = await API.studentDashboard.get();
            const grades = normalizeGrades(response.data.grades);

            if (grades.length === 0) {
                gradesCard.innerHTML = '<h3>Grades</h3><p>No grades found.</p>';
                return;
            }

            gradesCard.innerHTML = `
                <h3>Grades</h3>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>1st Quarter</th>
                        <th>2nd Quarter</th>
                        <th>3rd Quarter</th>
                        <th>4th Quarter</th>
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
                </table>
            `;
        } catch (error) {
            console.error(error);
            errorBox.style.display = 'block';
            errorBox.textContent = 'Unable to load grades. Please refresh the page.';
            gradesCard.innerHTML = '<h3>Grades</h3><p>Unable to load grades at this time.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', loadGrades);
</script>
</body>
</html>
