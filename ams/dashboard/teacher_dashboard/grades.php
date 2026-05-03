<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];
$classes = getTeacherClasses($pdo, $teacher_id);
$selectedPeriod = isset($_POST['period']) ? $_POST['period'] : '1st';
$selectedClass = isset($_POST['class_id']) ? intval($_POST['class_id']) : null;

if ($selectedClass === null && !empty($classes)) {
    $selectedClass = $classes[0]['class_id'];
}

if (isset($_POST['saveGrades'])) {
    foreach ($_POST['grade'] as $enrollment_id => $grade) {
        $computedRemarks = '';
        if (is_numeric($grade) && $grade !== '') {
            $computedRemarks = $grade >= 75 ? 'PASSED' : 'FAILED';
        }

        updateGrade(
            $pdo,
            $enrollment_id,
            $selectedPeriod,
            $grade,
            $computedRemarks
        );
    }
}

$students = getTeacherStudentEnrollments($pdo, $teacher_id, $selectedPeriod, $selectedClass);
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
            <label>Class / Subject</label>
            <select name="class_id" onchange="this.form.submit()">
                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['class_id'] ?>" <?= $selectedClass === intval($class['class_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($class['subject_name'] . ' — ' . $class['section']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Grading Period</label>
            <select name="period" onchange="this.form.submit()">
                <?php foreach (['1st', '2nd', '3rd', '4th'] as $period): ?>
                    <option value="<?= $period ?>" <?= $selectedPeriod === $period ? 'selected' : '' ?>><?= $period ?></option>
                <?php endforeach; ?>
            </select>

            <table>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                </tr>

                <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                        <td><?= htmlspecialchars($s['subject_name']) ?></td>
                        <td>
                            <input
                                type="number"
                                name="grade[<?= $s['enrollment_id'] ?>]"
                                min="0"
                                max="100"
                                value="<?= isset($s['final_grade']) ? htmlspecialchars($s['final_grade']) : '' ?>"
                            >
                        </td>
                        <td>
                            <input
                                type="text"
                                name="remarks[<?= $s['enrollment_id'] ?>]"
                                value="<?= isset($s['remarks']) ? htmlspecialchars($s['remarks']) : '' ?>"
                            >
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
