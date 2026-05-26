<?php
function renderTeacherSidebar(string $active = 'dashboard'): void {

    $items = [
        'dashboard'  => ['Dashboard', 'teacher_dashboard.php'],
        'classes'    => ['My Classes', 'teacher_classes.php'],
        'students'   => ['Students', 'teacher_manage_students.php'],
        'enroll'     => ['Enroll', '../../forms/enrollment_form/enrollment/enrollment.php'],
        'verify'     => ['Verify Enrollments', '../../forms/enrollment_form/verify/index.php'],
        'reenroll'   => ['Re-enroll Students', 'reenroll/reenroll.php'],
        'activities' => ['Activities', 'teacher_activities.php'],
        'scores'     => ['Scores', 'teacher_scores.php'],
        'grades'     => ['Grades', 'teacher_grades.php'],
        'attendance' => ['Attendance', 'teacher_attendance.php'],
    ];

    echo '<aside class="sidebar" id="main-sidebar">';
    echo '<div class="sidebar-brand">';
    echo '    <div>';
    echo '        <h3>Teacher Panel</h3>';
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
    foreach ($items as $key => [$label, $href]) {

        $activeClass = ($active === $key) ? 'active' : '';

        echo '<a class="' . $activeClass . '" href="' . 
            htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';

        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '</a>';
    }
    echo '</nav>';

    echo '<a href="../../login/logout.php">Logout</a>';
    echo '</aside>';
}