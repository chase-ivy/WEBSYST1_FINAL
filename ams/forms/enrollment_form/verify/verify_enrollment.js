// ============================================================
// verify/verify_enrollment.js
// Handles the verify-enrollment page:
//   – loading pending enrollments into the dropdown
//   – populating the multi-step review form from API data
//   – saving corrections (update endpoint)
//   – verifying (verify endpoint) — requires a save first if the
//     form has been edited since the last load/save
//   – rejecting
//   – assigning to a section after a successful verify
// ============================================================

// Raw API data last loaded from the server.  Set by applyEnrollmentToForm,
// cleared by resetVerifyForm.  Used by buildReviewSummary so the summary
// always reflects stored data, not potentially stale DOM values.
let currentEnrollmentData = null;

// True when the form has been changed since the last load or save.
// Cleared after a successful save or after a fresh load.
let formDirty = false;

// ── Utilities ─────────────────────────────────────────────────

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
    if (type === 'error')   container.classList.add('error');
    container.textContent = message;
}

function getField(name) {
    const byId = document.getElementById(name);
    if (byId) return byId;

    const fields = Array.from(document.querySelectorAll(`[name="${name}"]`));
    if (fields.length === 0) return null;

    if (fields[0].type === 'radio' || fields[0].type === 'checkbox') {
        return fields.find(f => f.checked) || fields[0];
    }
    return fields[0];
}

function getFields(name) {
    const fields = Array.from(document.querySelectorAll(`[name="${name}"]`));
    if (fields.length > 0) return fields;
    const el = document.getElementById(name);
    return el ? [el] : [];
}

function getInput(id) { return getField(id); }

function sameAddr(yes) {
    // Collapse permanent address box when "Same as Current" = Yes
    toggle('permBox', !yes);
}

function setValue(name, value) {
    const fields = getFields(name);
    if (fields.length === 0) return;

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
                    field.checked = value === '1' || value === 1 || value === true ||
                                    String(value).toLowerCase() === 'yes';
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
        if (radio) radio.checked = true;
        return;
    }

    if (field.type === 'checkbox') {
        field.checked = value === '1' || value === 1 || value === true ||
                        String(value).toLowerCase() === 'yes';
        return;
    }

    field.value = value ?? '';
}

function getAddressByType(addresses, type) {
    if (!Array.isArray(addresses)) return null;
    return addresses.find(
        a => String(a.address_type || '').toLowerCase() === String(type || '').toLowerCase()
    ) || addresses[0] || null;
}

function goToAndReview(n) {
    goTo(n);
    if (n === 5) loadSpecialNeedsTypesForVerify(currentEnrollmentData?.special_needs || []);
    if (n === 6) buildReviewSummary();
}

// ── Load special needs types ──────────────────────────────────

async function loadSpecialNeedsTypesForVerify(enrollmentSpecialNeeds = []) {
    try {
        // Fetch all special needs types
        const response = await API.special_needs_types.list();
        if (!response.success || !response.data) {
            console.warn('Failed to load special needs types for verify form');
            return;
        }

        const types = response.data;
        const diagnoses = types.filter(t => !t.category || t.category.toLowerCase() === 'diagnosis');
        const manifestations = types.filter(t => t.category && t.category.toLowerCase() === 'manifestation');

        // Populate diagnosis checkboxes
        const diagnosisDiv = document.getElementById('diagnosisTypes');
        if (diagnosisDiv) {
            diagnosisDiv.innerHTML = '';
            diagnoses.forEach(d => {
                const label = document.createElement('label');
                label.style.cssText = 'display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;';
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'special_needs_diagnosis[]';
                input.value = d.id;
                input.style.cssText = 'width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;';
                // Check if this special need is in enrollment data
                if (enrollmentSpecialNeeds.some(sn => String(sn.special_needs_type_id) === String(d.id))) {
                    input.checked = true;
                }
                label.appendChild(input);
                label.appendChild(document.createTextNode(d.name));
                diagnosisDiv.appendChild(label);
            });
        }

        // Populate manifestation checkboxes
        const manifestationDiv = document.getElementById('manifestationTypes');
        if (manifestationDiv) {
            manifestationDiv.innerHTML = '';
            manifestations.forEach(m => {
                const label = document.createElement('label');
                label.style.cssText = 'display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;';
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'special_needs_manifestation[]';
                input.value = m.id;
                input.style.cssText = 'width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;';
                // Check if this special need is in enrollment data
                if (enrollmentSpecialNeeds.some(sn => String(sn.special_needs_type_id) === String(m.id))) {
                    input.checked = true;
                }
                label.appendChild(input);
                label.appendChild(document.createTextNode(m.name));
                manifestationDiv.appendChild(label);
            });
        }
    } catch (error) {
        console.error('Error loading special needs types for verify form:', error);
    }
}

