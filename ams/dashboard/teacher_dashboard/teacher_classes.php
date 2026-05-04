<?php
require_once __DIR__ . '/teacher_config.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Activities</title>
    <link rel="stylesheet" href="../../style/style.css">

    <style>
        .activity-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 14px;
        }

        input, select, textarea {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
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
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }

        .btn {
            padding: 6px 10px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }

        .btn-primary { background: #2196F3; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-success { background: #4CAF50; color: white; }

        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Activities</h2>
</header>

<div class="container">

<?php renderTeacherSidebar('activities'); ?>

<div class="content">

    <!-- CLASS SELECT -->
    <div class="card">
        <h3>Select Class</h3>

        <select id="classSelect">
            <option value="">-- Choose Class --</option>
        </select>
    </div>

    <!-- CREATE ACTIVITY -->
    <div class="card">
        <h3>Create Activity</h3>

        <div class="activity-controls">
            <div class="form-group">
                <label>Title</label>
                <input type="text" id="title">
            </div>

            <div class="form-group">
                <label>Due Date</label>
                <input type="date" id="due_date">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea id="description" rows="3"></textarea>
        </div>

        <button class="btn btn-success" onclick="createActivity()">Add Activity</button>
    </div>

    <!-- LIST -->
    <div class="card">
        <h3>Activities</h3>
        <div id="activityList">Select a class...</div>
    </div>

    <!-- EDIT -->
    <div class="card" id="editCard" style="display:none;">
        <h3>Edit Activity</h3>

        <input type="hidden" id="edit_id">

        <div class="form-group">
            <label>Title</label>
            <input type="text" id="edit_title">
        </div>

        <div class="form-group">
            <label>Due Date</label>
            <input type="date" id="edit_due_date">
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea id="edit_description" rows="3"></textarea>
        </div>

        <button class="btn btn-primary" onclick="updateActivity()">Update</button>
        <button class="btn" onclick="document.getElementById('editCard').style.display='none'">Cancel</button>
    </div>

</div>
</div>

<script>

let currentClassId = null;

   //LOAD CLASSES
async function loadClasses() {

    const res = await fetch('../../api/classes.api.php?action=teacher_classes');
    const json = await res.json();

    if (!json.success) return;

    const select = document.getElementById('classSelect');

    select.innerHTML = '<option value="">-- Choose Class --</option>' +
        json.data.map(c =>
            `<option value="${c.class_id}">
                ${c.subject} - ${c.grade_level} ${c.section}
            </option>`
        ).join('');
}

document.getElementById('classSelect').addEventListener('change', function () {
    currentClassId = this.value;

    if (currentClassId) {
        loadActivities();
    }
});

   //LOAD ACTIVITIES
async function loadActivities() {

    const res = await fetch(`../../api/classes.api.php?action=activities&class_id=${currentClassId}`);
    const json = await res.json();

    if (!json.success) return;

    const list = document.getElementById('activityList');

    if (json.data.length === 0) {
        list.innerHTML = "No activities yet.";
        return;
    }

    list.innerHTML = `
        <table>
            <tr>
                <th>Title</th>
                <th>Due</th>
                <th>Action</th>
            </tr>
            ${json.data.map(a => `
                <tr>
                    <td>${a.title}</td>
                    <td>${a.due_date ?? ''}</td>
                    <td>
                        <button class="btn btn-primary" onclick='editActivity(${JSON.stringify(a)})'>Edit</button>
                        <button class="btn btn-danger" onclick="deleteActivity(${a.activity_id})">Delete</button>
                    </td>
                </tr>
            `).join('')}
        </table>
    `;
}

   //CREATE
async function createActivity() {

    if (!currentClassId) {
        alert("Select a class first");
        return;
    }

    const data = {
        class_id: currentClassId,
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        due_date: document.getElementById('due_date').value
    };

    await fetch('../../api/classes.api.php?action=create_activity', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });

    loadActivities();
}


   //EDIT
function editActivity(a) {

    document.getElementById('editCard').style.display = 'block';

    document.getElementById('edit_id').value = a.activity_id;
    document.getElementById('edit_title').value = a.title;
    document.getElementById('edit_due_date').value = a.due_date ?? '';
    document.getElementById('edit_description').value = a.description ?? '';
}


   //UPDATE
async function updateActivity() {

    const data = {
        activity_id: document.getElementById('edit_id').value,
        title: document.getElementById('edit_title').value,
        description: document.getElementById('edit_description').value,
        due_date: document.getElementById('edit_due_date').value
    };

    await fetch('../../api/classes.api.php?action=update_activity', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });

    document.getElementById('editCard').style.display = 'none';
    loadActivities();
}

   //DELETE
async function deleteActivity(id) {

    if (!confirm("Delete this activity?")) return;

    await fetch('../../api/classes.api.php?action=delete_activity', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ activity_id: id })
    });

    loadActivities();
}

/* INIT */
loadClasses();

</script>

</body>
</html>