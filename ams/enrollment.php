<?php
include "config.php";

$db = new Database();

if (isset($_POST['next'])){
    $school_start = $_POST['year_start'];
    $school_end = $_POST['year_end'];
    $grade_level = $_POST['Grade_Level'];
    $with_lrn = $_POST['with_lrn'];
    $returning = $_POST['returning'];
    $PSA = $_POST['PSA_Birth_Certificate_No'];
    $LRN = $_POST['Learner_Reference_No'];
    $LearnerLN = $_POST['Learner_Last_Name'];
    $LearnerFN = $_POST['Learner_First_Name'];
    $LearnerMN = $_POST['Learner_Middle_Name'];
    $LearnerEN = $_POST['Learner_Extension_Name'];
    $birthDate = $_POST['Birth_Date'];
    $sex = $_POST['sex'];
    $place_of_birth = $_POST['Place_of_Birth'];
    $age = $_POST['Age'];
    $mother_tongue = $_POST['Mother_Tongue'];
    $ip = $_POST['ip'];
    $IPspecify = $_POST['IP_Specify'];
    $fourp = $_POST['fourps'];
    $fourps = $_POST['FourPs_Specify'];
    $disability = $_POST['disability'];
    $current_house_number =$_POST['Current_House_No'];
    $current_street_name = $_POST['Current_Street_Name'];
    $current_barangay = $_POST['Current_Barangay'];
    $current_municipality_city = $_POST['Current_Municipality_City'];
    $current_province = $_POST['Current_Province'];
    $current_country = $_POST['Current_Country'];
    $current_zip = $_POST['Current_Zip_Code'];
    $same_address = $_POST['same_address'];
    $permanent_house_no = $_POST['Permanent_House_No'];
    $permanent_street_name = $_POST['Permnanent_Street_Name'];
    $permanent_barangay = $_POST['Permanent_Barangay'];
    $permanent_municipality_city = $_POST['Permanent_Municipality_City'];
    $permanent_country = $_POST['Permanent_Country'];
    $permanent_zip = $_POST['Permanent_Zip_Code'];
    $father_lname= $_POST['Father_Last_Name'];
    $father_fname = $_POST['Father_First_Name'];
    $father_mname = $_POST['Father_Middle_Name'];
    $father_contanct = $_POST['Father_Contact_Number'];
    $mother_lname = $_POST['Mother_Last_Name'];
    $mother_fname = $_POST['Mother_First_Name'];
    $mother_mname = $_POST['Mother_Middle_Name'];
    $mother_contact = $_POST['Mother_Contact_Number'];
    $returning_gLevel = $_POST['Returning_Grade_Level'];
    $last_school_attended = $_POST['Last_School_Attended'];
    $last_school_year_completed = $_POST['Last_School_Year_Completed'];
    $s_id = $_POST['school_ID'];
    $agree = $_POST['agree'];
    
}
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
                <input type="number" name="year_start">

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
                <label><input type="radio" name="with_lrn" value="Yes"> Yes</label>
                <label><input type="radio" name="with_lrn" value="No"> No</label>
                <br><br>

                <span>2. Returning(Babalik?)</span><br>
                <label><input type="radio" name="returning" value="Yes" onchange="returningField()"> Yes</label>
                <label><input type="radio" name="returning" value="No" onchange="returningField()"> No</label>
                <br><br>

                <span>PSA Birth Certificate No.(if available upon registration)</span><br>
                <input type="number" name="PSA_Birth_Certificate_No"><br><br>

                <span>Learner Reference No. (LRN)</span><br>
                <input type="number" name="Learner_Reference_No"><br><br>

                <span>Last Name</span><br>
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
                <label><input type="radio" name="sex" value="Male"> Male</label>
                <label><input type="radio" name="sex" value="Female"> Female</label>
                <br><br>

                <span>Place of Birth</span><br>
                <input type="text" name="Place_of_Birth" placeholder="Municipality/City"><br><br>

                <span>Age</span><br>
                <input type="number" name="Age"><br><br>

                <span>Mother Tongue</span><br>
                <input type="text" name="Mother_Tongue"><br><br>

                <span>Belonging to any Indigenous Group (IP) Community/Indigenous Cultural Community</span><br>
                <label><input type="radio" name="ip" value="Yes" onchange="ipField()"> Yes</label>
                <label><input type="radio" name="ip" value="No" onchange="ipField()"> No</label>
                <br><br>

                <div id="ipDetails" style="display:none;">
                    <span>If Yes, please specify:</span><br>
                    <input type="text" name="IP_Specify">
                </div>
                <br>

                <span>Is your family a beneficiary of 4Ps</span><br>
                <label><input type="radio" name="fourps" value="Yes" onchange="fourPsField()"> Yes</label>
                <label><input type="radio" name="fourps" value="No" onchange="fourPsField()"> No</label>

                <div id="fourPsDetails" style="display:none;">
                    <span>If Yes, write the 4Ps Household ID Number below:</span><br>
                    <input type="number" name="FourPs_Specify">
                </div>
                <br><br>

                <span>Is the child a Learner with Disability</span><br>
                <label><input type="radio" name="disability" value="Yes" onchange="disabilityField()"> Yes</label>
                <label><input type="radio" name="disability" value="No" onchange="disabilityField()"> No</label>
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
                <input type="number" name="Current_House_No"><br><br>

                <span>Street Name</span><br>
                <input type="text" name="Current_Street_Name"><br><br>

                <span>Barangay</span><br>
                <input type="text" name="Current_Barangay"><br><br>

                <span>Municipality / City</span><br>
                <input type="text" name="Current_Municipality_City"><br><br>

                <span>Province</span><br>
                <input type="text" name="Current_Province"><br><br>

                <span>Country</span><br>
                <input type="text" name="Current_Country"><br><br>

                <span>Zip Code</span><br>
                <input type="number" name="Current_Zip_Code"><br><br>

                <hr>
                <h3>Permanent Address</h3>
                <hr><br>

                <span>Same with your Current Address?  </span>
                <label><input type="radio" name="same_address" value="Yes"> Yes</label>
                <label><input type="radio" name="same_address" value="No"> No</label>
                <br><br>

                <span>House No.</span><br>
                <input type="number" name="Permanent_House_No"><br><br>

                <span>Street Name</span><br>
                <input type="text" name="Permanent_Street_Name"><br><br>

                <span>Barangay</span><br>
                <input type="text" name="Permanent_Barangay"><br><br>

                <span>Municipality / City</span><br>
                <input type="text" name="Permanent_Municipality_City"><br><br>

                <span>Province</span><br>
                <input type="text" name="Permanent_Province"><br><br>

                <span>Country</span><br>
                <input type="text" name="Permanent_Country"><br><br>

                <span>Zip Code</span><br>
                <input type="number" name="Permanent_Zip_Code"><br><br>

                <hr>
                <center><h2>PARENT'S/GUARDIAN'S INFORMATION</h2></center>
                <hr>

                <span>Father's Last Name</span><br>
                <input type="text" name="Father_Last_Name"><br><br>

                <span>Father's First Name</span><br>
                <input type="text" name="Father_First_Name"><br><br>

                <span>Father's Middle Name</span><br>
                <input type="text" name="Father_Middle_Name"><br><br>

                <span>Father's Contact Number</span><br>
                <input type="text" name="Father_Contact_Number"><br><br>

                <span>Mother's Last Name</span><br>
                <input type="text" name="Mother_Last_Name"><br><br>

                <span>Mother's First Name</span><br>
                <input type="text" name="Mother_First_Name"><br><br>

                <span>Mother's Middle Name</span><br>
                <input type="text" name="Mother_Middle_Name"><br><br>

                <span>Mother's Contact Number</span><br>
                <input type="text" name="Mother_Contact_Number"><br><br>

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
                    <input type="text" name="Last_School_Attended"><br><br>

                    <span>Last School Year Completed</span><br>
                    <input type="number" name="Last_School_Year_Completed"><br><br>

                    <span>School ID</span><br>
                    <input type="number" name="school_ID"><br><br>
                </div>
            </div>

            <table>
                <tr>
                    <td style="width: 50%">
                        <button type="button" class="button" name="next" onclick="window.location.href='medical.php'">Next</button>
                    </td>
                    <td style="width: 50%">
                        <button type="button" class="button" name="submit" onclick="window.location.href=''">Submit</button>
                    </td>
                </tr>
            </table>
        </form>
    </section>
</div>

<script src="enrollment.js"></script>
</body>
</html>
