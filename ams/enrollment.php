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
					<select name="School_Year" style="width: 100%">
						<option value="" hidden>Select School Year</option>
						<option value="School_Year">
							<!-- <php echo shenanigans for the value and display Idk ?> -->
						</option>
					</select><br><br>

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
					
					<table>
						<tr>
							<td>
								<span style="padding-right: 100px;">1. With LRN?</span><br>
								<input type="checkbox" value="Yes_LRN">Yes
								<input type="checkbox" value="No_LRN">No
							</td>
							<td>
								<span>2. Returning(Babalik?)</span><br>
								<input type="checkbox" value="Yes_Returning">Yes
								<input type="checkbox" value="No_Returning">No
							</td>
						</tr>
					</table>
					<br><br>
					
					<span>PSA Birth Certificate No.(if available upon registration)</span><br>
					<input type="number" name="PSA_Birth_Certificate_No"><br><br>
					
					<span>Learner Reference No. (LRN)</span><br>
					<input type="number" name="Learner_Reference_No"><br><br>
				
					<span>(LRN) Last Name:</span><br>
					<input type="text" name="Learner_Last_Name"><br><br>
					
					<span>First Name</span><br>
					<input type="text" name="Learner_First_Name"><br><br>
					
					<span>Middle Name</span><br>
					<input type="text" name="Learner_Middle_Name"><br><br>
					
					<span>Extension Name</span><br>
					<input type="text" name="Learner_Extension_Name"><br><br>

					<span>Birth Date</span><br>
					<input type="date" name="Birth_Date"><br><br>

					<span>Sex</span><br>
					<input type="checkbox" value="Sex_male">Male
					<input type="checkbox" value="Sex_female">Female
					<br><br>

					<span>Place of Birth</span><br>
					<input type="text" name="Place_of_Birth" placeholder="Municipality/City"><br><br>

					<span>Age</span><br>
					<input type="number" name="Age"><br><br>

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

					<span>Is the child a Learner with Disability</span>
					<input type="checkbox" value="Yes_Disability">Yes
					<input type="checkbox" value="No_Disability">No
					<br><br>

					<table>
						<tr>
							<td><input type="checkbox" value="Visual_Impairment"> Visual Impairment</td>
							<td><input type="checkbox" value="Hearing_Impairment"> Hearing Impairment</td>
							<td><input type="checkbox" value="Learning_Disability"> Learning Disability</td>
							<td><input type="checkbox" value="Intellectual_Disability"> Intellectual Disability</td>
						</tr>
						<tr>
							<td><input type="checkbox" value="Blind"> a. blind</td>
							<td><input type="checkbox" value="Autism"> Autism Spectrum Disorder</td>
							<td><input type="checkbox" value="Emotional_Behavioral_Disorder"> Emotional / Behavioral Disorder</td>
							<td><input type="checkbox" value="Orthopedic_Physical_Handicap"> Orthopedic / Physical Handicap</td>
						</tr>
						<tr>
							<td><input type="checkbox" value="Low_Vision"> b. low vision</td>
							<td><input type="checkbox" value="Speech_Language_Disorder">Speech / Language Disorder</td>
							<td><input type="checkbox" value="Cerebral_Palsy">Cerebral Palsy</td>
							<td><input type="checkbox" value="Low_Vision">Special Health Problem / Chronic Disease</td>
						</tr>
						<tr>
							<td><input type="checkbox" value="Multiple_Disorder"> Multiple Disorder</td>
							<td></td>
							<td></td>
							<td><input type="checkbox" value="Cancer"> a. Cancer</td>
						</tr>
					</table><br><br>

	<!---------------------------------------------------------------------------------->

					<hr>
					<h3>Current Address</h3>
					<hr><br>

					<span>House No.</span><br>
					<input type="number" name="Current_House_No"><br><br>
					
					<span>Street Name</span><br>
					<input type="text" name="Current_Street_Name"><br><br>
					
					<span>Barangay</span><br>
					<input type="text" name="Current_Bbarangay"><br><br>
					
					<span>House No.</span><br>
					<input type="number" name="Current_House_No"><br><br>
					
					<span>Municipality / City</span><br>
					<input type="text" name="Current_Municipality_City"><br><br>
					
					<span>Province</span><br>
					<input type="text" name="Current_Province"><br><br>
					
					<span>Country</span><br>
					<input type="text" name="Current_Country"><br><br>
					
					<span>Zip Code</span><br>
					<input type="number" name="Current_Zip_Code"><br><br>

	<!---------------------------------------------------------------------------------->
					
					<hr>
					<h3>Permanent Address</h3>
					<hr><br>

					<span>House No.</span><br>
					<input type="number" name="Permanent_House_No"><br><br>
					
					<span>Street Name</span><br>
					<input type="text" name="Permanent_	Street_Name"><br><br>
					
					<span>Barangay</span><br>
					<input type="text" name="Permanent_Bbarangay"><br><br>
					
					<span>House No.</span><br>
					<input type="number" name="Permanent_House_No"><br><br>
					
					<span>Municipality / City</span><br>
					<input type="text" name="Permanent_Municipality_City"><br><br>
					
					<span>Province</span><br>
					<input type="text" name="Permanent_Province"><br><br>
					
					<span>Country</span><br>
					<input type="text" name="Permanent_Country"><br><br>
					
					<span>Zip Code</span><br>
					<input type="number" name="Permanent_Zip_Code"><br><br>

	<!---------------------------------------------------------------------------------->
					
					<hr>
					<center><h2>PARENT'S/GUARDIAN'S INFORMATION</h2></center>
					<hr>

					<span>Father's Name</span><br>
					<span>Last Name</span><br>
					<input type="text" name="Father_Last_Name"><br><br>
					
					<span>First Name</span><br>
					<input type="text" name="Father_First_Name"><br><br>
					
					<span>Middle Name</span><br>
					<input type="text" name="Father_Middle_Name"><br><br>

					<span>Contact Number</span><br>
					<input type="text" name="Father_Contact_Number"><br><br>
					
					<span>Mother's Name</span><br>
					<span>Last Name</span><br>
					<input type="text" name="Mother_Last_Name"><br><br>
					
					<span>First Name</span><br>
					<input type="text" name="Mother_First_Name"><br><br>
					
					<span>Middle Name</span><br>
					<input type="text" name="Mother_Middle_Name"><br><br>
					
					<span>Contact Number</span><br>
					<input type="text" name="Mother_Contact_Number"><br><br>

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
					<input type="text" name="Last_School_Attended"><br><br>

					<span>Last School Year Completed</span><br>
					<input type="text" name="Last_School_Year_Completed"><br><br>
				</div>



			</form>
</section>
	</div>
<script src="enrollment.js"></script>
</body>
</html>
