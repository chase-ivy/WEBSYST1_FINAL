<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

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

$students = getStudentsWithEnrollments($pdo);
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
    <div class="container">
        <?php renderTeacherSidebar('dashboard'); ?>

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
                        <td>
                            <?php if ($s['enrollment_id']): ?>
                                <input type="number" name="grade[<?= $s['enrollment_id'] ?>]" min="0" max="100">
                            <?php else: ?>
                                <span style="color: #999;">Not enrolled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['enrollment_id']): ?>
                                <input type="text" name="remarks[<?= $s['enrollment_id'] ?>]">
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <button class="btn" name="saveGrades">Save Grades</button>
        </form>
    </div>
    </div>
</div>
</body>
</html>
