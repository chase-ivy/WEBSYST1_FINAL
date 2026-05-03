<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['teacher']);

$teacher_id = $_SESSION['user_id'];

if (isset($_POST['addSubject'])) {
    addSubject($pdo, $_POST['name'], $_POST['desc']);
}

if (isset($_POST['updateSubject'])) {
    updateSubject($pdo, $_POST['id'], $_POST['name'], $_POST['desc']);
}

if (isset($_GET['delete'])) {
    deleteSubject($pdo, intval($_GET['delete']));
    header("Location: subjects.php");
    exit();
}

if (isset($_POST['assignClass'])) {
    assignSubjectToClass(
        $pdo,
        $_POST['subject_id'],
        $_POST['grade_level'],
        $_POST['section'],
        $teacher_id
    );
}

$subjects = getSubjects($pdo);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subjects</title>
    <link rel="stylesheet" href="../../style/style.css">
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
    <a class="action-link" href="../../login/logout.php">Logout</a>
</header>

<div class="container">

    <?php renderTeacherSidebar('subjects'); ?>

    <div class="content">

        <div class="card">
            <h3>Add Subject</h3>

            <form method="POST">
                <label>Subject Name</label>
                <input type="text" name="name" required>

                <label>Description</label>
                <textarea name="desc"></textarea>

                <button class="btn" name="addSubject">Add Subject</button>
            </form>
        </div>

        <div class="card">
            <h3>Subjects</h3>

            <?php if (empty($subjects)): ?>
                <p>No subjects found.</p>
            <?php else: ?>

            <table>
                <tr>
                    <th>Subject</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($subjects as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['subject_name']) ?></td>
                        <td>
                            <a href="?delete=<?= $s['subject_id'] ?>" class="btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Edit Subject</h3>

            <label>Select Subject to Edit</label>
            <select id="edit_select" onchange="fillEditFromDropdown()">
                <option value="">-- Choose Subject --</option>
                <?php foreach ($subjects as $s): ?>
                    <option 
                        value="<?= $s['subject_id'] ?>"
                        data-name="<?= htmlspecialchars($s['subject_name'], ENT_QUOTES) ?>"
                        data-desc="<?= htmlspecialchars($s['description'], ENT_QUOTES) ?>"
                    >
                        <?= htmlspecialchars($s['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <form method="POST">
                <input type="hidden" name="id" id="edit_id">

                <label>Name</label>
                <input type="text" name="name" id="edit_name">

                <label>Description</label>
                <textarea name="desc" id="edit_desc"></textarea>

                <button class="btn" name="updateSubject">Update</button>
            </form>
        </div>

        <div class="card">
            <h3>Assign Subject to Class</h3>

            <label>Select Subject</label>
            <select id="assign_select" onchange="fillAssignFromDropdown()">
                <option value="">-- Choose Subject --</option>
                <?php foreach ($subjects as $s): ?>
                    <option 
                        value="<?= $s['subject_id'] ?>"
                        data-name="<?= htmlspecialchars($s['subject_name'], ENT_QUOTES) ?>"
                    >
                        <?= htmlspecialchars($s['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <form method="POST">
                <input type="hidden" name="subject_id" id="assign_id">

                <label>Subject</label>
                <input type="text" id="assign_name" disabled>

                <label>Grade Level</label>
                <input type="text" name="grade_level" required>

                <label>Section</label>
                <input type="text" name="section" required>

                <button class="btn" name="assignClass">Assign</button>
            </form>
        </div>

    </div>
</div>

<script>
function fillEditFromDropdown() {
    let select = document.getElementById("edit_select");
    let option = select.options[select.selectedIndex];

    if (!option.value) return;

    document.getElementById('edit_id').value = option.value;
    document.getElementById('edit_name').value = option.getAttribute('data-name');
    document.getElementById('edit_desc').value = option.getAttribute('data-desc');
}

function fillAssignFromDropdown() {
    let select = document.getElementById("assign_select");
    let option = select.options[select.selectedIndex];

    if (!option.value) return;

    document.getElementById('assign_id').value = option.value;
    document.getElementById('assign_name').value = option.getAttribute('data-name');
}
</script>

</body>
</html>