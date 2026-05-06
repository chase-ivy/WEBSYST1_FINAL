<?php
function renderAdminSidebar(string $active = 'dashboard'): void {
    $items = [
        'dashboard' => ['Dashboard', 'admin_dashboard.php', false],
        'staff_label' => ['Staff Management', '#', true],
        'create' => ['Create Staff', 'admin_create.php', false],
        'update' => ['Update Staff', 'admin_update.php', false],
        'delete' => ['Delete Staff', 'admin_delete.php', false],
        'students_label' => ['Student Management', '#', true],
        'manage_students' => ['Manage Students', 'admin_manage_students.php', false],
    ];

    echo '<aside class="sidebar">';
    echo '<div class="sidebar-brand">';
    echo '<h3>Admin Panel</h3>';
    echo '<p>Gibraltar AMS</p>';
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
    
    echo '<a href="../../login/logout.php">Logout</a>';
    echo '</aside>';
}

