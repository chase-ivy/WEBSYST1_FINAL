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
    // Medical Condition Others
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

	// Family History - Cancer
	const cancerCheckbox = document.getElementById('has_cancer');
	const cancerBox = document.getElementById('cancerBox');
	if (cancerCheckbox && cancerBox) {
		cancerCheckbox.addEventListener('change', function () {
			cancerBox.style.display = this.checked ? 'block' : 'none';
		});
		cancerBox.style.display = cancerCheckbox.checked ? 'block' : 'none';
	}

	// Family History - Others
	const otherCheckbox = document.getElementById('has_other');
	const otherBox = document.getElementById('otherBox');
	if (otherCheckbox && otherBox) {
		otherCheckbox.addEventListener('change', function () {
			otherBox.style.display = this.checked ? 'block' : 'none';
		});
		otherBox.style.display = otherCheckbox.checked ? 'block' : 'none';
	}
}


const _visualImpairmentEl = document.getElementById('visual_impairment');
if (_visualImpairmentEl) {
    const _visualOptionsBox = document.getElementById('visualOptionsBox');
    _visualImpairmentEl.addEventListener('change', function() {
        if (_visualOptionsBox) _visualOptionsBox.style.display = this.checked ? 'block' : 'none';
    });
    if (_visualOptionsBox) _visualOptionsBox.style.display = _visualImpairmentEl.checked ? 'block' : 'none';
}

const _specialHealthEl = document.getElementById('special_health');
if (_specialHealthEl) {
    const _healthOptionsBox = document.getElementById('healthOptionsBox');
    _specialHealthEl.addEventListener('change', function() {
        if (_healthOptionsBox) _healthOptionsBox.style.display = this.checked ? 'block' : 'none';
    });
    if (_healthOptionsBox) _healthOptionsBox.style.display = _specialHealthEl.checked ? 'block' : 'none';
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
    if (!motherTongueSelect || !ipGroupSelect) return;

    const injectedMotherTongues   = Array.isArray(window.MOTHER_TONGUES) ? window.MOTHER_TONGUES : [];
    const injectedIndigenousGroups = Array.isArray(window.INDIGENOUS_GROUPS) ? window.INDIGENOUS_GROUPS : [];

    if (injectedMotherTongues.length) {
        populateLookupSelect(motherTongueSelect, injectedMotherTongues, 'id', 'name');
    }
    if (injectedIndigenousGroups.length) {
        populateLookupSelect(ipGroupSelect, injectedIndigenousGroups, 'id', 'name');
    }

    if (injectedMotherTongues.length && injectedIndigenousGroups.length) {
        return;
    }

    if (!API?.mother_tongues || !API?.indigenous_groups) {
        return;
    }

    try {
        const [motherTonguesResponse, indigenousGroupsResponse] = await Promise.all([
            API.mother_tongues.list(),
            API.indigenous_groups.list()
        ]);

        const motherTongues = Array.isArray(motherTonguesResponse.data) ? motherTonguesResponse.data : [];
        const indigenousGroups = Array.isArray(indigenousGroupsResponse.data) ? indigenousGroupsResponse.data : [];

        if (motherTongues.length) populateLookupSelect(motherTongueSelect, motherTongues, 'mother_tongue_id', 'name');
        if (indigenousGroups.length) populateLookupSelect(ipGroupSelect, indigenousGroups, 'indigenous_group_id', 'name');
    } catch (error) {
        console.error('Failed to load lookup values:', error);
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
        const value = item[valueField] ?? item.id ?? item;
        const label = item[labelField] ?? item.name ?? String(item);
        if (value === undefined || value === null) return;

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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        loadEnrollmentLookups();
        setAutoSchoolYear();
    });
} else {
    loadEnrollmentLookups();
    setAutoSchoolYear();
}

function sameAddr(yes) {
    // When 'same as current' = Yes, collapse the permanent address box; No = show it
    toggle('permBox', !yes);
}

