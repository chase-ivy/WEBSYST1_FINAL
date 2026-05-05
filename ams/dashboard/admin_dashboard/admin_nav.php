<?php
function renderAdminSidebar(string $active = 'dashboard'): void {
    $items = [
        'dashboard' => ['Dashboard', 'admin_dashboard.php'],
        'create' => ['Create Staff', 'admin_create.php'],
        'update' => ['Update Staff', 'admin_update.php'],
        'delete' => ['Delete Staff', 'admin_delete.php'],
        'logout' => ['Logout', '../../login/logout.php'],
    ];

    echo '<aside class="sidebar">';
    echo '<div class="sidebar-brand">';
    echo '<h3>Admin Panel</h3>';
    echo '<p>Gibraltar AMS</p>';
    echo '</div>';
    foreach ($items as $key => [$label, $href]) {
        $activeClass = $active === $key ? 'active' : '';
        echo '<a class="' . $activeClass . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    echo '</aside>';
}
