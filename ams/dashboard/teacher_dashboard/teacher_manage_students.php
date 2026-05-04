<?php
require_once __DIR__ . '/teacher_config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);

$teacher_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if (isset($_POST['updateStudent'])) {
    try {
        $result = updateStudent(
            $pdo,
            $_POST['student_id'],
            $_POST['fname'],
            $_POST['lname'],
            strtolower($_POST['sex'])
        );
        if ($result) {
            $success_message = 'Student information updated successfully!';
            header("Refresh:2; url=" . $_SERVER['PHP_SELF']);
        }
    } catch (Exception $e) {
        $error_message = 'Error updating student: ' . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    try {
        $result = deleteStudent($pdo, $_GET['delete']);
        if ($result) {
            $success_message = 'Student deleted successfully!';
            header("Refresh:2; url=" . $_SERVER['PHP_SELF']);
        }
    } catch (Exception $e) {
        $error_message = 'Error deleting student: ' . $e->getMessage();
    }
}

if (isset($_POST['toggleEnrollment'])) {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];
    
    try {
        if ($_POST['action'] === 'enroll') {
            enrollStudent($pdo, $student_id, $class_id);
            $success_message = 'Student enrolled successfully!';
        } else {
            removeEnrollment($pdo, $student_id, $class_id);
            $success_message = 'Student unenrolled successfully!';
        }
        header("Refresh:2; url=" . $_SERVER['PHP_SELF']);
    } catch (Exception $e) {
        $error_message = 'Error toggling enrollment: ' . $e->getMessage();
    }
}

$students = getStudentsWithEnrollments($pdo);
$classes = getAllClasses($pdo);
$user_id = 1;
$staff = getStaffInfo($pdo, $user_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" type="text/css" href="../../style/style.css">
</head>

<body>
    <header>
        <h2>Gibraltar AMS - Staff Portal</h2>
        <img src="../../style/logo.png" class="logo">
    </header>

    <div class="container">
        
    <?php renderTeacherSidebar('dashboard'); ?>
        <div class="content">
            <?php if ($success_message): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <div id="students" class="card section active">
                <div class="card-header">
                    <h3>Students</h3>
                </div>

                <table>
                    <tr>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Enrollment</th>
                        <th>Action</th>
                    </tr>

                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= $s['first_name'] . ' ' . $s['last_name'] ?></td>
                            <td><?= htmlspecialchars($s['grade_level'] ?? '-') ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">
                                    <input type="hidden" name="class_id" value="<?= $s['class_id'] ?? 1 ?>">
                                    <input type="hidden" name="toggleEnrollment" value="1">
                                    <?php if ($s['enrollment_id']): ?>
                                        <input type="hidden" name="action" value="unenroll">
                                        <button type="submit" class="btn" style="background-color: #28a745; color: white;">Enrolled</button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="enroll">
                                        <button type="submit" class="btn" style="background-color: #999; color: white;">Not Enrolled</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td>
                                <button class="btn" onclick="fillForm(
                                    '<?= $s['student_id'] ?>',
                                    '<?= $s['first_name'] ?>',
                                    '<?= $s['last_name'] ?>',
                                    '<?= $s['sex'] ?>'
                                )">Edit</button>
                                <button class="btn" onclick="window.location.href='?delete=<?= $s['student_id'] ?>'">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div id="editForm" class="card section" style="display: none;">
                <div class="card-header">
                    <h3>Edit Student</h3>
                </div>

                <form method="POST">
                    <input type="hidden" id="id" name="student_id">
                    
                    <label>First Name:</label>
                    <input type="text" id="fname" name="fname" required>
                    
                    <label>Last Name:</label>
                    <input type="text" id="lname" name="lname" required>
                    
                    <label>Sex:</label>
                    <select id="sex" name="sex" required>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    
                    <button class="btn" type="submit" name="updateStudent">Save Changes</button>
                    <button class="btn" type="button" onclick="show('students')" style="background-color: #6c757d;">Cancel</button>
                </form>
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

        function fillForm(id, f, l, s) {
            document.getElementById('id').value = id;
            document.getElementById('fname').value = f;
            document.getElementById('lname').value = l;
            document.getElementById('sex').value = s;

            show('editForm');
        }
    </script>
</body>
</html>