// ── Dirty-tracking ────────────────────────────────────────────

function markDirty() {
    if (!formDirty) {
        formDirty = true;
        // Visual hint on the Save Changes button
        const saveBtn = document.getElementById('saveChangesBtn');
        if (saveBtn) saveBtn.classList.add('btn-warning');
    }
}

function clearDirty() {
    formDirty = false;
    const saveBtn = document.getElementById('saveChangesBtn');
    if (saveBtn) saveBtn.classList.remove('btn-warning');
}

function attachDirtyListeners() {
    const form = document.getElementById('enrollmentForm');
    if (!form) return;
    form.addEventListener('input',  markDirty);
    form.addEventListener('change', markDirty);
}

// ── Review summary (built from raw API data) ──────────────────

function buildReviewSummary() {
    const summary = document.getElementById('reviewSummary');
    if (!summary) return;

    if (!currentEnrollmentData) {
        summary.innerHTML = '<em>No enrollment loaded.</em>';
        return;
    }

    const e   = currentEnrollmentData.enrollment || {};
    const med = currentEnrollmentData.medical_info || {};
    const lines = [];

    const add = (label, value) => {
        if (value !== null && value !== undefined && String(value).trim() !== '') {
            lines.push(`<div><strong>${escapeHtml(label)}:</strong> ${escapeHtml(String(value))}</div>`);
        }
    };

    add('School Year',      e.school_year);
    add('Grade Level',      e.grade_level);
    add('LRN',              e.lrn);
    add('PSA BCN',          e.psa_bcn);

    const fullName = [e.last_name, e.first_name, e.middle_name, e.extension_name]
        .filter(Boolean).join(' ');
    add('Student Name', fullName);

    add('Birth Date',       e.birth_date);
    add('Sex',              e.sex);
    add('Place of Birth',   e.place_of_birth);

    // Resolve readable names from the lookup arrays injected by PHP
    const mtName = (window.MOTHER_TONGUES || []).find(
        m => String(m.id) === String(e.mother_tongue_id)
    )?.name || e.mother_tongue_id || null;
    add('Mother Tongue', mtName);

    if (e.is_indigenous) {
        const igName = (window.INDIGENOUS_GROUPS || []).find(
            g => String(g.id) === String(e.indigenous_group_id)
        )?.name || e.indigenous_group_id || null;
        add('Indigenous Group', igName);
    }

    if (e.is_four_ps_beneficiary) {
        add('4Ps Household ID', e.four_ps_household_id);
    }

    add('Learner with Disability', e.is_learner_with_disability ? 'Yes' : 'No');
    add('Returning Learner',       e.is_returning_learner       ? 'Yes' : 'No');

    // Addresses
    const addresses = currentEnrollmentData.addresses || [];
    const cur  = getAddressByType(addresses, 'Current');
    const perm = getAddressByType(addresses, 'Permanent');
    if (cur) {
        const parts = [cur.house_no, cur.street_name, cur.barangay,
                       cur.municipality_city, cur.province, cur.zip_code].filter(Boolean);
        add('Current Address', parts.join(', '));
    }
    if (perm) {
        const parts = [perm.house_no, perm.street_name, perm.barangay,
                       perm.municipality_city, perm.province, perm.zip_code].filter(Boolean);
        add('Permanent Address', parts.join(', '));
    }

    // Guardians
    const typeMap = { 1: 'Father', 2: 'Mother', 3: 'Guardian' };
    (currentEnrollmentData.guardians || []).forEach(g => {
        const label  = typeMap[g.parent_guardian_type_id] || 'Guardian';
        const gName  = [g.last_name, g.first_name, g.middle_name].filter(Boolean).join(' ');
        const gEntry = [
            gName,
            g.contact_number,
            g.occupation,
            g.relationship_status,
            g.facebook_messenger,
            g.is_emergency_contact ? '⚡ Primary Emergency Contact' : null,
        ].filter(Boolean).join(' · ');
        add(label, gEntry);
    });

    // Medical
    const allergies     = currentEnrollmentData.allergies     || [];
    const conditions    = currentEnrollmentData.conditions     || [];
    const surgeries     = currentEnrollmentData.surgeries      || [];
    const treatments    = currentEnrollmentData.treatments     || [];
    const familyHistory = currentEnrollmentData.family_history || [];

    if (allergies.length)     add('Allergies',            allergies.map(a => a.allergy_name || a.allergy_type_id).join(', '));
    if (conditions.length)    add('Medical Conditions',   conditions.map(c => c.condition_name || c.condition_type_id).join(', '));
    if (surgeries.length)     add('Surgeries',            surgeries.map(s => [s.body_part, s.hospital_name, s.surgery_date].filter(Boolean).join(' / ')).join('; '));
    if (treatments.length)    add('Treatments',           treatments.map(t => [t.treatment_medicine, t.schedule_dosage].filter(Boolean).join(' — ')).join('; '));
    if (familyHistory.length) add('Family Medical History', familyHistory.map(f => f.family_history_name || f.family_history_type_id).join(', '));

    add('Exposed to Smoke', med.exposed_to_cigarette_vape_smoke ? 'Yes' : 'No');
    add('Other Medical Notes', med.other_pertinent_information);

    if (formDirty) {
        lines.unshift('<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px 14px;color:#856404;margin-bottom:12px;">⚠ You have unsaved changes. Save before verifying.</div>');
    }

    summary.innerHTML = lines.length
        ? lines.join('')
        : '<em>No enrollment information available.</em>';
}

