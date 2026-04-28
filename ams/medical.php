<!DOCTYPE html>
<html>
<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>Gibraltar - Medical Form</title>
</head>
<body>
<header>
  <h2>Medical Form - AMS</h2>
<!-- Can someone put a pic logo ng school -->
  <img src="logo.png" alt="Logo" class="logo">
  <style>		
  		input[type="radio"]{
			width: auto;
			padding: 0;
			margin-right: 5px;
		}
	</style>
</header>
<body>
	<center><h1>Medical Form</h1></center>

<div class="card">
					<nav class="nav-card">
						<a href="index.php" class="select">Change Form Access <</a><br><br>

						<p><strong>Medical Module</strong></p>
					</nav>


<section>
	<form>
		<div class="card">
			<label>Name of Learner:</label>
			<input type="text" name="fullName" placeholder="Full Name (Family Name First)">

			<label>Date of Birth:</label>
			<input type="Date" name="DoB">

			<label>Address:</label>
			<input type="text" name="address">

			<label>Age:</label>
			<input type="number" name="age">

			<label>Sex</label><br>
			<label><input type="radio" name="sex" value="Male" onchange=""> Male</label>
			<label><input type="radio" name="sex" value="Female" onchange=""> Female</label>
			<br><br>
			<hr>

			<label>Name of Parent/Guardian:</label>
			<input type="text" name="">

			<label>Contact Number:</label>
			<input type="tel" name="">
			<hr>
			<strong><p>Instruction: Please put a check (✅) on appropriate items and fill up blanks as indicated.</p></strong>
			<h4>1. Does your child/ward have any allergies?</h4>	

					<select id="field" onchange="showField()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>

				<div id="fieldDetails"></div>

			<h4>2. Does your child/ward have any ongoing medical condition?</h4>
					<select id="Q2" onchange="showQ2()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>

				<div id="q2"></div>


			<h4>3. Did your child/ward ever have surgery / hospitalization?</h4>
					<select id="Q3" onchange="showQ3()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>

				<div id="q3"></div>

			<h4>4. Is your  child currently taking treatment / medicines?</h4>
					<select id="Q4" onchange="showQ4()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>

				<div id="q4"></div>

			<h4>5. Does your family have a history of the following conditions:</h4>
					<select id="Q5" onchange="showQ5()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>

				<div id="q5"></div>

			<h4>6. Does your child/ward have exposure to cigarette/vape smoke at home?:</h4>
					<select style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>

				<div id="q5"></div>

			<h4>7. Other pertinent learner information:</h4>
					<input type="text" placeholder="Please specify">

					<button type="submit" class="button">Submit</button>

			</div>





	

	</form>
</div>

</section>

<script src="medical.js"></script>
</body>

</html>
