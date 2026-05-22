const apiBase = '../../../api/verify_enrollment.php';
let currentEnrollmentData = null;

function escapeHtml(text) {
    if (text === undefined || text === null) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function setMessage(type, message) {
    const container = document.getElementById('veMessage');
    if (!container) return;
    container.className = 'message';
    if (type === 'success') container.classList.add('success');
    if (type === 'error') container.classList.add('error');
    container.textContent = message;
}

function getField(name) {
    const byId = document.getElementById(name);
    if (byId) return byId;

    const fields = Array.from(document.querySelectorAll(`[name="${name}"]`));
    if (fields.length === 0) {
        return null;
    }

    if (fields[0].type === 'radio' || fields[0].type === 'checkbox') {
        return fields.find(field => field.checked) || fields[0];
    }

    return fields[0];
}

function getFields(name) {
    const fields = Array.from(document.querySelectorAll(`[name="${name}"]`));
    if (fields.length > 0) {
        return fields;
    }
    const element = document.getElementById(name);
    return element ? [element] : [];
}

function getInput(id) {
    return getField(id);
}

function setValue(name, value) {
    const fields = getFields(name);
    if (fields.length === 0) {
        return;
    }

    if (fields.length > 1) {
        fields.forEach(field => {
            if (field.type === 'radio') {
                field.checked = String(field.value) === String(value);
                return;
            }
            if (field.type === 'checkbox') {
                if (Array.isArray(value)) {
                    field.checked = value.includes(field.value);
                } else {
                    field.checked = value === '1' || value === 1 || value === true || String(value).toLowerCase() === 'yes';
                }
                return;
            }
            field.value = value ?? '';
        });
        return;
    }

    const field = fields[0];
    if (!field) return;

    if (field.type === 'radio') {
        const radio = document.querySelector(`[name="${name}"][value="${value}"]`);
        if (radio) {
            radio.checked = true;
        }
        return;
    }

    if (field.type === 'checkbox') {
        field.checked = value === '1' || value === 1 || value === true || String(value).toLowerCase() === 'yes';
        return;
    }

    field.value = value ?? '';
}

function getAddressByType(addresses, type) {
    if (!Array.isArray(addresses)) return null;
    return addresses.find(a => String(a.address_type || '').toLowerCase() === String(type || '').toLowerCase()) || addresses[0] || null;
}

function goToAndReview(n) {
    goTo(n);
    if (n === 5) {
        buildReviewSummary();
    }
}

function serializeMedicalList(items) {
    if (!Array.isArray(items) || items.length === 0) return '';
    return items.map(item => {
        if (!item || typeof item !== 'object') return String(item);
        const parts = Object.entries(item)
            .filter(([key, value]) => value !== null && value !== undefined && String(value).trim() !== '')
            .map(([key, value]) => `${key.replace(/_/g, ' ')}: ${value}`);
        return parts.join(', ');
    }).join('\n');
}

function formatListText(items) {
    if (!Array.isArray(items) || items.length === 0) return '';
    return items.map(item => (item && item.name) ? item.name : String(item)).join('\n');
}

function buildReviewSummary() {
    const summary = document.getElementById('reviewSummary');
    if (!summary) return;

    const lines = [];
    const yearStart = getInput('year_start')?.value || '';
    const yearEnd = getInput('year_end')?.value || '';
    const schoolYear = [yearStart, yearEnd].filter(Boolean).join('-');
    if (schoolYear) {
        lines.push(`<div><strong>School Year:</strong> ${escapeHtml(schoolYear)}</div>`);
    }

    const gradeLevel = getInput('Grade_Level')?.value || '';
    if (gradeLevel) {
        lines.push(`<div><strong>Grade Level:</strong> ${escapeHtml(gradeLevel)}</div>`);
    }

    const lrn = getInput('Learner_Reference_No')?.value || '';
    if (lrn) {
        lines.push(`<div><strong>LRN:</strong> ${escapeHtml(lrn)}</div>`);
    }

    const fullName = [
        getInput('Learner_Last_Name')?.value,
        getInput('Learner_First_Name')?.value,
        getInput('Learner_Middle_Name')?.value,
        getInput('Learner_Extension_Name')?.value,
    ].filter(Boolean).join(' ');
    if (fullName) {
        lines.push(`<div><strong>Student Name:</strong> ${escapeHtml(fullName)}</div>`);
    }

    const birthDate = getInput('Birth_Date')?.value || '';
    if (birthDate) {
        lines.push(`<div><strong>Birth Date:</strong> ${escapeHtml(birthDate)}</div>`);
    }

    const sex = getInput('sex')?.value || '';
    if (sex) {
        lines.push(`<div><strong>Sex:</strong> ${escapeHtml(sex)}</div>`);
    }

    const placeOfBirth = getInput('Place_of_Birth')?.value || '';
    if (placeOfBirth) {
        lines.push(`<div><strong>Place of Birth:</strong> ${escapeHtml(placeOfBirth)}</div>`);
    }

    const motherTongue = getInput('Mother_Tongue')?.value || '';
    if (motherTongue) {
        lines.push(`<div><strong>Mother Tongue:</strong> ${escapeHtml(motherTongue)}</div>`);
    }

    const indigenous = getInput('ip')?.value || '';
    if (indigenous) {
        lines.push(`<div><strong>Indigenous Group:</strong> ${escapeHtml(indigenous)}</div>`);
    }

    const fourps = getInput('fourps')?.value || '';
    if (fourps) {
        lines.push(`<div><strong>4Ps Beneficiary:</strong> ${escapeHtml(fourps)}</div>`);
    }

    const fourpsId = getInput('FourPs_Specify')?.value || '';
    if (fourpsId) {
        lines.push(`<div><strong>4Ps Household ID:</strong> ${escapeHtml(fourpsId)}</div>`);
    }

    const disability = getInput('disability')?.value || '';
    if (disability) {
        lines.push(`<div><strong>Learner with Disability:</strong> ${escapeHtml(disability)}</div>`);
    }

    const returning = getInput('returning')?.value || '';
    if (returning) {
        lines.push(`<div><strong>Returning Learner:</strong> ${escapeHtml(returning)}</div>`);
    }

    const addresses = [
        ['Current Address', ['Current_House_No','Current_Street_Name','Current_Barangay','Current_Municipality_City','Current_Province','Current_Zip_Code']],
        ['Permanent Address', ['Permanent_House_No','Permanent_Street_Name','Permanent_Barangay','Permanent_Municipality_City','Permanent_Province','Permanent_Zip_Code']],
    ];
    addresses.forEach(([label, ids]) => {
        const parts = ids.map(id => getInput(id)?.value || '').filter(Boolean);
        if (parts.length) {
            lines.push(`<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(parts.join(', '))}</div>`);
        }
    });

    const parentEntries = [
        ['Father', ['father_last_name','father_first_name','father_middle_name','father_contact_number']],
        ['Mother', ['mother_last_name','mother_first_name','mother_middle_name','mother_contact_number']],
        ['Guardian', ['guardian_last_name','guardian_first_name','guardian_middle_name','guardian_contact_number']],
    ];
    parentEntries.forEach(([label, ids]) => {
        const values = ids.map(id => getInput(id)?.value || '').filter(Boolean);
        if (values.length) {
            lines.push(`<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(values.join(' '))}</div>`);
        }
    });

    const medicalFields = [
        ['Allergies', 'has_allergies'],
        ['Medical condition', 'has_med_condition'],
        ['Surgery / hospitalization', 'has_surgery_hospitalization'],
        ['Treatment / medicines', 'is_taking_treatment'],
        ['Family medical history', 'family_medical_history'],
        ['Exposure to smoke', 'exposed_to_cigarette_vape_smoke'],
        ['Other medical notes', 'other_pertinent_information'],
    ];
    medicalFields.forEach(([label, id]) => {
        const value = getInput(id)?.value || '';
        if (value) {
            lines.push(`<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</div>`);
        }
    });

    summary.innerHTML = lines.length ? lines.join('') : '<em>No review information available.</em>';
}

function applyEnrollmentToForm(data) {
    currentEnrollmentData = data;
    if (!data || !data.enrollment) return;

    const enrollment = data.enrollment;
    const studentIdInput = document.getElementById('studentIdInput');
    const enrollmentIdInput = document.getElementById('enrollmentIdInput');

    if (studentIdInput) {
        studentIdInput.value = enrollment.student_id || '';
    }
    if (enrollmentIdInput) {
        enrollmentIdInput.value = enrollment.enrollment_id || '';
    }

    if (typeof enrollment.school_year === 'string' && enrollment.school_year.includes('-')) {
        const parts = enrollment.school_year.split('-').map(part => part.trim());
        setValue('year_start', parts[0] || '');
        setValue('year_end', parts[1] || '');
    } else {
        setValue('year_start', enrollment.year_start);
        setValue('year_end', enrollment.year_end);
    }

    setValue('Grade_Level', enrollment.grade_level);
    setValue('Learner_Reference_No', enrollment.lrn);
    setValue('with_lrn', enrollment.with_lrn ? '1' : '0');
    setValue('psa_bcn', enrollment.psa_bcn);
    setValue('returning', enrollment.is_returning_learner ? '1' : '0');
    setValue('Returning_Grade_Level', enrollment.last_grade_level_completed);
    setValue('Last_School_Year_Completed', enrollment.last_school_year_completed);
    setValue('Last_School_Attended', enrollment.last_school_attended);
    setValue('school_ID', enrollment.school_id);

    setValue('Learner_Last_Name', enrollment.last_name);
    setValue('Learner_First_Name', enrollment.first_name);
    setValue('Learner_Middle_Name', enrollment.middle_name);
    setValue('Learner_Extension_Name', enrollment.extension_name);
    setValue('Birth_Date', enrollment.birth_date);
    setValue('Age', enrollment.age);
    setValue('sex', enrollment.sex);
    setValue('Place_of_Birth', enrollment.place_of_birth);

    setValue('Mother_Tongue', enrollment.mother_tongue);

    setValue('ip', enrollment.is_indigenous ? 'Yes' : 'No');
    setValue('IP_Group', enrollment.indigenous_group);
    setValue('fourps', enrollment.is_four_ps_beneficiary ? 'Yes' : 'No');
    setValue('FourPs_Specify', enrollment.four_ps_household_id);
    setValue('disability', enrollment.is_learner_with_disability ? 'Yes' : 'No');

    toggle('returningBox', enrollment.is_returning_learner ? true : false);
    toggle('ipBox', enrollment.is_indigenous ? true : false);
    toggle('fourpsBox', enrollment.is_four_ps_beneficiary ? true : false);
    toggle('disabilityBox', enrollment.is_learner_with_disability ? true : false);

    if (document.getElementById('Mother_Tongue')) {
        toggleMotherTongueOther();
    }
    if (document.getElementById('IP_Group')) {
        toggleIpOther();
    }

    const currentAddress = getAddressByType(data.addresses, 'Current');
    const permanentAddress = getAddressByType(data.addresses, 'Permanent');

    if (currentAddress) {
        setValue('Current_House_No', currentAddress.house_no);
        setValue('Current_Street_Name', currentAddress.street_name);
        setValue('Current_Barangay', currentAddress.barangay);
        setValue('Current_Municipality_City', currentAddress.municipality_city);
        setValue('Current_Province', currentAddress.province);
        setValue('Current_Country', currentAddress.country);
        setValue('Current_Zip_Code', currentAddress.zip_code);
    }

    if (permanentAddress) {
        setValue('Permanent_House_No', permanentAddress.house_no);
        setValue('Permanent_Street_Name', permanentAddress.street_name);
        setValue('Permanent_Barangay', permanentAddress.barangay);
        setValue('Permanent_Municipality_City', permanentAddress.municipality_city);
        setValue('Permanent_Province', permanentAddress.province);
        setValue('Permanent_Country', permanentAddress.country);
        setValue('Permanent_Zip_Code', permanentAddress.zip_code);
    }

    const medical = data.medical || {};
    setValue('has_allergies', medical.has_allergies);
    setValue('has_med_condition', medical.has_med_condition);
    setValue('has_surgery_hospitalization', medical.has_surgery_hospitalization);
    setValue('is_taking_treatment', medical.is_taking_treatment);
    setValue('family_medical_history', medical.family_medical_history);
    setValue('exposed_to_cigarette_vape_smoke', medical.exposed_to_cigarette_vape_smoke);
    setValue('other_pertinent_information', medical.other_pertinent_information);

    if (typeof showField === 'function') showField();
    if (typeof showQ2 === 'function') showQ2();
    if (typeof showQ3 === 'function') showQ3();
    if (typeof showQ4 === 'function') showQ4();
    if (typeof showQ5 === 'function') showQ5();

    buildReviewSummary();
}

function saveEnrollmentUpdates() {
    const enrollmentId = document.getElementById('enrollmentSelect')?.value;
    const studentId = document.getElementById('studentIdInput')?.value;
    if (!enrollmentId || !studentId) {
        setMessage('error', 'Select an enrollment and load it before saving changes.');
        return;
    }

    const button = document.getElementById('saveChangesBtn');
    if (button) button.disabled = true;
    setMessage('', 'Saving changes…');

    const form = document.getElementById('enrollmentForm');
    const payload = serializeForm(form);
    payload.student_id = parseInt(studentId, 10);
    payload.enrollment_id = parseInt(enrollmentId, 10);

    fetch(`${apiBase}?action=update`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    })
        .then(response => response.json())
        .then(json => {
            if (!json.success) {
                throw new Error(json.error || 'Unable to save enrollment updates');
            }
            setMessage('success', 'Enrollment updates saved successfully.');
            currentEnrollmentData = null;
            loadEnrollmentDetails(enrollmentId);
        })
        .catch(error => setMessage('error', error.message))
        .finally(() => {
            if (button) button.disabled = false;
        });
}

