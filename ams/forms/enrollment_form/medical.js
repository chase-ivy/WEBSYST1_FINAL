function showField(){
	const field = document.getElementById("field").value;
	const card = document.getElementById("fieldDetails");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<center>
				<div class="card">

					<div class="check">
					<label>Medicine:
					<input type="checkbox" id="has_allergy_box" name="medicine_allergy" value="1">
					</label>
					
					<div id="allergyBox" style="display:none; margin-top:8px;">
						<input type="text" name="has_allergy" placeholder="Please Specify">
					</div>
					</div>

					<hr>
					<span>Pollen:</span>
					<select name="medicine_allergy" style="width: 100%;">
						<option disabled selected>Choose</option>
						<option value="2">Yes</option>
						<option value="0">No</option>
					</select>
					<hr>
					<span>Food:</span>
					<input type="checkbox" id="food_allergy_checkbox" name="medicine_allergy" value="3">
					<div id="foodAllergyBox" style="display:none; margin-top:8px;">
						<input type="text" name="allergy_description" placeholder="Please Specify">
					</div>
					<hr>
					<span>Others:</span>
					<input type="checkbox" id="food_allergy_checkbox" name="medicine_allergy" value="4">
					<div id="foodAllergyBox" style="display:none; margin-top:8px;">
						<input type="text" name="allergy_description" placeholder="Please Specify">
					</div>
				</div>
		</center>
		`;
	}
}

function showQ2(){
	const field = document.getElementById("Q2").value;
	const card = document.getElementById("q2");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<center>
			<div class="card">
			<h4>Please identify below:</h4>

				<div class="check">
				  <label>Error of refraction (Eye Ailment)
				    <input type="checkbox" name="condition_type_id" value="1">
				  </label>
				</div>
				<hr>
				<div class="check">
				  <label>Asthma (Lung Ailment)
				    <input type="checkbox" name="condition_type_id" value="2">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Seizure (Convulsions)
				    <input type="checkbox" name="condition_type_id" value="3">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Heart Illness
				    <input type="checkbox" name="condition_type_id" value="4">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Anemia
				    <input type="checkbox" name="condition_type_id" value="5">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Bleeding disorder
				    <input type="checkbox" name="condition_type_id" value="6">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Fracture / Dislocation
				    <input class="toggle" type="checkbox" name="condition_type_id" value="7">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="checkbox" id="has_medical_condition" name="condition_type_id" value="8">
				  </label>

				<div id="medical_condition_details" style="display:none; margin-top:8px;">
					<input type="text" name="condition_description" placeholder="Please specify">
				</div>
				</div>

			</div>
		</center>
		`;
	}
}

function showQ3(){
	const field = document.getElementById("Q3").value;
	const card = document.getElementById("q3");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<center>
			<div class="card">
			<h4>Please specify details:</h4>

				<div class="check">
				  <label>(When/Where/What part of the body):
				    <input type="date" name="surgery_date" required>

					<input type="text" name="hospital_name" placeholder="Hospital Name" required>

					<input type="text" name="body_part" placeholder="What part of the body?" required>
				  </label>
				</div>

			</div>
		</center>
		`;
	}
}

function showQ4(){
	const field = document.getElementById("Q4").value;
	const card = document.getElementById("q4");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<center>
			<div class="card">
			<h4>Please specify as to:</h4>

				<div class="check">
				  <label>Kind of treatment / medicine:
				    <input type="text" name="treatment_medicine" placeholder="Please Specify">
				  </label>
				<br><br>
				  <label>Schedule / dosage:
				    <input type="text" name="schedule_dosage" placeholder="Please Specify">
				  </label>
				</div>

			</div>
		<center>
		`;
	}
}

function showQ5(){
	const field = document.getElementById("Q5").value;
	const card = document.getElementById("q5");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<center>
			<div class="card">

				<div class="check">
				  <label>Tuberculosis
				    <input type="checkbox" name="family_condition_type_id" value="1">
				</label>
				</div>
				<hr>

				<div class="check">
				<label>Cancer
					<input type="checkbox" id="has_cancer" name="family_condition_type_id" value="2">
				</label>

				<div id="cancerBox" style="display:none; margin-top:8px;">
					<input type="text" name="family_condition_description" placeholder="Please specify type of cancer">
				</div>
				<br>
				</div>

				<hr>
				<div class="check">
				  <label>Diabetes Mellitus
				    <input type="checkbox" name="family_condition_type_id" value="3">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Hypertension
				    <input type="checkbox" name="family_condition_type_id" value="4">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Stroke / Heart attack
				    <input type="checkbox" name="family_condition_type_id" value="5">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Depression
				    <input type="checkbox" name="family_condition_type_id" value="6">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Kidney problems
				    <input class="toggle" type="checkbox" name="family_condition_type_id" value="7">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="checkbox" id="has_other" name="family_condition_type_id" value="8">
				  </label>

				<div id="otherBox" style="display:none; margin-top:8px;">
					<input type="text" name="family_condition_description" placeholder="Please specify">
				</div>
				</div>

			</div>
		</center>
		`;
	}

	// ALLERGIES
		// Medicine Allergy
	setTimeout(() => {
		const allergyCheckbox = document.getElementById('has_allergy_box');
		const allergyBox = document.getElementById('allergyBox');

		if (allergyBox) {
			allergyCheckbox.addEventListener('change', function () {
				allergyBox.style.display = this.checked ? 'block' : 'none';
			});		
		}
	}, 0);
		setTimeout(() => {
    const otherCheckbox = document.getElementById('has_other');
    const otherBox = document.getElementById('otherBox');

    if (otherCheckbox) {
        otherCheckbox.addEventListener('change', function () {
            otherBox.style.display = this.checked ? 'block' : 'none';
        });
    }
	}, 0);


		// Food Allergy
	setTimeout(() => {
		const foodAllergyCheckbox = document.getElementById('food_allergy_checkbox');
		const foodAllergyBox = document.getElementById('foodAllergyBox');

		if (foodAllergyCheckbox) {
			foodAllergyCheckbox.addEventListener('change', function () {
				foodAllergyBox.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);
		// Other Medicine Input
	setTimeout(() => {
		const otherAllergyCheckbox = document.getElementById('has_other_allergy_box');
		const otherAllergyBox = document.getElementById('otherAllergyBox');
		if (otherAllergyCheckbox) {
			otherAllergyCheckbox.addEventListener('change', function () {
				otherAllergyBox.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);

	//DISABILITIES
	setTimeout(() => {
		const cancerCheckbox = document.getElementById('has_cancer');
		const cancerBox = document.getElementById('cancerBox');

		if (cancerCheckbox) {
			cancerCheckbox.addEventListener('change', function () {
				cancerBox.style.display = this.checked ? 'block' : 'none';
			});
		}
	}, 0);

	// DISABILITIES OTHERS INPUT TPYE
	setTimeout(() => {
    const otherCheckbox = document.getElementById('has_other');
    const otherBox = document.getElementById('otherBox');

    if (otherCheckbox) {
        otherCheckbox.addEventListener('change', function () {
            otherBox.style.display = this.checked ? 'block' : 'none';
        });
    }
	}, 0);

	// MEDICAL CONDITIONS OTHERS INPUT TYPE
	setTimeout(() => {
	const hasMedicalConditionCheckbox = document.getElementById('condition_type_id');
	const medicalConditionDetails = document.getElementById('condition_description');

	if (hasMedicalConditionCheckbox) {
		hasMedicalConditionCheckbox.addEventListener('change', function () {
			medicalConditionDetails.style.display = this.checked ? 'block' : 'none';
		});
	}
	}, 0);

}
