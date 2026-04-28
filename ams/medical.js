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
				</div>
		`;
	}
}