// ── Section dropdown filtering ────────────────────────────────

function filterSectionsByGradeLevel(gradeLevel) {
    const selectEl = document.getElementById('assignSectionSelect');
    if (!selectEl || !window.SECTIONS) return;

    while (selectEl.options.length > 1) selectEl.remove(1);

    const normalize = value => String(value || '').trim().toLowerCase();
    const targetGrade = normalize(gradeLevel);

    const matching = window.SECTIONS.filter(s => normalize(s.grade_level) === targetGrade);
    matching.forEach(sec => {
        const opt        = document.createElement('option');
        opt.value        = sec.section_id;
        opt.textContent  = `${sec.school_year} · ${sec.grade_level} · ${sec.name}`;
        selectEl.appendChild(opt);
    });
}

// ── Populate form from API data ───────────────────────────────

function applyEnrollmentToForm(data) {
    if (!data) return;
    // Normalise wrapper — get.php returns { enrollment, ... } inside data
    if (!data.enrollment && data.enrollment_id) data = { enrollment: data };
    if (!data.enrollment) {
        console.error('applyEnrollmentToForm: missing enrollment payload', data);
        return;
    }

    currentEnrollmentData = data;
    clearDirty();

    const enrollment = data.enrollment;

    const studentIdInput    = document.getElementById('studentIdInput');
    const enrollmentIdInput = document.getElementById('enrollmentIdInput');
    if (studentIdInput)    studentIdInput.value    = enrollment.student_id    || '';
    if (enrollmentIdInput) enrollmentIdInput.value  = enrollment.enrollment_id || '';

    // School year (stored as "2025-2026" or split fields)
    if (typeof enrollment.school_year === 'string' && enrollment.school_year.includes('-')) {
        const parts = enrollment.school_year.split('-').map(p => p.trim());
        setValue('year_start', parts[0] || '');
        setValue('year_end',   parts[1] || '');
    } else {
        setValue('year_start', enrollment.year_start);
        setValue('year_end',   enrollment.year_end);
    }

    setValue('Grade_Level',          enrollment.grade_level);
    setValue('Learner_Reference_No', enrollment.lrn);
    setValue('with_lrn',             enrollment.with_lrn ? '1' : '0');
    setValue('psa_bcn',              enrollment.psa_bcn);
    setValue('returning',            enrollment.is_returning_learner ? '1' : '0');

    if (data.returning_learner) {
        setValue('Returning_Grade_Level',      data.returning_learner.last_grade_level_completed);
        setValue('Last_School_Year_Completed', data.returning_learner.last_school_year_completed);
        setValue('Last_School_Attended',       data.returning_learner.last_school_attended);
        setValue('school_ID',                  data.returning_learner.school_id);
    }

    setValue('Learner_Last_Name',      enrollment.last_name);
    setValue('Learner_First_Name',     enrollment.first_name);
    setValue('Learner_Middle_Name',    enrollment.middle_name);
    setValue('Learner_Extension_Name', enrollment.extension_name);
    setValue('Birth_Date',             enrollment.birth_date);
    setValue('Age',                    enrollment.age);
    setValue('sex',                    enrollment.sex);
    setValue('Place_of_Birth',         enrollment.place_of_birth);

    // Mother tongue dropdown
    if (window.MOTHER_TONGUES && Array.isArray(window.MOTHER_TONGUES)) {
        const mtSelect = document.getElementById('Mother_Tongue');
        if (mtSelect) {
            mtSelect.innerHTML = '<option value="">Select...</option><option value="Other">Other</option>';
            window.MOTHER_TONGUES.forEach(item => {
                const option     = document.createElement('option');
                option.value     = item.id;
                option.textContent = item.name;
                if (String(item.id) === String(enrollment.mother_tongue_id)) option.selected = true;
                mtSelect.insertBefore(option, mtSelect.querySelector('option[value="Other"]'));
            });
        }
    }
    setValue('Mother_Tongue', enrollment.mother_tongue_id);

    // Religion dropdown
    if (window.RELIGIONS && Array.isArray(window.RELIGIONS)) {
        const relSelect = document.getElementById('religion_id');
        if (relSelect) {
            relSelect.innerHTML = '<option value="">Select...</option>';
            window.RELIGIONS.forEach(item => {
                const option       = document.createElement('option');
                option.value       = item.id;
                option.textContent = item.name;
                if (String(item.id) === String(enrollment.religion_id)) option.selected = true;
                relSelect.appendChild(option);
            });
        }
    }
    setValue('religion_id', enrollment.religion_id);

    // Learning Classification
    setValue('learning_classification', enrollment.learning_classification || 'graded');

    // Early Learning Program
    setValue('attended_early_learning_program', enrollment.attended_early_learning_program ? '1' : '0');
    setValue('early_learning_program_name', enrollment.early_learning_program_name || '');
    toggle('earlyLearningBox', !!enrollment.attended_early_learning_program);

    setValue('ip', enrollment.is_indigenous ? 'Yes' : 'No');

    // IP group dropdown
    if (window.INDIGENOUS_GROUPS && Array.isArray(window.INDIGENOUS_GROUPS)) {
        const igSelect = document.getElementById('IP_Group');
        if (igSelect) {
            igSelect.innerHTML = '<option value="">Select...</option><option value="Other">Other</option>';
            window.INDIGENOUS_GROUPS.forEach(item => {
                const option       = document.createElement('option');
                option.value       = item.id;
                option.textContent = item.name;
                if (String(item.id) === String(enrollment.indigenous_group_id)) option.selected = true;
                igSelect.insertBefore(option, igSelect.querySelector('option[value="Other"]'));
            });
        }
    }
    setValue('IP_Group', enrollment.indigenous_group_id);

    setValue('fourps',         enrollment.is_four_ps_beneficiary ? 'Yes' : 'No');
    setValue('FourPs_Specify', enrollment.four_ps_household_id);
    setValue('disability',     enrollment.is_learner_with_disability ? 'Yes' : 'No');

    toggle('returningBox',  !!enrollment.is_returning_learner);
    toggle('ipBox',         !!enrollment.is_indigenous);
    toggle('fourpsBox',     !!enrollment.is_four_ps_beneficiary);
    toggle('disabilityBox', !!enrollment.is_learner_with_disability);

    (data.disabilities || []).forEach(d => {
        const cb = document.querySelector(`[name="disabilityDetails[${d.disability_type_id}][]"]`);
        if (cb) cb.checked = true;
        if (d.disability_subtype_id) {
            const sub = document.querySelector(`[name="disability_sub[${d.disability_type_id}][]"][value="${d.disability_subtype_id}"]`);
            if (sub) sub.checked = true;
        }
        if (d.disability_type_id == 1) {
            const vBox = document.getElementById('visualOptionsBox');
            if (vBox) vBox.style.display = '';
        }
    });

    if (document.getElementById('Mother_Tongue') && typeof toggleMotherTongueOther === 'function') toggleMotherTongueOther();
    if (document.getElementById('IP_Group')      && typeof toggleIpOther          === 'function') toggleIpOther();

    // Addresses
    const ownershipLabels = {
        rented: 'Rental',
        rental: 'Rental',
        owned: 'Owned',
        'living_with_relatives': 'Living with Relatives',
        'living with relatives': 'Living with Relatives',
        inherited: 'Inherited'
    };

    const cur  = getAddressByType(data.addresses, 'Current');
    const perm = getAddressByType(data.addresses, 'Permanent');
    if (cur) {
        setValue('Current_House_No',          cur.house_no);
        setValue('Current_Street_Name',       cur.street_name);
        setValue('Current_Barangay',          cur.barangay);
        setValue('Current_Municipality_City', cur.municipality_city);
        setValue('Current_Province',          cur.province);
        setValue('Current_Country',           cur.country);
        setValue('Current_Zip_Code',          cur.zip_code);
        setValue('Current_Address_Status',    ownershipLabels[String(cur.ownership_type || '').toLowerCase()] ?? cur.ownership_type ?? '');
    }
    if (perm) {
        setValue('Permanent_House_No',          perm.house_no);
        setValue('Permanent_Street_Name',       perm.street_name);
        setValue('Permanent_Barangay',          perm.barangay);
        setValue('Permanent_Municipality_City', perm.municipality_city);
        setValue('Permanent_Province',          perm.province);
        setValue('Permanent_Country',           perm.country);
        setValue('Permanent_Zip_Code',          perm.zip_code);
        setValue('Permanent_Address_Status',    ownershipLabels[String(perm.ownership_type || '').toLowerCase()] ?? perm.ownership_type ?? '');
    }

    // Detect whether current == permanent address and toggle permBox accordingly
    const addressesMatch = cur && perm && (
        String(cur.house_no || '')           === String(perm.house_no || '')           &&
        String(cur.street_name || '')        === String(perm.street_name || '')        &&
        String(cur.barangay || '')           === String(perm.barangay || '')           &&
        String(cur.municipality_city || '')  === String(perm.municipality_city || '')  &&
        String(cur.province || '')           === String(perm.province || '')           &&
        String(cur.zip_code || '')           === String(perm.zip_code || '')
    );
    setValue('same_address', addressesMatch ? 'Yes' : 'No');
    sameAddr(!!addressesMatch);

    // Parent/guardians
    const guardianTypeMap = { 1: 'father', 2: 'mother', 3: 'guardian' };
    (data.guardians || []).forEach(g => {
        const prefix = guardianTypeMap[g.parent_guardian_type_id];
        if (!prefix) return;
        setValue(`${prefix}_last_name`,           g.last_name);
        setValue(`${prefix}_first_name`,          g.first_name);
        setValue(`${prefix}_middle_name`,         g.middle_name);
        setValue(`${prefix}_contact_number`,      g.contact_number);
        setValue(`${prefix}_occupation`,          g.occupation);
        setValue(`${prefix}_relationship_status`, g.relationship_status);
        setValue(`${prefix}_facebook_messenger`,  g.facebook_messenger);
        // Populate single emergency contact radio from stored per-guardian flags
        if (g.is_emergency_contact) {
            setValue('emergency_contact', prefix);
        }
    });

    // Medical toggles
    const medInfo = data.medical_info || {};
    setValue('has_allergies',              (data.allergies     && data.allergies.length     > 0) ? '1' : '0');
    setValue('has_med_condition',          (data.conditions    && data.conditions.length    > 0) ? '1' : '0');
    setValue('has_surgery_hospitalization',(data.surgeries     && data.surgeries.length     > 0) ? '1' : '0');
    setValue('is_taking_treatment',        (data.treatments    && data.treatments.length    > 0) ? '1' : '0');
    setValue('family_medical_history',     (data.family_history && data.family_history.length > 0) ? '1' : '0');
    setValue('exposed_to_cigarette_vape_smoke', medInfo.exposed_to_cigarette_vape_smoke);
    setValue('other_pertinent_information',     medInfo.other_pertinent_information);

    // Re-render conditional medical sections, then populate their fields
    if (typeof showField === 'function') showField();
    if (typeof showQ2    === 'function') showQ2();
    if (typeof showQ3    === 'function') showQ3();
    if (typeof showQ4    === 'function') showQ4();
    if (typeof showQ5    === 'function') showQ5();

    (data.allergies || []).forEach(a => {
        const cb = document.querySelector(`[name="medicine_allergy[]"][value="${a.allergy_type_id}"]`);
        if (cb) cb.checked = true;
        if (a.description) {
            const txt = document.querySelector(`[name="allergy_description[${a.allergy_type_id}]"]`);
            if (txt) txt.value = a.description;
        }
    });

    (data.conditions || []).forEach(c => {
        const cb = document.querySelector(`[name="condition_type_id"][value="${c.condition_type_id}"]`);
        if (cb) cb.checked = true;
    });
    if (data.conditions && data.conditions.length > 0 && data.conditions[0].description) {
        const txt = document.querySelector('[name="condition_description"]');
        if (txt) txt.value = data.conditions[0].description;
    }

    if (data.surgeries && data.surgeries.length > 0) {
        const s = data.surgeries[0];
        const dateEl = document.querySelector('[name="surgery_date"]');
        const hospEl = document.querySelector('[name="hospital_name"]');
        const partEl = document.querySelector('[name="body_part"]');
        if (dateEl) dateEl.value = s.surgery_date  || '';
        if (hospEl) hospEl.value = s.hospital_name || '';
        if (partEl) partEl.value = s.body_part      || '';
    }

    if (data.treatments && data.treatments.length > 0) {
        const t = data.treatments[0];
        const medEl = document.querySelector('[name="treatment_medicine"]');
        const dosEl = document.querySelector('[name="schedule_dosage"]');
        if (medEl) medEl.value = t.treatment_medicine || '';
        if (dosEl) dosEl.value = t.schedule_dosage    || '';
    }

    (data.family_history || []).forEach(f => {
        const cb = document.querySelector(`[name="family_condition_type_id"][value="${f.family_history_type_id}"]`);
        if (cb) cb.checked = true;
    });
    if (data.family_history && data.family_history.length > 0 && data.family_history[0].description) {
        const txt = document.querySelector('[name="family_condition_description"]');
        if (txt) txt.value = data.family_history[0].description;
    }

    if (enrollment.grade_level) filterSectionsByGradeLevel(enrollment.grade_level);

    // Special needs
    setValue('has_special_needs', enrollment.has_special_needs ? '1' : '0');
    setValue('has_pwd_id', enrollment.has_pwd_id ? '1' : '0');
    setValue('pwd_id_number', enrollment.pwd_id_number || '');
    toggle('specialNeedsDetails', !!enrollment.has_special_needs);
    toggle('pwdIdBox', !!enrollment.has_pwd_id);

    // Load special needs types and populate checkboxes
    if (enrollment.has_special_needs && (data.special_needs || []).length > 0) {
        loadSpecialNeedsTypesForVerify(data.special_needs);
    }

    // After populating, clear the dirty flag (populating the form fires change
    // events that would otherwise mark it dirty immediately)
    clearDirty();
    buildReviewSummary();
}

