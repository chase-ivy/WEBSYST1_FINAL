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
						<option value="Yes">Yes</option>
						<option value="No">No</option>
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
				    <input type="checkbox" name="asthma" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Seizure (Convulsions)
				    <input type="checkbox" name="seizure" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Heart Illness
				    <input type="checkbox" name="heart_illness" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Anemia
				    <input type="checkbox" name="anemia" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Bleeding disorder
				    <input type="checkbox" name="bleeding_disorder" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Fracture / Dislocation
				    <input class="toggle" type="checkbox" name="fracture_dislocation" value="1">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="text" name="other_condition" placeholder="Please Specify">
				  </label>
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

					<input type="text" name="hospital_name" placeholder="Where?" required>

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
				  <label>Cancer, what kind?
				    <input type="text" name="cancer_type" placeholder="Please Specify">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Diabetes Mellitus
				    <input type="checkbox" name="diabetes_mellitus" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Hypertension
				    <input type="checkbox" name="hypertension" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Stroke / Heart attack
				    <input type="checkbox" name="stroke_heart_attack" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Depression
				    <input type="checkbox" name="depression" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Kidney problems
				    <input class="toggle" type="checkbox" name="kidney_problems" value="1">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="text" name="other_condition" placeholder="Please Specify">
				  </label>
				</div>

			</div>
		`;
	}
}
