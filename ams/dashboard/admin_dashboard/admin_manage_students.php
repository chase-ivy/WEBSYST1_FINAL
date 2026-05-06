<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/admin_nav.php';

require_special_admin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Students · Gibraltar AMS</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
<style>
    body {
        font-family: 'DM Sans', sans-serif;
        background-image: url('hallway.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-color: #2a1a1a;
        color: var(--text);
        min-height: 100vh;
        font-size: 14px;
        line-height: 1.5;
    }
</style>
</head>

<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
</header>

<div class="shell">

<?php renderAdminSidebar('manage_students'); ?>

<main class="main">

<div class="page-header">
    <h1>Students</h1>
    <p>Manage student enrollment and records</p>
</div>

<section class="section">

<div class="section-header">
    <h2>Enrolled Students</h2>
    <p>View all students who have completed enrollment</p>
</div>

<div class="section-body">
    <div id="studentsTable">
        <div class="empty-row">Loading students...</div>
    </div>
    <div id="modalContainer"></div>
</div>

</section>

</main>
</div>

<script src="../../api/client.js?v=2"></script>

<script>
let adminClassesCache = null;

async function loadStudents() {
    try {
        const res = await API.students.list();

        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load students');
        }

        const students = res.data.sort((a, b) => a.last_name.localeCompare(b.last_name));

        renderStudents(students);

    } catch (error) {
        console.error('Student load error:', error);

        document.getElementById('studentsTable').innerHTML = `
            <div class="empty-row">Error loading students</div>
        `;
    }
}