// ── Save corrections ──────────────────────────────────────────

async function saveEnrollmentUpdates() {
    const enrollmentId = document.getElementById('enrollmentSelect')?.value;
    const studentId    = document.getElementById('studentIdInput')?.value;
    if (!enrollmentId || !studentId) {
        setMessage('error', 'Select an enrollment and load it before saving changes.');
        return;
    }

    const button = document.getElementById('saveChangesBtn');
    if (button) button.disabled = true;
    setMessage('', 'Saving changes…');

    try {
        const form    = document.getElementById('enrollmentForm');
        const payload = serializeForm(form);
        payload.student_id    = parseInt(studentId, 10);
        payload.enrollment_id = parseInt(enrollmentId, 10);

        const response = await API.enrollment.update(parseInt(enrollmentId, 10), payload);
        if (!response.success) throw new Error(response.error || 'Unable to save enrollment updates');

        setMessage('success', 'Changes saved. You may now verify.');
        clearDirty();

        // Reload from server so currentEnrollmentData stays in sync with DB
        await loadEnrollmentDetails(enrollmentId, /* preserveStep= */ true);
    } catch (error) {
        setMessage('error', error.message || 'Failed to save enrollment updates.');
    } finally {
        if (button) button.disabled = false;
    }
}

// ── Load enrollment details ───────────────────────────────────

