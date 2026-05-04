<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <link rel="stylesheet" href="../../style/style.css">

    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
        .card { background: #fff; padding: 15px; margin-bottom: 15px; border-radius: 6px; }
        input, select { padding: 6px; margin: 5px 0; width: 100%; }
        button { padding: 6px 10px; cursor: pointer; }

        label { display:block; margin:4px 0; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Staff Portal</h2>
</header>

<div class="container">

<?php renderTeacherSidebar('students'); ?>

<div class="content">

<!-- ================= CREATE ================= -->
<div class="card">
    <h3>Add Student</h3>

    <input id="lrn" placeholder="LRN">
    <input id="fname" placeholder="First Name">
    <input id="lname" placeholder="Last Name">
    <input id="mname" placeholder="Middle Name">
    <input type="date" id="bdate">

    <select id="sex">
        <option value="">Sex</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
    </select>

    <input id="pob" placeholder="Place of Birth">

    <button onclick="createStudent()">Create Student</button>
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
                    
                    <label>Grade Level:</label>
                    <input type="text" id="grade" name="grade" required>
                    
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

        function fillForm(id, f, l, g, s) {
            document.getElementById('id').value = id;
            document.getElementById('fname').value = f;
            document.getElementById('lname').value = l;
            document.getElementById('grade').value = g;
            document.getElementById('sex').value = s;

            show('editForm');
        }
    </script>
</body>
</html>