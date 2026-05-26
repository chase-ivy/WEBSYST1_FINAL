<?php
function renderAdminSidebar(string $active = 'dashboard'): void {
    $items = [
        'dashboard'        => ['Dashboard',          'admin_dashboard.php', false],
        'staff_label'      => ['User Management',    '#',                   true],
        'users'            => ['Manage Users',      'admin_users.php',     false],
        'students_label'   => ['Student Management', '#',                   true],
        'manage_students'  => ['Manage Students',    'admin_manage_students.php', false],
        'masterlist'       => ['Student Masterlist', 'admin_masterlist.php', false],
        'sections_label'   => ['Section Management', '#',                   true],
        'sections'         => ['Manage Sections',    'admin_sections.php',  false],
        'enrollment_label' => ['Enrollment',         '#',                   true],
        'enrollment_queue' => ['Enrollment Queue',   'admin_enrollment_queue.php', false],
        'lookups'          => ['Lookup Tables',      'admin_lookups.php',   false],
        'subjects'         => ['Subject Master List','admin_subjects.php',  false],
    ];

    echo '<aside class="sidebar" id="main-sidebar">';
    echo '<div class="sidebar-brand">';
    echo '    <div>';
    echo '        <h3>Admin Panel</h3>';
    echo '        <p>Gibraltar AMS</p>';
    echo '    </div>';

    echo '    <button class="sidebar-close-btn" aria-label="Close menu">';
    echo '        <svg viewBox="0 0 24 24" aria-hidden="true">';
    echo '            <line x1="18" y1="6" x2="6" y2="18"/>';
    echo '            <line x1="6" y1="6" x2="18" y2="18"/>';
    echo '        </svg>';
    echo '    </button>';

    echo '</div>';

    echo '<nav>';
    foreach ($items as $key => [$label, $href, $isLabel]) {
        if ($isLabel) {
            echo '<span class="sidebar-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
        } else {
            $activeClass = $active === $key ? 'active' : '';
            echo '<a class="' . $activeClass . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
        }
    }
    echo '</nav>';
    
    // Load API client for frontend admin pages (versioned to bust caches)
    echo '<script src="/WEBSYST1_FINAL/ams/api/client.js?v=2"></script>';
    // Include shared admin utilities
    echo '<script src="/WEBSYST1_FINAL/ams/dashboard/admin_dashboard/admin.js?v=1"></script>';

    echo '<a href="../../login/logout.php">Logout</a>';
    echo '</aside>';
}
