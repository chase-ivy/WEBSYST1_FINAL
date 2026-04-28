<!DOCTYPE html>
<html>
<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>Gibraltar AMS</title>
</head>
<body>
<header>
  <h2>Medical Form - AMS</h2>
<!-- Can someone put a pic logo ng school -->
  <img src="logo.png" alt="Logo" class="logo">
</header>
<body>
	<center><h1>Medical Form</h1></center>

<div class="card">
					<nav class="nav-card">
						<a href="index.php" class="select">Change Form Access <</a><br><br>

						<p><strong>Student Module</strong></p>
					</nav>


<section>
	<form>
		<div class="card">
			<span>Name of Learner:</span>
			<input type="text" name="fullName" placeholder="Full Name (Family Name First)">

			<span>Date of Birth:</span>
			<input type="Date" name="DoB">

			<span>Address:</span>
			<input type="text" name="address">

			<span>Age:</span>
			<input type="number" name="age">

			<span>Sex:</span>
			<select style="width: 100%">
				<option disabled selected>Choose</option>
				<option value="Male">Male</option>
				<option value="Female">Female</option>
			</select>
			<hr>

			<span>Name of Parent/Guardian:</span>
			<input type="text" name="">

			<span>Contact Number:</span>
			<input type="tel" name="">
			<hr>
			<strong><p>Instruction: Please put a check (✅) on appropriate items and fill up blanks as indicated.</p></strong>
			<h4>1. Does your child/ward have any allergies?</h4>	

					<select id="field" onchange="showField()" style="width:100%;">
						<option disabled selected>Choose</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					</select>


				<div id="fieldDetails"></div>

			</div>

		</div>

	</form>


</section>

<script src="medical.js"></script>
</body>

</html>