// Auto-calculate Age when Birth Date changes — replaces enroll.js birthDate listener
const birthDateEl = document.getElementById('birthDate');
if (birthDateEl) {
    birthDateEl.addEventListener('change', function () {
        const birthDate = new Date(this.value);
        const today     = new Date();

        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        document.getElementById('ageField').value = age;
    });
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

    // Normalize Address Status values to match DB enum (`ownership_type`)
    const ownershipMap = {
        'Rental': 'rented',
        'Rented': 'rented',
        'rental': 'rented',
        'rented': 'rented',
        'Owned': 'owned',
        'owned': 'owned',
        'Living with Relatives': 'living_with_relatives',
        'living with relatives': 'living_with_relatives',
        'living_with_relatives': 'living_with_relatives',
        'Inherited': 'inherited',
        'inherited': 'inherited'
    };

    if (data.Current_Address_Status !== undefined) {
        const raw = data.Current_Address_Status;
        data.Current_Address_Status = ownershipMap[raw] ?? (typeof raw === 'string' ? raw.toLowerCase().replace(/\s+/g, '_') : raw);
    }

    if (data.same_address === 'Yes') {
        data.Permanent_Address_Status = data.Current_Address_Status;
    } else if (data.Permanent_Address_Status !== undefined) {
        const rawP = data.Permanent_Address_Status;
        data.Permanent_Address_Status = ownershipMap[rawP] ?? (typeof rawP === 'string' ? rawP.toLowerCase().replace(/\s+/g, '_') : rawP);
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
    container.className = `message ${type}`;
    container.textContent = message;
}

function generateEnrollmentXlsx(studentId) {
    const url = new URL('/WEBSYST1_FINAL/ams/generation/excel/excel.php', window.location.origin);
    url.searchParams.set('student_id', studentId);
    url.searchParams.set('type', 'combined');
    window.open(url.toString(), '_blank');
}

const enrollmentFormEl = document.getElementById('enrollmentForm');
if (enrollmentFormEl && typeof showConfirmation === 'function') {
    enrollmentFormEl.addEventListener('submit', async function (event) {
        event.preventDefault();
        showConfirmation(event.target);
    });
}

function generateConfirmationSummary(form) {
    const data = serializeForm(form);

    function safe(v) { return (v === undefined || v === null || (typeof v === 'string' && v.trim() === '')) ? null : v; }

    const studentName = [safe(data.Learner_First_Name), safe(data.Learner_Last_Name)].filter(Boolean).join(' ');
    const dob = safe(data.Birth_Date);
    const grade = safe(data.Grade_Level) || safe(data.Returning_Grade_Level);

    const addressParts = [];
    if (safe(data.Current_Street_Name)) addressParts.push(safe(data.Current_Street_Name));
    if (safe(data.Current_Barangay)) addressParts.push(safe(data.Current_Barangay));
    if (safe(data.Current_Municipality_City)) addressParts.push(safe(data.Current_Municipality_City));
    if (safe(data.Current_Province)) addressParts.push(safe(data.Current_Province));
    if (safe(data.Current_Zip_Code)) addressParts.push(safe(data.Current_Zip_Code));
    const address = addressParts.length ? addressParts.join(', ') : null;

    const fatherName = [safe(data.father_first_name), safe(data.father_last_name)].filter(Boolean).join(' ');
    const motherName = [safe(data.mother_first_name), safe(data.mother_last_name)].filter(Boolean).join(' ');
    const guardianName = [safe(data.guardian_first_name), safe(data.guardian_last_name)].filter(Boolean).join(' ');

    const fatherPhone = safe(data.father_contact_number);
    const motherPhone = safe(data.mother_contact_number);
    const guardianPhone = safe(data.guardian_contact_number);

    let summary = '<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">';

    if (studentName) summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Learner</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(studentName)}</span></div>`;
    if (dob) summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Date of Birth</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(dob)}</span></div>`;
    if (grade) summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Grade Level</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(grade)}</span></div>`;
    if (address) summary += `<div style="grid-column:1 / -1"><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Address</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(address)}</span></div>`;

    if (fatherName || fatherPhone) {
        let val = fatherName ? escapeHtml(fatherName) : '';
        if (fatherPhone) val += (val ? ' — ' : '') + escapeHtml(fatherPhone);
        summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Father</strong><span style="font-size:13px; color:var(--text);">${val}</span></div>`;
    }

    if (motherName || motherPhone) {
        let val = motherName ? escapeHtml(motherName) : '';
        if (motherPhone) val += (val ? ' — ' : '') + escapeHtml(motherPhone);
        summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Mother</strong><span style="font-size:13px; color:var(--text);">${val}</span></div>`;
    }

    if (guardianName || guardianPhone) {
        let val = guardianName ? escapeHtml(guardianName) : '';
        if (guardianPhone) val += (val ? ' — ' : '') + escapeHtml(guardianPhone);
        summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Guardian</strong><span style="font-size:13px; color:var(--text);">${val}</span></div>`;
    }

    summary += '</div>';
    return summary;
}

