<!DOCTYPE html>
<html>
<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>Gibraltar AMS</title>
	<style>
		.pass-card .toggle {
			position: static;
			transform: none;
		}

		input[type="checkbox"]{
			width: auto;
			padding: 0;
			margin-right: 5px;
		}

		input[type="radio"]{
			width: auto;
			padding: 0;
			margin-right: 5px;
		}
	</style>
</head>
<body>
<header>
  <h2>Gibraltar - AMS</h2>
<!-- Can someone put a pic logo ng school -->
  <img src="logo.png" alt="Logo" class="logo">
</header>
<body>
	<center><h1>Enrollment Form</h1></center>

	<div class="card">
		<section>
			<form>
				<div class="card">
					<nav class="nav-card">
						<a href="index.php" class="select">Change Form Access <</a><br><br>

						<p><strong>Enrollment Form</strong></p>
					</nav>
					<hr>
					<center><h2>LEARNER INFORMATION</h2></center>
					<hr>

					<span>School Year</span><br>
					<input style="width: 45%;" type="number" name="year_start">-<input style="width: 45%;" type="number" name="year_end">
					<br><br>

					<span>Grade Level</span><br>
					<select name="Grade_Level" style="width: 100%">
						<option value="" hidden>Select Grade Level</option>
						<option value="Kinder">Kinder</option>
						<option value="Grade 1">Grade 1</option>
						<option value="Grade 2">Grade 2</option>
						<option value="Grade 3">Grade 3</option>
						<option value="Grade 4">Grade 4</option>
						<option value="Grade 5">Grade 5</option>
						<option value="Grade 6">Grade 6</option>
					</select><br><br>

						<span style="padding-right: 100px;">1. With LRN?</span><br>
						<input type="radio" name="with_lrn" value="Yes" onchange=""> Yes
						<input type="radio" name="with_lrn" value="No" onchange=""> No
						<br><br>

						<span>2. Returning(Babalik?)</span><br>
						<input type="radio" name="returning" value="Yes" onchange="returningField()"> Yes
						<input type="radio" name="returning" value="No" onchange="returningField()"> No
						<br><br>
					
					<span>PSA Birth Certificate No.(if available upon registration)</span><br>
					<input type="number" name="birth_certificate_no"><br><br>
					
					<span>Learner Reference No. (LRN)</span><br>
					<input type="number" name="learner_reference_no"><br><br>
				
					<span>(LRN) Last Name:</span><br>
					<input type="text" name="student_last_name"><br><br>
					
					<span>First Name</span><br>
					<input type="text" name="student_first_name"><br><br>
					
					<span>Middle Name</span><br>
					<input type="text" name="student_middle_name"><br><br>
					
					<span>Extension Name</span><br>
					<input type="text" name="student_extension_name"><br><br>

					<span>Birth Date</span><br>
					<input type="date" name="birth_date"><br><br>

					<span>Sex</span><br>
					<input type="radio" name="sex" value="Male" onchange=""> Male
					<input type="radio" name="sex" value="Female" onchange=""> Female
					<br><br>

					<span>Place of Birth</span><br>
					<input type="text" name="Place_of_Birth" placeholder="Municipality/City"><br><br>

					<span>Age</span><br>
					<input type="number" name="age"><br><br>

					<span>Mother Tongue</span><br>
					<input type="text" name="Mother_Tongue"><br><br>
					
					<span>Belonging to any Indigenous Group (IP) Community/Indigenous Cultural Community</span><br>
					<input type="radio" name="ip" value="Yes" onchange="ipField()"> Yes
					<input type="radio" name="ip" value="No" onchange="ipField()"> No
					<br><br>
					
					<div id="ipDetails" style="display:none;">
						<span>If Yes, please specify:</span><br>
						<input type="text" name="IP_Specify">
					</div>
					<br>
					
					<span>Is your family a beneficiary of 4Ps</span><br>
					<input type="radio" name="fourps" value="Yes" onchange="fourPsField()"> Yes
					<input type="radio" name="fourps" value="No" onchange="fourPsField()"> No

					<div id="fourPsDetails" style="display:none;">
						<span>If Yes, write the 4Ps Household ID Number below:</span><br>
						<input type="number" name="FourPs_Specify">
					</div>
					<br><br>

