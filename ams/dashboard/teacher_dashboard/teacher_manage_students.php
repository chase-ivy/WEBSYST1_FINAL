<?php
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/teacher_nav.php';

require_role(['staff']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <link rel="stylesheet" href="../../style/style.css">

    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
        .card { background: #fff; padding: 15px; margin-bottom: 15px; border-radius: 6px; }
        input, select { padding: 6px; margin: 5px 0; width: 100%; }
        button { padding: 6px 10px; cursor: pointer; }
        label { display:block; margin:4px 0; }
        .loading { color: #999; font-style: italic; }
        .section { display: none; }
        .section.active { display: block; }
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Teacher Portal</h2>
</header>

<div class="dashboard-layout">

<?php renderTeacherSidebar('students'); ?>

<div class="content">

<!-- ================= STUDENTS LIST ================= -->
<div id="students" class="section active">
    <div class="card">
        <h3>My Students</h3>
        <div id="studentsTable">
            <div class="loading">Loading students...</div>
        </div>
    </div>
</div>

<!-- ================= EDIT FORM ================= -->
<div id="editForm" class="section">
    <div class="card">
        <h3>Edit Student</h3>

        <form id="editStudentForm">
            <input type="hidden" id="edit_student_id" name="student_id">

            <label>First Name:</label>
            <input type="text" id="edit_fname" name="first_name" required>

            <label>Last Name:</label>
            <input type="text" id="edit_lname" name="last_name" required>

            <label>Grade Level:</label>
            <input type="text" id="edit_grade" name="grade_level" required>

            <label>Sex:</label>
            <select id="edit_sex" name="sex" required>
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <button type="submit">Save Changes</button>
            <button type="button" onclick="showSection('students')">Cancel</button>
        </form>
    </div>
</div>

</div>
</div>

<script src="../../api/client.js"></script>
<script>
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');
}

async function loadStudents() {
    try {
        const response = await API.teacher.students();
        if (response.success) {
            renderStudentsTable(response.data);
        } else {
            document.getElementById('studentsTable').innerHTML = 'Failed to load students';
        }
    } catch (error) {
        console.error('Failed to load students:', error);
        document.getElementById('studentsTable').innerHTML = 'Error loading students';
    }
}

function renderStudentsTable(students) {
    if (students.length === 0) {
        document.getElementById('studentsTable').innerHTML = '<p>No students assigned to your classes.</p>';
        return;
    }

    let html = `
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>LRN</th>
                    <th>Grade</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    students.forEach(student => {
        html += `
            <tr>
                <td>${escapeHtml(student.first_name + ' ' + student.last_name)}</td>
                <td>${escapeHtml(student.lrn || 'N/A')}</td>
                <td>${escapeHtml(student.grade_level || 'N/A')}</td>
                <td>${escapeHtml(student.subject_name || 'N/A')}</td>
                <td>
                    <button onclick="editStudent(${student.student_id}, '${escapeHtml(student.first_name)}', '${escapeHtml(student.last_name)}', '${escapeHtml(student.grade_level || '')}', '${escapeHtml(student.sex || '')}')">Edit</button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    document.getElementById('studentsTable').innerHTML = html;
}

function editStudent(studentId, firstName, lastName, gradeLevel, sex) {
    document.getElementById('edit_student_id').value = studentId;
    document.getElementById('edit_fname').value = firstName;
    document.getElementById('edit_lname').value = lastName;
    document.getElementById('edit_grade').value = gradeLevel;
    document.getElementById('edit_sex').value = sex;
    showSection('editForm');
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
            showSection('students');
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

// Load students on page load
loadStudents();
</script>

</body>
</html>
