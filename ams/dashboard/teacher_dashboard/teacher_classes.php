<?php
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

<div class="dashboard-layout">

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

<script src="../../api/client.js"></script>
<script>

let currentClassId = null;

   //LOAD CLASSES
async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Choose Class --</option>' +
                response.data.map(c =>
                    `<option value="${c.class_id}">
                        ${c.subject_name} - ${c.grade_level} ${c.section}
                    </option>`
                ).join('');
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
    }
}

document.getElementById('classSelect').addEventListener('change', function () {
    currentClassId = this.value;

    if (currentClassId) {
        loadActivities();
    }
});

   //LOAD ACTIVITIES
async function loadActivities() {
    try {
        const response = await API.activities.listByClass(currentClassId);
        if (response.success) {
            renderActivities(response.data);
        }
    } catch (error) {
        console.error('Failed to load activities:', error);
    }
}

function renderActivities(data) {

    const list = document.getElementById('activityList');

    if (data.length === 0) {
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
            ${data.map(a => `
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
        class_subject_id: currentClassId,
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        due_date: document.getElementById('due_date').value
    };

    try {
        const response = await API.activities.create(data);
        if (response.success) {
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('due_date').value = '';
            loadActivities();
        } else {
            alert('Failed to create activity');
        }
    } catch (error) {
        console.error('Failed to create activity:', error);
        alert('Failed to create activity');
    }
}


   //EDIT
function editActivity(activity) {
    document.getElementById('editCard').style.display = 'block';
    document.getElementById('edit_id').value = activity.activity_id;
    document.getElementById('edit_title').value = activity.title;
    document.getElementById('edit_due_date').value = activity.due_date;
    document.getElementById('edit_description').value = activity.description;
}


   //UPDATE
async function updateActivity() {
    const data = {
        activity_id: document.getElementById('edit_id').value,
        title: document.getElementById('edit_title').value,
        description: document.getElementById('edit_description').value,
        due_date: document.getElementById('edit_due_date').value
    };

    try {
        const response = await API.call('activities', 'update', data, 'POST');
        if (response.success) {
            document.getElementById('editCard').style.display = 'none';
            loadActivities();
        } else {
            alert('Failed to update activity');
        }
    } catch (error) {
        console.error('Failed to update activity:', error);
        alert('Failed to update activity');
    }
}

   //DELETE
async function deleteActivity(activity_id) {
    if (!confirm('Delete this activity?')) return;

    try {
        const response = await API.call('activities', 'delete', { activity_id }, 'POST');
        if (response.success) {
            loadActivities();
        } else {
            alert('Failed to delete activity');
        }
    } catch (error) {
        console.error('Failed to delete activity:', error);
        alert('Failed to delete activity');
    }
}

/* INIT */
loadClasses();

</script>

</body>
</html>
