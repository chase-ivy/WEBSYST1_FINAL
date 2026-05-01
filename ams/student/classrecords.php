<?php
include "config.php";

   //AUTO LOAD STUDENT
$stmt = $pdo->query("SELECT student_id FROM students LIMIT 1");
$row = $stmt->fetch();

if (!$row) {
    die("No students found.");
}

$student_id = $row['student_id'];

   //FETCH DATA
$student = getStudentInfo($pdo, $student_id);
$attendance = getAttendance($pdo, $student_id);
$attendanceRecords = getAttendanceRecords($pdo, $student_id); // NEW
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Record</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

<header>
    <h2>Gibraltar AMS - Class Record</h2>
    <img src="../style/logo.png" class="logo">
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

            <h3>Attendance Summary</h3>

            <div class="student-info">
                <h3><?= $student['first_name'] . ' ' . $student['last_name'] ?></h3>
                <h3>Grade Level: <?= $student['grade_level'] ?></h3>
            </div>

            <table>
                <tr>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Excused</th>
                </tr>
                <tr>
                    <td><?= $attendance['present'] ?? 0 ?></td>
                    <td><?= $attendance['absent'] ?? 0 ?></td>
                    <td><?= $attendance['late_count'] ?? 0 ?></td>
                    <td><?= $attendance['excused'] ?? 0 ?></td>
                </tr>
            </table>

        </div>

        <div class="card">

            <h3>Attendance Records</h3>

            <table>
                <tr>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>

                <?php if (!empty($attendanceRecords)): ?>
                    <?php foreach ($attendanceRecords as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['subject_name']) ?></td>
                        <td><?= htmlspecialchars($a['date']) ?></td>
                        <td class="
                            <?= $a['status'] == 'Present' ? 'text-success' : '' ?>
                            <?= $a['status'] == 'Absent' ? 'text-danger' : '' ?>
                            <?= $a['status'] == 'Late' ? 'text-warning' : '' ?>
                        ">
                            <?= $a['status'] ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No attendance records found.</td>
                    </tr>
                <?php endif; ?>

            </table>

        </div>

    </div>
</div>

</body>
</html>