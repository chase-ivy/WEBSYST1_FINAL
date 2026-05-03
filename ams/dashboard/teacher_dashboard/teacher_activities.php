<?php
require_once __DIR__ . '/teacher_config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

if (isset($_POST['addActivity'])) {
    addActivity(
        $pdo,
        $_POST['class_id'],
        $_POST['name'],
        $_POST['max_score'],
        $_POST['date']
    );
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM activities WHERE activity_id = ?");
    $stmt->execute([$_GET['delete']]);

    header("Location: activities.php");
    exit();
}

$classes = getAllClasses($pdo);

$stmt = $pdo->prepare("
    SELECT 
        a.activity_id,
        a.activity_name,
        a.max_score,
        a.activity_date,
        c.section,
        s.subject_name
    FROM activities a
    JOIN classes c ON a.class_id = c.class_id
    JOIN subjects s ON c.subject_id = s.subject_id
    WHERE c.teacher_id = ?
    ORDER BY a.activity_date DESC
");
$stmt->execute([$teacher_id]);

$activities = $stmt->fetchAll();
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

  <?php renderTeacherSidebar('dashboard'); ?>
<div class="content">

<div class="card">
    <h3>Create Activity</h3>

    <form method="POST">

        <label>Class</label>
        <select name="class_id" required>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['class_id'] ?>">
                    <?= htmlspecialchars($c['subject_name'] . ' - ' . $c['section']) ?>
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

<div class="card">
    <h3>Activities List</h3>

    <?php if (empty($activities)): ?>
        <p>No activities found.</p>
    <?php else: ?>

    <table>
        <tr>
            <th>Activity</th>
            <th>Subject</th>
            <th>Section</th>
            <th>Date</th>
            <th>Max Score</th>
            <th>Action</th>
        </tr>

        <?php foreach ($activities as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['activity_name']) ?></td>
                <td><?= htmlspecialchars($a['subject_name']) ?></td>
                <td><?= htmlspecialchars($a['section']) ?></td>
                <td><?= htmlspecialchars($a['activity_date']) ?></td>
                <td><?= $a['max_score'] ?></td>
                <td>
                    <a class="btn" href="scores.php?activity_id=<?= $a['activity_id'] ?>">
                        Scores
                    </a>

                    <a class="btn" href="?delete=<?= $a['activity_id'] ?>"
                       onclick="return confirm('Delete this activity?')">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

    <?php endif; ?>
</div>

</div>
</div>

</body>
</html>