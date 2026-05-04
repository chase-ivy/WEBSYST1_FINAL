function showField(){
	const field = document.getElementById("field").value;
	const card = document.getElementById("fieldDetails");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
				<div class="card">
					<span>Medicine:</span>
					<input type="text" name="medicine_allergy" placeholder="Please Specify">
					<span>Pollen:</span>
					<select name="pollen_allergy" style="width: 100%;">
						<option disabled selected>Choose</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
					<span>Food:</span>
					<input type="text" name="food_allergy" placeholder="Please Specify">
					<span>Others:</span>
					<input type="text" name="other_allergy" placeholder="Please Specify">
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

			<div class="card">
			<h4>Please identify below:</h4>

				<div class="check">
				  <label>Error of refraction (Eye Ailment)
				    <input type="checkbox" name="error_of_refraction" value="1">
				  </label>
				</div>
				<hr>
				<div class="check">
				  <label>Asthma (Lung Ailment)
				    <input type="checkbox" name="asthma" value="2">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Seizure (Convulsions)
				    <input type="checkbox" name="seizure" value="3">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Heart Illness
				    <input type="checkbox" name="heart_illness" value="4">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Anemia
				    <input type="checkbox" name="anemia" value="5">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Bleeding disorder
				    <input type="checkbox" name="bleeding_disorder" value="6">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Fracture / Dislocation
				    <input class="toggle" type="checkbox" name="fracture_dislocation" value="7">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="checkbox" id="has_medical_condition" name="other_medical_condition" value="8">
				  </label>

				<div id="medical_condition_details" style="display:none; margin-top:8px;">
					<input type="text" name="other_medical_condition_text" placeholder="Please specify">
				</div>
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
		`;
	}
}

function showQ4(){
	const field = document.getElementById("Q4").value;
	const card = document.getElementById("q4");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
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
		`;
	}
}

function showQ5(){
	const field = document.getElementById("Q5").value;
	const card = document.getElementById("q5");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `

			<div class="card">

				<div class="check">
				  <label>Tuberculosis
				    <input type="checkbox" name="tuberculosis" value="1">
				</label>
				</div>
				<hr>

				<div class="check">
				<label>Cancer
					<input type="checkbox" id="has_cancer" name="has_cancer" value="2">
				</label>

				<div id="cancerBox" style="display:none; margin-top:8px;">
					<input type="text" name="cancer_type" placeholder="Please specify type of cancer">
				</div>
				<br>
				</div>

				<hr>
				<div class="check">
				  <label>Diabetes Mellitus
				    <input type="checkbox" name="diabetes_mellitus" value="3">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Hypertension
				    <input type="checkbox" name="hypertension" value="4">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Stroke / Heart attack
				    <input type="checkbox" name="stroke_heart_attack" value="5">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Depression
				    <input type="checkbox" name="depression" value="6">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Kidney problems
				    <input class="toggle" type="checkbox" name="kidney_problems" value="7">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="checkbox" id="has_other" name="other_condition_check" value="8">
				  </label>

				<div id="otherBox" style="display:none; margin-top:8px;">
					<input type="text" name="other_condition" placeholder="Please specify">
				</div>
				</div>

			</div>
		`;
	}
	setTimeout(() => {
		const cancerCheckbox = document.getElementById('has_cancer');
		const cancerBox = document.getElementById('cancerBox');

		if (cancerCheckbox) {
			cancerCheckbox.addEventListener('change', function () {
				cancerBox.style.display = this.checked ? 'block' : 'none';
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

	setTimeout(() => {
	const hasMedicalConditionCheckbox = document.getElementById('has_medical_condition');
	const medicalConditionDetails = document.getElementById('medical_condition_details');

	if (hasMedicalConditionCheckbox) {
		hasMedicalConditionCheckbox.addEventListener('change', function () {
			medicalConditionDetails.style.display = this.checked ? 'block' : 'none';
		});
	}
	}, 0);

}
