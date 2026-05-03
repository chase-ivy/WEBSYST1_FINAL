<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT a.activity_id, a.activity_name, a.max_score
    FROM activities a
    JOIN classes c ON a.class_id = c.class_id
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$activities = $stmt->fetchAll();

$selectedActivity = $_GET['activity_id'] ?? null;
$maxScore = null;
$enrollments = [];
$scores = [];

if ($selectedActivity) {

    foreach ($activities as $a) {
        if ($a['activity_id'] == $selectedActivity) {
            $maxScore = $a['max_score'];
            break;
        }
    }

    $stmt = $pdo->prepare("
        SELECT 
            e.enrollment_id,
            s.first_name,
            s.last_name
        FROM enrollments e
        JOIN students s ON e.student_id = s.student_id
        JOIN classes c ON e.class_id = c.class_id
        JOIN activities a ON a.class_id = c.class_id
        WHERE a.activity_id = ?
    ");
    $stmt->execute([$selectedActivity]);
    $enrollments = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT enrollment_id, score
        FROM student_activity_scores
        WHERE activity_id = ?
    ");
    $stmt->execute([$selectedActivity]);

    foreach ($stmt->fetchAll() as $row) {
        $scores[$row['enrollment_id']] = $row['score'];
    }
}

if (isset($_POST['saveScores'])) {

    $activity_id = $_POST['activity_id'];
    $max_score = $_POST['max_score'];

    foreach ($_POST['score'] as $enrollment_id => $score) {

        if ($score === '') continue;

        if ($score > $max_score) {
            continue; 
        }

        addOrUpdateStudentScore(
            $pdo,
            $activity_id,
            $enrollment_id,
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
                    <?= htmlspecialchars($a['activity_name']) ?>
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

            <?php foreach ($enrollments as $e): ?>
                <?php $currentScore = $scores[$e['enrollment_id']] ?? null; ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                    </td>
                    <td>
                        <span style="display:inline-block; min-width:120px;">
                            <?= $currentScore !== null ? htmlspecialchars($currentScore) : '0' ?> / <?= htmlspecialchars($maxScore) ?>
                        </span>
                    </td>
                    <td>
                        <input 
                            type="number"
                            name="score[<?= $e['enrollment_id'] ?>]"
                            value="<?= $currentScore !== null ? htmlspecialchars($currentScore) : '' ?>"
                            max="<?= $maxScore ?>"
                            min="0"
                            style="width:80px; margin-left:12px;"
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