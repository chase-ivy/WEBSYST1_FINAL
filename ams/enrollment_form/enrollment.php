<?php
    include "enroll_config.php";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="./../style/style.css">
    <title>Gibraltar AMS</title>
</head>
<body>

<header>
    <h2>Gibraltar - AMES</h2>
    <img src="./../style/logo.png" alt="Logo" class="logo">
</header>

<center><h1>Enrollment Form</h1></center>

<div class="card">
    <section>
        <form method="post">
            <div class="card">
                <nav class="nav-card">
                    <a href="./../login/index.php" class="select">BACK <</a><br><br>
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
                <label><input type="radio" name="with_lrn" value="1"> Yes</label>
                <label><input type="radio" name="with_lrn" value="0"> No</label>
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
                <input type="date" name="Birth_Date" id="birthDate"><br><br>

                <span>Sex</span><br>
                <label><input type="radio" name="sex" value="Male"> Male</label>
                <label><input type="radio" name="sex" value="Female"> Female</label>
                <br><br>

                <span>Place of Birth</span><br>
                <input type="text" name="Place_of_Birth" placeholder="Municipality/City"><br><br>

                <span>Age</span><br>
                <input type="number" name="Age" id="age"><br><br>

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
                    <input type="text" pattern="[a-zA-0-9]*"name="FourPs_Specify">
                </div>
                <br><br>

                <span>Is the child a Learner with Disability</span><br>
                <label><input type="radio" name="disability" value="Yes" onchange="disabilityField()"> Yes</label>
                <label><input type="radio" name="disability" value="No" onchange="disabilityField()"> No</label>
                <br><br>

                <div id="disabilityDetails" style="display:none;">
                    <table>
                        <tr>
                            <td>
                                <input type="checkbox" id="visual_impairment" name="disabilityDetails[]" value="1"> Visual Impairment
                                
                                <div id="visualOptions" style="display:none; margin-left:15px;">
                                    <input type="checkbox" name="disabilityDetails[]" value="2"> a. blind<br>
                                    <input type="checkbox" name="disabilityDetails[]" value="3"> b. low vision
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" name="disabilityDetails[]" value="4"> Hearing Impairment</td>
                            <td><input type="checkbox" name="disabilityDetails[]" value="5"> Autism Spectrum Disorder</td>
                            <td><input type="checkbox" name="disabilityDetails[]" value="6"> Speech / Language Disorder</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" name="disabilityDetails[]" value="7"> Learning Disability</td>
                            <td><input type="checkbox" name="disabilityDetails[]" value="8"> Emotional / Behavioral Disorder</td>
                            <td><input type="checkbox" name="disabilityDetails[]" value="9"> Cerebral Palsy</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" name="disabilityDetails[]" value="10"> Intellectual Disability</td>
                            <td><input type="checkbox" name="disabilityDetails[]" value="11"> Orthopedic / Physical Handicap</td>
                            <td><input type="checkbox" name="disabilityDetails[]" value="14"> Multiple Disorder</td>
                            
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" id="special_health" name="disabilityDetails[]" value="12"> Special Health Problem
                                
                                <div id="healthOptions" style="display:none; margin-left:15px;">
                                    <input type="checkbox" name="disabilityDetails[]" value="13"> a. Cancer
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <br><br>
// WORKING
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
                <input type="text" name="Current_Country" value="Philippines"><br><br>

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
                    <select name="Last_Grade_Level_Completed" style="width: 100%">
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
                    <input type="text" name="Last_School_Year_Completed" placeholder="e.g., 2020-2021"><br><br>

                    <span>School ID</span><br>
                    <input type="number" name="school_ID"><br><br>
                </div>
            </div>

            <button type="submit" class="button">Submit</button>
        </form>
    </section>
</div>
 

<script>
    document.getElementById('visual_impairment').addEventListener('change', function() {
        document.getElementById('visualOptions').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('special_health').addEventListener('change', function() {
        document.getElementById('healthOptions').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById("birthDate").addEventListener("change", function() {
    const birthDate = new Date(this.value);
    const today = new Date();

    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    document.getElementById("age").value = age;
});
document.addEventListener("DOMContentLoaded", function () {

    const sameYes = document.querySelector('input[name="same_address"][value="Yes"]');
    const sameNo  = document.querySelector('input[name="same_address"][value="No"]');

    const currentFields = {
        house: document.querySelector('[name="Current_House_No"]'),
        street: document.querySelector('[name="Current_Street_Name"]'),
        barangay: document.querySelector('[name="Current_Barangay"]'),
        city: document.querySelector('[name="Current_Municipality_City"]'),
        province: document.querySelector('[name="Current_Province"]'),
        country: document.querySelector('[name="Current_Country"]'),
        zip: document.querySelector('[name="Current_Zip_Code"]')
    };

    const permanentFields = {
        house: document.querySelector('[name="Permanent_House_No"]'),
        street: document.querySelector('[name="Permanent_Street_Name"]'),
        barangay: document.querySelector('[name="Permanent_Barangay"]'),
        city: document.querySelector('[name="Permanent_Municipality_City"]'),
        province: document.querySelector('[name="Permanent_Province"]'),
        country: document.querySelector('[name="Permanent_Country"]'),
        zip: document.querySelector('[name="Permanent_Zip_Code"]')
    };

    function copyAddress() {
        permanentFields.house.value = currentFields.house.value;
        permanentFields.street.value = currentFields.street.value;
        permanentFields.barangay.value = currentFields.barangay.value;
        permanentFields.city.value = currentFields.city.value;
        permanentFields.province.value = currentFields.province.value;
        permanentFields.country.value = currentFields.country.value;
        permanentFields.zip.value = currentFields.zip.value;
    }

    function clearPermanent() {
        for (let key in permanentFields) {
            permanentFields[key].value = '';
        }
    }

    sameYes.addEventListener("change", function () {
        if (this.checked) {
            copyAddress();
        }
    });

    sameNo.addEventListener("change", function () {
        if (this.checked) {
            clearPermanent();
        }
    });

    Object.values(currentFields).forEach(field => {
        field.addEventListener("input", function () {
            if (sameYes.checked) {
                copyAddress();
            }
        });
    });

});
</script>

<script src="enrollment.js"></script> 
</body>
</html>


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