<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
$teacherId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>My Classes</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="teacher.css">
</head>

<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">

<?php renderTeacherSidebar('classes'); ?>

<main class="main">

    <div class="page-header">
        <h1>My Classes</h1>
        <p>Manage and view your assigned classes</p>
    </div>

    <section class="section">
        <div class="section-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
            <div>
                <h2>Assigned Classes</h2>
                <p>All classes assigned to you</p>
            </div>
            <button class="btn-action" type="button" onclick="openClassForm('create')">Create Class</button>
        </div>

        <div id="classes-error" class="alert-error" style="display:none; margin-bottom:1rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span id="classes-error-msg"></span>
        </div>

        <div class="section-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Students</th>
                            <th>School Year</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="classes-tbody">
                        <tr class="empty-row">
                            <td colspan="6">Loading classes...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>
</div>

<!-- USE YOUR EXISTING CLIENT -->
<script src="/WEBSYST1_FINAL/ams/api/client.js?v=2"></script>

<div id="class-form-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="class-form-title">Create Class</h3>
            <button class="modal-close" type="button" onclick="closeClassForm()">×</button>
        </div>
        <div class="modal-body">
            <form id="class-form">
                <input type="hidden" id="class-id" value="" />
                <div class="form-row">
                    <label>School Year</label>
                    <input type="text" id="school-year" required />
                </div>
                <div class="form-row">
                    <label>Grade Level</label>
                    <select id="grade-level" required aria-label="Grade Level">
                        <option value="" hidden>Select grade level</option>
                        <option value="Kinder">Kinder</option>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                        <option value="Grade 4">Grade 4</option>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Section</label>
                    <input type="text" id="section" required />
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" type="button" onclick="closeClassForm()">Cancel</button>
            <button class="btn-action" type="button" onclick="submitClassForm()">Save</button>
        </div>
    </div>
</div>

<script>
const TEACHER_ID = <?= json_encode($teacherId) ?>;
let classesMap = {};

function getDeleteClassApi() {
    if (API && API.classes) {
        if (typeof API.classes.delete === 'function') {
            return API.classes.delete;
        }
        if (typeof API.classes.remove === 'function') {
            return API.classes.remove;
        }
    }
    return async classId => API.call('classes', 'delete', { class_id: classId }, 'POST');
}

function showClassesError(message) {
    const container = document.getElementById('classes-error');
    const msg = document.getElementById('classes-error-msg');
    if (container && msg) {
        msg.textContent = message;
        container.style.display = 'flex';
    }
}

function hideClassesError() {
    const container = document.getElementById('classes-error');
    if (container) {
        container.style.display = 'none';
    }
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function openClassForm(mode, classData = null) {
    const modal = document.getElementById('class-form-modal');
    const title = document.getElementById('class-form-title');
    const classId = document.getElementById('class-id');
    const schoolYear = document.getElementById('school-year');
    const gradeLevel = document.getElementById('grade-level');
    const section = document.getElementById('section');

    hideClassesError();

    if (mode === 'edit' && classData) {
        title.textContent = 'Edit Class';
        classId.value = classData.class_id;
        schoolYear.value = classData.school_year || '';
        gradeLevel.value = classData.grade_level || '';
        section.value = classData.section || '';
    } else {
        title.textContent = 'Create Class';
        classId.value = '';
        schoolYear.value = '';
        gradeLevel.value = '';
        section.value = '';
    }

    modal.style.display = 'flex';
}

function closeClassForm() {
    const modal = document.getElementById('class-form-modal');
    modal.style.display = 'none';
}

async function loadClasses() {
    try {
        const res = await API.classes.getTeacherClasses();

        if (!res.success) {
            throw new Error(res.error || 'API failed');
        }

        const classes = res.data;
        const tbody = document.getElementById('classes-tbody');
        classesMap = {};

        if (!classes || classes.length === 0) {
            tbody.innerHTML = `
                <tr class="empty-row">
                    <td colspan="6">No classes assigned</td>
                </tr>
            `;
            return;
        }

        classes.forEach(c => {
            classesMap[c.class_id] = c;
        });

        tbody.innerHTML = classes.map(c => `
            <tr>
                <td class="td-primary">${escapeHtml(c.subject || 'N/A')}</td>
                <td>${escapeHtml(c.grade_level)}</td>
                <td>${escapeHtml(c.section)}</td>
                <td>${escapeHtml(c.student_count ?? 0)}</td>
                <td>${escapeHtml(c.school_year)}</td>
                <td>
                    <button class="btn-secondary" type="button" onclick="editClass(${c.class_id})">Edit</button>
                    <button class="btn-action" type="button" onclick="deleteClass(${c.class_id})">Delete</button>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        showClassesError(err.message || 'Failed to load classes');
        document.getElementById('classes-tbody').innerHTML = `
            <tr class="empty-row">
                <td colspan="6">Failed to load classes</td>
            </tr>
        `;
        console.error('LOAD ERROR:', err);
    }
}

function editClass(classId) {
    const classData = classesMap[classId];
    if (!classData) {
        showClassesError('Class not found.');
        return;
    }
    openClassForm('edit', classData);
}

async function submitClassForm() {
    hideClassesError();
    const classId = document.getElementById('class-id').value;
    const schoolYear = document.getElementById('school-year').value.trim();
    const gradeLevel = document.getElementById('grade-level').value.trim();
    const section = document.getElementById('section').value.trim();

    if (!schoolYear || !gradeLevel || !section) {
        showClassesError('School year, grade level, and section are required.');
        return;
    }

    const payload = {
        school_year: schoolYear,
        grade_level: gradeLevel,
        section: section
    };

    try {
        let res;

        if (classId) {
            res = await API.classes.update(parseInt(classId, 10), payload);
        } else {
            res = await API.classes.create(payload);
        }

        if (!res.success) {
            throw new Error(res.error || 'Save failed');
        }

        closeClassForm();
        await loadClasses();
    } catch (err) {
        showClassesError(err.message || 'Unable to save class');
        console.error('SAVE ERROR:', err);
    }
}

async function deleteClass(classId) {
    hideClassesError();

    if (!confirm('Delete this class? This cannot be undone.')) {
        return;
    }

    try {
        const deleteFn = getDeleteClassApi();
        const res = await deleteFn(classId);
        if (!res.success) {
            throw new Error(res.error || 'Delete failed');
        }
        await loadClasses();
    } catch (err) {
        showClassesError(err.message || 'Unable to delete class');
        console.error('DELETE ERROR:', err);
    }
}

document.addEventListener('DOMContentLoaded', loadClasses);
</script>

</body>
</html>