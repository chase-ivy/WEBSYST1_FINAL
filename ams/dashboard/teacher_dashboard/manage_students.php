<?php
require 'config.php';

// UPDATE STUDENT
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

$students = getAllStudents($pdo);
$classes = getAllClasses($pdo);
$user_id = 1;
$staff = getStaffInfo($pdo, $user_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
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
            <div id="students" class="card section active">
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
                            <td><?= $s['first_name'] . ' ' . $s['last_name'] ?></td>
                            <td><?= $s['grade_level'] ?></td>
                            <td>
                                <button class="btn" onclick="fillForm(
                                    '<?= $s['first_name'] ?>',
                                    '<?= $s['last_name'] ?>',
                                    '<?= $s['grade_level'] ?>'
                                )">Edit</button>
                                <button>
                                    <a href="?delete=<?= $s['student_id'] ?>" class="btn">Delete</a>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        function show(id) {
            document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
            document.getElementById(id).style.display = 'block';
        }

        // default
        show('students');

        function fillForm(id, f, l, g, s) {
            document.getElementById('id').value = id;
            document.getElementById('fname').value = f;
            document.getElementById('lname').value = l;
            document.getElementById('grade').value = g;
            document.getElementById('sex').value = s;

            show('students');
        }
    </script>
</body>
</html>