async function loadEnrollmentDetails(enrollmentId, preserveStep = false) {
    if (!enrollmentId) return;
    setMessage('', 'Loading enrollment details…');
    try {
        const response = await API.enrollment.get(parseInt(enrollmentId, 10));
        let enrollmentData = null;

        if (response.success && response.data)  enrollmentData = response.data;
        else if (response.enrollment)           enrollmentData = response;
        else if (response.data)                 enrollmentData = response.data;
        else if (!response.success)             throw new Error(response.error || 'Unable to load enrollment details');

        if (!enrollmentData) throw new Error('No enrollment data returned from API');

        applyEnrollmentToForm(enrollmentData);
        setMessage('success', 'Enrollment loaded. Review the details, then click Verify & Archive.');
        if (!preserveStep) goTo(1);
    } catch (error) {
        setMessage('error', error.message || 'Failed to load enrollment details.');
        console.error('loadEnrollmentDetails error:', error);
    }
}

// ── Fetch pending enrollments list ────────────────────────────

async function fetchPendingEnrollments() {
    const select     = document.getElementById('enrollmentSelect');
    const yearFilter = document.getElementById('schoolYearFilter');
    const selectedYear = yearFilter?.value || null;
    select.innerHTML = '<option value="">Loading…</option>';

    try {
        const response = await API.enrollment.queue(selectedYear, 'pending');

        let enrollments = [];
        if      (Array.isArray(response.data))        enrollments = response.data;
        else if (Array.isArray(response))             enrollments = response;
        else if (Array.isArray(response.enrollments)) enrollments = response.enrollments;
        else if (!response.success)                   throw new Error(response.error || 'Could not load pending enrollments');

        // Server already filters by status=pending; this guards against stale caches
        const pending = enrollments.filter(
            e => e.enrollment_status === 'pending' || e.status === 'pending'
        );

        // Rebuild year filter with discovered values
        if (yearFilter) {
            const years = Array.from(
                new Set(enrollments.map(e => e.school_year).filter(Boolean))
            ).sort((a, b) => b.localeCompare(a));
            const currentValue = yearFilter.value;
            yearFilter.innerHTML = '<option value="">All school years</option>';
            years.forEach(year => {
                const option = document.createElement('option');
                option.value     = year;
                option.textContent = year;
                if (year === currentValue) option.selected = true;
                yearFilter.appendChild(option);
            });
        }

        if (pending.length === 0) {
            select.innerHTML = '<option value="">-- no pending enrollments --</option>';
            setMessage('info', 'No pending enrollments to verify.');
            return;
        }

        select.innerHTML = '<option value="">-- select enrollment --</option>';
        pending.forEach(item => {
            const option = document.createElement('option');
            option.value = item.enrollment_id;
            let studentName = item.student_name;
            if (!studentName) {
                const parts = [];
                if (item.last_name)  parts.push(item.last_name);
                if (item.first_name) parts.push(item.first_name);
                studentName = parts.length > 0
                    ? parts.join(', ')
                    : `Enrollment #${item.enrollment_id}`;
            }
            option.textContent = `${studentName} — ${item.school_year || ''} ${item.grade_level || ''}`.trim();
            select.appendChild(option);
        });

        setMessage('', '');
    } catch (error) {
        select.innerHTML = '<option value="">Failed to load</option>';
        setMessage('error', error.message || 'Failed to load pending enrollments');
        console.error('fetchPendingEnrollments error:', error);
    }
}

