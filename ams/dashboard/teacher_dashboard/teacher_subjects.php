<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subjects · Gibraltar AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">
    <?php renderTeacherSidebar('subjects'); ?>

    <main class="main">
        <div class="page-header">
            <h1>Subjects</h1>
            <p>Manage the subjects used in your classes.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Add Subject</h2>
                <p>Create a new subject for your course list.</p>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" id="newSubject" placeholder="Subject name">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-primary" onclick="createSubject()">Add Subject</button>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Subject List</h2>
                <p>View and manage available subjects.</p>
            </div>
            <div class="section-body">
                <div id="subjectList">Loading...</div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Edit Subject</h2>
                <p>Update the selected subject name.</p>
            </div>
            <div class="section-body">
                <input type="hidden" id="edit_id">
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" id="edit_name" placeholder="Subject name">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-primary" onclick="updateSubject()">Update Subject</button>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
async function loadSubjects() {
    try {
        const response = await API.subjects.list();
        if (response.success) {
            let html = '<div class="table-wrap"><table>';
            html += '<thead><tr><th>Name</th><th>Action</th></tr></thead><tbody>';

            response.data.forEach(s => {
                html += `
                    <tr>
                        <td class="td-primary">${escapeHtml(s.name)}</td>
                        <td>
                            <button type="button" class="btn-secondary" onclick="editSubject(${s.subject_id}, '${escapeHtml(s.name)}')">Edit</button>
                            <button type="button" class="btn-secondary" onclick="deleteSubject(${s.subject_id})">Delete</button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            document.getElementById('subjectList').innerHTML = html;
        } else {
            document.getElementById('subjectList').innerHTML = 'Failed to load subjects';
        }
    } catch (error) {
        console.error('Failed to load subjects:', error);
        document.getElementById('subjectList').innerHTML = 'Failed to load subjects';
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
        const response = await API.subjects.update(subject_id, { name });
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
        const response = await API.subjects.delete(id);
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
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

window.addEventListener('DOMContentLoaded', loadSubjects);
</script>

</body>
</html>
