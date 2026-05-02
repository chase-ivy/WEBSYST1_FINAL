<?php
include 'student_config.php';
require_once __DIR__ . '/student_nav.php';


   //AUTO LOAD STUDENT to fix since no sessions
$stmt = $pdo->query("SELECT student_id FROM students LIMIT 1");
$row = $stmt->fetch();

if (!$row) {
    die("No students found.");
}

$student_id = $row['student_id'];


//FETCH DATA
$student = getStudentInfo($pdo, $student_id);
$rawGrades = getGrades($pdo, $student_id);
$report = getReportCard($pdo, $student_id);

//TRANSFORM SUBJECT GRADES
$grades = [];

foreach ($rawGrades as $g) {
    $subject = $g['subject_name'];
    $period = strtolower($g['grading_period']);

    if (!isset($grades[$subject])) {
        $grades[$subject] = [
            'subject_name' => $subject,
            'q1' => '-',
            'q2' => '-',
            'q3' => '-',
            'q4' => '-',
            'remarks' => '-'
        ];
    }

    if ($period == '1st grading' || $period == 'q1') $grades[$subject]['q1'] = $g['final_grade'];
    if ($period == '2nd grading' || $period == 'q2') $grades[$subject]['q2'] = $g['final_grade'];
    if ($period == '3rd grading' || $period == 'q3') $grades[$subject]['q3'] = $g['final_grade'];
    if ($period == '4th grading' || $period == 'q4') $grades[$subject]['q4'] = $g['final_grade'];

    $grades[$subject]['remarks'] = $g['remarks'];
}
//TRANSFORM AVERAGES
$averages = [
    'q1' => '-',
    'q2' => '-',
    'q3' => '-',
    'q4' => '-'
];

foreach ($report as $r) {
    $period = strtolower($r['grading_period']);

    if ($period == '1st grading' || $period == 'q1') $averages['q1'] = $r['general_average'];
    if ($period == '2nd grading' || $period == 'q2') $averages['q2'] = $r['general_average'];
    if ($period == '3rd grading' || $period == 'q3') $averages['q3'] = $r['general_average'];
    if ($period == '4th grading' || $period == 'q4') $averages['q4'] = $r['general_average'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
</head>

<body>

<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../../style/logo.png" alt="Logo" class="logo">
</header>

<div class="container">

    <?php renderStudentSidebar('report', $student_id); ?>


    <div class="content">

        <div class="card">

            <div class="student-info">
                <h3>Name: <?= $student['first_name'] . ' ' . $student['last_name'] ?></h3>
                <h3>Grade Level: <?= $student['grade_level'] ?></h3>
            </div>

            <h3>Report Card</h3>

            <table>
                <tr>
                    <th>Subject</th>
                    <th>Q1</th>
                    <th>Q2</th>
                    <th>Q3</th>
                    <th>Q4</th>
                    <th>Remarks</th>
                </tr>

                <?php foreach ($grades as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['subject_name']) ?></td>
                    <td><?= $g['q1'] ?></td>
                    <td><?= $g['q2'] ?></td>
                    <td><?= $g['q3'] ?></td>
                    <td><?= $g['q4'] ?></td>
                    <td><?= $g['remarks'] ?></td>
                </tr>
                <?php endforeach; ?>

                <tr class="average-row">
                    <td>General Average</td>
                    <td><?= $averages['q1'] ?></td>
                    <td><?= $averages['q2'] ?></td>
                    <td><?= $averages['q3'] ?></td>
                    <td><?= $averages['q4'] ?></td>
                    <td>-</td>
                </tr>
            </table>
        </div>
            <button class="btn">Print Report Card</button>
    </div>
</div>

</body>
</html>