function renderStudents(students) {

    const container = document.getElementById('studentsTable');

    if (!students.length) {
        container.innerHTML = `
            <div class="empty-row">No enrolled students found</div>
        `;
        return;
    }

    let html = `
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>LRN</th>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>School Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

    students.forEach(student => {
        const fullName = `${student.first_name || ''} ${student.last_name || ''}`.trim();

        html += `
            <tr>
                <td class="td-primary">${escapeHtml(fullName)}</td>
                <td>${escapeHtml(student.lrn || 'N/A')}</td>
                <td>${escapeHtml(student.grade_level || 'N/A')}</td>
                <td>${escapeHtml(student.section || 'N/A')}</td>
                <td>${escapeHtml(student.school_year || 'N/A')}</td>
                <td class="td-actions">
                    <button class="btn-secondary btn-sm" type="button" onclick="openEnrollmentModal(${student.student_id})">Update Enrollment</button>
                    <button class="btn-secondary btn-sm" type="button" onclick="openAssignClassModal(${student.student_id})">Assign Class</button>
                    <button class="btn-secondary btn-sm" type="button" onclick="openAccountModal(${student.student_id})">Update Account</button>
                    <button class="btn-secondary btn-sm" type="button" onclick="downloadEnrollmentForm(${student.student_id})">Download Form</button>
                    <button class="btn-danger btn-sm" type="button" onclick="confirmDeleteStudent(${student.student_id})">Delete</button>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function showModal(contentHtml) {
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.innerHTML = `
        <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-content">
                <div class="modal-header">
                    ${contentHtml.header}
                    <button class="modal-close" type="button" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">${contentHtml.body}</div>
            </div>
        </div>
    `;
}

function closeModal() {
    document.getElementById('modalContainer').innerHTML = '';
}

async function openEnrollmentModal(studentId) {
    try {
        const res = await API.students.get(studentId);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load student details');
        }

        const data = res.data;
        const student = data.student || {};
        const enrollment = data.latest_enrollment || {};
        const currentAddress = data.current_address || {};
        const permanentAddress = data.permanent_address || {};
        const parents = data.parents || {};
        const returning = data.returning || {};
        const disabilities = data.disabilities || [];
        const medical = data.medical || {};

        const header = `<h3>Update Enrollment</h3>`;
        const body = `
            <form id="enrollmentForm" data-student-id="${studentId}">
                <input type="hidden" name="student_id" value="${studentId}" />
                
                <!-- School Year & Grade -->
                <div class="form-section">
                    <h4>School Year & Grade Level</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="year_start">School Year Start</label>
                            <input id="year_start" name="year_start" type="number" value="${escapeHtml(enrollment.year_start || '')}" min="2000" max="2099" />
                        </div>
                        <div class="form-group">
                            <label for="year_end">School Year End</label>
                            <input id="year_end" name="year_end" type="number" value="${escapeHtml(enrollment.year_end || '')}" min="2000" max="2099" />
                        </div>
                        <div class="form-group">
                            <label for="Grade_Level">Grade Level</label>
                            <select id="Grade_Level" name="Grade_Level">
                                <option value="">Select grade</option>
                                <option value="Kinder" ${enrollment.grade_level === 'Kinder' ? 'selected' : ''}>Kinder</option>
                                <option value="Grade 1" ${enrollment.grade_level === 'Grade 1' ? 'selected' : ''}>Grade 1</option>
                                <option value="Grade 2" ${enrollment.grade_level === 'Grade 2' ? 'selected' : ''}>Grade 2</option>
                                <option value="Grade 3" ${enrollment.grade_level === 'Grade 3' ? 'selected' : ''}>Grade 3</option>
                                <option value="Grade 4" ${enrollment.grade_level === 'Grade 4' ? 'selected' : ''}>Grade 4</option>
                                <option value="Grade 5" ${enrollment.grade_level === 'Grade 5' ? 'selected' : ''}>Grade 5</option>
                                <option value="Grade 6" ${enrollment.grade_level === 'Grade 6' ? 'selected' : ''}>Grade 6</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- LRN & Returning -->
                <div class="form-section">
                    <h4>LRN & Learner Status</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Learner_Reference_No">LRN</label>
                            <input id="Learner_Reference_No" name="Learner_Reference_No" value="${escapeHtml(student.lrn || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="with_lrn">With LRN?</label>
                            <select id="with_lrn" name="with_lrn">
                                <option value="1" ${enrollment.with_lrn == 1 ? 'selected' : ''}>Yes</option>
                                <option value="0" ${enrollment.with_lrn == 0 ? 'selected' : ''}>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="returning">Returning Learner?</label>
                            <select id="returning" name="returning">
                                <option value="1" ${enrollment.is_returning_learner == 1 ? 'selected' : ''}>Yes</option>
                                <option value="0" ${enrollment.is_returning_learner == 0 ? 'selected' : ''}>No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                    <h4>Personal Information</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Learner_Last_Name">Last Name</label>
                            <input id="Learner_Last_Name" name="Learner_Last_Name" value="${escapeHtml(student.last_name || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Learner_First_Name">First Name</label>
                            <input id="Learner_First_Name" name="Learner_First_Name" value="${escapeHtml(student.first_name || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Learner_Middle_Name">Middle Name</label>
                            <input id="Learner_Middle_Name" name="Learner_Middle_Name" value="${escapeHtml(student.middle_name || '')}" />
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn-secondary" type="button" onclick="closeModal()">Cancel</button>
                    <button class="btn-primary" type="submit">Save Enrollment</button>
                </div>
            </form>
        `;

        showModal({ header, body });
        document.getElementById('enrollmentForm').addEventListener('submit', saveEnrollmentUpdate);

        // Toggle returning section
        document.getElementById('returning').addEventListener('change', function() {
            document.getElementById('returningSection').style.display = this.value === '1' ? 'block' : 'none';
        });
    } catch (error) {
        console.error(error);
        showAlert('error', `Unable to open enrollment editor: ${error.message}`);
    }
}

function serializeForm(form) {
    const formData = new FormData(form);
    const data = {};

    for (const [name, value] of formData.entries()) {
        if (name.includes('[')) {
            // Handle array fields like disabilities[]
            const arrayName = name.replace('[]', '');
            if (!data[arrayName]) {
                data[arrayName] = [];
            }
            data[arrayName].push(value);
            continue;
        }

        if (data[name] !== undefined) {
            if (!Array.isArray(data[name])) {
                data[name] = [data[name]];
            }
            data[name].push(value);
        } else {
            data[name] = value;
        }
    }

    return data;
}

async function saveEnrollmentUpdate(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const studentId = parseInt(form.dataset.studentId, 10);
    const data = serializeForm(form);

    try {
        const res = await API.students.update(studentId, data);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to update enrollment');
        }
        closeModal();
        showAlert('success', res.message || 'Enrollment updated successfully.');
        await loadStudents();
    } catch (error) {
        console.error(error);
        showAlert('error', error.message || 'Error updating enrollment.');
    }
}

