<?php
// This page submits to config.php which handles the database insert
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Gibraltar AMS</title>
    <style>
        .pass-card .toggle {
            position: static;
            transform: none;
        }
        input[type="checkbox"] {
            width: auto;
            padding: 0;
            margin-right: 5px;
        }
        input[type="radio"] {
            width: auto;
            padding: 0;
            margin-right: 5px;
        }

        /* Declaration Box */
        .declaration-box {
            border: 1.5px solid #1560a8;
            border-radius: 8px;
            margin-top: 30px;
            overflow: hidden;
        }
        .declaration-header {
            background: #1560a8;
            color: white;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
}
        .declaration-header span {
            color: white;   
        }
       .declaration-body {
            padding: 16px;
            font-size: 0.88rem;
            color: #333;
            line-height: 1.7;
            background: white;
}
        .declaration-body ol {
            padding-left: 20px;
            margin: 8px 0;
        }
        .declaration-body ol li {
            margin-bottom: 6px;
        }
        .declaration-body p {
            margin: 0 0 10px;
        }
     .declaration-check {
            background: #f5f5f5;
            border-top: 1px solid #ccc;
            padding: 12px 16px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #333;
}
        .declaration-check label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<header>
    <h2>Gibraltar - AMS</h2>
    <img src="logo.png" alt="Logo" class="logo">
</header>

<center><h1>Enrollment Form</h1></center>

