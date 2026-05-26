<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/admin_nav.php';

require_special_admin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Students · Gibraltar AMS</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../mobile-nav.css">
<link rel="stylesheet" href="admin.css">
<style>
    body {
        font-family: 'DM Sans', sans-serif;
        background-image: url('hallway.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #2a1a1a;
        color: var(--text);
        min-height: 100vh;
        font-size: 14px;
        line-height: 1.5;
    }
</style>
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
    <span class="topbar-label">Admin Panel</span>
</header>

<div class="shell">

<?php renderAdminSidebar('manage_students'); ?>

<main class="main">

<div class="page-header">
    <h1>Students</h1>
    <p>Manage student enrollment and records</p>
</div>

<section class="section">

<div class="section-header">
    <h2>Enrolled Students</h2>
    <p>View all students who have completed enrollment</p>
</div>

<div class="section-body">
    <div id="studentFilterBar"></div>
    <div id="studentsTable">
        <div class="empty-row">Loading students...</div>
    </div>
    <div id="modalContainer"></div>
</div>

</section>

</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', adminManageStudentsInit);
</script>

<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>

</body>
</html>
