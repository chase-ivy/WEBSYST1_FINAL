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

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
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

    if (parseInt(e.is_indigenous)) {
        const igName = (window.INDIGENOUS_GROUPS || []).find(
            g => String(g.id) === String(e.indigenous_group_id)
        )?.name || e.indigenous_group_id || null;
        add('Indigenous Group', igName);
    }

    if (parseInt(e.is_four_ps_beneficiary)) {
        add('4Ps Household ID', e.four_ps_household_id);
    }

    add('Learner with Disability', parseInt(e.is_learner_with_disability) ? 'Yes' : 'No');
    add('Returning Learner',       parseInt(e.is_returning_learner)       ? 'Yes' : 'No');

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
    setValue('with_lrn',             parseInt(enrollment.with_lrn) ? '1' : '0');
    setValue('psa_bcn',              enrollment.psa_bcn);
    setValue('returning',            parseInt(enrollment.is_returning_learner) ? '1' : '0');

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
    const attendedELP = parseInt(enrollment.attended_early_learning_program) || 0;
    setValue('attended_early_learning_program', attendedELP ? '1' : '0');
    setValue('early_learning_program_name', enrollment.early_learning_program_name || '');
    toggle('earlyLearningBox', !!attendedELP);

    setValue('ip', parseInt(enrollment.is_indigenous) ? 'Yes' : 'No');

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

    setValue('fourps',         parseInt(enrollment.is_four_ps_beneficiary) ? 'Yes' : 'No');
    setValue('FourPs_Specify', enrollment.four_ps_household_id);
    setValue('disability',     parseInt(enrollment.is_learner_with_disability) ? 'Yes' : 'No');

    toggle('returningBox',  !!parseInt(enrollment.is_returning_learner));
    toggle('ipBox',         !!parseInt(enrollment.is_indigenous));
    toggle('fourpsBox',     !!parseInt(enrollment.is_four_ps_beneficiary));
    toggle('disabilityBox', !!parseInt(enrollment.is_learner_with_disability));

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
    const hasSN = parseInt(enrollment.has_special_needs) ? 1 : 0;
    const hasPwd = parseInt(enrollment.has_pwd_id) ? 1 : 0;
    setValue('has_special_needs', hasSN ? '1' : '0');
    setValue('has_pwd_id', hasPwd ? '1' : '0');
    setValue('pwd_id_number', enrollment.pwd_id_number || '');
    toggle('specialNeedsDetails', !!hasSN);
    toggle('pwdIdBox', !!hasPwd);

    // Load special needs types and populate checkboxes
    if (hasSN && (data.special_needs || []).length > 0) {
        loadSpecialNeedsTypesForVerify(data.special_needs);
    }

    // After populating, clear the dirty flag (populating the form fires change
    // events that would otherwise mark it dirty immediately)
    clearDirty();
    buildReviewSummary();
}

// ── Save corrections ──────────────────────────────────────────

async function saveEnrollmentUpdates() {
    const studentId = document.getElementById('studentIdInput')?.value;
    if (!studentId) {
        setMessage('error', 'Please select a student first.');
        return;
    }

    const button = document.getElementById('saveChangesBtn');
    if (button) button.disabled = true;
    setMessage('', 'Validating form…');

    try {
        const form = document.getElementById('enrollmentForm');
        // For reenroll, "Save Changes" just validates the form is complete
        // The actual new enrollment is submitted by submitRenrollment()
        
        // Check required fields (basic validation)
        const requiredFields = ['year_start', 'year_end', 'Grade_Level'];
        for (const field of requiredFields) {
            const el = getField(field);
            if (!el || !el.value) {
                throw new Error(`Required field missing: ${field}`);
            }
        }

        setMessage('success', 'Form validated. Ready to submit re-enrollment.');
        clearDirty();
    } catch (error) {
        setMessage('error', error.message || 'Please complete all required fields.');
    } finally {
        if (button) button.disabled = false;
    }
}

// ── Load last enrollment for reenrollment ────────────────────

async function loadEnrollmentDetails(studentId, preserveStep = false) {
    if (!studentId) return;
    setMessage('', 'Loading student enrollment history…');
    try {
        const response = await API.enrollment.getByStudent(parseInt(studentId, 10));
        let enrollmentData = null;

        if (response.success && response.data)  enrollmentData = response.data;
        else if (response.enrollment)           enrollmentData = response;
        else if (response.data)                 enrollmentData = response.data;
        else if (Array.isArray(response))       enrollmentData = response[0];  // First/most recent
        else if (!response.success)             throw new Error(response.error || 'Unable to load enrollment history');

        if (!enrollmentData) throw new Error('No enrollment history found for this student');

        // Store the original enrollment ID for reenroll tracking
        window.originalEnrollmentId = enrollmentData.enrollment_id;
        
        // Increment school year for the new enrollment
        incrementSchoolYear(enrollmentData);
        
        applyEnrollmentToForm(enrollmentData);
        setMessage('success', 'Student enrollment loaded. Update as needed for the new school year, then submit.');
        if (!preserveStep) goTo(1);
    } catch (error) {
        setMessage('error', error.message || 'Failed to load enrollment details.');
        console.error('loadEnrollmentDetails error:', error);
    }
}

