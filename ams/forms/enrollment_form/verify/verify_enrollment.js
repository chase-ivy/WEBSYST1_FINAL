const apiBase = '../../api/verify_enrollment.php';
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

function getInput(id) {
    return document.getElementById(id);
}

function setValue(id, value) {
    const field = getInput(id);
    if (!field) return;
    if (field.type === 'checkbox') {
        field.checked = value === '1' || value === 1 || value === true || String(value).toLowerCase() === 'yes';
    } else {
        field.value = value ?? '';
    }
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

    const fields = [
        ['School Year', 'school_year'],
        ['Grade Level', 'grade_level'],
        ['LRN', 'lrn'],
        ['Student Name', null],
        ['Birth Date', 'birth_date'],
        ['Sex', 'sex'],
        ['Place of Birth', 'place_of_birth'],
        ['Mother Tongue', 'mother_tongue'],
        ['Indigenous Group', 'indigenous_group'],
        ['4Ps Household ID', 'four_ps_household_id'],
        ['Learner with Disability', 'is_learner_with_disability'],
        ['Returning Learner', 'is_returning_learner'],
    ];

    const lines = [];
    fields.forEach(([label, id]) => {
        if (id) {
            const value = getInput(id)?.value || '';
            if (value !== '') {
                lines.push(`<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</div>`);
            }
        }
    });

    const fullName = [
        getInput('last_name')?.value,
        getInput('first_name')?.value,
        getInput('middle_name')?.value,
        getInput('extension_name')?.value,
    ].filter(Boolean).join(' ');
    if (fullName) {
        lines.splice(3, 0, `<div><strong>Student Name:</strong> ${escapeHtml(fullName)}</div>`);
    }

    const addressFields = [
        ['Current Address', ['Current_House_No','Current_Street_Name','Current_Barangay','Current_Municipality_City','Current_Province','Current_Zip_Code']],
        ['Permanent Address', ['Permanent_House_No','Permanent_Street_Name','Permanent_Barangay','Permanent_Municipality_City','Permanent_Province','Permanent_Zip_Code']],
    ];
    addressFields.forEach(([title, ids]) => {
        const parts = ids.map(id => getInput(id)?.value || '').filter(Boolean);
        if (parts.length) {
            lines.push(`<div><strong>${escapeHtml(title)}:</strong> ${escapeHtml(parts.join(', '))}</div>`);
        }
    });

    const parentFields = [
        ['Father', 'father_name'],
        ['Mother', 'mother_name'],
        ['Guardian', 'guardian_name'],
        ['Guardian Contact', 'guardian_contact'],
    ];
    parentFields.forEach(([label, id]) => {
        const value = getInput(id)?.value || '';
        if (value) {
            lines.push(`<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</div>`);
        }
    });

    const medicalSections = [
        ['Exposed to cigarette/vape smoke', 'exposed_to_cigarette_vape_smoke'],
        ['Other medical notes', 'other_pertinent_information'],
        ['Allergies', 'allergies'],
        ['Conditions', 'conditions'],
        ['Surgeries', 'surgeries'],
        ['Treatments', 'treatments'],
        ['Family medical history', 'family_medical_history'],
    ];
    medicalSections.forEach(([label, id]) => {
        const value = getInput(id)?.value || '';
        if (value) {
            lines.push(`<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value).replace(/\n/g, '<br>')}</div>`);
        }
    });

    summary.innerHTML = lines.length ? lines.join('') : '<em>No review information available.</em>';
}

function applyEnrollmentToForm(data) {
    currentEnrollmentData = data;
    if (!data) return;

    const enrollment = data.enrollment || {};
    setValue('school_year', enrollment.school_year);
    setValue('grade_level', enrollment.grade_level);
    setValue('lrn', enrollment.lrn);
    setValue('last_name', enrollment.last_name);
    setValue('first_name', enrollment.first_name);
    setValue('middle_name', enrollment.middle_name);
    setValue('extension_name', enrollment.extension_name);
    setValue('birth_date', enrollment.birth_date);
    setValue('sex', enrollment.sex);
    setValue('place_of_birth', enrollment.place_of_birth);
    setValue('mother_tongue', enrollment.mother_tongue);
    setValue('indigenous_group', enrollment.indigenous_group);
    setValue('four_ps_household_id', enrollment.four_ps_household_id);
    setValue('is_learner_with_disability', enrollment.is_learner_with_disability);
    setValue('is_returning_learner', enrollment.is_returning_learner);

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
        setValue('Current_Zip_Code', currentAddress.zip_code);
    }

    if (permanentAddress) {
        setValue('Permanent_House_No', permanentAddress.house_no);
        setValue('Permanent_Street_Name', permanentAddress.street_name);
        setValue('Permanent_Barangay', permanentAddress.barangay);
        setValue('Permanent_Municipality_City', permanentAddress.municipality_city);
        setValue('Permanent_Province', permanentAddress.province);
        setValue('Permanent_Zip_Code', permanentAddress.zip_code);
    }

    const medical = data.medical || {};
    setValue('exposed_to_cigarette_vape_smoke', medical.exposed_to_cigarette_vape_smoke);
    setValue('other_pertinent_information', medical.other_pertinent_information);
    setValue('allergies', serializeMedicalList(data.medical_allergies));
    setValue('conditions', serializeMedicalList(data.medical_conditions));
    setValue('surgeries', serializeMedicalList(data.medical_surgeries));
    setValue('treatments', serializeMedicalList(data.medical_treatments));
    setValue('family_medical_history', serializeMedicalList(data.medical_family));

    buildReviewSummary();
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
        const response = await fetch(`${apiBase}?action=verify`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ enrollment_id: enrollmentId }),
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchPendingEnrollments);
    } else {
        fetchPendingEnrollments();
    }
}

initializeVerifyPage();
