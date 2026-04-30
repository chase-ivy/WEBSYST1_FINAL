<?php
include "config.php";

   //AUTO LOAD STUDENT (TEST)
$stmt = $pdo->query("SELECT student_id FROM students LIMIT 1");
$studentRow = $stmt->fetch();

if (!$studentRow) {
    die("No students found.");
}

$student_id = $studentRow['student_id'];

   //FETCH DATA
$student = getStudentInfo($pdo, $student_id);
$activities = getActivities($pdo, $student_id);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activities</title>
    <link rel="stylesheet" type="text/css" href="../style/style.css">
</head>

<body>

<header>
    <h2>Gibraltar AMS - Student Portal</h2>
    <img src="../style/logo.png" alt="Logo" class="logo">
</header>

<div class="container">


    <div class="sidebar">
        <a href="student.php">Dashboard</a>
        <a href="grades.php">Grades</a>
        <a href="activities.php">Activities</a>
        <a href="report.php">Report Card</a>
        <a href="classrecords.php">Class Record</a>
        <a href="../login/index.php">Logout</a>
    </div>

    <div class="content">

        <div class="card">

            <h3>Activities</h3>

            <table>
                <tr>
                    <th>Subject</th>
                    <th>Activity</th>
                    <th>Date</th>
                    <th>Score</th>
                    <th>Max Score</th>
                    <th>Status</th>
                </tr>

                <?php if (!empty($activities)): ?>
                    <?php foreach ($activities as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['subject_name']) ?></td>
                        <td><?= htmlspecialchars($a['activity_name']) ?></td>
                        <td><?= htmlspecialchars($a['activity_date']) ?></td>
                        <td><?= $a['score'] ?></td>
                        <td><?= $a['max_score'] ?></td>
                        <td class="<?= ($a['score'] >= ($a['max_score'] * 0.75)) ? 'high' : 'low' ?>">
                            <?= ($a['score'] >= ($a['max_score'] * 0.75)) ? 'Good' : 'Needs Improvement' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No activities found.</td>
                    </tr>
                <?php endif; ?>

            </table>

        </div>

    </div>
</div>

</body>
</html>