// ── Increment school year and grade ───────────────────────────

function incrementSchoolYear(enrollmentData) {
    if (!enrollmentData.enrollment) return;
    
    const enrollment = enrollmentData.enrollment;
    
    // Parse school year (e.g., "2025-2026" or separate year_start/year_end)
    let yearStart = enrollment.year_start;
    let yearEnd   = enrollment.year_end;
    
    if (!yearStart && enrollment.school_year) {
        const parts = enrollment.school_year.split('-');
        yearStart = parseInt(parts[0], 10);
        yearEnd   = parseInt(parts[1], 10);
    }
    
    if (yearStart && yearEnd) {
        enrollment.year_start = (yearStart + 1).toString();
        enrollment.year_end   = (yearEnd + 1).toString();
        enrollment.school_year = `${yearStart + 1}-${yearEnd + 1}`;
    }
    
    // Increment grade level
    const gradeMap = {
        'Kinder': 'Grade 1',
        'Grade 1': 'Grade 2',
        'Grade 2': 'Grade 3',
        'Grade 3': 'Grade 4',
        'Grade 4': 'Grade 5',
        'Grade 5': 'Grade 6',
        'Grade 6': 'Grade 6'  // Stay at Grade 6 if already there
    };
    
    if (enrollment.grade_level && gradeMap[enrollment.grade_level]) {
        enrollment.grade_level = gradeMap[enrollment.grade_level];
    }
}

// ── Fetch students for reenrollment ──────────────────────────

let allStudentsForRenroll = [];  // Cache of all students

async function fetchStudentsForRenroll() {
    const select       = document.getElementById('enrollmentSelect');
    const searchInput  = document.getElementById('studentSearch');
    const yearFilter   = document.getElementById('schoolYearFilter');
    
    select.innerHTML = '<option value="">Loading…</option>';
    setMessage('', 'Loading active students…');

    try {
        // Get all active/enrolled students
        const response = await API.students.list();
        let students = [];
        
        if (Array.isArray(response.data))      students = response.data;
        else if (Array.isArray(response))      students = response;
        else if (response.success && Array.isArray(response.students)) students = response.students;
        else if (!response.success)            throw new Error(response.error || 'Could not load students');

        // Filter to only active students
        allStudentsForRenroll = students.filter(s => s.status === 'active' || s.is_active);

        if (allStudentsForRenroll.length === 0) {
            select.innerHTML = '<option value="">-- no active students --</option>';
            setMessage('info', 'No active students available for re-enrollment.');
            return;
        }

        // Get enrollment history for each student to build year filter
        let enrollmentsByStudent = {};
        for (const student of allStudentsForRenroll) {
            try {
                const enrollments = await API.enrollment.getAllByStudent(student.student_id);
                if (enrollments && enrollments.data && Array.isArray(enrollments.data)) {
                    enrollmentsByStudent[student.student_id] = enrollments.data;
                } else if (Array.isArray(enrollments)) {
                    enrollmentsByStudent[student.student_id] = enrollments;
                }
            } catch (e) {
                // Skip students with no enrollment history
            }
        }

        // Build year filter from discovered enrollment years
        if (yearFilter) {
            const years = new Set();
            Object.values(enrollmentsByStudent).forEach(enrollments => {
                if (Array.isArray(enrollments)) {
                    enrollments.forEach(e => {
                        if (e.school_year) years.add(e.school_year);
                    });
                }
            });
            const sortedYears = Array.from(years).sort((a, b) => b.localeCompare(a));
            yearFilter.innerHTML = '<option value="">All years</option>';
            sortedYears.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearFilter.appendChild(option);
            });
        }

        // Populate student dropdown
        populateStudentOptions(yearFilter?.value || null);
        
        // Add search functionality
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => {
                populateStudentOptions(yearFilter?.value || null, searchInput.value);
            }, 300));
        }

        setMessage('', '');
    } catch (error) {
        select.innerHTML = '<option value="">Failed to load</option>';
        setMessage('error', error.message || 'Failed to load students');
        console.error('fetchStudentsForRenroll error:', error);
    }
}

