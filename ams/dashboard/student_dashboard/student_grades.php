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
    <title>Grades · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="student.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Student Portal</span>
</header>

<div class="shell">
    <?php renderStudentSidebar('grades'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Grades</h1>
            <p>View your grades across all subjects and quarters.</p>
        </div>

        <div id="error" class="alert alert-error" style="display:none;"></div>

        <section class="section">
            <div class="section-header">
                <h2>Grade Report</h2>
                <p>Your academic performance by subject and grading period.</p>
            </div>
            <div class="section-body">
                <div id="grades-content">
                    <div class="empty-row">Loading grades...</div>
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

async function loadGrades() {
    const errorBox = document.getElementById('error');
    const gradesContent = document.getElementById('grades-content');

    try {
        const response = await API.studentDashboard.get();
        const grades = normalizeGrades(response.data.grades);

        if (grades.length === 0) {
            gradesContent.innerHTML = '<div class="empty-row">No grades found.</div>';
            return;
        }

        gradesContent.innerHTML = `
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>1st Quarter</th>
                            <th>2nd Quarter</th>
                            <th>3rd Quarter</th>
                            <th>4th Quarter</th>
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
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        console.error(error);
        errorBox.style.display = 'block';
        errorBox.textContent = 'Unable to load grades. Please refresh the page.';
        gradesContent.innerHTML = '<div class="empty-row">Unable to load grades at this time.</div>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', loadGrades);
</script>

</body>
</html>