function resetVerifyForm() {
    document.getElementById('enrollmentForm')?.reset();
    currentEnrollmentData = null;
    document.getElementById('reviewSummary').innerHTML = '<em>Select an enrollment to populate the form.</em>';
    setMessage('', '');
    goTo(1);
}

async function fetchPendingEnrollments() {
    const select = document.getElementById('enrollmentSelect');
    select.innerHTML = '<option value="">Loading…</option>';
    try {
        const response = await fetch(`${apiBase}?action=list`, { credentials: 'same-origin' });
        const json = await response.json();
        if (!json.success) {
            throw new Error(json.error || 'Could not load pending enrollments');
        }
        if (!Array.isArray(json.data) || json.data.length === 0) {
            select.innerHTML = '<option value="">-- no pending enrollments --</option>';
            setMessage('', 'No pending enrollments to verify.');
            return;
        }
        select.innerHTML = '<option value="">-- select enrollment --</option>';
        json.data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.enrollment_id;
            const label = item.student_name?.trim() || `Enrollment #${item.enrollment_id}`;
            option.textContent = `${label} — ${item.school_year || ''} ${item.grade_level || ''}`.trim();
            select.appendChild(option);
        });
        setMessage('', '');
    } catch (error) {
        select.innerHTML = '<option value="">Failed to load</option>';
        setMessage('error', error.message);
    }
}