<!---------------------------------------------------------------------------------->

					<span>Is the child a Learner with Disability</span><br>
					<input type="radio" name="disability" value="Yes" onchange="disabilityField()"> Yes
					<input type="radio" name="disability" value="No" onchange="disabilityField()"> No
					<br><br>

					<div id="disabilityDetails" style="display:none;">
						<table>
							<tr>
								<td><input type="checkbox" value="Visual_Impairment"> Visual Impairment</td>
								<td><input type="checkbox" value="Blind"> a. blind</td>
								<td><input type="checkbox" value="Low_Vision"> b. low vision</td>
							</tr>
							<tr>
								<td><input type="checkbox" value="Hearing_Impairment"> Hearing Impairment</td>
								<td><input type="checkbox" value="Autism"> Autism Spectrum Disorder</td>
								<td><input type="checkbox" value="Speech_Language_Disorder">Speech / Language Disorder</td>
							</tr>
							<tr>
								<td><input type="checkbox" value="Learning_Disability"> Learning Disability</td>
								<td><input type="checkbox" value="Emotional_Behavioral_Disorder"> Emotional / Behavioral Disorder</td>
								<td><input type="checkbox" value="Cerebral_Palsy">Cerebral Palsy</td>
							</tr>
							<tr>
								<td><input type="checkbox" value="Intellectual_Disability"> Intellectual Disability</td>
								<td><input type="checkbox" value="Orthopedic_Physical_Handicap"> Orthopedic / Physical Handicap</td>
							</tr>
							<tr>
								<td><input type="checkbox" value="Special_Health_Problem">Special Health Problem / Chronic Disease</td>
								<td><input type="checkbox" value="Cancer"> a. Cancer</td>
								<td><input type="checkbox" value="Multiple_Disorder"> Multiple Disorder</td>
							</tr>
						</table>
					</div>
					<br><br>

<!---------------------------------------------------------------------------------->

					<hr>
					<h3>Current Address</h3>
					<hr><br>

					<span>House No.</span><br>
					<input type="number" name="current_house_no"><br><br>
					
					<span>Street Name</span><br>
					<input type="text" name="current_street_name"><br><br>
					
					<span>Barangay</span><br>
					<input type="text" name="current_barangay"><br><br>
					
					<span>House No.</span><br>
					<input type="number" name="current_house_no"><br><br>
					
					<span>Municipality / City</span><br>
					<input type="text" name="current_municipality_city"><br><br>
					
					<span>Province</span><br>
					<input type="text" name="current_province"><br><br>
					
					<span>Country</span><br>
					<input type="text" name="current_country"><br><br>
					
					<span>Zip Code</span><br>
					<input type="number" name="current_zip_code"><br><br>

	<!---------------------------------------------------------------------------------->
					
					<hr>
					<h3>Permanent Address</h3>
					<hr><br>

					<span>House No.</span><br>
					<input type="number" name="permanent_house_no"><br><br>
					
					<span>Street Name</span><br>
					<input type="text" name="permanent_street_name"><br><br>
					
					<span>Barangay</span><br>
					<input type="text" name="permanent_barangay"><br><br>
					
					<span>House No.</span><br>
					<input type="number" name="permanent_house_no"><br><br>
					
					<span>Municipality / City</span><br>
					<input type="text" name="permanent_municipality_city"><br><br>
					
					<span>Province</span><br>
					<input type="text" name="permanent_province"><br><br>
					
					<span>Country</span><br>
					<input type="text" name="permanent_country"><br><br>
					
					<span>Zip Code</span><br>
					<input type="number" name="permanent_zip_code"><br><br>

	<!---------------------------------------------------------------------------------->
					
					<hr>
					<center><h2>PARENT'S/GUARDIAN'S INFORMATION</h2></center>
					<hr>

					<span>Father's Name</span><br>
					<span>Last Name</span><br>
					<input type="text" name="father_last_name"><br><br>
					
					<span>First Name</span><br>
					<input type="text" name="father_first_name"><br><br>
					
					<span>Middle Name</span><br>
					<input type="text" name="father_middle_name"><br><br>

					<span>Contact Number</span><br>
					<input type="text" name="father_contact_number"><br><br>
					
					<span>Mother's Name</span><br>
					<span>Last Name</span><br>
					<input type="text" name="mother_last_name"><br><br>
					
					<span>First Name</span><br>
					<input type="text" name="mother_first_name"><br><br>
					
					<span>Middle Name</span><br>
					<input type="text" name="mother_middle_name"><br><br>
					
					<span>Contact Number</span><br>
					<input type="text" name="mother_contact_number"><br><br>


					<div id="returningDetails" style="display:none;">
					<hr>
					<center><h2>For Returning Learner (Balik-Aral) And Those Who will Transfer/Move In</h2></center>
					<hr>

					<span>Last Grade Level Completed</span><br>
					<select name="Returning_Grade_Level" style="width: 100%">
						<option value="" hidden>Select Grade Level</option>
						<option value="Kinder">Kinder</option>
						<option value="Grade 1">Grade 1</option>
						<option value="Grade 2">Grade 2</option>
						<option value="Grade 3">Grade 3</option>
						<option value="Grade 4">Grade 4</option>
						<option value="Grade 5">Grade 5</option>
						<option value="Grade 6">Grade 6</option>
					</select><br><br>

					<span>Last School Attended</span><br>
					<input type="text" name="last_school_attended"><br><br>

					<span>Last School Year Completed</span><br>
					<input type="number" name="last_school_year_completed"><br><br>

					<span>School ID</span><br>
					<input type="number" name="school_ID"><br><br>
					</div>
				</div>

			</form>
				<a href="medical.php" class="select"><button type="submit" class="button">Next</button></a>
</section>
	</div>
<script src="enrollment.js"></script>
</body>
</html>
