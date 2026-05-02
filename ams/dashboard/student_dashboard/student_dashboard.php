<?php
include 'student_config.php';
require_once __DIR__ . '/student_nav.php';

$student_id = 1; 

$student = getStudentInfo($pdo, $student_id);
$grades = getGrades($pdo, $student_id);
$activities = getActivities($pdo, $student_id);
$report = getReportCard($pdo, $student_id);
$attendance = getAttendance($pdo, $student_id);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal - Gibraltar AMS</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
</head>

<body>

<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../../style/logo.png" alt="Logo" class="logo">
</header>

<div class="container">

<?php renderStudentSidebar('dashboard', $student_id); ?>

<div class="content">
    <div class="card">
        <h3>Recorded Grades</h3>
        <table>
            <tr>
                <th>Subject</th>
                <th>Quarter</th>
                <th>Grade</th>
                <th>Remarks</th>
            </tr>

            <?php foreach ($grades as $g): ?>
            <tr>
                <td><?= $g['subject_name'] ?></td>
                <td><?= $g['grading_period'] ?></td>
                <td><?= $g['final_grade'] ?></td>
                <td><?= $g['remarks'] ?></td>
            </tr>
            <?php endforeach; ?>

        </table>
    </div>

    <div class="card">
        <h3>Activities</h3>
        <table>
            <tr>
                <th>Subject</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Score</th>
            </tr>

            <?php foreach ($activities as $a): ?>
            <tr>
                <td><?= $a['subject_name'] ?></td>
                <td><?= $a['activity_name'] ?></td>
                <td><?= $a['activity_date'] ?></td>
                <td><?= $a['score'] ?> / <?= $a['max_score'] ?></td>
            </tr>
            <?php endforeach; ?>

        </table>
    </div>

    <div class="card">
        <h3>Report Card</h3>

        <?php 
        $total = 0;
        $count = count($report);

        foreach ($report as $r) {
            $total += $r['general_average'];
        }

        $overall = $count > 0 ? round($total / $count, 2) : 0;
        ?>

        <p>General Average: <strong><?= $overall ?></strong></p>
        <p>Remarks: <?= $overall >= 75 ? 'Passed' : 'Failed' ?></p>
    </div>

    <div class="card">
        <h3>Class Record</h3>
        <table>
            <tr>
                <th>Quarter</th>
                <th>Average</th>
            </tr>

            <?php foreach ($report as $r): ?>
            <tr>
                <td><?= $r['grading_period'] ?></td>
                <td><?= $r['general_average'] ?></td>
            </tr>
            <?php endforeach; ?>

        </table>
    </div>

    <div class="card">
        <h3>Attendance Summary</h3>
        <p>Present: <?= $attendance['present'] ?? 0 ?></p>
        <p>Absent: <?= $attendance['absent'] ?? 0 ?></p>
        <p>Late: <?= $attendance['late_count'] ?? 0 ?></p>
        <p>Excused: <?= $attendance['excused'] ?? 0 ?></p>
    </div>

</div>
</div>

</body>
</html>