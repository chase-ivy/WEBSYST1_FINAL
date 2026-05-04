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
    echo '<div class="sidebar">';

    foreach ($items as $key => [$label, $href]) {
        $activeClass = $active === $key ? ' class="active"' : '';
        echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"' . $activeClass . '>' .
             htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
             '</a>';
    }

    echo '</div>';
}
