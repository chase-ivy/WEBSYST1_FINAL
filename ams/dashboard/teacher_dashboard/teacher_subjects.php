<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subjects</title>
    <link rel="stylesheet" href="../../style/style.css">

    <style>
        .card { padding: 15px; margin-bottom: 15px; background: #fff; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
        input { padding: 6px; width: 100%; margin: 5px 0; }
        button { padding: 6px 10px; cursor: pointer; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="dashboard-layout">

<?php renderTeacherSidebar('subjects'); ?>

<div class="content">

    <div class="card">
        <h3>Add Subject</h3>

        <input type="text" id="newSubject" placeholder="Subject name">
        <button onclick="createSubject()">Add</button>
    </div>

    <div class="card">
        <h3>Subjects</h3>
        <div id="subjectList">Loading...</div>
    </div>

    <div class="card">
        <h3>Edit Subject</h3>

        <input type="hidden" id="edit_id">
        <input type="text" id="edit_name" placeholder="Subject name">

        <button onclick="updateSubject()">Update</button>
    </div>

</div>
</div>

<script src="../../api/client.js"></script>
<script>

async function loadSubjects() {
    try {
        const response = await API.subjects.list();
        if (response.success) {
            let html = '<table>';
            html += '<tr><th>Name</th><th>Action</th></tr>';

            response.data.forEach(s => {
                html += `
                    <tr>
                        <td>${s.name}</td>
                        <td>
                            <button onclick="editSubject(${s.subject_id}, '${escapeHtml(s.name)}')">Edit</button>
                            <button onclick="deleteSubject(${s.subject_id})">Delete</button>
                        </td>
                    </tr>
                `;
            });

            html += '</table>';

            document.getElementById('subjectList').innerHTML = html;
        } else {
            document.getElementById('subjectList').innerHTML = 'Failed to load';
        }
    } catch (error) {
        console.error('Failed to load subjects:', error);
        document.getElementById('subjectList').innerHTML = 'Failed to load';
    }
}

async function createSubject() {
    const name = document.getElementById('newSubject').value;

    try {
        const response = await API.subjects.create({ name });
        if (response.success) {
            document.getElementById('newSubject').value = '';
            loadSubjects();
        } else {
            alert('Failed to create subject');
        }
    } catch (error) {
        console.error('Failed to create subject:', error);
        alert('Failed to create subject');
    }
}

function editSubject(id, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
}

async function updateSubject() {
    const subject_id = document.getElementById('edit_id').value;
    const name = document.getElementById('edit_name').value;

    try {
        const response = await API.subjects.update({ subject_id, name });
        if (response.success) {
            loadSubjects();
        } else {
            alert('Failed to update subject');
        }
    } catch (error) {
        console.error('Failed to update subject:', error);
        alert('Failed to update subject');
    }
}

async function deleteSubject(id) {
    if (!confirm('Delete this subject?')) return;

    try {
        const response = await API.subjects.delete({ subject_id: id });
        if (response.success) {
            loadSubjects();
        } else {
            alert('Failed to delete subject');
        }
    } catch (error) {
        console.error('Failed to delete subject:', error);
        alert('Failed to delete subject');
    }
}

function escapeHtml(text) {
    return text.replace(/'/g, "\\'");
}

/* INIT */
loadSubjects();

</script>

</body>
</html>