// ── Verify enrollment ─────────────────────────────────────────

async function verifyEnrollment() {
    const enrollmentId = document.getElementById('enrollmentSelect')?.value;
    if (!enrollmentId) {
        setMessage('error', 'Please select a pending enrollment first.');
        return;
    }

    // Guard: unsaved edits must be saved before verifying
    if (formDirty) {
        setMessage('error', 'You have unsaved changes. Click "Save Changes" before verifying.');
        return;
    }

    if (!confirm('Verify and archive this enrollment? This action cannot be undone.')) return;

    const button = document.getElementById('verifySubmitBtn');
    button.disabled = true;
    setMessage('', 'Processing verification…');

    try {
        const response = await API.enrollment.verify(parseInt(enrollmentId, 10));
        if (!response || !response.success) {
            throw new Error(response?.error || response?.message || 'Verification failed');
        }

        setMessage('success', 'Enrollment verified and archived successfully.');

        const schoolRecordId = response.school_record_id ||
                               (response.data && response.data.school_record_id) || null;
        if (schoolRecordId) {
            const assignSelect = document.getElementById('assignSectionSelect');
            const assignBtn    = document.getElementById('assignSectionBtn');
            if (assignSelect && assignBtn) {
                assignSelect.disabled           = false;
                assignBtn.disabled              = false;
                assignBtn.dataset.schoolRecordId = schoolRecordId;
            }
        }

        // Capture section-assign state before reset wipes the form
        const capturedRecordId = schoolRecordId;
        resetVerifyForm(true);  // resets form but preserves assign UI
        await fetchPendingEnrollments();
        // Re-apply schoolRecordId and re-enable assign controls after the list refresh
        if (capturedRecordId) {
            const assignSelect2 = document.getElementById('assignSectionSelect');
            const assignBtn2    = document.getElementById('assignSectionBtn');
            if (assignSelect2 && assignBtn2) {
                assignSelect2.disabled            = false;
                assignBtn2.disabled               = false;
                assignBtn2.dataset.schoolRecordId  = capturedRecordId;
            }
        }
    } catch (error) {
        setMessage('error', error.message || 'Verification failed. Please try again.');
    } finally {
        button.disabled = false;
    }
}