function showConfirmation(form) {
    const modal = document.getElementById('confirmationModal');
    const summary = document.getElementById('confirmationSummary');
    summary.innerHTML = generateConfirmationSummary(form);
    modal.style.display = 'flex';
    modal.style.background = 'rgba(0,0,0,0.5)';
}

function cancelConfirmation() {
    const modal = document.getElementById('confirmationModal');
    modal.style.display = 'none';
}

async function confirmSubmission() {
    const modal = document.getElementById('confirmationModal');
    modal.style.display = 'none';
    
    const form = document.getElementById('enrollmentForm');
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    showMessage('', '');

    try {
        const payload = serializeForm(form);
        
        // Validate required fields
        if (!payload.Learner_First_Name || !payload.Learner_Last_Name || !payload.Birth_Date) {
            throw new Error('Please fill in required learner information (First Name, Last Name, Date of Birth).');
        }

        // Create or update student record
        let studentId = payload.student_id ? parseInt(payload.student_id, 10) : null;
        let generatedPassword = '';
        let registeredUsername = '';
        let registrationEmail = '';
        let registrationPassword = '';

        if (!studentId) {
            showMessage('', 'Creating student record...');
            
            // Map enrollment form fields to the public student register endpoint
            const studentPayload = {
                // allow server to auto-generate username if not provided
                username: payload.user_email ? (payload.user_email.split('@')[0]) : '',
                password: payload.user_password || '',
                email: payload.user_email || '',
                lrn: payload.Learner_Reference_No || '',
                psa_bcn: payload.psa_bcn || '',
                last_name: payload.Learner_Last_Name || '',
                first_name: payload.Learner_First_Name || '',
                middle_name: payload.Learner_Middle_Name || '',
                extension_name: payload.Learner_Extension_Name || '',
                birth_date: payload.Birth_Date || '',
                sex: payload.sex || '',
                place_of_birth: payload.Place_of_Birth || ''
            };

            // Use the public register endpoint for open enrollment (creates account + student)
            const studentResp = await API.students.register(studentPayload);
            
            generatedPassword = studentResp.generated_password || studentResp.data?.generated_password || '';
            registeredUsername = studentResp.username || studentResp.data?.username || studentPayload.username || '';
            registrationEmail = studentPayload.email || '';
            registrationPassword = studentPayload.password || '';

            // Handle different API response formats
            if (studentResp.success && studentResp.data) {
                studentId = studentResp.data.id || studentResp.data.student_id;
            } else if (studentResp.id) {
                studentId = studentResp.id;
            } else if (studentResp.student_id) {
                studentId = studentResp.student_id;
            }
            
            if (!studentId) {
                throw new Error('Failed to create student record. Please try again or contact support.');
            }

            payload.student_id = studentId;
        }

        showMessage('', 'Submitting enrollment...');

        // Submit enrollment
        const response = await API.enrollment.submit(payload);
        
        // Handle different API response formats
        let enrollmentId = null;
        if (response.success && response.data) {
            enrollmentId = response.data.enrollment_id || response.data.id;
        } else if (response.enrollment_id) {
            enrollmentId = response.enrollment_id;
        } else if (response.id) {
            enrollmentId = response.id;
        }

        if (!response.success && !enrollmentId) {
            throw new Error(response.error || response.message || 'Enrollment submission failed.');
        }

        let credentialMessage = '';
        if (registrationEmail) {
            credentialMessage += `Email: ${registrationEmail}`;
        }
        if (generatedPassword) {
            credentialMessage += (credentialMessage ? ' | ' : '') + `Password: ${generatedPassword}`;
        } else if (registrationPassword) {
            credentialMessage += (credentialMessage ? ' | ' : '') + 'Password: (the one you entered)';
        }
        if (!registrationEmail && registeredUsername) {
            credentialMessage = `Username: ${registeredUsername}` + (generatedPassword ? ` | Password: ${generatedPassword}` : '');
        }

        const successText = 'Enrollment submitted successfully!' + (enrollmentId ? ' Enrollment ID: ' + enrollmentId + '.' : '')
            + (credentialMessage ? ` Login credentials: ${credentialMessage}.` : '')
            + ' Redirecting...';

        showMessage('success', successText);

        setTimeout(() => {
            window.location.href = '../../../dashboard/student_dashboard/student_dashboard.php';
        }, 5000);

        form.reset();
        goTo(1);
        document.getElementById('ageField').value = '';
        toggle('permBox', true); // Reset permanent address box to visible on form reset
    } catch (error) {
        showMessage('error', error.message || 'Enrollment submission failed. Please review the form and try again.');
        console.error('Enrollment submission error:', error);
    } finally {
        submitButton.disabled = false;
    }
}