<div class="card">
    <section>
        <form method="POST" action="config.php">
            <div class="card">
                <nav class="nav-card">
                    <a href="index.php" class="select">Change Form Access <</a><br><br>
                    <p><strong>Enrollment Form</strong></p>
                </nav>
                <hr>
                <center><h2>LEARNER INFORMATION</h2></center>
                <hr>

                <span>School Year</span><br>
                <input style="width: 45%;" type="text" name="school_year" placeholder="e.g. 2025-2026"><br><br>

                <span>Grade Level</span><br>
                <select name="grade_level" style="width: 100%">
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
                <label><input type="radio" name="with_lrn" value="Yes"> Yes</label>
                <label><input type="radio" name="with_lrn" value="No"> No</label>
                <br><br>

                <span>2. Returning(Babalik?)</span><br>
                <label><input type="radio" name="returning" value="Yes" onchange="returningField()"> Yes</label>
                <label><input type="radio" name="returning" value="No" onchange="returningField()"> No</label>
                <br><br>

                <span>PSA Birth Certificate No.(if available upon registration)</span><br>
                <input type="number" name="psa_bcn"><br><br>

                <span>LRN (Learner Reference No.)</span><br>
                <input type="number" name="lrn"><br><br>

                <span>Last Name</span><br>
                <input type="text" name="last_name"><br><br>

                <span>First Name</span><br>
                <input type="text" name="first_name"><br><br>

                <span>Middle Name</span><br>
                <input type="text" name="middle_name"><br><br>

                <span>Extension Name</span><br>
                <input type="text" name="extension_name" placeholder="e.g. Jr., Sr., III"><br><br>

                <span>Birth Date</span><br>
                <input type="date" name="birth_date"><br><br>

                <span>Sex</span><br>
                <label><input type="radio" name="sex" value="Male"> Male</label>
                <label><input type="radio" name="sex" value="Female"> Female</label>
                <br><br>

                <span>Place of Birth</span><br>
                <input type="text" name="place_of_birth" placeholder="Municipality/City"><br><br>

                <span>Age</span><br>
                <input type="number" name="age"><br><br>

                <span>Mother Tongue</span><br>
                <input type="text" name="mother_tongue"><br><br>

                <span>Belonging to any Indigenous Group (IP) Community/Indigenous Cultural Community</span><br>
                <label><input type="radio" name="indigenous_group" value="Yes" onchange="ipField()"> Yes</label>
                <label><input type="radio" name="indigenous_group" value="No" onchange="ipField()"> No</label>
                <br><br>

                <div id="ipDetails" style="display:none;">
                    <span>If Yes, please specify:</span><br>
                    <input type="text" name="IP_Specify">
                </div>
                <br>

                <span>Is your family a beneficiary of 4Ps</span><br>
                <label><input type="radio" name="4p_benificiary" value="Yes" onchange="fourPsField()"> Yes</label>
                <label><input type="radio" name="4p_benificiary" value="No" onchange="fourPsField()"> No</label>

                <div id="fourPsDetails" style="display:none;">
                    <span>If Yes, write the 4Ps Household ID Number below:</span><br>
                    <input type="number" name="FourPs_Specify">
                </div>
                <br><br>

                <span>Is the child a Learner with Disability</span><br>
                <label><input type="radio" name="is_learner_with_disability" value="Yes" onchange="disabilityField()"> Yes</label>
                <label><input type="radio" name="is_learner_with_disability" value="No" onchange="disabilityField()"> No</label>
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
                            <td><input type="checkbox" value="Speech_Language_Disorder"> Speech / Language Disorder</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" value="Learning_Disability"> Learning Disability</td>
                            <td><input type="checkbox" value="Emotional_Behavioral_Disorder"> Emotional / Behavioral Disorder</td>
                            <td><input type="checkbox" value="Cerebral_Palsy"> Cerebral Palsy</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" value="Intellectual_Disability"> Intellectual Disability</td>
                            <td><input type="checkbox" value="Orthopedic_Physical_Handicap"> Orthopedic / Physical Handicap</td>
                            <td><input type="checkbox" value="Multiple_Disorder"> Multiple Disabilities</td>
                            
                        </tr>
                        <tr>
                            <td><input type="checkbox" value="Special_Health_Problem"> Special Health Problem / Chronic Disease</td>
                            <td><input type="checkbox" value="Cancer"> a. Cancer</td>
                            <td><input type="checkbox" value="Non_Cancer"> b. Non-Cancer</td>
                        </tr>
                    </table>
                </div>
                <br><br>

                <hr>
                <h3>Current Address</h3>
                <hr><br>

                <span>House No.</span><br>
                <input type="text" name="house_no"><br><br>

                <span>Street Name</span><br>
                <input type="text" name="street_name"><br><br>

                <span>Barangay</span><br>
                <input type="text" name="barangay"><br><br>

                <span>Subdivision</span><br>
                <input type="text" name="subdivision_house_no"><br><br>

                <span>Municipality / City</span><br>
                <input type="text" name="municipality_city"><br><br>

                <span>Province</span><br>
                <input type="text" name="province"><br><br>

                <span>Country</span><br>
                <input type="text" name="country"><br><br>

                <span>Zip Code</span><br>
                <input type="text" name="zip_code"><br><br>

                <hr>
                <h3>Permanent Address</h3>
                <hr><br>

                <span>Same with your Current Address?  </span>
                <label><input type="radio" name="same_address" value="Yes"> Yes</label>
                <label><input type="radio" name="same_address" value="No"> No</label>
                <br><br>

                <span>House No.</span><br>
                <input type="text" name="permanent_house_no"><br><br>

                <span>Street Name</span><br>
                <input type="text" name="permanent_street_name"><br><br>

                <span>Barangay</span><br>
                <input type="text" name="permanent_barangay"><br><br>

                <span>Subdivision</span><br>
                <input type="text" name="permanent_subdivision_house_no"><br><br>

                <span>Municipality / City</span><br>
                <input type="text" name="permanent_municipality_city"><br><br>

                <span>Province</span><br>
                <input type="text" name="permanent_province"><br><br>

                <span>Country</span><br>
                <input type="text" name="permanent_country"><br><br>

                <span>Zip Code</span><br>
                <input type="text" name="permanent_zip_code"><br><br>

                <hr>
                <center><h2>PARENT'S/GUARDIAN'S INFORMATION</h2></center>
                <hr>

                <span>Father's Last Name</span><br>
                <input type="text" name="father_last_name"><br><br>

                <span>Father's First Name</span><br>
                <input type="text" name="father_first_name"><br><br>

                <span>Father's Middle Name</span><br>
                <input type="text" name="father_middle_name"><br><br>

                <span>Father's Contact Number</span><br>
                <input type="text" name="father_contact_number"><br><br>

                <span>Mother's Last Name</span><br>
                <input type="text" name="mother_last_name"><br><br>

                <span>Mother's First Name</span><br>
                <input type="text" name="mother_first_name"><br><br>

                <span>Mother's Middle Name</span><br>
                <input type="text" name="mother_middle_name"><br><br>

                <span>Mother's Contact Number</span><br>
                <input type="text" name="mother_contact_number"><br><br>

                <div id="returningDetails" style="display:none;">
                    <hr>
                    <center><h2>For Returning Learner (Balik-Aral) And Those Who will Transfer/Move In</h2></center>
                    <hr>

                    <span>Last Grade Level Completed</span><br>
                    <select name="last_grade_level_completed" style="width: 100%">
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
                    <input type="text" name="last_school_year_completed"><br><br>

                    <span>School ID</span><br>
                    <input type="number" name="school_ID"><br><br>
                </div>
            </div>

            <button type="submit" class="button" name="next">Next</button>
        </form>
    </section>
</div>

<script src="enrollment.js"></script>
</body>
</html>