// ── Reject enrollment ─────────────────────────────────────────

async function rejectEnrollment() {
    const enrollmentId = document.getElementById('enrollmentSelect')?.value;
    if (!enrollmentId) {
        setMessage('error', 'Please select a pending enrollment first.');
        return;
    }
    const reason = document.getElementById('rejectReason')?.value.trim() || null;
    if (!confirm('Reject this enrollment? This will mark it as rejected.')) return;

    const button = document.getElementById('rejectSubmitBtn');
    button.disabled = true;
    setMessage('', 'Processing rejection…');

    try {
        const response = await API.enrollment.reject(parseInt(enrollmentId, 10), reason);
        if (!response || !response.success) {
            throw new Error(response?.error || response?.message || 'Rejection failed');
        }
        setMessage('success', 'Enrollment rejected successfully.');
        document.getElementById('rejectReason').value = '';
        await fetchPendingEnrollments();
        resetVerifyForm();
    } catch (error) {
        setMessage('error', error.message || 'Rejection failed. Please try again.');
    } finally {
        button.disabled = false;
    }
}

// ── Assign to section ─────────────────────────────────────────

async function assignSection() {
    const assignBtn    = document.getElementById('assignSectionBtn');
    const assignSelect = document.getElementById('assignSectionSelect');
    if (!assignBtn || !assignSelect) return;

    const schoolRecordId = parseInt(assignBtn.dataset.schoolRecordId || 0, 10);
    const sectionId      = parseInt(assignSelect.value || 0, 10);
    if (!schoolRecordId || !sectionId) {
        setMessage('error', 'Select a section before assigning.');
        return;
    }

    assignBtn.disabled = true;
    setMessage('', 'Assigning to section…');

    try {
        const res = await API.sections.assignStudent(schoolRecordId, sectionId);
        if (!res || !res.success) throw new Error(res?.error || 'Assignment failed');
        setMessage('success', 'Student assigned to section successfully.');
        await fetchPendingEnrollments();
        resetVerifyForm();
    } catch (err) {
        console.error(err);
        setMessage('error', err.message || 'Assignment failed.');
    } finally {
        assignBtn.disabled = false;
    }
}

