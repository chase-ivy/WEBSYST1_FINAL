<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Classes</title>
    <link rel="stylesheet" href="../../style/style.css">

    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 4px;
        }

        input, select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .card {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f5f5f5;
        }

        .btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary { background: #2196F3; color: white; }
        .btn-success { background: #4CAF50; color: white; }
        .btn-danger { background: #f44336; color: white; }

        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .alert-success { background: #d4edda; }
        .alert-error { background: #f8d7da; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Manage Classes</h2>
</header>

<div class="container">
<?php renderTeacherSidebar('classes'); ?>

<div class="content">

    <!-- CREATE CLASS -->
    <div class="card">
        <h3>Create Class</h3>

        <div class="form-grid">
            <div class="form-group">
                <label>School Year</label>
                <input type="text" id="school_year" placeholder="2025-2026">
            </div>

            <div class="form-group">
                <label>Grade Level</label>
                <input type="text" id="grade_level">
            </div>

            <div class="form-group">
                <label>Section</label>
                <input type="text" id="section">
            </div>
        </div>

        <button class="btn btn-success" onclick="createClass()">Create</button>
    </div>

    <!-- CLASS LIST -->
    <div class="card">
        <h3>Your Classes</h3>
        <div id="classList">Loading...</div>
    </div>

    <!-- EDIT -->
    <div class="card" id="editCard" style="display:none;">
        <h3>Edit Class</h3>

        <input type="hidden" id="edit_id">

        <div class="form-group">
            <label>School Year</label>
            <input type="text" id="edit_school_year">
        </div>

        <div class="form-group">
            <label>Grade Level</label>
            <input type="text" id="edit_grade_level">
        </div>

        <div class="form-group">
            <label>Section</label>
            <input type="text" id="edit_section">
        </div>

        <button class="btn btn-primary" onclick="updateClass()">Update</button>
        <button class="btn" onclick="hideEdit()">Cancel</button>
    </div>

</div>
</div>

<script src="../../api/client.js"></script>
<script>

let classes = [];

// LOAD TEACHER CLASSES
async function loadClasses() {
    try {
        const res = await API.classes.getTeacherClasses();

        if (res.success) {
            classes = res.data;
            renderClasses();
        }
    } catch (err) {
        console.error(err);
        document.getElementById('classList').innerHTML = "Failed to load classes";
    }
}

function renderClasses() {

    const container = document.getElementById('classList');

    if (classes.length === 0) {
        container.innerHTML = "No classes found.";
        return;
    }

    container.innerHTML = `
        <table>
            <tr>
                <th>School Year</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Subject</th>
                <th>Actions</th>
            </tr>

            ${classes.map(c => `
                <tr>
                    <td>${c.school_year}</td>
                    <td>${c.grade_level}</td>
                    <td>${c.section}</td>
                    <td>${c.subject}</td>
                    <td>
                        <button class="btn btn-primary" onclick='editClass(${JSON.stringify(c)})'>Edit</button>
                        <button class="btn btn-danger" onclick="deleteClass(${c.class_id})">Delete</button>
                    </td>
                </tr>
            `).join('')}
        </table>
    `;
}

// CREATE
async function createClass() {

    const data = {
        school_year: document.getElementById('school_year').value,
        grade_level: document.getElementById('grade_level').value,
        section: document.getElementById('section').value
    };

    try {
        const res = await API.classes.create(data);

        if (res.success) {
            loadClasses();
        }
    } catch (err) {
        console.error(err);
        alert("Failed to create class");
    }
}

// EDIT
function editClass(c) {
    document.getElementById('editCard').style.display = 'block';

    document.getElementById('edit_id').value = c.class_id;
    document.getElementById('edit_school_year').value = c.school_year;
    document.getElementById('edit_grade_level').value = c.grade_level;
    document.getElementById('edit_section').value = c.section;
}

function hideEdit() {
    document.getElementById('editCard').style.display = 'none';
}

// UPDATE
async function updateClass() {

    const id = document.getElementById('edit_id').value;

    const data = {
        school_year: document.getElementById('edit_school_year').value,
        grade_level: document.getElementById('edit_grade_level').value,
        section: document.getElementById('edit_section').value
    };

    try {
        const res = await API.classes.update(id, data);

        if (res.success) {
            hideEdit();
            loadClasses();
        }
    } catch (err) {
        console.error(err);
        alert("Failed to update");
    }
}

async function deleteClass(id) {

    if (!confirm("Delete this class?")) return;

    try {
        const res = await API.call('classes', 'delete', { class_id: id }, 'POST');

        if (res.success) {
            loadClasses();
        }
    } catch (err) {
        console.error(err);
        alert("Delete failed");
    }
}

// INIT
loadClasses();

</script>

</body>
</html>
