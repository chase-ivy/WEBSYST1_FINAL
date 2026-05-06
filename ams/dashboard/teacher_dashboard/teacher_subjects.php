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
            <p>Manage subjects and assign them to your classes.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Select Class</h2>
                <p>Choose a class to assign subjects to.</p>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label>Class</label>
                    <select id="classSelect" onchange="onClassChange()">
                        <option value="">-- Select Class --</option>
                    </select>
                </div>
            </div>
        </section>

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
                <h2>Assign Subject to Class</h2>
                <p>Assign subjects to the selected class.</p>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Subject</label>
                        <select id="subjectSelect">
                            <option value="">-- Select Subject --</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-primary" onclick="assignSubjectToClass()">Assign to Class</button>
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
                <h2>Assigned Subjects</h2>
                <p>Subjects assigned to the selected class.</p>
            </div>
            <div class="section-body">
                <div id="assignedSubjects">Select a class to view assigned subjects</div>
            </div>
        </section>

    </main>
</div>

<div id="subject-form-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="subject-form-title">Edit Subject</h3>
            <button class="modal-close" type="button" onclick="closeSubjectForm()">×</button>
        </div>
        <div class="modal-body">
            <div id="subject-form-error" class="alert-error" style="display:none; margin-bottom:1rem;">
                <span id="subject-form-error-msg"></span>
            </div>
            <form id="subject-form">
                <input type="hidden" id="subject-id" value="" />
                <div class="form-row">
                    <label>Subject Name</label>
                    <input type="text" id="subject-name" required />
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" type="button" onclick="closeSubjectForm()">Cancel</button>
            <button class="btn-action" type="button" onclick="submitSubjectForm()">Save</button>
        </div>
    </div>
</div>

<script src="../../api/client.js"></script>
<script>
let currentClassId = null;

