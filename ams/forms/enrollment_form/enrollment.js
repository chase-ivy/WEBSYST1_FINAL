function showField(){
    const field = document.getElementById("field").value;
    const card = document.getElementById("fieldDetails");

    card.innerHTML = "";

    if (field === "1"){
        card.innerHTML = `
<div style="background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius-md); padding:14px 16px; display:flex; flex-direction:column; gap:12px;">

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="medicine_allergy_checkbox" name="medicine_allergy[]" value="1" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Medicine
    </label>
    <div id="medicineAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[1]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="pollen_allergy_checkbox" name="medicine_allergy[]" value="2" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Pollen
    </label>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="food_allergy_checkbox" name="medicine_allergy[]" value="3" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Food
    </label>
    <div id="foodAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[3]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="other_allergy_checkbox" name="medicine_allergy[]" value="4" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Others
    </label>
    <div id="otherAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[4]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

</div>
`;

        const attachToggle = (checkboxId, boxId) => {
            const checkbox = document.getElementById(checkboxId);
            const box = document.getElementById(boxId);
            if (!checkbox || !box) return;
            checkbox.addEventListener('change', () => {
                box.style.display = checkbox.checked ? 'block' : 'none';
            });
            box.style.display = checkbox.checked ? 'block' : 'none';
        };

        attachToggle('medicine_allergy_checkbox', 'medicineAllergyBox');
        attachToggle('food_allergy_checkbox', 'foodAllergyBox');
        attachToggle('other_allergy_checkbox', 'otherAllergyBox');
    }
}

function showQ2(){
    const field = document.getElementById("Q2").value;
    const card = document.getElementById("q2");

    card.innerHTML = "";

    if (field === "1"){
        card.innerHTML = `
        <div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="condition_type_id" value="1" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Error of refraction (Eye Ailment)</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="condition_type_id" value="2" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Asthma (Lung Ailment)</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="condition_type_id" value="3" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Seizure (Convulsions)</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="condition_type_id" value="4" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Heart Illness</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="condition_type_id" value="5" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Anemia</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="condition_type_id" value="6" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Bleeding disorder</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="condition_type_id" value="7" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Fracture / Dislocation</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease; grid-column:span 2;">
                <input type="checkbox" id="has_medical_condition" name="condition_type_id" value="8" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Others</span>
            </label>
            <div id="medical_condition_details" style="display:none; margin-top:8px; grid-column:span 2;">
                <input type="text" name="condition_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
            </div>
        </div>
        `;
    }
    const medicalCheckbox = document.getElementById('has_medical_condition');
    const medicalBox = document.getElementById('medical_condition_details');
    if (medicalCheckbox && medicalBox) {
        medicalCheckbox.addEventListener('change', function () {
            medicalBox.style.display = this.checked ? 'block' : 'none';
        });
        medicalBox.style.display = medicalCheckbox.checked ? 'block' : 'none';
    }
}

function showQ3(){
    const field = document.getElementById("Q3").value;
    const card = document.getElementById("q3");

    card.innerHTML = "";

    if (field === "1"){
        card.innerHTML = `
        <div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Surgery Date</label>
                <input type="date" name="surgery_date" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Hospital Name</label>
                <input type="text" name="hospital_name" placeholder="Hospital name" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Body Part Affected</label>
                <input type="text" name="body_part" placeholder="What part of the body?" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
            </div>
        </div>
        `;
    }
}

function showQ4(){
    const field = document.getElementById("Q4").value;
    const card = document.getElementById("q4");

    card.innerHTML = "";

    if (field === "1"){
        card.innerHTML = `
        <div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Medicine / Treatment Type</label>
                <input type="text" name="treatment_medicine" placeholder="Name of medicine or treatment" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Dosage Schedule</label>
                <input type="text" name="schedule_dosage" placeholder="e.g., 2x daily, morning/evening" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
            </div>
        </div>
        `;
    }
}

function showQ5(){
    const field = document.getElementById("Q5").value;
    const card = document.getElementById("q5");

    card.innerHTML = "";

    if (field === "1"){
        card.innerHTML = `
        <div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="family_condition_type_id" value="1" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Tuberculosis</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" id="has_cancer" name="family_condition_type_id" value="2" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Cancer</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="family_condition_type_id" value="3" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Diabetes Mellitus</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="family_condition_type_id" value="4" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Hypertension</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="family_condition_type_id" value="5" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Stroke / Heart attack</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="family_condition_type_id" value="6" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Depression</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
                <input type="checkbox" name="family_condition_type_id" value="7" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Kidney problems</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease; grid-column:span 2;">
                <input type="checkbox" id="has_other" name="family_condition_type_id" value="8" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
                <span>Others</span>
            </label>
            <div id="otherBox" style="display:none; margin-top:8px; grid-column:span 2;">
                <input type="text" name="family_condition_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
            </div>
            <div id="cancerBox" style="display:none; margin-top:8px; grid-column:1;">
                <input type="text" name="family_condition_description" placeholder="Specify type of cancer" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
            </div>
        </div>
        `;
    }

    const cancerCheckbox = document.getElementById('has_cancer');
    const cancerBox = document.getElementById('cancerBox');
    if (cancerCheckbox && cancerBox) {
        cancerCheckbox.addEventListener('change', function () {
            cancerBox.style.display = this.checked ? 'block' : 'none';
        });
        cancerBox.style.display = cancerCheckbox.checked ? 'block' : 'none';
    }

    const otherCheckbox = document.getElementById('has_other');
    const otherBox = document.getElementById('otherBox');
    if (otherCheckbox && otherBox) {
        otherCheckbox.addEventListener('change', function () {
            otherBox.style.display = this.checked ? 'block' : 'none';
        });
        otherBox.style.display = otherCheckbox.checked ? 'block' : 'none';
    }
}

