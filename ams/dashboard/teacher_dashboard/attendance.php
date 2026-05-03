<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

if (isset($_POST['saveAttendance'])) {
    $date = $_POST['date'];

    foreach ($_POST['attendance'] as $enrollment_id => $status) {
        addOrUpdateAttendance($pdo, $enrollment_id, $date, $status);
    }
}

$class_id = isset($_POST['class_id']) ? $_POST['class_id'] : 1;

$classes = getAllClasses($pdo);
$students = getEnrollmentsByClass($pdo, $class_id);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
    <title>Attendance</title>
</head>
<body>
    <header>
        <h2>Gibraltar AMS - Staff Portal</h2>
        <img src="../../style/logo.png" class="logo">
    </header>
    <div class="container">
        <?php renderTeacherSidebar('dashboard'); ?>

        <div class="content">
            <div class="card">
                <h3>Attendance</h3>

                <form method="POST">
                    <label>Select Class</label>
                    <select name="class_id" onchange="this.form.submit()">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['class_id'] ?>" <?= ($class_id == $c['class_id']) ? 'selected' : '' ?>>
                                <?= $c['subject_name'].' - '.$c['section'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <br>

                <form method="POST">
                    <input type="hidden" name="class_id" value="<?= $class_id ?>">

                    <label>Date</label>
                    <input type="date" name="date" required>

                    <table>
                        <tr>
                            <th>Student</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                        </tr>

                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?= $s['first_name'].' '.$s['last_name'] ?></td>
                                <td><input type="radio" name="attendance[<?= $s['enrollment_id'] ?>]" value="Present" required></td>
                                <td><input type="radio" name="attendance[<?= $s['enrollment_id'] ?>]" value="Absent"></td>
                                <td><input type="radio" name="attendance[<?= $s['enrollment_id'] ?>]" value="Late"></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <br>
                    <button class="btn" name="saveAttendance">Save Attendance</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
````