async function loadEnrollmentDetails(enrollmentId) {
    if (!enrollmentId) return;
    setMessage('', 'Loading enrollment details…');
    try {
        const response = await fetch(`${apiBase}?action=details&enrollment_id=${encodeURIComponent(enrollmentId)}`, { credentials: 'same-origin' });
        const json = await response.json();
        if (!json.success) {
            throw new Error(json.error || 'Unable to load enrollment details');
        }
        applyEnrollmentToForm(json.data);
        setMessage('success', 'Enrollment loaded. Review the values and click Verify & Archive.');
        goTo(1);
    } catch (error) {
        setMessage('error', error.message);
    }
}

async function verifyEnrollment() {
    const enrollmentId = document.getElementById('enrollmentSelect')?.value;
    if (!enrollmentId) {
        setMessage('error', 'Please select a pending enrollment first.');
        return;
    }
    if (!confirm('Verify and archive this enrollment? This action cannot be undone.')) {
        return;
    }
    const button = document.getElementById('verifySubmitBtn');
    button.disabled = true;
    setMessage('', 'Processing verification…');
    try {
        const response = await fetch('../../../api/crud/enrollments/c_enrollments.php?action=verify', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                enrollment_id: parseInt(enrollmentId, 10),
                verified_by: window.CURRENT_USER_ID 
            }),
        });
        const json = await response.json();
        if (!json.success) {
            throw new Error(json.error || json.message || 'Verification failed');
        }
        setMessage('success', `Enrollment verified and archived. School record ID: ${json.school_record_id || 'N/A'}`);
        await fetchPendingEnrollments();
        resetVerifyForm();
    } catch (error) {
        setMessage('error', error.message);
    } finally {
        button.disabled = false;
    }
}

function initializeVerifyPage() {
    const enrollmentSelect = document.getElementById('enrollmentSelect');
    const clearBtn = document.getElementById('clearBtn');
    const verifyBtn = document.getElementById('verifySubmitBtn');

    if (enrollmentSelect) {
        enrollmentSelect.addEventListener('change', () => {
            const id = enrollmentSelect.value;
            if (!id) {
                resetVerifyForm();
                return;
            }
            loadEnrollmentDetails(id);
        });
    }
    if (clearBtn) {
        clearBtn.addEventListener('click', event => {
            event.preventDefault();
            enrollmentSelect.value = '';
            resetVerifyForm();
        });
    }
    if (verifyBtn) {
        verifyBtn.addEventListener('click', verifyEnrollment);
    }
    const saveBtn = document.getElementById('saveChangesBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveEnrollmentUpdates);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchPendingEnrollments);
    } else {
        fetchPendingEnrollments();
    }
}

initializeVerifyPage();