async function loadClasses() {
    try {
        const response = await API.teacher.classes();
        if (response.success) {
            const select = document.getElementById('classSelect');
            select.innerHTML = '<option value="">-- Select Class --</option>';

            response.data.forEach(c => {
                const option = document.createElement('option');
                option.value = c.class_id;
                option.textContent = `${c.subject_name || 'Unassigned'} - Grade ${c.grade_level} ${c.section} (${c.school_year})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load classes:', error);
    }
}

function onClassChange() {
    currentClassId = document.getElementById('classSelect').value;
    if (currentClassId) {
        loadAssignedSubjects();
    } else {
        document.getElementById('assignedSubjects').innerHTML = 'Select a class to view assigned subjects';
    }
}

async function loadSubjects() {
    try {
        const response = await API.subjects.list();
        if (response.success) {
            // Update subject list
            let html = '<div class="table-wrap"><table>';
            html += '<thead><tr><th>Name</th><th>Action</th></tr></thead><tbody>';

            response.data.forEach(s => {
                html += `
                    <tr>
                        <td class="td-primary">${escapeHtml(s.name)}</td>
                        <td>
                            <button type="button" class="btn-secondary" onclick='editSubject(${s.subject_id}, ${JSON.stringify(s.name)})'>Edit</button>
                            <button type="button" class="btn-danger" onclick="deleteSubject(${s.subject_id})">Delete</button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            document.getElementById('subjectList').innerHTML = html;

            // Update subject select dropdown
            const select = document.getElementById('subjectSelect');
            select.innerHTML = '<option value="">-- Select Subject --</option>';
            response.data.forEach(s => {
                const option = document.createElement('option');
                option.value = s.subject_id;
                option.textContent = s.name;
                select.appendChild(option);
            });
        } else {
            document.getElementById('subjectList').innerHTML = 'Failed to load subjects';
        }
    } catch (error) {
        console.error('Failed to load subjects:', error);
        document.getElementById('subjectList').innerHTML = 'Failed to load subjects';
    }
}

async function loadAssignedSubjects() {
    if (!currentClassId) return;

    document.getElementById('assignedSubjects').innerHTML = '<div class="empty-row">Loading assigned subjects...</div>';

    try {
        // Get class subjects for the current class
        const response = await API.classes.getSubjects(currentClassId);
        if (response.success) {
            let html = '<div class="table-wrap"><table>';
            html += '<thead><tr><th>Subject Name</th><th>Action</th></tr></thead><tbody>';

            if (response.data.length === 0) {
                html += '<tr><td colspan="2">No subjects assigned to this class</td></tr>';
            } else {
                response.data.forEach(cs => {
                    html += `
                        <tr>
                            <td class="td-primary">${escapeHtml(cs.subject_name)}</td>
                            <td>
                                <button type="button" class="btn-danger" onclick="unassignSubject(${cs.class_subject_id})">Unassign</button>
                            </td>
                        </tr>
                    `;
                });
            }

            html += '</tbody></table></div>';
            document.getElementById('assignedSubjects').innerHTML = html;
        }
    } catch (error) {
        console.error('Failed to load assigned subjects:', error);
        document.getElementById('assignedSubjects').innerHTML = '<div class="empty-row">Failed to load assigned subjects</div>';
    }
}

async function createSubject() {
    const name = document.getElementById('newSubject').value.trim();

    if (!name) {
        alert('Please enter a subject name');
        return;
    }

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

async function assignSubjectToClass() {
    if (!currentClassId) {
        alert('Please select a class first');
        return;
    }

    const subjectId = document.getElementById('subjectSelect').value;

    if (!subjectId) {
        alert('Please select a subject to assign');
        return;
    }

    try {
        const response = await API.teacher.assignSubject({
            class_id: parseInt(currentClassId),
            subject_id: parseInt(subjectId)
        });

        if (response.success) {
            alert(response.message || 'Subject assigned successfully');
            loadAssignedSubjects();
        } else {
            alert(response.error || 'Failed to assign subject');
        }
    } catch (error) {
        console.error('Failed to assign subject:', error);
        alert('Failed to assign subject');
    }
}

async function unassignSubject(classSubjectId) {
    if (!confirm('Unassign this subject from the class?')) return;

    try {
        const response = await API.classes.unassignSubject(classSubjectId);
        if (response.success) {
            loadAssignedSubjects();
        } else {
            alert('Failed to unassign subject');
        }
    } catch (error) {
        console.error('Failed to unassign subject:', error);
        alert('Failed to unassign subject');
    }
}

function showSubjectError(message) {
    const container = document.getElementById('subject-form-error');
    const msg = document.getElementById('subject-form-error-msg');
    if (container && msg) {
        msg.textContent = message;
        container.style.display = 'flex';
    }
}

function hideSubjectError() {
    const container = document.getElementById('subject-form-error');
    if (container) {
        container.style.display = 'none';
    }
}

function openSubjectForm(subjectId, subjectName) {
    hideSubjectError();

    const modal = document.getElementById('subject-form-modal');
    const title = document.getElementById('subject-form-title');
    const idInput = document.getElementById('subject-id');
    const nameInput = document.getElementById('subject-name');

    title.textContent = 'Edit Subject';
    idInput.value = subjectId;
    nameInput.value = subjectName || '';

    modal.style.display = 'flex';
}

function closeSubjectForm() {
    const modal = document.getElementById('subject-form-modal');
    modal.style.display = 'none';
}

function editSubject(id, currentName) {
    openSubjectForm(id, currentName);
}

async function submitSubjectForm() {
    hideSubjectError();

    const id = document.getElementById('subject-id').value;
    const name = document.getElementById('subject-name').value.trim();

    if (!name) {
        showSubjectError('Subject name cannot be empty.');
        return;
    }

    try {
        const response = await API.subjects.update(parseInt(id, 10), { name });
        if (response.success) {
            closeSubjectForm();
            loadSubjects();
        } else {
            showSubjectError(response.error || 'Failed to update subject');
        }
    } catch (error) {
        console.error('Failed to update subject:', error);
        showSubjectError('Failed to update subject');
    }
}

async function deleteSubject(id) {
    if (!confirm('Delete this subject? This will unassign it from all classes.')) return;

    try {
        const response = await API.subjects.delete(id);
        if (response.success) {
            loadSubjects();
            loadAssignedSubjects();
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

window.addEventListener('DOMContentLoaded', () => {
    loadClasses();
    loadSubjects();
});
</script>

</body>
</html>
