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
    <div id="studentFilterBar"></div>
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

let adminStudentsCache = [];

function getUniqueValues(items, field) {
    return [...new Set(items.filter(item => item && item[field]).map(item => item[field]))].sort((a, b) => a.localeCompare(b));
}

function buildFilterOptions(values, defaultLabel, selectedValue = '') {
    return [`<option value="">${escapeHtml(defaultLabel)}</option>`,
        ...values.map(value => `<option value="${escapeHtml(value)}"${value === selectedValue ? ' selected' : ''}>${escapeHtml(value)}</option>`)
    ].join('');
}

function renderStudentFilterBar() {
    const filterBar = document.getElementById('studentFilterBar');
    if (!filterBar) return;

    const currentYear = document.getElementById('filterSchoolYear')?.value || '';
    const currentGrade = document.getElementById('filterGradeLevel')?.value || '';
    const currentSection = document.getElementById('filterSection')?.value || '';

    const yearOptions = getUniqueValues(adminStudentsCache, 'school_year');
    const gradeOptions = getUniqueValues(
        adminStudentsCache.filter(student => !currentYear || student.school_year === currentYear),
        'grade_level'
    );
    const sectionOptions = getUniqueValues(
        adminStudentsCache.filter(student =>
            (!currentYear || student.school_year === currentYear) &&
            (!currentGrade || student.grade_level === currentGrade)
        ),
        'section'
    );

    const selectedYear = yearOptions.includes(currentYear) ? currentYear : '';
    const selectedGrade = gradeOptions.includes(currentGrade) ? currentGrade : '';
    const selectedSection = sectionOptions.includes(currentSection) ? currentSection : '';

    filterBar.innerHTML = `
        <div class="filter-row" style="display:flex; flex-wrap:wrap; gap:16px; margin-bottom:16px; align-items:flex-end;">
            <div class="form-group" style="min-width:180px;">
                <label for="filterSchoolYear">School Year</label>
                <select id="filterSchoolYear">${buildFilterOptions(yearOptions, 'All school years', selectedYear)}</select>
            </div>
            <div class="form-group" style="min-width:180px;">
                <label for="filterGradeLevel">Grade Level</label>
                <select id="filterGradeLevel">${buildFilterOptions(gradeOptions, 'All grades', selectedGrade)}</select>
            </div>
            <div class="form-group" style="min-width:180px;">
                <label for="filterSection">Section</label>
                <select id="filterSection">${buildFilterOptions(sectionOptions, 'All sections', selectedSection)}</select>
            </div>
        </div>
    `;

    ['filterSchoolYear', 'filterGradeLevel', 'filterSection'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', applyStudentFilters);
        }
    });
}

function applyStudentFilters() {
    renderStudentFilterBar();

    const year = document.getElementById('filterSchoolYear')?.value || '';
    const grade = document.getElementById('filterGradeLevel')?.value || '';
    const section = document.getElementById('filterSection')?.value || '';

    const filtered = adminStudentsCache.filter(student => {
        return (!year || student.school_year === year)
            && (!grade || student.grade_level === grade)
            && (!section || student.section === section);
    });

    renderStudents(filtered);
}

async function loadStudents() {
    try {
        const res = await API.students.list();

        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load students');
        }

        const students = res.data.sort((a, b) => a.last_name.localeCompare(b.last_name));
        adminStudentsCache = students;

        renderStudentFilterBar();
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
                    <button class="btn-secondary btn-sm" type="button" onclick="openEnrollmentModal(${student.student_id})">Edit Student</button>
                    <button class="btn-danger btn-sm" type="button" onclick="confirmDeleteStudent(${student.student_id}, this)">Delete</button>
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


async function openEnrollmentModal(studentId) {
    try {
        const studentRes = await API.students.get(studentId);

        if (!studentRes || !studentRes.success) {
            throw new Error(studentRes?.error || 'Failed to load student details');
        }

        const data = studentRes.data;
        const student = data.student || {};
        const enrollment = data.latest_enrollment || {};
        const currentAddress = data.current_address || {};
        const permanentAddress = data.permanent_address || {};
        const parents = data.parents || {};
        const returning = data.returning || {};
        const disabilities = data.disabilities || [];
        const medical = data.medical || {};

        const header = `<h3>Edit Student</h3>`;
        const body = `
            <form id="enrollmentForm" data-student-id="${studentId}">
                <input type="hidden" name="student_id" value="${studentId}" />

                <div class="form-section">
                    <h4>User Account</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" name="username" type="text" value="${escapeHtml(student.username || '')}" required />
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="${escapeHtml(student.email || '')}" required />
                        </div>
                        <div class="form-group">
                            <label for="password">Password (leave blank to keep current)</label>
                            <input id="password" name="password" type="password" />
                        </div>
                    </div>
                </div>

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
        showAlert('error', `Unable to open student editor: ${error.message}`);
    }
}


async function saveEnrollmentUpdate(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');
    const origText = submitBtn ? submitBtn.textContent : null;
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving…';
    }
    const studentId = parseInt(form.dataset.studentId, 10);
    const data = serializeForm(form);

    try {
        if (data.username && data.email) {
            const accountRes = await API.teacher.updateStudentAccount({
                student_id: studentId,
                username: data.username.trim(),
                email: data.email.trim(),
                password: (data.password || '').trim()
            });
            if (!accountRes || !accountRes.success) {
                throw new Error(accountRes?.error || 'Failed to update student account');
            }
        }

        const res = await API.students.update(studentId, data);
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to update enrollment');
        }
        closeModal();
        showAlert('success', res.message || 'Student updated successfully.');
        await loadStudents();
    } catch (error) {
        console.error(error);
        showAlert('error', error.message || 'Error updating student.');
    }
    finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = origText || 'Save Enrollment';
        }
    }
}

async function confirmDeleteStudent(studentId, btn) {
    if (!confirm('Delete this student and all related enrollment records?')) {
        return;
    }

    const origText = btn ? btn.textContent : null;
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Deleting…';
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
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = origText || 'Delete';
        }
    }
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

