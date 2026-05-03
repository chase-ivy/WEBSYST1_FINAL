<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

$staff = getStaffInfo($pdo, $teacher_id);

$stmt = $pdo->prepare("
    SELECT c.class_id, s.subject_name, c.section
    FROM classes c
    JOIN subjects s ON c.subject_id = s.subject_id
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$classes = $stmt->fetchAll();

$totalSections = count($classes);

$subjects = array_unique(array_column($classes, 'subject_name'));

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.student_id) as total_students
    FROM enrollments e
    JOIN classes c ON e.class_id = c.class_id
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$totalStudents = $stmt->fetch()['total_students'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_activities
    FROM activities a
    JOIN classes c ON a.class_id = c.class_id
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$totalActivities = $stmt->fetch()['total_activities'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../../style/style.css">
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="container">
<?php renderTeacherSidebar('dashboard'); ?>

    <div class="content">

        <div class="card">
            <h3>Welcome, <?= htmlspecialchars($staff['username']) ?> 👋</h3>
        </div>

        <!-- SUMMARY -->
        <div class="card">
            <h3>Dashboard Summary</h3>

            <div class="grid">

                <div class="card">
                    <h4>Total Students</h4>
                    <p><?= $totalStudents ?></p>
                </div>

                <div class="card">
                    <h4>Total Sections</h4>
                    <p><?= $totalSections ?></p>
                </div>

                <div class="card">
                    <h4>Total Activities</h4>
                    <p><?= $totalActivities ?></p>
                </div>

            </div>
        </div>

        <!-- SUBJECTS -->
        <div class="card">
            <h3>Subjects Handled</h3>

            <ul>
                <?php foreach ($subjects as $subject): ?>
                    <li><?= htmlspecialchars($subject) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- CLASSES -->
        <div class="card">
            <h3>Your Sections</h3>

            <table>
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
                </tr>

                <?php foreach ($classes as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['subject_name']) ?></td>
                        <td><?= htmlspecialchars($c['section']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>