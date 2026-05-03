
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

if (isset($_POST['saveGrades'])) {
    foreach ($_POST['grade'] as $enrollment_id => $grade) {
        updateGrade(
            $pdo,
            $enrollment_id,
            $_POST['period'],
            $grade,
            $_POST['remarks'][$enrollment_id]
        );
    }
}

$students = getAllStudents($pdo);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Grades</title>
</head>
<body>
    <header>
        <h2>Gibraltar AMS - Staff Portal</h2>
        <img src="../../style/logo.png" class="logo">
    </header>

    <div class="sidebar">
            <a href="manage_students.php" onclick="show('students')">Students</a>
            <a href="../../forms/enrollment_form/enrollment.php" onclick="show('enroll')">Enroll</a>
            <a href="teacher_dashboard.php" onclick="show('profile')">Profile</a>
            <a href="activities.php" onclick="show('activities')">Activities</a>
            <a href="subjects.php" onclick="show('subjects')">Subjects</a>
            <a href="scores.php" onclick="show('scores')">Scores</a>
            <a href="grades.php" onclick="show('grades')">Grades</a>
            <a href="attendance.php" onclick="show('attendance')">Attendance</a>
        </div>

    <div class="card">
        <h3>Grade Encoder</h3>

        <form method="POST">
            <label>Grading Period</label>
            <select name="period">
                <option>1st</option>
                <option>2nd</option>
                <option>3rd</option>
                <option>4th</option>
            </select>

            <table>
                <tr>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                </tr>

                <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= $s['first_name'] . ' ' . $s['last_name'] ?></td>
                        <td><input type="number" name="grade[<?= $s['student_id'] ?>]"></td>
                        <td><input type="text" name="remarks[<?= $s['student_id'] ?>]"></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <button class="btn" name="saveGrades">Save Grades</button>
        </form>
    </div>
</body>
</html>
````