<?php
function renderStudentSidebar(string $active = 'dashboard', int $student_id = 0): void {
    $items = [
        'dashboard' => ['Dashboard', 'student_dashboard.php'],
        'grades' => ['Grades', 'student_grades.php'],
        'activities' => ['Activities', 'student_activities.php'],
        'report' => ['Report Card', 'student_report.php'],
        'classrecords' => ['Class Record', 'student_classrecords.php'],
        'logout' => ['Logout', '../../login/index.php'],
    ];

    echo '<div class="sidebar">';

    foreach ($items as $key => [$label, $href]) {
        $url = $href;
        if ($student_id && $key !== 'logout') {
            $url .= '?student_id=' . urlencode((string) $student_id);
        }

        $activeClass = $active === $key ? ' class="active"' : '';
        echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $activeClass . '>' .
             htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
             '</a>';
    }

    echo '</div>';
}
