<?php
function renderTeacherSidebar(string $active = 'dashboard'): void {

    $items = [
        'dashboard'  => ['Dashboard', 'teacher_dashboard.php'],
        'classes'    => ['My Classes', 'teacher_classes.php'],
        'students'   => ['Students', 'teacher_manage_students.php'],
        'enroll'     => ['Enroll', '../../forms/enrollment_form/enrollment/enrollment.php'],
        'verify'     => ['Verify Enrollments', '../../forms/enrollment_form/verify_enrollment/verify_enrollment.php'],
        'subjects'   => ['Subjects', 'teacher_subjects.php'],
        'activities' => ['Activities', 'teacher_activities.php'],
        'scores'     => ['Scores', 'teacher_scores.php'],
        'grades'     => ['Grades', 'teacher_grades.php'],
        'attendance' => ['Attendance', 'teacher_attendance.php'],
    ];

    echo '<aside class="sidebar">';
    
    echo '<div class="sidebar-brand">';
    echo '<h3>Teacher Panel</h3>';
    echo '<p>Gibraltar AMS</p>';
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