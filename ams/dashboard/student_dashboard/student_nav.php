<?php
function renderStudentSidebar(string $active = 'dashboard'): void {
    $items = [
        'dashboard' => ['Dashboard', 'student_dashboard.php'],
        'grades' => ['Grades', 'student_grades.php'],
        'activities' => ['Activities', 'student_activities.php'],
        'report' => ['Report Card', 'student_report.php'],
        'classrecords' => ['Class Record', 'student_classrecords.php'],
        'logout' => ['Logout', '../../login/logout.php'],
    ];

    echo '<aside class="sidebar">';

    echo '<div class="sidebar-brand">';
    echo '<h3>Student Panel</h3>';
    echo '<p>Gibraltar AMS</p>';
    echo '</div>';

    echo '<nav>';
    foreach ($items as $key => [$label, $href]) {
        $activeClass = ($active === $key) ? 'active' : '';
        echo '<a class="' . $activeClass . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '</a>';
    }
    echo '</nav>';

    // Logout link already included in the `$items` list above.
    echo '</aside>';
}
