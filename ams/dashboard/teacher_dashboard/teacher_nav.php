<?php
function renderTeacherSidebar(string $active = 'dashboard'): void {

    $items = [
        'dashboard'  => ['Dashboard', 'teacher_dashboard.php'],
        'students'   => ['Students', 'teacher_manage_students.php'],
        'enroll'     => ['Enroll', '../../forms/enrollment_form/enrollment.php'],
        'activities' => ['Activities', 'teacher_activities.php'],
        'subjects'   => ['Subjects', 'teacher_subjects.php'],
        'scores'     => ['Scores', 'teacher_scores.php'],
        'grades'     => ['Grades', 'teacher_grades.php'],
        'attendance' => ['Attendance', 'teacher_attendance.php'],
        'logout' => ['Logout', '../../login/login.php'],
    ];

    echo '<aside class="sidebar">';
    
    echo '<div class="sidebar-brand">';
    echo '<h3>Teacher Panel</h3>';
    echo '<p>Gibraltar AMS</p>';
    echo '</div>';

    foreach ($items as $key => [$label, $href]) {
        $activeClass = ($active === $key) ? 'active' : '';

        echo '<a class="' . $activeClass . '" href="' . 
            htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
        
        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '</a>';
    }

    echo '</aside>';
}