function populateStudentOptions(filterYear = null, searchTerm = '') {
    const select = document.getElementById('enrollmentSelect');
    const search = (searchTerm || '').toLowerCase().trim();
    
    select.innerHTML = '<option value="">-- select student --</option>';

    let filtered = allStudentsForRenroll;
    
    if (search) {
        filtered = filtered.filter(s => {
            const fullName = `${s.last_name || ''} ${s.first_name || ''}`.toLowerCase();
            const lrn = (s.lrn || '').toString();
            return fullName.includes(search) || lrn.includes(search);
        });
    }

    filtered.forEach(student => {
        const option = document.createElement('option');
        option.value = student.student_id;  // Store student_id, not enrollment_id
        const fullName = `${student.last_name || ''}, ${student.first_name || ''}`.trim().replace(/^,\s*/, '');
        option.textContent = `${fullName} (LRN: ${student.lrn || 'N/A'})`;
        select.appendChild(option);
    });

    if (filtered.length === 0) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = '-- no students found --';
        option.disabled = true;
        select.appendChild(option);
    }
}

// ── Verify enrollment ─────────────────────────────────────────

async function submitRenrollment() {
    const studentIdInput = document.getElementById('studentIdInput');
    const studentId = studentIdInput?.value ? parseInt(studentIdInput.value, 10) : null;
    
    if (!studentId) {
        setMessage('error', 'Please select a student first.');
        return;
    }

    // Guard: unsaved edits must be saved before submitting
    if (formDirty) {
        setMessage('error', 'You have unsaved changes. Click "Save Changes" before submitting.');
        return;
    }

    if (!confirm('Submit this re-enrollment? This will create a new enrollment record for the student for the new school year.')) return;

    const button = document.getElementById('submitRenrollBtn');
    button.disabled = true;
    setMessage('', 'Processing re-enrollment…');

    try {
        const form    = document.getElementById('enrollmentForm');
        const payload = serializeForm(form);
        payload.student_id = studentId;
        payload.reenroll_from = window.originalEnrollmentId || null;  // Track the original enrollment

        // Submit as new enrollment (similar to initial enrollment)
        const response = await API.enrollment.submit(payload);
        
        if (!response || !response.success) {
            throw new Error(response?.error || response?.message || 'Re-enrollment submission failed');
        }

        setMessage('success', 'Student re-enrolled successfully! The new enrollment has been created.');
        
        // Reset form and clear student selection
        resetVerifyForm(true);
        await fetchStudentsForRenroll();
        
        // Clear search
        const searchInput = document.getElementById('studentSearch');
        if (searchInput) searchInput.value = '';
        
    } catch (error) {
        setMessage('error', error.message || 'Re-enrollment submission failed. Please try again.');
    } finally {
        button.disabled = false;
    }
}

// ── Clear form and close ─────────────────────────────────────

function clearRenrollForm() {
    resetVerifyForm();
    const enrollmentSelect = document.getElementById('enrollmentSelect');
    if (enrollmentSelect) enrollmentSelect.value = '';
    const searchInput = document.getElementById('studentSearch');
    if (searchInput) searchInput.value = '';
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
    const submitBtn        = document.getElementById('submitRenrollBtn');
    const clearFormBtn     = document.getElementById('clearFormBtn');
    const yearFilter       = document.getElementById('schoolYearFilter');
    const saveBtn          = document.getElementById('saveChangesBtn');

    if (enrollmentSelect) {
        enrollmentSelect.addEventListener('change', () => {
            const id = enrollmentSelect.value;
            if (!id) { resetVerifyForm(); return; }
            loadEnrollmentDetails(parseInt(id, 10));
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', e => {
            e.preventDefault();
            enrollmentSelect.value = '';
            resetVerifyForm();
        });
    }

    if (clearFormBtn) {
        clearFormBtn.addEventListener('click', e => {
            e.preventDefault();
            clearRenrollForm();
        });
    }

    if (yearFilter) yearFilter.addEventListener('change', () => fetchStudentsForRenroll());
    if (submitBtn)  submitBtn.addEventListener('click',  submitRenrollment);
    if (saveBtn)    saveBtn.addEventListener('click',    saveEnrollmentUpdates);

    const sameAddressRadios = Array.from(document.querySelectorAll('[name="same_address"]'));
    sameAddressRadios.forEach(radio => {
        radio.addEventListener('change', () => sameAddr(radio.value === 'Yes'));
    });
    const checkedSameAddr = sameAddressRadios.find(r => r.checked);
    if (checkedSameAddr) sameAddr(checkedSameAddr.value === 'Yes');

    attachDirtyListeners();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchStudentsForRenroll);
    } else {
        fetchStudentsForRenroll();
    }
}

initializeVerifyPage();