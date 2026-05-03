<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

if (isset($_GET['delete'])) {
    $student_id = intval($_GET['delete']);
    deleteStudent($pdo, $student_id);
    header("Location: teacher_dashboard.php");
    exit();
}

if (isset($_POST['updateStudent'])) {
    updateStudent(
        $pdo,
        $_POST['student_id'],
        $_POST['fname'],
        $_POST['lname'],
        $_POST['grade'],
        $_POST['sex']
    );
}

if (isset($_POST['enroll'])) {
    enrollStudent($pdo, $_POST['student_id'], $_POST['class_id']);
}

if (isset($_POST['updateProfile'])) {
    updateStaffInfo(
        $pdo,
        $_SESSION['user_id'],
        $_POST['email'] 
    );
}

$students = getAllStudents($pdo);
$classes = getAllClasses($pdo);
$user_id = $_SESSION['user_id'];
$staff = getStaffInfo($pdo, $user_id);
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
    <a class="action-link" href="../../login/logout.php">Logout</a>
</header>

<div class="container">

    <div class="sidebar">
        <a href="#" onclick="show('students')">Students</a>
        <a href="../../forms/enrollment_form/enrollment.php">Enroll</a>
        <a href="#" onclick="show('profile')">Profile</a>
        <a href="activities.php">Activities</a>
        <a href="subjects.php">Subjects</a>
        <a href="scores.php">Scores</a>
        <a href="grades.php">Grades</a>
        <a href="attendance.php">Attendance</a>
    </div>

    <div class="content">

        <div id="students" class="card section">
            <div class="card-header">
                <h3>Students</h3>
            </div>

            <table>
                <tr>
                    <th>Name</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($students as $s): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                        </td>
                        <td><?= htmlspecialchars($s['grade_level']) ?></td>
                        <td>
                            <a href="?delete=<?= $s['student_id'] ?>" class="btn">Delete</a>

                            <button class="btn" onclick="fillForm(
                                '<?= $s['student_id'] ?>',
                                '<?= $s['first_name'] ?>',
                                '<?= $s['last_name'] ?>',
                                '<?= $s['grade_level'] ?>',
                                '<?= $s['sex'] ?>'
                            )">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div id="profile" class="card section" style="display:none;">
            <h3>My Profile</h3>

            <form method="POST">
                <label>Username</label>
                <input type="text" value="<?= htmlspecialchars($staff['username']) ?>" disabled>

                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($staff['email']) ?>">

                <button class="btn" name="updateProfile">Update</button>
            </form>
        </div>

    </div>
</div>

<script>
function show(id) {
    document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
    document.getElementById(id).style.display = 'block';
}

// default view
show('students');

function fillForm(id, f, l, g, s) {
    console.log(id, f, l, g, s);
}
</script>

</body>
</html>