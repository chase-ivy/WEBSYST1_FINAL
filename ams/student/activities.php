<?php
include "config.php";

$student_id = 1;

$stmt = $pdo->prepare("
    SELECT 
        s.subject_name,
        MAX(CASE WHEN g.quarter = 1 THEN g.grade END) AS q1,
        MAX(CASE WHEN g.quarter = 2 THEN g.grade END) AS q2,
        MAX(CASE WHEN g.quarter = 3 THEN g.grade END) AS q3,
        MAX(CASE WHEN g.quarter = 4 THEN g.grade END) AS q4
    FROM grades g
    JOIN subjects s ON g.subject_id = s.id
    WHERE g.student_id = ?
    GROUP BY s.subject_name
");
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll();
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
        <a href="student.php">Dashboard</a>
        <a href="grades.php">Grades</a>
        <a href="activities.php">Activities</a>
        <a href="report.php">Report Card</a>
        <a href="classrecords.php">Class Record</a>
        <a href="index.php">Logout</a>
    </div>


    <div class="content">
        <div class="card">
            <h3>Activities</h3>
            <table>
                <tr>
                    <th>Subject</th>
                    <th>Activity Name</th>
                    <th>Score</th>
                </tr>
                <?php foreach ($grades as $g): ?>
                <tr>
                    <td><?= $g['subject_name'] ?></td>
                    <td><?= $g['q1'] ?? '-' ?></td>
                    <td><?= $g['q2'] ?? '-' ?></td>
                    <td><?= $g['q3'] ?? '-' ?></td>
                    <td><?= $g['q4'] ?? '-' ?></td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>
    </div>
</div>

</body>
</html>