async function openAssignClassModal(studentId) {
    try {
        const classRes = await API.teacher.classes();
        if (!classRes || !classRes.success) {
            throw new Error('Failed to load classes');
        }

        const assignRes = await API.students.getClassAssignments(studentId);
        const currentAssignments = assignRes?.data || [];

        const classes = classRes.data || [];
        const classesList = classes.map(c => ({
            id: c.class_id,
            name: `${c.subject_name} - ${c.section} (${c.grade_level})`
        }));

        const header = `<h3>Assign Classes</h3>`;
        let body = `
            <form id="assignClassForm" data-student-id="${studentId}">
                <div class="form-group">
                    <label>Classes</label>
                    <div class="checkbox-group">
        `;

        classesList.forEach(cls => {
            const isChecked = currentAssignments.some(a => a.class_id === cls.id);
            body += `
                <label class="checkbox-label">
                    <input type="checkbox" name="class_ids" value="${cls.id}" ${isChecked ? 'checked' : ''} />
                    ${escapeHtml(cls.name)}
                </label>
            `;
        });

        body += `
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-secondary" type="button" onclick="closeModal()">Cancel</button>
                    <button class="btn-primary" type="submit">Save Assignments</button>
                </div>
            </form>
        `;

        showModal({ header, body });
        document.getElementById('assignClassForm').addEventListener('submit', saveClassAssignment);
    } catch (error) {
        console.error(error);
        showAlert('error', `Unable to open class assignment: ${error.message}`);
    }
}

async function saveClassAssignment(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const studentId = parseInt(form.dataset.studentId, 10);
    const classIds = Array.from(form.querySelectorAll('input[name="class_ids"]:checked')).map(el => el.value);

    try {
        const res = await API.students.assignClasses(studentId, classIds);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to assign classes');
        }
        closeModal();
        showAlert('success', res.message || 'Classes assigned successfully.');
    } catch (error) {
        console.error(error);
        showAlert('error', error.message || 'Error assigning classes.');
    }
}

async function openAccountModal(studentId) {
    try {
        const res = await API.students.get(studentId);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load student details');
        }

        const student = res.data.student || {};

        const header = `<h3>Update Student Account</h3>`;
        const body = `
            <form id="accountForm" data-student-id="${studentId}">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="${escapeHtml(student.email || '')}" />
                </div>
                <div class="form-group">
                    <label for="password">Password (leave blank to keep current)</label>
                    <input id="password" name="password" type="password" />
                </div>
                <div class="form-actions">
                    <button class="btn-secondary" type="button" onclick="closeModal()">Cancel</button>
                    <button class="btn-primary" type="submit">Update Account</button>
                </div>
            </form>
        `;

        showModal({ header, body });
        document.getElementById('accountForm').addEventListener('submit', saveAccountUpdate);
    } catch (error) {
        console.error(error);
        showAlert('error', `Unable to open account editor: ${error.message}`);
    }
}

async function saveAccountUpdate(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const studentId = parseInt(form.dataset.studentId, 10);
    const data = Object.fromEntries(new FormData(form).entries());
    data.student_id = studentId;

    try {
        const res = await API.teacher.updateStudentAccount(data);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to update student account');
        }
        closeModal();
        showAlert('success', res.message || 'Student account updated successfully.');
    } catch (error) {
        console.error(error);
        showAlert('error', error.message || 'Error updating student account.');
    }
}

async function confirmDeleteStudent(studentId) {
    if (!confirm('Delete this student and all related enrollment records?')) {
        return;
    }

    try {
        const res = await API.students.delete(studentId);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to delete student');
        }
        showAlert('success', res.message || 'Student deleted successfully.');
        await loadStudents();
    } catch (error) {
        console.error(error);
        showAlert('error', error.message || 'Error deleting student.');
    }
}

function showAlert(type, message) {
    const existing = document.querySelector('.alert');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.className = `alert ${type === 'success' ? 'alert-success' : 'alert-error'}`;
    alert.textContent = message;
    document.querySelector('.page-header').insertAdjacentElement('afterend', alert);
    setTimeout(() => alert.remove(), 5000);
}

function downloadEnrollmentForm(studentId) {
    window.open(`../../forms/enrollment_form/pdf.php?student_id=${studentId}&type=combined`, '_blank');
}

document.addEventListener('DOMContentLoaded', () => {
    loadStudents();
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    if (editId) {
        openEnrollmentModal(editId);
    }
});
</script>

</body>
</html>
