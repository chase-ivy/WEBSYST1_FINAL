function showField(){
	const field = document.getElementById("field").value;
	const card = document.getElementById("fieldDetails");

	card.innerHTML = "";

	if (field === "Yes"){
		card.innerHTML = `
				<div class="card">
					<span>Medicine:</span>
					<input type="text" placeholder="Please Specify">
					<span>Pollen:</span>
					<select style="width: 100%;">
						<option disabled selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>
					<span>Food:</span>
					<input type="text" placeholder="Please Specify">
					<span>Others:</span>
					<input type="text" placeholder="Please Specify">
				</div>
		`;
	}
}

function showQ2(){
	const field = document.getElementById("Q2").value;
	const card = document.getElementById("q2");

	card.innerHTML = "";

	if (field === "Yes"){
		card.innerHTML = `

			<div class="card">
			<h4>Please identify below:</h4>

				<div class="check">
				  <label>Error of refraction (Eye Ailment)
				    <input type="checkbox" value="Error of refraction (Eye Ailment)">
				  </label>
				</div>
				<hr>
				<div class="check">
				  <label>Asthma (Lung Ailment)
				    <input type="checkbox" value="Asthma (Lung Ailment)">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Seizure (Convulsions)
				    <input type="checkbox" value="Seizure (Convulsions)">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Heart Illness
				    <input type="checkbox" value="Heart Illness">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Anemia
				    <input type="checkbox" value="Anemia">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Bleeding disorder
				    <input type="checkbox" value="Bleeding disorder">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Fracture / Dislocation
				    <input class="toggle" type="checkbox" value="Fracture / Dislocation">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="text" placeholder="Please Specify">
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

	if (field === "Yes"){
		card.innerHTML = `
			<div class="card">
			<h4>Please specify details:</h4>

				<div class="check">
				  <label>(When/Where/What part of the body):
				    <input type="text">
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

	if (field === "Yes"){
		card.innerHTML = `
			<div class="card">
			<h4>Please specify as to:</h4>

				<div class="check">
				  <label>Kind of treatment / medicie:
				    <input type="text">
				  </label>

				  <label>Schedule / dosage:
				    <input type="text">
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

	if (field === "Yes"){
		card.innerHTML = `

			<div class="card">

				<div class="check">
				  <label>Tuberculosis
				    <input type="checkbox" value="Tuberculosis">
				  </label>
				</div>
				<hr>
				<div class="check">
				  <label>Cancer, what kind?
				    <input type="text" >
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Diabetes Mellitus
				    <input type="checkbox" value="Diabetes Mellitus">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Hypertension
				    <input type="checkbox" value="Hypertension">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Stroke / Heart attack
				    <input type="checkbox" value="Stroke / Heart attack">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Depression
				    <input type="checkbox" value="Depression">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Kidney problems
				    <input class="toggle" type="checkbox" value="Kidney problems">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="text" placeholder="Please Specify">
				  </label>
				</div>

			</div>
		`;
	}
}