// ── Reset form ────────────────────────────────────────────────

function resetVerifyForm(preserveAssign = false) {
    document.getElementById('enrollmentForm')?.reset();
    currentEnrollmentData = null;
    clearDirty();

    const summary = document.getElementById('reviewSummary');
    if (summary) summary.innerHTML = '<em>Select an enrollment to populate the form.</em>';
    setMessage('', '');

    // Always reset collapse boxes to their default state
    toggle('returningBox',  false);
    toggle('ipBox',         false);
    toggle('fourpsBox',     false);
    toggle('disabilityBox', false);
    toggle('permBox',       true);   // permBox defaults to open (no same_address selected)

    if (!preserveAssign) {
        const assignSelect = document.getElementById('assignSectionSelect');
        const assignBtn    = document.getElementById('assignSectionBtn');
        const rejectInput  = document.getElementById('rejectReason');
        if (assignSelect) { assignSelect.disabled = true; assignSelect.value = ''; }
        if (assignBtn)    { assignBtn.disabled = true; delete assignBtn.dataset.schoolRecordId; }
        if (rejectInput)  rejectInput.value = '';
        goTo(1);
    } else {
        // Clear dropdown selection; navigate to step 5 so assign UI is visible
        const enrollmentSelect = document.getElementById('enrollmentSelect');
        if (enrollmentSelect) enrollmentSelect.value = '';
        goTo(5);
    }
}

// ── Page initialisation ───────────────────────────────────────

function initializeVerifyPage() {
    const enrollmentSelect = document.getElementById('enrollmentSelect');
    const clearBtn         = document.getElementById('clearBtn');
    const verifyBtn        = document.getElementById('verifySubmitBtn');
    const yearFilter       = document.getElementById('schoolYearFilter');
    const assignBtn        = document.getElementById('assignSectionBtn');
    const rejectBtn        = document.getElementById('rejectSubmitBtn');
    const saveBtn          = document.getElementById('saveChangesBtn');

    if (enrollmentSelect) {
        enrollmentSelect.addEventListener('change', () => {
            const id = enrollmentSelect.value;
            if (!id) { resetVerifyForm(); return; }
            loadEnrollmentDetails(id);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', e => {
            e.preventDefault();
            enrollmentSelect.value = '';
            resetVerifyForm();
        });
    }

    if (yearFilter) yearFilter.addEventListener('change', () => fetchPendingEnrollments());
    if (verifyBtn)  verifyBtn.addEventListener('click',  verifyEnrollment);
    if (assignBtn)  assignBtn.addEventListener('click',  assignSection);
    if (rejectBtn)  rejectBtn.addEventListener('click',  rejectEnrollment);
    if (saveBtn)    saveBtn.addEventListener('click',    saveEnrollmentUpdates);

    const sameAddressRadios = Array.from(document.querySelectorAll('[name="same_address"]'));
    sameAddressRadios.forEach(radio => {
        radio.addEventListener('change', () => sameAddr(radio.value === 'Yes'));
    });
    const checkedSameAddr = sameAddressRadios.find(r => r.checked);
    if (checkedSameAddr) sameAddr(checkedSameAddr.value === 'Yes');

    attachDirtyListeners();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchPendingEnrollments);
    } else {
        fetchPendingEnrollments();
    }
}

initializeVerifyPage();