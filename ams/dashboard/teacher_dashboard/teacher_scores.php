<?php
require_once __DIR__ . '/teacher_config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);

$teacher_id = $_SESSION['user_id'];

   //GET ACTIVITIES (FIXED)
$stmt = $pdo->prepare("
    SELECT 
        a.activity_id,
        a.title,
        a.max_score
    FROM activities a
    JOIN class_subjects cs ON a.class_subject_id = cs.class_subject_id
    WHERE cs.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedActivity = $_GET['activity_id'] ?? null;
$maxScore = null;
$classStudents = [];
$scores = [];


   //LOAD STUDENTS + SCORES
if ($selectedActivity) {

    // get max score
    foreach ($activities as $a) {
        if ($a['activity_id'] == $selectedActivity) {
            $maxScore = $a['max_score'];
            break;
        }
    }

    /* GET STUDENTS IN CLASS (FIXED PROPER RELATION) */
    $stmt = $pdo->prepare("
        SELECT 
            cs.class_student_id,
            s.first_name,
            s.last_name
        FROM class_students cs
        JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
        JOIN students s ON e.student_id = s.student_id
        JOIN class_subjects csub ON cs.class_id = csub.class_id
        WHERE csub.teacher_id = ?
    ");
    $stmt->execute([$teacher_id]);
    $classStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* GET SCORES (FIXED TABLE) */
    $stmt = $pdo->prepare("
        SELECT class_student_id, score
        FROM activity_scores
        WHERE activity_id = ?
    ");
    $stmt->execute([$selectedActivity]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $scores[$row['class_student_id']] = $row['score'];
    }
}

   //SAVE SCORES (FIXED)
if (isset($_POST['saveScores'])) {

    $activity_id = $_POST['activity_id'];
    $max_score = $_POST['max_score'];

    foreach ($_POST['score'] as $class_student_id => $score) {

        if ($score === '') continue;
        if ($score > $max_score) continue;

        addOrUpdateStudentScore(
            $pdo,
            $activity_id,
            $class_student_id,
            $score
        );
    }

    header("Location: scores.php?activity_id=" . $activity_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../../style/style.css">
    <title>Scores</title>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="container">

<?php renderTeacherSidebar('scores'); ?>

<div class="content">

<div class="card">
    <h3>Select Activity</h3>

    <form method="GET">
        <select name="activity_id" onchange="this.form.submit()" required>
            <option value="">-- Select Activity --</option>
            <?php foreach ($activities as $a): ?>
                <option value="<?= $a['activity_id'] ?>"
                    <?= ($selectedActivity == $a['activity_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($selectedActivity): ?>

<div class="card">
    <h3>Score Entry</h3>

    <p><strong>Max Score:</strong> <?= $maxScore ?></p>

    <form method="POST">

        <input type="hidden" name="activity_id" value="<?= $selectedActivity ?>">
        <input type="hidden" name="max_score" value="<?= $maxScore ?>">

        <table>
            <tr>
                <th>Student</th>
                <th>Current / Max</th>
                <th>New Score</th>
            </tr>

            <?php foreach ($classStudents as $s): ?>
                <?php $currentScore = $scores[$s['class_student_id']] ?? null; ?>

                <tr>
                    <td>
                        <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                    </td>

                    <td>
                        <?= $currentScore !== null ? $currentScore : '0' ?> / <?= $maxScore ?>
                    </td>

                    <td>
                        <input 
                            type="number"
                            name="score[<?= $s['class_student_id'] ?>]"
                            value="<?= $currentScore !== null ? $currentScore : '' ?>"
                            max="<?= $maxScore ?>"
                            min="0"
                            style="width:80px;"
                        >
                    </td>
                </tr>

            <?php endforeach; ?>
        </table>

        <button class="btn" name="saveScores">Save Scores</button>

    </form>
</div>

<?php endif; ?>

</div>
</div>

</body>
</html>