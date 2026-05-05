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
            <p>Manage the students assigned to your classes.</p>
        </div>

        <section id="studentsSection" class="section">
            <div class="section-header">
                <h2>My Students</h2>
                <p>View and edit student records for your classes.</p>
            </div>
            <div class="section-body">
                <div id="studentsTable">
                    <div class="empty-row">Loading students...</div>
                </div>
            </div>
        </section>

        <section id="editSection" class="section" style="display:none;">
            <div class="section-header">
                <h2>Edit Student</h2>
                <p>Update an existing student record.</p>
            </div>
            <div class="section-body">
                <form id="editStudentForm">
                    <input type="hidden" id="edit_student_id" name="student_id">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="edit_fname" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="edit_lname" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label>Grade Level</label>
                            <input type="text" id="edit_grade" name="grade_level" required>
                        </div>
                        <div class="form-group">
                            <label>Sex</label>
                            <select id="edit_sex" name="sex" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Changes</button>
                        <button type="button" class="btn-secondary" onclick="showSection('studentsSection')">Cancel</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
    document.getElementById(sectionId).style.display = 'block';
}

async function loadStudents() {
    try {
        const response = await API.teacher.students();
        if (response.success) {
            renderStudentsTable(response.data);
        } else {
            document.getElementById('studentsTable').innerHTML = '<div class="empty-row">Failed to load students</div>';
        }
    } catch (error) {
        console.error('Failed to load students:', error);
        document.getElementById('studentsTable').innerHTML = '<div class="empty-row">Error loading students</div>';
    }
}

function renderStudentsTable(students) {
    if (students.length === 0) {
        document.getElementById('studentsTable').innerHTML = '<div class="empty-row">No students assigned to your classes.</div>';
        return;
    }

    let html = '<div class="table-wrap"><table>';
    html += '<thead><tr><th>Name</th><th>LRN</th><th>Grade</th><th>Subject</th><th>Actions</th></tr></thead><tbody>';

    students.forEach(student => {
        html += `
            <tr>
                <td class="td-primary">${escapeHtml(student.first_name + ' ' + student.last_name)}</td>
                <td>${escapeHtml(student.lrn || 'N/A')}</td>
                <td>${escapeHtml(student.grade_level || 'N/A')}</td>
                <td>${escapeHtml(student.subject_name || 'N/A')}</td>
                <td>
                    <button type="button" class="btn-secondary" onclick="editStudent(${student.student_id}, '${escapeHtml(student.first_name)}', '${escapeHtml(student.last_name)}', '${escapeHtml(student.grade_level || '')}', '${escapeHtml(student.sex || '')}')">Edit</button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    document.getElementById('studentsTable').innerHTML = html;
}

function editStudent(studentId, firstName, lastName, gradeLevel, sex) {
    document.getElementById('edit_student_id').value = studentId;
    document.getElementById('edit_fname').value = firstName;
    document.getElementById('edit_lname').value = lastName;
    document.getElementById('edit_grade').value = gradeLevel;
    document.getElementById('edit_sex').value = sex;
    showSection('editSection');
}

document.getElementById('editStudentForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        student_id: formData.get('student_id'),
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        grade_level: formData.get('grade_level'),
        sex: formData.get('sex')
    };

    try {
        const response = await API.students.update(data.student_id, data);
        if (response.success) {
            alert('Student updated successfully!');
            showSection('studentsSection');
            loadStudents();
        } else {
            alert('Failed to update student: ' + (response.errors ? response.errors.join(', ') : 'Unknown error'));
        }
    } catch (error) {
        alert('Error updating student: ' + error.message);
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

window.addEventListener('DOMContentLoaded', loadStudents);
</script>

</body>
</html>
