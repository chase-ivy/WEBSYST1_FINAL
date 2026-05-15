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
                    <button class="btn-secondary btn-sm" type="button" onclick="downloadEnrollmentForm(${student.student_id})">Download Enrollment Form</button>
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

        const data = res.data;
        const student = data.student || {};
        const enrollment = data.latest_enrollment || {};
        const currentAddress = data.current_address || {};
        const permanentAddress = data.permanent_address || {};
        const parents = data.parents || {};
        const returning = data.returning || {};
        const disabilities = data.disabilities || [];
        const medical = data.medical || {};

        let medicalAllergyItems = [];
        let medicalConditionItems = [];
        let medicalSurgery = {};
        let medicalTreatment = {};
        let medicalFamilyHistory = {};
        if (enrollment.enrollment_id) {
            try {
                const medicalRes = await API.medical.getByEnrollment(enrollment.enrollment_id);
                if (medicalRes && medicalRes.success && medicalRes.data) {
                    medicalAllergyItems = Array.isArray(medicalRes.data.allergies) ? medicalRes.data.allergies : [];
                    medicalConditionItems = Array.isArray(medicalRes.data.conditions) ? medicalRes.data.conditions : [];
                    medicalSurgery = Array.isArray(medicalRes.data.surgeries) ? medicalRes.data.surgeries[0] || {} : {};
                    medicalTreatment = Array.isArray(medicalRes.data.treatments) ? medicalRes.data.treatments[0] || {} : {};
                    medicalFamilyHistory = medicalRes.data.family_history || {};
                }
            } catch (err) {
                console.warn('Medical detail fetch failed', err);
            }
        }

        const selectedAllergyIds = medicalAllergyItems.map(item => Number(item.allergy_type_id));
        const allergyDescriptions = medicalAllergyItems.reduce((map, item) => {
            map[Number(item.allergy_type_id)] = item.description || '';
            return map;
        }, {});

        const selectedConditionIds = medicalConditionItems.map(item => Number(item.condition_type_id));
        const conditionDescription = (medicalConditionItems.find(item => Number(item.condition_type_id) === 8) || {}).description || '';

        const selectedFamilyConditionIds = Array.isArray(medicalFamilyHistory.conditions)
            ? medicalFamilyHistory.conditions.map(item => Number(item.family_condition_type_id))
            : [];
        const familyCancerDescription = Array.isArray(medicalFamilyHistory.conditions)
            ? (medicalFamilyHistory.conditions.find(item => item.condition_name?.toLowerCase().includes('cancer')) || {}).description || ''
            : '';
        const familyOtherDescription = Array.isArray(medicalFamilyHistory.conditions)
            ? (medicalFamilyHistory.conditions.find(item => item.condition_name?.toLowerCase().includes('other')) || {}).description || ''
            : '';

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

                <!-- Returning Learner Details -->
                <div class="form-section" id="returningSection" style="display: ${enrollment.is_returning_learner == 1 ? 'block' : 'none'};">
                    <h4>Returning Learner Details</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Returning_Grade_Level">Last Grade Level Completed</label>
                            <select id="Returning_Grade_Level" name="Returning_Grade_Level">
                                <option value="">Select</option>
                                <option value="Kinder" ${returning.last_grade_level_completed === 'Kinder' ? 'selected' : ''}>Kinder</option>
                                <option value="Grade 1" ${returning.last_grade_level_completed === 'Grade 1' ? 'selected' : ''}>Grade 1</option>
                                <option value="Grade 2" ${returning.last_grade_level_completed === 'Grade 2' ? 'selected' : ''}>Grade 2</option>
                                <option value="Grade 3" ${returning.last_grade_level_completed === 'Grade 3' ? 'selected' : ''}>Grade 3</option>
                                <option value="Grade 4" ${returning.last_grade_level_completed === 'Grade 4' ? 'selected' : ''}>Grade 4</option>
                                <option value="Grade 5" ${returning.last_grade_level_completed === 'Grade 5' ? 'selected' : ''}>Grade 5</option>
                                <option value="Grade 6" ${returning.last_grade_level_completed === 'Grade 6' ? 'selected' : ''}>Grade 6</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Last_School_Year_Completed">Last School Year Completed</label>
                            <input id="Last_School_Year_Completed" name="Last_School_Year_Completed" type="number" value="${escapeHtml(returning.last_school_year_completed || '')}" />
                        </div>
                        <div class="form-group full">
                            <label for="Last_School_Attended">Last School Attended</label>
                            <input id="Last_School_Attended" name="Last_School_Attended" value="${escapeHtml(returning.last_school_attended || '')}" />
                        </div>
                        <div class="form-group full">
                            <label for="school_ID">School ID</label>
                            <input id="school_ID" name="school_ID" value="${escapeHtml(returning.school_id || '')}" />
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                    <h4>Personal Information</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="psa_bcn">PSA Birth Certificate No.</label>
                            <input id="psa_bcn" name="psa_bcn" value="${escapeHtml(enrollment.psa_bcn || '')}" />
                        </div>
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
                        <div class="form-group">
                            <label for="Learner_Extension_Name">Extension Name</label>
                            <input id="Learner_Extension_Name" name="Learner_Extension_Name" value="${escapeHtml(student.extension_name || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Birth_Date">Birth Date</label>
                            <input id="Birth_Date" name="Birth_Date" type="date" value="${escapeHtml(student.birth_date || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="sex">Sex</label>
                            <select id="sex" name="sex">
                                <option value="">Select</option>
                                <option value="Male" ${student.sex === 'Male' ? 'selected' : ''}>Male</option>
                                <option value="Female" ${student.sex === 'Female' ? 'selected' : ''}>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Place_of_Birth">Place of Birth</label>
                            <input id="Place_of_Birth" name="Place_of_Birth" value="${escapeHtml(student.place_of_birth || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Mother_Tongue">Mother Tongue</label>
                            <input id="Mother_Tongue" name="Mother_Tongue" value="${escapeHtml(enrollment.mother_tongue || '')}" />
                        </div>
                    </div>
                </div>

                <!-- Additional Classification -->
                <div class="form-section">
                    <h4>Additional Classification</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="ip">Indigenous People (IP)?</label>
                            <select id="ip" name="ip">
                                <option value="No" ${enrollment.is_indigenous == 0 ? 'selected' : ''}>No</option>
                                <option value="Yes" ${enrollment.is_indigenous == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="IP_Specify">IP Community / Cultural Group</label>
                            <input id="IP_Specify" name="IP_Specify" value="${escapeHtml(enrollment.indigenous_group || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="fourps">4Ps Beneficiary?</label>
                            <select id="fourps" name="fourps">
                                <option value="No" ${enrollment.is_four_ps_beneficiary == 0 ? 'selected' : ''}>No</option>
                                <option value="Yes" ${enrollment.is_four_ps_beneficiary == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="FourPs_Specify">4Ps Household ID Number</label>
                            <input id="FourPs_Specify" name="FourPs_Specify" value="${escapeHtml(enrollment.four_ps_household_id || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="disability">Learner with Disability?</label>
                            <select id="disability" name="disability">
                                <option value="No" ${enrollment.is_learner_with_disability == 0 ? 'selected' : ''}>No</option>
                                <option value="Yes" ${enrollment.is_learner_with_disability == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Disabilities -->
                <div class="form-section" id="disabilitySection" style="display: ${enrollment.is_learner_with_disability == 1 ? 'block' : 'none'};">
                    <h4>Disabilities</h4>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Disability Types</label>
                            <div>
                                <label><input type="checkbox" name="disabilities[]" value="1" ${disabilities.includes(1) ? 'checked' : ''}> Visual Impairment</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="2" ${disabilities.includes(2) ? 'checked' : ''}> Hearing Impairment</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="3" ${disabilities.includes(3) ? 'checked' : ''}> Learning Disability</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="4" ${disabilities.includes(4) ? 'checked' : ''}> Intellectual Disability</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="5" ${disabilities.includes(5) ? 'checked' : ''}> Autism Spectrum Disorder</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="6" ${disabilities.includes(6) ? 'checked' : ''}> Emotional / Behavioral Disorder</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="7" ${disabilities.includes(7) ? 'checked' : ''}> Speech / Language Disorder</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="8" ${disabilities.includes(8) ? 'checked' : ''}> Cerebral Palsy</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="9" ${disabilities.includes(9) ? 'checked' : ''}> Orthopedic / Physical Handicap</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="10" ${disabilities.includes(10) ? 'checked' : ''}> Special Health Problem</label><br>
                                <label><input type="checkbox" name="disabilities[]" value="11" ${disabilities.includes(11) ? 'checked' : ''}> Multiple Disorder</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Information -->
                <div class="form-section">
                    <h4>Medical Information</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="has_allergies">Does your child/ward have any allergies?</label>
                            <select id="has_allergies" name="has_allergies">
                                <option value="0" ${medical.allergies?.has_allergies == 0 ? 'selected' : ''}>No</option>
                                <option value="1" ${medical.allergies?.has_allergies == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group full" id="has_allergies_details"></div>
                        <div class="form-group">
                            <label for="has_med_condition">Does your child/ward have any ongoing medical condition?</label>
                            <select id="has_med_condition" name="has_med_condition">
                                <option value="0" ${medical.conditions?.has_conditions == 0 ? 'selected' : ''}>No</option>
                                <option value="1" ${medical.conditions?.has_conditions == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group full" id="has_med_condition_details"></div>
                        <div class="form-group">
                            <label for="has_surgery_hospitalization">Did your child/ward ever have surgery / hospitalization?</label>
                            <select id="has_surgery_hospitalization" name="has_surgery_hospitalization">
                                <option value="0" ${medical.surgeries?.has_surgery == 0 ? 'selected' : ''}>No</option>
                                <option value="1" ${medical.surgeries?.has_surgery == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group full" id="has_surgery_hospitalization_details"></div>
                        <div class="form-group">
                            <label for="is_taking_treatment">Is your child currently taking treatment / medicines?</label>
                            <select id="is_taking_treatment" name="is_taking_treatment">
                                <option value="0" ${medical.treatments?.is_taking_treatment == 0 ? 'selected' : ''}>No</option>
                                <option value="1" ${medical.treatments?.is_taking_treatment == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group full" id="is_taking_treatment_details"></div>
                        <div class="form-group">
                            <label for="family_medical_history">Does your family have a history of medical conditions?</label>
                            <select id="family_medical_history" name="family_medical_history">
                                <option value="0" ${medical.family_history?.has_family_history == 0 ? 'selected' : ''}>No</option>
                                <option value="1" ${medical.family_history?.has_family_history == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group full" id="family_medical_history_details"></div>
                        <div class="form-group">
                            <label for="exposed_to_cigarette_vape_smoke">Does your child/ward have exposure to cigarette/vape smoke at home?</label>
                            <select id="exposed_to_cigarette_vape_smoke" name="exposed_to_cigarette_vape_smoke">
                                <option value="0" ${medical.exposed_to_cigarette_vape_smoke == 0 ? 'selected' : ''}>No</option>
                                <option value="1" ${medical.exposed_to_cigarette_vape_smoke == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="other_pertinent_information">Other pertinent learner information:</label>
                            <input id="other_pertinent_information" name="other_pertinent_information" value="${escapeHtml(medical.other_pertinent_information || '')}" placeholder="Optional">
                        </div>
                    </div>
                </div>

                <!-- Current Address -->
                <div class="form-section">
                    <h4>Current Address</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Current_House_No">House No.</label>
                            <input id="Current_House_No" name="Current_House_No" value="${escapeHtml(currentAddress.house_no || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Current_Street_Name">Street Name</label>
                            <input id="Current_Street_Name" name="Current_Street_Name" value="${escapeHtml(currentAddress.street_name || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Current_Barangay">Barangay</label>
                            <input id="Current_Barangay" name="Current_Barangay" value="${escapeHtml(currentAddress.barangay || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Current_Municipality_City">Municipality / City</label>
                            <input id="Current_Municipality_City" name="Current_Municipality_City" value="${escapeHtml(currentAddress.municipality_city || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Current_Province">Province</label>
                            <input id="Current_Province" name="Current_Province" value="${escapeHtml(currentAddress.province || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Current_Country">Country</label>
                            <input id="Current_Country" name="Current_Country" value="${escapeHtml(currentAddress.country || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Current_Zip_Code">Zip Code</label>
                            <input id="Current_Zip_Code" name="Current_Zip_Code" value="${escapeHtml(currentAddress.zip_code || '')}" />
                        </div>
                    </div>
                </div>

                <!-- Permanent Address -->
                <div class="form-section">
                    <h4>Permanent Address</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Permanent_House_No">House No.</label>
                            <input id="Permanent_House_No" name="Permanent_House_No" value="${escapeHtml(permanentAddress.house_no || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Permanent_Street_Name">Street Name</label>
                            <input id="Permanent_Street_Name" name="Permanent_Street_Name" value="${escapeHtml(permanentAddress.street_name || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Permanent_Barangay">Barangay</label>
                            <input id="Permanent_Barangay" name="Permanent_Barangay" value="${escapeHtml(permanentAddress.barangay || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Permanent_Municipality_City">Municipality / City</label>
                            <input id="Permanent_Municipality_City" name="Permanent_Municipality_City" value="${escapeHtml(permanentAddress.municipality_city || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Permanent_Province">Province</label>
                            <input id="Permanent_Province" name="Permanent_Province" value="${escapeHtml(permanentAddress.province || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Permanent_Country">Country</label>
                            <input id="Permanent_Country" name="Permanent_Country" value="${escapeHtml(permanentAddress.country || '')}" />
                        </div>
                        <div class="form-group">
                            <label for="Permanent_Zip_Code">Zip Code</label>
                            <input id="Permanent_Zip_Code" name="Permanent_Zip_Code" value="${escapeHtml(permanentAddress.zip_code || '')}" />
                        </div>
                    </div>
                </div>

                <!-- Parents / Guardians -->
                <div class="form-section">
                    <h4>Parents / Guardians</h4>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Father</label>
                            <div class="form-grid">
                                <div class="form-group">
                                    <input name="father_last_name" placeholder="Last Name" value="${escapeHtml((parents.father || {}).last_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="father_first_name" placeholder="First Name" value="${escapeHtml((parents.father || {}).first_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="father_middle_name" placeholder="Middle Name" value="${escapeHtml((parents.father || {}).middle_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="father_contact_number" placeholder="Contact Number" value="${escapeHtml((parents.father || {}).contact_number || '')}" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group full">
                            <label>Mother</label>
                            <div class="form-grid">
                                <div class="form-group">
                                    <input name="mother_last_name" placeholder="Last Name" value="${escapeHtml((parents.mother || {}).last_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="mother_first_name" placeholder="First Name" value="${escapeHtml((parents.mother || {}).first_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="mother_middle_name" placeholder="Middle Name" value="${escapeHtml((parents.mother || {}).middle_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="mother_contact_number" placeholder="Contact Number" value="${escapeHtml((parents.mother || {}).contact_number || '')}" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group full">
                            <label>Guardian</label>
                            <div class="form-grid">
                                <div class="form-group">
                                    <input name="guardian_last_name" placeholder="Last Name" value="${escapeHtml((parents.guardian || {}).last_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="guardian_first_name" placeholder="First Name" value="${escapeHtml((parents.guardian || {}).first_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="guardian_middle_name" placeholder="Middle Name" value="${escapeHtml((parents.guardian || {}).middle_name || '')}" />
                                </div>
                                <div class="form-group">
                                    <input name="guardian_contact_number" placeholder="Contact Number" value="${escapeHtml((parents.guardian || {}).contact_number || '')}" />
                                </div>
                            </div>
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

        // Toggle sections
        document.getElementById('returning').addEventListener('change', function() {
            document.getElementById('returningSection').style.display = this.value === '1' ? 'block' : 'none';
        });
        document.getElementById('disability').addEventListener('change', function() {
            document.getElementById('disabilitySection').style.display = this.value === 'Yes' ? 'block' : 'none';
        });

        const medicalData = medical || {};

        const allergiesToggle = document.getElementById('has_allergies');
        const conditionsToggle = document.getElementById('has_med_condition');
        const surgeryToggle = document.getElementById('has_surgery_hospitalization');
        const treatmentToggle = document.getElementById('is_taking_treatment');
        const familyHistoryToggle = document.getElementById('family_medical_history');

        allergiesToggle.addEventListener('change', function() {
            renderAllergyDetails(this.value === '1', selectedAllergyIds, allergyDescriptions);
        });
        conditionsToggle.addEventListener('change', function() {
            renderConditionDetails(this.value === '1', selectedConditionIds, conditionDescription);
        });
        surgeryToggle.addEventListener('change', function() {
            renderSurgeryDetails(this.value === '1', medicalSurgery);
        });
        treatmentToggle.addEventListener('change', function() {
            renderTreatmentDetails(this.value === '1', medicalTreatment);
        });
        familyHistoryToggle.addEventListener('change', function() {
            renderFamilyHistoryDetails(this.value === '1', selectedFamilyConditionIds, familyCancerDescription, familyOtherDescription);
        });

        renderAllergyDetails(allergiesToggle.value === '1', selectedAllergyIds, allergyDescriptions);
        renderConditionDetails(conditionsToggle.value === '1', selectedConditionIds, conditionDescription);
        renderSurgeryDetails(surgeryToggle.value === '1', medicalSurgery);
        renderTreatmentDetails(treatmentToggle.value === '1', medicalTreatment);
        renderFamilyHistoryDetails(familyHistoryToggle.value === '1', selectedFamilyConditionIds, familyCancerDescription, familyOtherDescription);
    } catch (error) {
        console.error(error);
        showAlert('error', `Unable to open enrollment editor: ${error.message}`);
    }
}

function renderAllergyDetails(show, selectedIds = [], descriptions = {}) {
    const target = document.getElementById('has_allergies_details');
    if (!target) return;
    target.innerHTML = '';
    if (!show) return;

    target.innerHTML = `
        <div style="display:grid; gap:10px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" id="medicine_allergy_checkbox" name="medicine_allergy[]" value="1" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(1) ? 'checked' : ''}>
                Medicine
            </label>
            <div id="medicineAllergyBox" style="display:${selectedIds.includes(1) ? 'block' : 'none'}; margin-left:23px;">
                <input type="text" name="allergy_description[1]" placeholder="Please specify" value="${escapeHtml(descriptions[1] || '')}" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
            </div>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" id="pollen_allergy_checkbox" name="medicine_allergy[]" value="2" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(2) ? 'checked' : ''}>
                Pollen
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" id="food_allergy_checkbox" name="medicine_allergy[]" value="3" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(3) ? 'checked' : ''}>
                Food
            </label>
            <div id="foodAllergyBox" style="display:${selectedIds.includes(3) ? 'block' : 'none'}; margin-left:23px;">
                <input type="text" name="allergy_description[3]" placeholder="Please specify" value="${escapeHtml(descriptions[3] || '')}" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
            </div>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" id="other_allergy_checkbox" name="medicine_allergy[]" value="4" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(4) ? 'checked' : ''}>
                Others
            </label>
            <div id="otherAllergyBox" style="display:${selectedIds.includes(4) ? 'block' : 'none'}; margin-left:23px;">
                <input type="text" name="allergy_description[4]" placeholder="Please specify" value="${escapeHtml(descriptions[4] || '')}" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
            </div>
        </div>
    `;

    attachToggle('medicine_allergy_checkbox', 'medicineAllergyBox');
    attachToggle('food_allergy_checkbox', 'foodAllergyBox');
    attachToggle('other_allergy_checkbox', 'otherAllergyBox');
}

function renderConditionDetails(show, selectedIds = [], description = '') {
    const target = document.getElementById('has_med_condition_details');
    if (!target) return;
    target.innerHTML = '';
    if (!show) return;

    target.innerHTML = `
        <div style="display:grid; gap:10px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="condition_type_id[]" value="1" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(1) ? 'checked' : ''}>
                Error of refraction (Eye Ailment)
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="condition_type_id[]" value="2" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(2) ? 'checked' : ''}>
                Asthma (Lung Ailment)
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="condition_type_id[]" value="3" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(3) ? 'checked' : ''}>
                Seizure (Convulsions)
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="condition_type_id[]" value="4" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(4) ? 'checked' : ''}>
                Heart Illness
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="condition_type_id[]" value="5" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(5) ? 'checked' : ''}>
                Anemia
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="condition_type_id[]" value="6" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(6) ? 'checked' : ''}>
                Bleeding disorder
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="condition_type_id[]" value="7" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(7) ? 'checked' : ''}>
                Fracture / Dislocation
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" id="has_medical_condition" name="condition_type_id[]" value="8" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(8) ? 'checked' : ''}>
                Others
            </label>
            <div id="medical_condition_details" class="medical-detail-input-wrapper" style="display:${selectedIds.includes(8) ? 'block' : 'none'};">
                <input type="text" name="condition_description" placeholder="Please specify" class="medical-detail-input" value="${escapeHtml(description)}">
            </div>
        </div>
    `;

    attachToggle('has_medical_condition', 'medical_condition_details');
}

function renderSurgeryDetails(show, surgery = {}) {
    const target = document.getElementById('has_surgery_hospitalization_details');
    if (!target) return;
    target.innerHTML = '';
    if (!show) return;

    target.innerHTML = `
        <div class="medical-detail-panel">
            <div class="medical-detail-grid-1">
                <div class="medical-detail-field">
                    <label>Surgery Date</label>
                    <input type="date" name="surgery_date" class="medical-detail-input" value="${escapeHtml(surgery.surgery_date || '')}" />
                </div>
                <div class="medical-detail-field">
                    <label>Hospital Name</label>
                    <input type="text" name="hospital_name" placeholder="Hospital name" class="medical-detail-input" value="${escapeHtml(surgery.hospital_name || '')}" />
                </div>
                <div class="medical-detail-field" style="grid-column:1 / -1;">
                    <label>Body Part Affected</label>
                    <input type="text" name="body_part" placeholder="What part of the body?" class="medical-detail-input" value="${escapeHtml(surgery.body_part || '')}" />
                </div>
            </div>
        </div>
    `;
}

function renderTreatmentDetails(show, treatment = {}) {
    const target = document.getElementById('is_taking_treatment_details');
    if (!target) return;
    target.innerHTML = '';
    if (!show) return;

    target.innerHTML = `
        <div class="medical-detail-panel">
            <div class="medical-detail-grid-1">
                <div class="medical-detail-field">
                    <label>Medicine / Treatment Type</label>
                    <input type="text" name="treatment_medicine" placeholder="Name of medicine or treatment" class="medical-detail-input" value="${escapeHtml(treatment.treatment_medicine || '')}" />
                </div>
                <div class="medical-detail-field">
                    <label>Dosage Schedule</label>
                    <input type="text" name="schedule_dosage" placeholder="e.g., 2x daily, morning/evening" class="medical-detail-input" value="${escapeHtml(treatment.schedule_dosage || '')}" />
                </div>
            </div>
        </div>
    `;
}

function renderFamilyHistoryDetails(show, selectedIds = [], cancerDescription = '', otherDescription = '') {
    const target = document.getElementById('family_medical_history_details');
    if (!target) return;
    target.innerHTML = '';
    if (!show) return;

    target.innerHTML = `
        <div style="display:grid; gap:10px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="family_condition_type_id[]" value="1" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(1) ? 'checked' : ''}>
                Tuberculosis
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" id="has_cancer" name="family_condition_type_id[]" value="2" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(2) ? 'checked' : ''}>
                Cancer
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="family_condition_type_id[]" value="3" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(3) ? 'checked' : ''}>
                Diabetes Mellitus
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="family_condition_type_id[]" value="4" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(4) ? 'checked' : ''}>
                Hypertension
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="family_condition_type_id[]" value="5" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(5) ? 'checked' : ''}>
                Stroke / Heart attack
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="family_condition_type_id[]" value="6" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(6) ? 'checked' : ''}>
                Depression
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" name="family_condition_type_id[]" value="7" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(7) ? 'checked' : ''}>
                Kidney problems
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px;">
                <input type="checkbox" id="has_other" name="family_condition_type_id[]" value="8" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;" ${selectedIds.includes(8) ? 'checked' : ''}>
                Others
            </label>
            <div id="otherBox" class="medical-detail-input-wrapper" style="display:${selectedIds.includes(8) ? 'block' : 'none'};">
                <input type="text" name="family_condition_description" placeholder="Please specify" class="medical-detail-input" value="${escapeHtml(otherDescription)}">
            </div>
            <div id="cancerBox" class="medical-detail-input-wrapper" style="display:${selectedIds.includes(2) ? 'block' : 'none'};">
                <input type="text" name="family_condition_description" placeholder="Specify type of cancer" class="medical-detail-input" value="${escapeHtml(cancerDescription)}">
            </div>
        </div>
    `;

    attachToggle('has_cancer', 'cancerBox');
    attachToggle('has_other', 'otherBox');
}

function attachToggle(checkboxId, boxId) {
    const checkbox = document.getElementById(checkboxId);
    const box = document.getElementById(boxId);
    if (!checkbox || !box) return;
    checkbox.addEventListener('change', function() {
        box.style.display = this.checked ? 'block' : 'none';
    });
    box.style.display = checkbox.checked ? 'block' : 'none';
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

        if (teacherClassesCache.length === 0) {
            const header = `<h3>Assign Student to Class</h3>`;
            const body = `
                <div class="form-group full">
                    <p>You don't have any classes available to assign students to. Please create a class first.</p>
                </div>
                <div class="form-actions">
                    <button class="btn-secondary" type="button" onclick="closeModal()">Close</button>
                    <a href="teacher_classes.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Create Class</a>
                </div>
            `;
            showModal({ header, body });
            return;
        }

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

function downloadEnrollmentForm(studentId) {
    window.open(`../../forms/enrollment_form/pdf.php?student_id=${studentId}&type=combined`, '_blank');
}

   //INIT
document.addEventListener('DOMContentLoaded', () => {
    loadStudents();
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    if (editId) {
        openEnrollmentModal(editId);
    }
    const assignId = urlParams.get('assign');
    if (assignId) {
        openAssignClassModal(assignId);
    }
});
</script>

</body>
</html>