````php
<?php
require 'config.php';

if (isset($_POST['addActivity'])) {
    addActivity(
        $pdo,
        $_POST['class_id'],
        $_POST['name'],
        $_POST['max_score'],
        $_POST['date']
    );
}

$classes = getAllClasses($pdo);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Activities</title>
</head>
<body>
    <header>
        <h2>Gibraltar AMS - Staff Portal</h2>
        <img src="../../style/logo.png" class="logo">
    </header>
    <div class="container">
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
    <div class="content">
        <div class="card">
            <h3>Create Activity</h3>

            <form method="POST">
            <label>Class</label>
                <select name="class_id">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['class_id'] ?>">
                            <?= $c['subject_name'] . ' - ' . $c['section'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Activity Name</label>
                <input type="text" name="name" required>

                <label>Max Score</label>
                <input type="number" name="max_score" required>

                <label>Date</label>
                <input type="date" name="date" required>

                <button class="btn" name="addActivity">Create</button>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
````