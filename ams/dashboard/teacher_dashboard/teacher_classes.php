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
    <title>My Classes · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="teacher.css">
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
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">
    <?php renderTeacherSidebar('classes'); ?>

    <main class="main">
        <div class="page-header">
            <h1>My Classes</h1>
            <p>Classes assigned to you</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Your Classes</h2>
            </div>
            <div class="section-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>Students</th>
                                <th>School Year</th>
                            </tr>
                        </thead>
                        <tbody id="classes-tbody">
                            <tr class="empty-row"><td colspan="5">Loading classes...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="/WEBSYST1_FINAL/ams/api/client.js"></script>
<script>
    async function loadClasses() {
        try {
            const response = await API.teacher.classes();
            if (response && response.success) {
                const classes = response.data || [];
                renderClasses(classes);
            }
        } catch (error) {
            console.error('Failed to load classes:', error);
            document.getElementById('classes-tbody').innerHTML = '<tr><td colspan="5" class="empty-row">Error loading classes</td></tr>';
        }
    }

    function renderClasses(classes) {
        const tbody = document.getElementById('classes-tbody');
        if (classes.length === 0) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="5">No classes assigned</td></tr>';
            return;
        }

        tbody.innerHTML = classes.map(cls => `
            <tr>
                <td class="td-primary">${cls.subject_name || 'N/A'}</td>
                <td>${cls.grade_level || 'N/A'}</td>
                <td>${cls.section || 'N/A'}</td>
                <td>${cls.student_count || 0}</td>
                <td>${cls.school_year || 'N/A'}</td>
            </tr>
        `).join('');
    }

    document.addEventListener('DOMContentLoaded', loadClasses);
</script>
<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>

</body>
</html>