const visualImpairment = document.getElementById('visual_impairment');
const visualOptionsBox = document.getElementById('visualOptionsBox');
if (visualImpairment && visualOptionsBox) {
    visualImpairment.addEventListener('change', function() {
        visualOptionsBox.style.display = this.checked ? 'block' : 'none';
    });
    visualOptionsBox.style.display = visualImpairment.checked ? 'block' : 'none';
}

const specialHealth = document.getElementById('special_health');
const healthOptionsBox = document.getElementById('healthOptionsBox');
if (specialHealth && healthOptionsBox) {
    specialHealth.addEventListener('change', function() {
        healthOptionsBox.style.display = this.checked ? 'block' : 'none';
    });
    healthOptionsBox.style.display = specialHealth.checked ? 'block' : 'none';
}

let current = 1;

function goTo(n) {
    document.getElementById('panel-' + current).classList.remove('active');
    document.getElementById('s' + current).classList.remove('active');
    document.getElementById('s' + current).classList.add('done');

    current = n;

    document.getElementById('panel-' + n).classList.add('active');
    document.querySelectorAll('#stepper .step').forEach((s, i) => {
        if (i + 1 <  n) { s.classList.add('done');    s.classList.remove('active'); }
        if (i + 1 === n) { s.classList.add('active');  s.classList.remove('done');   }
        if (i + 1 >  n) { s.classList.remove('done', 'active'); }
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toggle(id, open) {
    document.getElementById(id).classList.toggle('open', open);
}

function toggleMotherTongueOther() {
    const select = document.getElementById('Mother_Tongue');
    const other  = document.getElementById('Mother_Tongue_Other');
    if (!select || !other) return;
    if (select.value === 'Other') {
        other.style.display = 'block';
    } else {
        other.style.display = 'none';
        other.value = '';
    }
}

function toggleIpOther() {
    const select = document.getElementById('IP_Group');
    const other  = document.getElementById('IP_Specify');
    if (!select || !other) return;
    if (select.value === 'Other') {
        other.style.display = 'block';
    } else {
        other.style.display = 'none';
        other.value = '';
    }
}

async function loadEnrollmentLookups() {
    const motherTongueSelect = document.getElementById('Mother_Tongue');
    const ipGroupSelect = document.getElementById('IP_Group');

    if (!motherTongueSelect || !ipGroupSelect) {
        return;
    }

    const populateFromGlobals = () => {
        if (window.MOTHER_TONGUES && Array.isArray(window.MOTHER_TONGUES)) {
            populateLookupSelect(motherTongueSelect, window.MOTHER_TONGUES, 'id', 'name');
        }
        if (window.INDIGENOUS_GROUPS && Array.isArray(window.INDIGENOUS_GROUPS)) {
            populateLookupSelect(ipGroupSelect, window.INDIGENOUS_GROUPS, 'id', 'name');
        }
    };

    if (window.MOTHER_TONGUES && Array.isArray(window.MOTHER_TONGUES) && window.INDIGENOUS_GROUPS && Array.isArray(window.INDIGENOUS_GROUPS)) {
        populateFromGlobals();
        return;
    }

    try {
        // Load mother tongues from API
        let motherTongues = [];
        if (API?.mother_tongues) {
            const mtResponse = await API.mother_tongues.list();
            if (mtResponse.success && Array.isArray(mtResponse.data)) {
                motherTongues = mtResponse.data;
            } else if (Array.isArray(mtResponse)) {
                motherTongues = mtResponse;
            }
        }
        
        // Load indigenous groups from API
        let indigenousGroups = [];
        if (API?.indigenous_groups) {
            const igResponse = await API.indigenous_groups.list();
            if (igResponse.success && Array.isArray(igResponse.data)) {
                indigenousGroups = igResponse.data;
            } else if (Array.isArray(igResponse)) {
                indigenousGroups = igResponse;
            }
        }

        if (motherTongues.length > 0) populateLookupSelect(motherTongueSelect, motherTongues, 'mother_tongue_id', 'name');
        if (indigenousGroups.length > 0) populateLookupSelect(ipGroupSelect, indigenousGroups, 'indigenous_group_id', 'name');
    } catch (error) {
        console.error('Failed to load lookup values:', error);
        populateFromGlobals();
        // Silently fail - form will work with hardcoded defaults if lookups unavailable
    }
}

function populateLookupSelect(select, values, valueField = 'id', labelField = 'name') {
    const otherOption = Array.from(select.options).find(option => option.value === 'Other');
    select.querySelectorAll('option').forEach(option => {
        if (option.value !== '' && option.value !== 'Other') {
            option.remove();
        }
    });

    values.forEach(item => {
        if (!item) return;
        const value = typeof item === 'object' ? (item[valueField] ?? item.id ?? '') : item;
        const label = typeof item === 'object' ? (item[labelField] ?? item.name ?? String(item)) : String(item);
        if (!value && value !== 0) return;

        const option = document.createElement('option');
        option.value = String(value);
        option.textContent = label;
        select.insertBefore(option, otherOption || null);
    });
}

function setAutoSchoolYear() {
    const startInput = document.querySelector('input[name="year_start"]');
    const endInput = document.querySelector('input[name="year_end"]');
    if (!startInput || !endInput) {
        return;
    }

    const now = new Date();
    const month = now.getMonth();
    const year = now.getFullYear();
    const startYear = month >= 5 ? year : year - 1;
    const endYear = startYear + 1;

    if (!startInput.value) {
        startInput.value = startYear;
    }
    if (!endInput.value) {
        endInput.value = endYear;
    }
}

function sameAddr(yes) {
    const permBox = document.getElementById('permBox');
    if (!permBox) return;
    permBox.style.opacity       = yes ? '.4'    : '1';
    permBox.style.pointerEvents = yes ? 'none'  : 'auto';
}

function addNestedValue(target, name, value) {
    const parts = name.split('[').map(part => part.replace(/\]$/, ''));
    let currentNode = target;

    parts.forEach((part, index) => {
        const isLast = index === parts.length - 1;
        const nextPart = parts[index + 1];
        const nextPartIsNumeric = /^\d+$/.test(nextPart);

        if (part === '') {
            if (isLast) {
                currentNode.push(value);
            } else {
                if (!Array.isArray(currentNode)) {
                    currentNode = [];
                }
                if (currentNode.length === 0) {
                    currentNode.push(nextPartIsNumeric ? {} : []);
                }
                currentNode = currentNode[currentNode.length - 1];
            }
        } else {
            if (isLast) {
                const isNumericKey = /^\d+$/.test(part);
                if (isNumericKey) {
                    if (typeof currentNode[part] !== 'object' || currentNode[part] === null) {
                        currentNode[part] = value;
                    }
                } else {
                    if (currentNode[part] === undefined) {
                        currentNode[part] = [];
                    }
                    if (!Array.isArray(currentNode[part])) {
                        currentNode[part] = [currentNode[part]];
                    }
                    currentNode[part].push(value);
                }
            } else {
                if (currentNode[part] === undefined) {
                    currentNode[part] = nextPartIsNumeric ? {} : [];
                }
                currentNode = currentNode[part];
            }
        }
    });
}

function serializeForm(form) {
    const formData = new FormData(form);
    const data = {};

    for (const [name, value] of formData.entries()) {
        if (name === 'next') {
            continue;
        }

        if (name.includes('[')) {
            addNestedValue(data, name, value);
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

    if (data.same_address === 'Yes') {
        data.Permanent_House_No          = data.Current_House_No;
        data.Permanent_Street_Name       = data.Current_Street_Name;
        data.Permanent_Barangay          = data.Current_Barangay;
        data.Permanent_Municipality_City = data.Current_Municipality_City;
        data.Permanent_Province          = data.Current_Province;
        data.Permanent_Country           = data.Current_Country;
        data.Permanent_Zip_Code          = data.Current_Zip_Code;
    }

    return data;
}

function escapeHtml(text) {
    if (text === undefined || text === null) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showMessage(type, message) {
    const container = document.getElementById('formMessage');
    if (!container) return;
    container.className = `message ${type}`;
    container.textContent = message;
}

function generateEnrollmentXlsx(studentId) {
    const url = new URL('/WEBSYST1_FINAL/ams/generation/excel/excel.php', window.location.origin);
    url.searchParams.set('student_id', studentId);
    url.searchParams.set('type', 'combined');
    window.open(url.toString(), '_blank');
}

function initEnrollmentPage() {
    loadEnrollmentLookups();
    setAutoSchoolYear();

    const birthDateInput = document.getElementById('birthDate');
    if (birthDateInput) {
        birthDateInput.addEventListener('change', function () {
            const birthDate = new Date(this.value);
            const today     = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            const ageField = document.getElementById('ageField');
            if (ageField) {
                ageField.value = age;
            }
        });
    }

    const enrollmentForm = document.getElementById('enrollmentForm');
    if (enrollmentForm) {
        enrollmentForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (typeof showConfirmation === 'function') {
                showConfirmation(event.target);
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEnrollmentPage);
} else {
    initEnrollmentPage();
}
