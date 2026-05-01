<?php
include 'student_config.php';
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 1;
$rawGrades = getGrades($pdo, $student_id);
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

    // Adjust depending on your DB values
    if ($period == '1st grading' || $period == 'q1') {
        $grades[$subject]['q1'] = $g['final_grade'];
    } elseif ($period == '2nd grading' || $period == 'q2') {
        $grades[$subject]['q2'] = $g['final_grade'];
    } elseif ($period == '3rd grading' || $period == 'q3') {
        $grades[$subject]['q3'] = $g['final_grade'];
    } elseif ($period == '4th grading' || $period == 'q4') {
        $grades[$subject]['q4'] = $g['final_grade'];
    }

    $grades[$subject]['remarks'] = $g['remarks'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal - Grades</title>
    <link rel="stylesheet" type="text/css" href="../style/style.css">
</head>

<body>

<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../style/logo.png" alt="Logo" class="logo">
</header>

<div class="container">
    <div class="sidebar">
        <a href="student.php?student_id=<?= $student_id ?>">Dashboard</a>
        <a href="grades.php?student_id=<?= $student_id ?>">Grades</a>
        <a href="activities.php?student_id=<?= $student_id ?>">Activities</a>
        <a href="report.php?student_id=<?= $student_id ?>">Report Card</a>
        <a href="classrecords.php?student_id=<?= $student_id ?>">Class Record</a>
        <a href="index.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h3>Grades</h3>

            <table>
                <tr>
                    <th>Subject</th>
                    <th>1st Quarter</th>
                    <th>2nd Quarter</th>
                    <th>3rd Quarter</th>
                    <th>4th Quarter</th>
                    <th>Remarks</th>
                </tr>

                <?php if (!empty($grades)): ?>
                    <?php foreach ($grades as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['subject_name']) ?></td>
                        <td><?= htmlspecialchars($g['q1']) ?></td>
                        <td><?= htmlspecialchars($g['q2']) ?></td>
                        <td><?= htmlspecialchars($g['q3']) ?></td>
                        <td><?= htmlspecialchars($g['q4']) ?></td>
                        <td><?= htmlspecialchars($g['remarks']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No grades found.</td>
                    </tr>
                <?php endif; ?>

            </table>
        </div>
    </div>
</div>

</body>
</html>