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

<title>Students · Gibraltar AMS</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="teacher.css">
</head>

<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">

<?php renderTeacherSidebar('students'); ?>

<main class="main">

<div class="page-header">
    <h1>Students</h1>
    <p>Students assigned to your classes</p>
</div>

<section class="section">

<div class="section-header">
    <h2>My Students</h2>
    <p>View all enrolled students under your classes</p>
</div>

<div class="section-body">
    <div id="studentsTable">
        <div class="empty-row">Loading students...</div>
    </div>
</div>

</section>

</main>
</div>

<script src="../../api/client.js"></script>

<script>
async function loadStudents() {
    try {
        const res = await API.teacher.students();

        console.log('STUDENTS API:', res); 

        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load students');
        }

        const unique = {};
        res.data.forEach(s => {
            unique[s.student_id] = s;
        });

        const students = Object.values(unique);

        renderStudents(students);

    } catch (error) {
        console.error('Student load error:', error);

        document.getElementById('studentsTable').innerHTML = `
            <div class="empty-row">Error loading students</div>
        `;
    }
}

   //RENDER TABLE
function renderStudents(students) {

    const container = document.getElementById('studentsTable');

    if (!students.length) {
        container.innerHTML = `
            <div class="empty-row">No students assigned to your classes</div>
        `;
        return;
    }

    students.sort((a, b) => a.last_name.localeCompare(b.last_name));

    let html = `
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>LRN</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>School Year</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
    `;

    students.forEach(s => {

        const fullName = `${s.first_name || ''} ${s.last_name || ''}`.trim();

        html += `
            <tr>
                <td class="td-primary">${escapeHtml(fullName)}</td>
                <td>${escapeHtml(s.lrn || 'N/A')}</td>
                <td>${escapeHtml(s.grade_level || 'N/A')}</td>
                <td>${escapeHtml(s.section || 'N/A')}</td>
                <td>${escapeHtml(s.school_year || 'N/A')}</td>
                <td>${escapeHtml(s.subject_name || 'N/A')}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
        </div>
    `;

    container.innerHTML = html;
}

   //SAFE HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

   //INIT
document.addEventListener('DOMContentLoaded', loadStudents);
</script>

</body>
</html>