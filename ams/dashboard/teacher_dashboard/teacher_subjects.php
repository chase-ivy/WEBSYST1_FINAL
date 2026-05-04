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

<div class="container">

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

<script>

async function loadSubjects() {
    const res = await fetch('../../api/subjects.php?action=list');
    const json = await res.json();

    if (!json.success) {
        document.getElementById('subjectList').innerHTML = 'Failed to load';
        return;
    }

    let html = '<table>';
    html += '<tr><th>Name</th><th>Action</th></tr>';

    json.data.forEach(s => {
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
}

async function createSubject() {
    const name = document.getElementById('newSubject').value;

    await fetch('../../api/subjects.php?action=create', {
        method: 'POST',
        body: JSON.stringify({ name }),
        headers: { 'Content-Type': 'application/json' }
    });

    document.getElementById('newSubject').value = '';
    loadSubjects();
}

function editSubject(id, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
}

async function updateSubject() {
    const subject_id = document.getElementById('edit_id').value;
    const name = document.getElementById('edit_name').value;

    await fetch('../../api/subjects.php?action=update', {
        method: 'POST',
        body: JSON.stringify({ subject_id, name }),
        headers: { 'Content-Type': 'application/json' }
    });

    loadSubjects();
}

async function deleteSubject(id) {
    if (!confirm('Delete this subject?')) return;

    await fetch('../../api/subjects.php?action=delete', {
        method: 'POST',
        body: JSON.stringify({ subject_id: id }),
        headers: { 'Content-Type': 'application/json' }
    });

    loadSubjects();
}

function escapeHtml(text) {
    return text.replace(/'/g, "\\'");
}

/* INIT */
loadSubjects();

</script>

</body>
</html>