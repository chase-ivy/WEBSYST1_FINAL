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

    echo '<aside class="sidebar" id="main-sidebar">';
    echo '<div class="sidebar-brand">';
    echo '<div>';
    echo '<h3>Student Panel</h3>';
    echo '<p>Gibraltar AMS</p>';
    echo '</div>';

    // Close button — hidden on desktop views via loaded mobile-nav.css
    echo '<button class="sidebar-close-btn" aria-label="Close menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6"  y1="6" x2="18" y2="18"/>
            </svg>
        </button>';

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
