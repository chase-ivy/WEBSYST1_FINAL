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

<title>My Classes</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="teacher.css">
</head>

<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">

<?php renderTeacherSidebar('classes'); ?>

<main class="main">

    <div class="page-header">
        <h1>My Classes</h1>
        <p>Manage and view your assigned classes</p>
    </div>

    <section class="section">
        <div class="section-header">
            <h2>Assigned Classes</h2>
            <p>All classes assigned to you</p>
        </div>

        <div class="section-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Students</th>
                            <th>School Year</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="classes-tbody">
                        <tr class="empty-row">
                            <td colspan="6">Loading classes...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>
</div>

<!-- USE YOUR EXISTING CLIENT -->
<script src="../../api/client.js"></script>

<script>
async function loadClasses() {
    try {
        const res = await API.teacher.classes();

        console.log('API RESPONSE:', res); // 🔥 DEBUG (remove later)

        if (!res.success) {
            throw new Error(res.error || 'API failed');
        }

        const classes = res.data;
        const tbody = document.getElementById('classes-tbody');

        if (!classes || classes.length === 0) {
            tbody.innerHTML = `
                <tr class="empty-row">
                    <td colspan="6">No classes assigned</td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = classes.map(c => `
            <tr>
                <td class="td-primary">${c.subject_name}</td>
                <td>Grade ${c.grade_level}</td>
                <td>${c.section}</td>
                <td>${c.student_count}</td>
                <td>${c.school_year}</td>
                <td>
                    <button class="btn-view" onclick="viewClass(${c.class_id})">
                        View
                    </button>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        console.error('LOAD ERROR:', err);

        document.getElementById('classes-tbody').innerHTML = `
            <tr class="empty-row">
                <td colspan="6">Failed to load classes</td>
            </tr>
        `;
    }
}

function viewClass(classId) {
    window.location.href = `teacher_class_view.php?class_id=${classId}`;
}

document.addEventListener('DOMContentLoaded', loadClasses);
</script>

</body>
</html>