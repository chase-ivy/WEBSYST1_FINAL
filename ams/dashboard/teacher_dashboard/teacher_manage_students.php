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

<title>Students · Gibraltar AMS</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="teacher.css">
</head>

<body>

<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span></div>
    <span class="topbar-label">Teacher Portal</span>
</header>

<div class="shell">

<?php renderTeacherSidebar('students'); ?>

<main class="main">

<div class="page-header">
    <h1>Students</h1>
    <p>Students with enrollment records</p>
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
let teacherClassesCache = null;

async function loadStudents() {
    try {
        const res = await API.teacher.students();

        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load students');
        }

        const unique = {};
        res.data.forEach(student => {
            unique[student.student_id] = student;
        });

        const students = Object.values(unique).sort((a, b) => a.last_name.localeCompare(b.last_name));

        renderStudents(students);

    } catch (error) {
        console.error('Student load error:', error);

        document.getElementById('studentsTable').innerHTML = `
            <div class="empty-row">Error loading students</div>
        `;
    }
}

   //RENDER TABLE
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

   //SAFE HTML
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

        const student = res.data.student || {};
        const enrollment = res.data.latest_enrollment || {};
        const header = `<h3>Update Enrollment</h3>`;
        const body = `
            <form id="enrollmentForm" data-student-id="${studentId}">
                <input type="hidden" name="student_id" value="${studentId}" />
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="Learner_Reference_No">LRN</label>
                        <input id="Learner_Reference_No" name="Learner_Reference_No" value="${escapeHtml(student.lrn || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="Learner_First_Name">First name</label>
                        <input id="Learner_First_Name" name="Learner_First_Name" value="${escapeHtml(student.first_name || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="Learner_Last_Name">Last name</label>
                        <input id="Learner_Last_Name" name="Learner_Last_Name" value="${escapeHtml(student.last_name || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="Learner_Middle_Name">Middle name</label>
                        <input id="Learner_Middle_Name" name="Learner_Middle_Name" value="${escapeHtml(student.middle_name || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="Birth_Date">Birth date</label>
                        <input id="Birth_Date" name="Birth_Date" type="date" value="${escapeHtml(student.birth_date || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="Place_of_Birth">Place of birth</label>
                        <input id="Place_of_Birth" name="Place_of_Birth" value="${escapeHtml(student.place_of_birth || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="Grade_Level">Grade level</label>
                        <input id="Grade_Level" name="Grade_Level" value="${escapeHtml(enrollment.grade_level || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="year_start">School year start</label>
                        <input id="year_start" name="year_start" value="${escapeHtml(enrollment.year_start || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="year_end">School year end</label>
                        <input id="year_end" name="year_end" value="${escapeHtml(enrollment.year_end || '')}" />
                    </div>
                    <div class="form-group">
                        <label for="with_lrn">With LRN</label>
                        <select id="with_lrn" name="with_lrn">
                            <option value="">Select</option>
                            <option value="Yes" ${enrollment.with_lrn ? 'selected' : ''}>Yes</option>
                            <option value="No" ${enrollment.with_lrn ? '' : 'selected'}>No</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="Mother_Tongue">Mother tongue</label>
                        <input id="Mother_Tongue" name="Mother_Tongue" value="${escapeHtml(enrollment.mother_tongue || '')}" />
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
    } catch (error) {
        console.error(error);
        showAlert('error', `Unable to open enrollment editor: ${error.message}`);
    }
}

async function saveEnrollmentUpdate(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const studentId = parseInt(form.dataset.studentId, 10);
    const data = Object.fromEntries(new FormData(form).entries());

    try {
        const res = await API.students.update(studentId, data);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to update enrollment');
        }
        closeModal();
        showAlert('success', 'Student enrollment information updated.');
        await loadStudents();
    } catch (error) {
        console.error(error);
        showAlert('error', error.message || 'Error updating enrollment.');
    }
}

async function openAssignClassModal(studentId) {
    try {
        if (!teacherClassesCache) {
            const res = await API.classes.getTeacherClasses();
            if (!res || !res.success) {
                throw new Error(res?.error || 'Failed to load classes');
            }
            teacherClassesCache = res.data;
        }

        const options = teacherClassesCache.map(cls => `
            <option value="${cls.class_id}">${escapeHtml(cls.school_year || '')} • Grade ${escapeHtml(cls.grade_level || '')} • Section ${escapeHtml(cls.section || '')}</option>
        `).join('');

        const header = `<h3>Assign Student to Class</h3>`;
        const body = `
            <form id="assignClassForm" data-student-id="${studentId}">
                <div class="form-group full">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">Choose a class</option>
                        ${options}
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn-secondary" type="button" onclick="closeModal()">Cancel</button>
                    <button class="btn-primary" type="submit">Assign</button>
                </div>
            </form>
        `;

        showModal({ header, body });
        document.getElementById('assignClassForm').addEventListener('submit', saveAssignClass);
    } catch (error) {
        console.error(error);
        showAlert('error', `Unable to open class assignment dialog: ${error.message}`);
    }
}

async function saveAssignClass(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const studentId = parseInt(form.dataset.studentId, 10);
    const classId = parseInt(form.class_id.value, 10);

    try {
        const res = await API.classes.assignStudent({ student_id: studentId, class_id: classId });
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to assign student to class');
        }
        closeModal();
        showAlert('success', res.message || 'Student assigned successfully.');
        await loadStudents();
    } catch (error) {
        console.error(error);
        showAlert('error', error.message || 'Error assigning student.');
    }
}

async function openAccountModal(studentId) {
    try {
        const res = await API.teacher.getStudentAccount(studentId);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load student account');
        }

        const account = res.data || {};
        const header = `<h3>Update Student Account</h3>`;
        const body = `
            <form id="accountForm" data-student-id="${studentId}">
                <input type="hidden" name="student_id" value="${studentId}" />
                <input type="hidden" name="user_id" value="${escapeHtml(account.user_id || '')}" />
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="username">Username</label>
                        <input id="username" name="username" required value="${escapeHtml(account.username || '')}" />
                    </div>
                    <div class="form-group full">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required value="${escapeHtml(account.email || '')}" />
                    </div>
                    <div class="form-group full">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" placeholder="Leave blank to keep current password" />
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-secondary" type="button" onclick="closeModal()">Cancel</button>
                    <button class="btn-primary" type="submit">Save Account</button>
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

   //INIT
document.addEventListener('DOMContentLoaded', loadStudents);
</script>

</body>
</html>