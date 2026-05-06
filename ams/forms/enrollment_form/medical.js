function showField(){
	const field = document.getElementById("field").value;
	const card = document.getElementById("fieldDetails");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:flex; flex-direction:column; gap:10px;">
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">
					<input type="checkbox" id="has_allergy_box" name="medicine_allergy" value="1" style="accent-color:var(--brand);">
					Medicine
				</label>
				<div id="allergyBox" style="display:none; margin-left:20px;">
					<input type="text" name="has_allergy" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
				</div>
			</div>

			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">
					<input type="checkbox" name="medicine_allergy" value="2" style="accent-color:var(--brand);">
					Pollen
				</label>
			</div>

			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">
					<input type="checkbox" id="food_allergy_checkbox" name="medicine_allergy" value="3" style="accent-color:var(--brand);">
					Food
				</label>
				<div id="foodAllergyBox" style="display:none; margin-left:20px;">
					<input type="text" name="allergy_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
				</div>
			</div>

			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">
					<input type="checkbox" id="other_allergy_checkbox" name="medicine_allergy" value="4" style="accent-color:var(--brand);">
					Others
				</label>
				<div id="otherAllergyBox" style="display:none; margin-left:20px;">
					<input type="text" name="allergy_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
				</div>
			</div>
		</div>
		`;
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

	// EVENT LISTENERS FOR CONDITIONAL FIELDS

	// Medicine Allergy
	setTimeout(() => {
		const checkbox = document.getElementById('has_allergy_box');
		const box = document.getElementById('allergyBox');
		if (checkbox) {
			checkbox.addEventListener('change', function () {
				if (box) box.style.display = this.checked ? 'block' : 'none';
			});		
		}
	}, 0);

	// Food Allergy
	setTimeout(() => {
		const checkbox = document.getElementById('food_allergy_checkbox');
		const box = document.getElementById('foodAllergyBox');
		if (checkbox) {
			checkbox.addEventListener('change', function () {
				if (box) box.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);

	// Other Allergy
	setTimeout(() => {
		const checkbox = document.getElementById('other_allergy_checkbox');
		const box = document.getElementById('otherAllergyBox');
		if (checkbox) {
			checkbox.addEventListener('change', function () {
				if (box) box.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);

	// Medical Condition Others
	setTimeout(() => {
		const checkbox = document.getElementById('has_medical_condition');
		const box = document.getElementById('medical_condition_details');
		if (checkbox) {
			checkbox.addEventListener('change', function () {
				if (box) box.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);

	// Family History - Cancer
	setTimeout(() => {
		const checkbox = document.getElementById('has_cancer');
		const box = document.getElementById('cancerBox');
		if (checkbox) {
			checkbox.addEventListener('change', function () {
				if (box) box.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);

	// Family History - Others
	setTimeout(() => {
		const checkbox = document.getElementById('has_other');
		const box = document.getElementById('otherBox');
		if (checkbox) {
			checkbox.addEventListener('change', function () {
				if (box) box.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);
}
