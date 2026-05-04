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
    </style>
</head>

<body>

<header>
    <h2>Gibraltar AMS - Staff Portal</h2>
</header>

<div class="container">

<?php renderTeacherSidebar('students'); ?>

<div class="content">

<!-- ================= CREATE ================= -->
<div class="card">
    <h3>Add Student</h3>

    <input id="lrn" placeholder="LRN">
    <input id="fname" placeholder="First Name">
    <input id="lname" placeholder="Last Name">
    <input id="mname" placeholder="Middle Name">
    <input type="date" id="bdate">

    <select id="sex">
        <option value="">Sex</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
    </select>

    <input id="pob" placeholder="Place of Birth">

    <button onclick="createStudent()">Create Student</button>
</div>

<!-- ================= LIST ================= -->
<div class="card">
    <h3>Students</h3>
    <div id="studentList">Loading...</div>
</div>

<!-- ================= EDIT FULL ================= -->
<div class="card">
    <h3>Edit Student</h3>

    <input type="hidden" id="edit_id">

    <h4>Basic Info</h4>
    <input id="edit_lrn" placeholder="LRN">
    <input id="edit_fname" placeholder="First Name">
    <input id="edit_lname" placeholder="Last Name">
    <input id="edit_mname" placeholder="Middle Name">
    <input type="date" id="edit_bdate">

    <select id="edit_sex">
        <option value="male">Male</option>
        <option value="female">Female</option>
    </select>

    <input id="edit_pob" placeholder="Place of Birth">

    <h4>Current Address</h4>
    <input id="c_house" placeholder="House No">
    <input id="c_street" placeholder="Street">
    <input id="c_barangay" placeholder="Barangay">
    <input id="c_city" placeholder="City">
    <input id="c_province" placeholder="Province">
    <input id="c_country" placeholder="Country">
    <input id="c_zip" placeholder="Zip Code">

    <h4>Permanent Address</h4>
    <input id="p_house" placeholder="House No">
    <input id="p_street" placeholder="Street">
    <input id="p_barangay" placeholder="Barangay">
    <input id="p_city" placeholder="City">
    <input id="p_province" placeholder="Province">
    <input id="p_country" placeholder="Country">
    <input id="p_zip" placeholder="Zip Code">

    <h4>Disabilities</h4>

    <label><input type="checkbox" class="disability" value="1"> Visual Impairment</label>
    <label><input type="checkbox" class="disability" value="2"> Blind</label>
    <label><input type="checkbox" class="disability" value="3"> Low Vision</label>
    <label><input type="checkbox" class="disability" value="4"> Hearing Impairment</label>
    <label><input type="checkbox" class="disability" value="5"> Autism</label>
    <label><input type="checkbox" class="disability" value="6"> Speech Disorder</label>
    <label><input type="checkbox" class="disability" value="7"> Learning Disorder</label>
    <label><input type="checkbox" class="disability" value="8"> Emotional/Behavioral</label>
    <label><input type="checkbox" class="disability" value="9"> Cerebral Palsy</label>
    <label><input type="checkbox" class="disability" value="10"> Intellectual Disability</label>
    <label><input type="checkbox" class="disability" value="11"> Physical Disability</label>

    <button onclick="updateStudent()">Update Full Profile</button>
</div>

</div>
</div>

<script>

const API = '../../api/students.php';

/* ================= LIST ================= */
async function loadStudents() {

    const res = await fetch(API + '?action=list');
    const json = await res.json();

    if (!json.success) return;

    let html = '<table>';
    html += '<tr><th>Name</th><th>LRN</th><th>Action</th></tr>';

    json.data.forEach(s => {
        html += `
        <tr>
            <td>${s.first_name} ${s.last_name}</td>
            <td>${s.lrn ?? ''}</td>
            <td>
                <button onclick="editStudent(${s.student_id})">Edit</button>
                <button onclick="deleteStudent(${s.student_id})">Delete</button>
            </td>
        </tr>`;
    });

    html += '</table>';

    document.getElementById('studentList').innerHTML = html;
}

/* ================= CREATE ================= */
async function createStudent() {

    const data = {
        lrn: lrn.value,
        first_name: fname.value,
        last_name: lname.value,
        middle_name: mname.value,
        birth_date: bdate.value,
        sex: sex.value,
        place_of_birth: pob.value
    };

    await fetch(API + '?action=create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });

    loadStudents();
}

/* ================= EDIT LOAD ================= */
async function editStudent(id) {

    const res = await fetch(API + '?action=get&student_id=' + id);
    const json = await res.json();

    if (!json.success) return;

    const s = json.data.student;
    const c = json.data.current_address;
    const p = json.data.permanent_address;
    const d = json.data.disabilities;

    edit_id.value = s.student_id;

    edit_lrn.value = s.lrn ?? '';
    edit_fname.value = s.first_name;
    edit_lname.value = s.last_name;
    edit_mname.value = s.middle_name ?? '';
    edit_bdate.value = s.birth_date ?? '';
    edit_sex.value = s.sex ?? '';
    edit_pob.value = s.place_of_birth ?? '';

    c_house.value = c.house_no ?? '';
    c_street.value = c.street_name ?? '';
    c_barangay.value = c.barangay ?? '';
    c_city.value = c.municipality_city ?? '';
    c_province.value = c.province ?? '';
    c_country.value = c.country ?? '';
    c_zip.value = c.zip_code ?? '';

    p_house.value = p.house_no ?? '';
    p_street.value = p.street_name ?? '';
    p_barangay.value = p.barangay ?? '';
    p_city.value = p.municipality_city ?? '';
    p_province.value = p.province ?? '';
    p_country.value = p.country ?? '';
    p_zip.value = p.zip_code ?? '';

    document.querySelectorAll('.disability').forEach(cb => {
        cb.checked = d.includes(parseInt(cb.value));
    });
}

/* ================= UPDATE ================= */
async function updateStudent() {

    const disabilities = [];
    document.querySelectorAll('.disability:checked').forEach(cb => {
        disabilities.push(parseInt(cb.value));
    });

    const data = {
        student_id: edit_id.value,

        lrn: edit_lrn.value,
        first_name: edit_fname.value,
        last_name: edit_lname.value,
        middle_name: edit_mname.value,
        birth_date: edit_bdate.value,
        sex: edit_sex.value,
        place_of_birth: edit_pob.value,

        current: {
            house_no: c_house.value,
            street_name: c_street.value,
            barangay: c_barangay.value,
            municipality_city: c_city.value,
            province: c_province.value,
            country: c_country.value,
            zip_code: c_zip.value
        },

        permanent: {
            house_no: p_house.value,
            street_name: p_street.value,
            barangay: p_barangay.value,
            municipality_city: p_city.value,
            province: p_province.value,
            country: p_country.value,
            zip_code: p_zip.value
        },

        disabilities: disabilities
    };

    await fetch(API + '?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });

    loadStudents();
}

/* ================= DELETE ================= */
async function deleteStudent(id) {

    if (!confirm("Delete student?")) return;

    await fetch(API + '?action=delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ student_id: id })
    });

    loadStudents();
}

/* INIT */
loadStudents();

</script>

</body>
</html>