<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

if (isset($_POST['addSubject'])) {
    addSubject($pdo, $_POST['name'], $_POST['desc']);
}

$subjects = getSubjects($pdo);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Subjects</title>
</head>
<body>
    <header>
        <h2>Gibraltar AMS - Staff Portal</h2>
        <img src="../../style/logo.png" class="logo">
    </header>

    <div class="sidebar">
            <a href="manage_students.php" onclick="show('students')">Students</a>
            <a href="././forms/enrollment_form/enrollment.php" onclick="show('enroll')">Enroll</a>
            <a href="teacher_dashboard.php" onclick="show('profile')">Profile</a>
            <a href="activities.php" onclick="show('activities')">Activities</a>
            <a href="subjects.php" onclick="show('subjects')">Subjects</a>
            <a href="scores.php" onclick="show('scores')">Scores</a>
            <a href="grades.php" onclick="show('grades')">Grades</a>
            <a href="attendance.php" onclick="show('attendance')">Attendance</a>
        </div>

    <div class="card">
        <h3>Subjects</h3>

        <form method="POST">
            <label>Subject Name</label>
            <input type="text" name="name" required>

            <label>Description</label>
            <textarea name="desc"></textarea>

            <button class="btn" name="addSubject">Add Subject</button>
        </form>

        <hr>

        <ul>
            <?php foreach ($subjects as $s): ?>
                <li><?= $s['subject_name'] ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
````