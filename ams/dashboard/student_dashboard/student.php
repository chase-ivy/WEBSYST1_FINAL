<?php
include 'student_config.php';

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


    <div class="sidebar">
        <a href="student.php">Dashboard</a>
        <a href="grades.php">Grades</a>
        <a href="activities.php">Activities</a>
        <a href="report.php">Report Card</a>
        <a href="classrecords.php">Class Record</a>
        <a href="../login/index.php">Logout</a>
    </div>


    <div class="content">
    <h3>Welcome <?= $student['first_name'] . ' ' . $student['last_name'] ?></h3>

        <div class="card">
            <h3>Recorded Grades</h3>
            <table>
                <tr>
                    <th>Subject</th>
                    <th>Grade</th>
                </tr>
                <tr>
                    <td>Math</td>
                    <td>90</td>
                </tr>
                <tr>
                    <td>English</td>
                    <td>88</td>
                </tr>
            </table>
        </div>


        <div class="card">
            <h3>Activities</h3>
            <table>
                <tr>
                    <th>Activity</th>
                    <th>Score</th>
                </tr>
                <tr>
                    <td>Science Project</td>
                    <td>Completed</td>
                </tr>
                <tr>
                    <td>Math Quiz</td>
                    <td>Pending</td>
                </tr>
            </table>
        </div>


        <div class="card">
            <h3>Report Card</h3>
            <p>General Average: <strong>89.5</strong></p>
            <p>Remarks: Passed</p>
        </div>


        <div class="card">
            <h3>Class Record</h3>
            <table>
                <tr>
                    <th>Quarter</th>
                    <th>Average</th>
                </tr>
                <tr>
                    <td>1st</td>
                    <td>88</td>
                </tr>
                <tr>
                    <td>2nd</td>
                    <td>91</td>
                </tr>
            </table>
        </div>

</body>
</html>