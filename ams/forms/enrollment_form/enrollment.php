<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Form · Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="enrollment.css" rel="stylesheet">
</head>
<body>

    <!-- TOP NAV -->
    <div class="topbar">
        <div class="topbar-logo">
            <img src="../../style/logo.png" alt="Logo">
            <span>Gibraltar Elementary School</span>
        </div>
        <a href="../../dashboard/teacher_dashboard/teacher_dashboard.php">← Back to Dashboard</a>
    </div>

    <!-- PROGRESS STEPPER -->
    <div class="stepper" id="stepper">
        <div class="step active" id="s1">
            <div class="step-dot">1</div>
            <span class="step-label">Learner Info</span>
        </div>
        <div class="step" id="s2">
            <div class="step-dot">2</div>
            <span class="step-label">Address</span>
        </div>
        <div class="step" id="s3">
            <div class="step-dot">3</div>
            <span class="step-label">Parents</span>
        </div>
        <div class="step" id="s4">
            <div class="step-dot">4</div>
            <span class="step-label">Medical Form</span>
        </div>
        <div class="step" id="s5">
            <div class="step-dot">5</div>
            <span class="step-label">Review</span>
        </div>
    </div>

    <div class="wrap">
        <div id="formMessage" class="message" aria-live="polite"></div>
        <form id="enrollmentForm" novalidate>

        <!-- ═══════════════════════════════════════════════════════
             STEP 1 — LEARNER INFORMATION
        ════════════════════════════════════════════════════════ -->
        <div class="panel active" id="panel-1">
            <div class="card">

                <div class="card-head">
                    <h2>Learner Information</h2>
                    <p>Basic details about the student being enrolled</p>
                </div>

                <div class="card-body">

                    <!-- School Year -->
                    <div class="grid-2">
                        <div class="field">
                            <label>School Year Start</label>
                            <input type="number" name="year_start" placeholder="2025" min="2000" max="2099">
                        </div>
                        <div class="field">
                            <label>School Year End</label>
                            <input type="number" name="year_end" placeholder="2026" min="2000" max="2099">
                        </div>
                    </div>

                    <div class="sec-divider"><span>Enrollment Details</span></div>

                    <!-- Grade Level & LRN -->
                    <div class="grid-2">
                        <div class="field">
                            <label>Grade Level</label>
                            <select name="Grade_Level">
                                <option value="" hidden>Select grade</option>
                                <option value="Kinder">Kinder</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>LRN (if available)</label>
                            <input type="number" name="Learner_Reference_No" placeholder="12-digit LRN">
                        </div>
                    </div>

                    <!-- With LRN / Returning -->
                    <div class="grid-2" style="margin-top:16px;">
                        <div class="field">
                            <label>With LRN?</label>
                            <div class="radio-group">
                                <label class="radio-pill">
                                    <input type="radio" name="with_lrn" value="1" required> Yes
                                </label>
                                <label class="radio-pill">
                                    <input type="radio" name="with_lrn" value="0"> No
                                </label>
                            </div>
                        </div>
                        <div class="field">
                            <label>Returning Learner?</label>
                            <div class="radio-group">
                                <label class="radio-pill">
                                    <input type="radio" name="returning" value="1" onchange="toggle('returningBox', true)"> Yes
                                </label>
                                <label class="radio-pill">
                                    <input type="radio" name="returning" value="0"  onchange="toggle('returningBox', false)"> No
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Returning Learner (collapsible) -->
                    <div class="collapse" id="returningBox">
                        <div class="sec-divider"><span>Returning / Transfer Learner</span></div>
                        <div class="grid-2">
                            <div class="field">
                                <label>Last Grade Level Completed</label>
                                <select name="Returning_Grade_Level">
                                    <option value="" hidden>Select</option>
                                    <option value="Kinder">Kinder</option>
                                    <option value="Grade 1">Grade 1</option>
                                    <option value="Grade 2">Grade 2</option>
                                    <option value="Grade 3">Grade 3</option>
                                    <option value="Grade 4">Grade 4</option>
                                    <option value="Grade 5">Grade 5</option>
                                    <option value="Grade 6">Grade 6</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Last School Year Completed</label>
                                <input type="number" name="Last_School_Year_Completed" placeholder="e.g. 2024">
                            </div>
                            <div class="field span-2">
                                <label>Last School Attended</label>
                                <input type="text" name="Last_School_Attended" placeholder="School name">
                            </div>
                            <div class="field span-2">
                                <label>School ID</label>
                                <input type="text" name="school_ID" placeholder="School ID">
                            </div>
                        </div>
                    </div>

                    <div class="sec-divider"><span>Personal Information</span></div>

                    <!-- PSA -->
                    <div class="field">
                        <label>PSA Birth Certificate No. (if available)</label>
                        <input type="text" name="psa_bcn" placeholder="PSA number">
                    </div>

                    <!-- Name -->
                    <div class="grid-3" style="margin-top:16px;">
                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" name="Learner_Last_Name" placeholder="Dela Cruz">
                        </div>
                        <div class="field">
                            <label>First Name</label>
                            <input type="text" name="Learner_First_Name" placeholder="Juan">
                        </div>
                        <div class="field">
                            <label>Middle Name</label>
                            <input type="text" name="Learner_Middle_Name" placeholder="Santos">
                        </div>
                    </div>

                    <!-- Extension / Birth Date / Age -->
                    <div class="grid-3" style="margin-top:16px;">
                        <div class="field">
                            <label>Extension (Jr./III)</label>
                            <input type="text" name="Learner_Extension_Name" placeholder="Jr.">
                        </div>
                        <div class="field">
                            <label>Birth Date</label>
                            <input type="date" name="Birth_Date" id="birthDate">
                        </div>
                        <div class="field">
                            <label>Age</label>
                            <input type="number" name="Age" min="3" max="20" placeholder="6" id="ageField" readonly>
                        </div>
                    </div>

                    <!-- Sex / Place of Birth -->
                    <div class="grid-2" style="margin-top:16px;">
                        <div class="field">
                            <label>Sex</label>
                            <div class="radio-group">
                                <label class="radio-pill">
                                    <input type="radio" name="sex" value="Male"> Male
                                </label>
                                <label class="radio-pill">
                                    <input type="radio" name="sex" value="Female"> Female
                                </label>
                            </div>
                        </div>
                        <div class="field">
                            <label>Place of Birth</label>
                            <input type="text" name="Place_of_Birth" placeholder="Municipality / City">
                        </div>
                    </div>

                    <!-- Mother Tongue -->
                    <div class="grid-2" style="margin-top:16px;">
                        <div class="field">
                            <label>Mother Tongue</label>
                            <select name="Mother_Tongue" id="Mother_Tongue" onchange="toggleMotherTongueOther()">
                                <option value="" hidden>Select mother tongue</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" name="Mother_Tongue_Other" id="Mother_Tongue_Other" placeholder="Specify other mother tongue" style="display:none; margin-top:10px; padding:10px 13px; border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:'DM Sans',sans-serif; font-size:14px; color:var(--text); background:var(--canvas);">
                        </div>
                    </div>

                    <div class="sec-divider"><span>Additional Classification</span></div>

                    <!-- IP / 4Ps -->
                    <div class="grid-2">
                        <div class="field">
                            <label>Indigenous People (IP)?</label>
                            <div class="radio-group">
                                <label class="radio-pill">
                                    <input type="radio" name="ip" value="Yes" onchange="toggle('ipBox', true)"> Yes
                                </label>
                                <label class="radio-pill">
                                    <input type="radio" name="ip" value="No"  onchange="toggle('ipBox', false)"> No
                                </label>
                            </div>
                        </div>
                        <div class="field">
                            <label>4Ps Beneficiary?</label>
                            <div class="radio-group">
                                <label class="radio-pill">
                                    <input type="radio" name="fourps" value="Yes" onchange="toggle('fourpsBox', true)"> Yes
                                </label>
                                <label class="radio-pill">
                                    <input type="radio" name="fourps" value="No"  onchange="toggle('fourpsBox', false)"> No
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- IP Specify (collapsible) -->
                    <div class="collapse" id="ipBox" style="margin-top:10px;">
                        <div class="field">
                            <label>IP Community / Cultural Group</label>
                            <select name="IP_Group" id="IP_Group" onchange="toggleIpOther()">
                                <option value="" hidden>Select IP group</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" name="IP_Specify" id="IP_Specify" placeholder="Specify other IP group" style="display:none; margin-top:10px; padding:10px 13px; border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:'DM Sans',sans-serif; font-size:14px; color:var(--text); background:var(--canvas);">
                        </div>
                    </div>

                    <!-- 4Ps Specify (collapsible) -->
                    <div class="collapse" id="fourpsBox" style="margin-top:10px;">
                        <div class="field">
                            <label>4Ps Household ID Number</label>
                            <input type="number" name="FourPs_Specify" placeholder="Household ID">
                        </div>
                    </div>

                    <!-- Disability -->
                    <div class="field" style="margin-top:16px;">
                        <label>Learner with Disability?</label>
                        <div class="radio-group">
                            <label class="radio-pill">
                                <input type="radio" name="disability" value="Yes"  onchange="toggle('disabilityBox', true)"> Yes
                            </label>
                            <label class="radio-pill">
                                <input type="radio" name="disability" value="No"  onchange="toggle('disabilityBox', false)"> No
                            </label>
                        </div>
                    </div>

                    <!-- Disability Types (collapsible) -->
                    <!-- IDs match disability_types table (DB):
                         1=Visual Impairment, 2=Hearing Impairment, 3=Learning Disability,
                         4=Intellectual Disability, 5=Autism Spectrum Disorder,
                         6=Emotional/Behavioral Disorder, 7=Orthopedic/Physical Handicap,
                         8=Speech/Language Disorder, 9=Chronic Illness, 10=Others
                         Subtypes (disability_subtypes): 1=Blind, 2=Low Vision (type 1 only) -->
        <div class="collapse" id="disabilityBox">
                        <div class="disability-grid" style="margin-top:10px;">
                            <label class="check-item">
                                <input type="checkbox" id="visual_impairment" name="disabilityDetails[1][]" value="1"> Visual Impairment

                                <div id="visualOptionsBox" style="display:none; margin-left:15px;">
                                    <input type="checkbox" name="disability_sub[1][]" value="1"> Blind<br>
                                    <input type="checkbox" name="disability_sub[1][]" value="2"> Low Vision
                                </div>
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[2][]" value="2"> Hearing Impairment
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[3][]" value="3"> Learning Disability
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[4][]" value="4"> Intellectual Disability
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[5][]" value="5"> Autism Spectrum Disorder
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[6][]" value="6"> Emotional / Behavioral Disorder
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[7][]" value="7"> Orthopedic / Physical Handicap
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[8][]" value="8"> Speech / Language Disorder
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[9][]" value="9"> Chronic Illness
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disabilityDetails[10][]" value="10"> Others (Multiple Disorder, Cerebral Palsy, etc.)
                            </label>
                        </div>
                    </div>

                    <div class="sec-divider"><span>User Account (Optional)</span></div>

                    <div style="background: var(--brand-light); border: 1px solid #b3cce8; border-radius: var(--radius-md); padding: 14px 16px; font-size: 12px; color: #1560a8; margin-bottom: 16px; line-height: 1.6;">
                        <strong>Account Information:</strong> Leave blank to auto-generate credentials. You can update these later through admin/teacher dashboard. A user account will be created regardless to ensure login access.
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label>Email Address (Optional)</label>
                            <input type="email" name="user_email" placeholder="student@example.com">
                        </div>
                        <div class="field">
                            <label>Password (Optional)</label>
                            <input type="password" name="user_password" placeholder="Leave blank for auto-generated">
                        </div>
                    </div>

                </div><!-- /.card-body -->

                <div class="card-foot">
                    <span class="step-count">Step 1 of 5</span>
                    <button type="button" class="btn btn-primary" onclick="goTo(2)">Next: Address →</button>
                </div>

            </div><!-- /.card -->
        </div><!-- /#panel-1 -->


        <!-- ═══════════════════════════════════════════════════════
             STEP 2 — ADDRESS INFORMATION
        ════════════════════════════════════════════════════════ -->
        <div class="panel" id="panel-2">
            <div class="card">

                <div class="card-head">
                    <h2>Address Information</h2>
                    <p>Current and permanent address of the learner</p>
                </div>

                <div class="card-body">

                    <div class="sec-divider"><span>Current Address</span></div>

                    <div class="grid-2">
                        <div class="field">
                            <label>House No.</label>
                            <input type="number" name="Current_House_No" placeholder="123">
                        </div>
                        <div class="field">
                            <label>Street Name</label>
                            <input type="text" name="Current_Street_Name" placeholder="Rizal St.">
                        </div>
                        <div class="field">
                            <label>Barangay</label>
                            <input type="text" name="Current_Barangay" placeholder="Brgy. Name">
                        </div>
                        <div class="field">
                            <label>Municipality / City</label>
                            <input type="text" name="Current_Municipality_City" placeholder="Baguio City">
                        </div>
                        <div class="field">
                            <label>Province</label>
                            <input type="text" name="Current_Province" placeholder="Benguet">
                        </div>
                        <div class="field">
                            <label>Country</label>
                            <input type="text" name="Current_Country" placeholder="Philippines">
                        </div>
                        <div class="field">
                            <label>Zip Code</label>
                            <input type="number" name="Current_Zip_Code" placeholder="2600">
                        </div>
                    </div>

                    <div class="sec-divider"><span>Permanent Address</span></div>

                    <!-- Same as current toggle -->
                    <div class="field" style="margin-bottom:14px;">
                        <label>Same as Current Address?</label>
                        <div class="radio-group">
                            <label class="radio-pill">
                                <input type="radio" name="same_address" value="Yes" onchange="sameAddr(true)"> Yes
                            </label>
                            <label class="radio-pill">
                                <input type="radio" name="same_address" value="No"  onchange="sameAddr(false)"> No
                            </label>
                        </div>
                    </div>

                    <div id="permBox">
                        <div class="grid-2">
                            <div class="field">
                                <label>House No.</label>
                                <input type="number" name="Permanent_House_No" placeholder="123">
                            </div>
                            <div class="field">
                                <label>Street Name</label>
                                <input type="text" name="Permanent_Street_Name" placeholder="Rizal St.">
                            </div>
                            <div class="field">
                                <label>Barangay</label>
                                <input type="text" name="Permanent_Barangay" placeholder="Brgy. Name">
                            </div>
                            <div class="field">
                                <label>Municipality / City</label>
                                <input type="text" name="Permanent_Municipality_City" placeholder="Baguio City">
                            </div>
                            <div class="field">
                                <label>Province</label>
                                <input type="text" name="Permanent_Province" placeholder="Benguet">
                            </div>
                            <div class="field">
                                <label>Country</label>
                                <input type="text" name="Permanent_Country" placeholder="Philippines">
                            </div>
                            <div class="field">
                                <label>Zip Code</label>
                                <input type="number" name="Permanent_Zip_Code" placeholder="2600">
                            </div>
                        </div>
                    </div><!-- /#permBox -->

                </div><!-- /.card-body -->

                <div class="card-foot">
                    <button type="button" class="btn btn-ghost"   onclick="goTo(1)">← Back</button>
                    <span class="step-count">Step 2 of 5</span>
                    <button type="button" class="btn btn-primary" onclick="goTo(3)">Next: Parents →</button>
                </div>

            </div><!-- /.card -->
        </div><!-- /#panel-2 -->


        <!-- ═══════════════════════════════════════════════════════
             STEP 3 — PARENT / GUARDIAN INFORMATION
        ════════════════════════════════════════════════════════ -->
        <div class="panel" id="panel-3">
            <div class="card">

                <div class="card-head">
                    <h2>Parent / Guardian Information</h2>
                    <p>Contact details of the learner's parents or guardians</p>
                </div>

                <div class="card-body">

                    <div class="sec-divider"><span>Father's Information</span></div>

                    <div class="grid-3">
                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" name="father_last_name" placeholder="Dela Cruz">
                        </div>
                        <div class="field">
                            <label>First Name</label>
                            <input type="text" name="father_first_name" placeholder="Juan">
                        </div>
                        <div class="field">
                            <label>Middle Name</label>
                            <input type="text" name="father_middle_name" placeholder="Santos">
                        </div>
                    </div>

                    <div class="field" style="margin-top:14px; max-width:300px;">
                        <label>Contact Number</label>
                        <input type="text" name="father_contact_number" placeholder="09XX XXX XXXX">
                    </div>

                    <div class="sec-divider"><span>Mother's Information</span></div>

                    <div class="grid-3">
                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" name="mother_last_name" placeholder="Dela Cruz">
                        </div>
                        <div class="field">
                            <label>First Name</label>
                            <input type="text" name="mother_first_name" placeholder="Maria">
                        </div>
                        <div class="field">
                            <label>Middle Name</label>
                            <input type="text" name="mother_middle_name" placeholder="Reyes">
                        </div>
                    </div>

                    <div class="field" style="margin-top:14px; max-width:300px;">
                        <label>Contact Number</label>
                        <input type="text" name="mother_contact_number" placeholder="09XX XXX XXXX">
                    </div>

                    <div class="sec-divider"><span>Guardian's Information</span></div>

                    <div class="grid-3">
                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" name="guardian_last_name" placeholder="Dela Cruz">
                        </div>
                        <div class="field">
                            <label>First Name</label>
                            <input type="text" name="guardian_first_name" placeholder="Maria">
                        </div>
                        <div class="field">
                            <label>Middle Name</label>
                            <input type="text" name="guardian_middle_name" placeholder="Reyes">
                        </div>
                    </div>

                    <div class="field" style="margin-top:14px; max-width:300px;">
                        <label>Contact Number</label>
                        <input type="text" name="guardian_contact_number" placeholder="09XX XXX XXXX">
                    </div>

                </div><!-- /.card-body -->

                <div class="card-foot">
                    <button type="button" class="btn btn-ghost"   onclick="goTo(2)">← Back</button>
                    <span class="step-count">Step 3 of 5</span>
                    <button type="button" class="btn btn-primary" onclick="goTo(4)"> Next: Medical →</button>
                </div>

            </div><!-- /.card -->
        </div><!-- /#panel-3 -->


        <!-- ═══════════════════════════════════════════════════════
             STEP 4 — MEDICAL INFORMATION   
        ════════════════════════════════════════════════════════ -->
        <div class="panel" id="panel-4">
            <div class="card">

                <div class="card-head">
                    <h2>Medical Form</h2>
                    <p>Student medical information</p>
                </div>

                <div class="card-body">

                    <div style="background:var(--brand-light); border:1px solid #b3cce8; border-radius:var(--radius-md); padding:12px 14px; font-size:12px; color:#1560a8; margin-bottom:20px; line-height:1.5;">
                        <strong>Instructions:</strong> Please select the appropriate responses and provide details where indicated.
                    </div>

                    <div class="field">
                        <label>1. Does your child/ward have any allergies?</label>
                        <select id="field" onchange="showField()" name="has_allergies">
                            <option value="" hidden>Choose</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <div id="fieldDetails" style="margin-top:12px;"></div>
                    </div>

                    <div class="field" style="margin-top:18px;">
                        <label>2. Does your child/ward have any ongoing medical condition?</label>
                        <select id="Q2" onchange="showQ2()" name="has_med_condition">
                            <option value="" hidden>Choose</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <div id="q2" style="margin-top:12px;"></div>
                    </div>

                    <div class="field" style="margin-top:18px;">
                        <label>3. Did your child/ward ever have surgery / hospitalization?</label>
                        <select id="Q3" onchange="showQ3()" name="has_surgery_hospitalization">
                            <option value="" hidden>Choose</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <div id="q3" style="margin-top:12px;"></div>
                    </div>

                    <div class="field" style="margin-top:18px;">
                        <label>4. Is your child currently taking treatment / medicines?</label>
                        <select id="Q4" onchange="showQ4()" name="is_taking_treatment">
                            <option value="" hidden>Choose</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <div id="q4" style="margin-top:12px;"></div>
                    </div>

                    <div class="field" style="margin-top:18px;">
                        <label>5. Does your family have a history of medical conditions?</label>
                        <select id="Q5" onchange="showQ5()" name="family_medical_history">
                            <option value="" hidden>Choose</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <div id="q5" style="margin-top:12px;"></div>
                    </div>

                    <div class="field" style="margin-top:18px;">
                        <label>6. Does your child/ward have exposure to cigarette/vape smoke at home?</label>
                        <select name="exposed_to_cigarette_vape_smoke">
                            <option value="" hidden>Choose</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="field" style="margin-top:18px;">
                        <label>7. Other pertinent learner information:</label>
                        <input type="text" name="other_pertinent_information" placeholder="Optional">
                    </div>

                </div>

                <div class="card-foot">
                    <button type="button" class="btn btn-ghost"  onclick="goTo(3)">← Back</button>
                    <span class="step-count">Step 4 of 5</span>
                    <button type="button" class="btn btn-primary" onclick="goTo(5)">Next: Agreement →</button>
                </div>

            </div>
        </div>


               <!-- ═══════════════════════════════════════════════════════
             STEP 5 — REVIEW AND SUBMIT
        ════════════════════════════════════════════════════════ -->
        <div class="panel" id="panel-5">
            <div class="card">

                <div class="card-head">
                    <h2>Review and Submit</h2>
                    <p>Please confirm the information before submitting</p>
                </div>

                <div class="card-body">

                    <!-- Info notice -->
                    <div style="background:var(--brand-light); border:1px solid #b3cce8; border-radius:var(--radius-md); padding:16px 18px; font-size:13px; color:#1560a8; line-height:1.65; margin-bottom:20px; display:flex; gap:10px; align-items:flex-start;">
                        <svg style="width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; flex-shrink:0; margin-top:1px;" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8"  x2="12"    y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>By submitting this form, you confirm that all information provided is accurate and complete to the best of your knowledge.</span>
                    </div>

                    <!-- Declaration box -->
                    <div style="border:1.5px solid var(--brand); border-radius:var(--radius-md); overflow:hidden;">

                        <div style="background:var(--brand); color:#fff; padding:10px 16px; font-size:13px; font-weight:600;">
                            Declaration of Parents / Guardians
                        </div>

                        <div style="padding:16px; font-size:13px; color:#333; line-height:1.7; background:#fff;">
                            <p>I hereby certify that the information provided in this enrollment form is true and correct. I understand that providing false information may result in the cancellation of the enrollment.</p>
                        </div>

                        <div style="background:#f5f5f5; border-top:1px solid var(--border); padding:12px 16px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer;">
                                <input type="checkbox" name="declaration" required style="width:15px; height:15px; accent-color:var(--brand);">
                                I agree to the above declaration
                            </label>
                        </div>

                    </div><!-- /.declaration box -->

                </div><!-- /.card-body -->

                <div class="card-foot">
                    <button type="button" class="btn btn-ghost"  onclick="goTo(4)">← Back</button>
                    <span class="step-count">Step 5 of 5</span>
                    <button type="submit" name="next" class="btn btn-primary">Submit Enrollment</button>
                </div>

            </div><!-- /.card -->
        </div><!-- /#panel-5 -->
        
    </form>
    
    <!-- CONFIRMATION MODAL -->
    <div id="confirmationModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:12px; padding:32px; max-width:500px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h2 style="margin-bottom:12px; font-size:20px; font-weight:700; color:var(--text);">Verify Enrollment Information</h2>
            <p style="margin-bottom:24px; font-size:14px; color:var(--muted);">Please review the information below before submitting your enrollment.</p>
            
            <div id="confirmationSummary" style="background:var(--canvas); border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:24px; max-height:300px; overflow-y:auto; font-size:13px;">
            </div>
            
            <div style="display:flex; gap:8px;">
                <button type="button" onclick="cancelConfirmation()" style="flex:1; padding:10px 16px; background:var(--canvas); color:var(--text); border:1px solid var(--border); border-radius:6px; font-weight:600; cursor:pointer; transition:background var(--transition);" onmouseover="this.style.background='#efefef'" onmouseout="this.style.background='var(--canvas)'">Cancel</button>
                <button type="button" onclick="confirmSubmission()" style="flex:1; padding:10px 16px; background:var(--brand); color:white; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition:background var(--transition);" onmouseover="this.style.background='var(--brand-dark)'" onmouseout="this.style.background='var(--brand)'">Confirm & Submit</button>
            </div>
        </div>
    </div>
</div><!-- /.wrap -->

    <footer>
        &copy; 2025 <strong>Gibraltar Elementary School — AMES</strong>. All rights reserved.
    </footer>

    <script src="../../api/client.js"></script>
<script>
function showField(){
	const field = document.getElementById("field").value;
	const card = document.getElementById("fieldDetails");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
<div style="background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius-md); padding:14px 16px; display:flex; flex-direction:column; gap:12px;">

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="medicine_allergy_checkbox" name="medicine_allergy[]" value="1" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Medicine
    </label>
    <div id="medicineAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[1]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="pollen_allergy_checkbox" name="medicine_allergy[]" value="2" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Pollen
    </label>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="food_allergy_checkbox" name="medicine_allergy[]" value="3" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Food
    </label>
    <div id="foodAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[3]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="other_allergy_checkbox" name="medicine_allergy[]" value="4" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Others
    </label>
    <div id="otherAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[4]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

</div>
`;

		const attachToggle = (checkboxId, boxId) => {
			const checkbox = document.getElementById(checkboxId);
			const box = document.getElementById(boxId);
			if (!checkbox || !box) return;
			checkbox.addEventListener('change', () => {
				box.style.display = checkbox.checked ? 'block' : 'none';
			});
			box.style.display = checkbox.checked ? 'block' : 'none';
		};

		attachToggle('medicine_allergy_checkbox', 'medicineAllergyBox');
		attachToggle('food_allergy_checkbox', 'foodAllergyBox');
		attachToggle('other_allergy_checkbox', 'otherAllergyBox');
	}
}

function showQ2(){
	const field = document.getElementById("Q2").value;
	const card = document.getElementById("q2");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="1" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Error of refraction (Eye Ailment)</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="2" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Asthma (Lung Ailment)</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="3" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Seizure (Convulsions)</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="4" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Heart Illness</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="5" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Anemia</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="6" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Bleeding disorder</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="7" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Fracture / Dislocation</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease; grid-column:span 2;">
				<input type="checkbox" id="has_medical_condition" name="condition_type_id" value="8" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Others</span>
			</label>
			<div id="medical_condition_details" style="display:none; margin-top:8px; grid-column:span 2;">
				<input type="text" name="condition_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
			</div>
		</div>
		`;
	}
    // Medical Condition Others
	const medicalCheckbox = document.getElementById('has_medical_condition');
	const medicalBox = document.getElementById('medical_condition_details');
	if (medicalCheckbox && medicalBox) {
		medicalCheckbox.addEventListener('change', function () {
			medicalBox.style.display = this.checked ? 'block' : 'none';
		});
		medicalBox.style.display = medicalCheckbox.checked ? 'block' : 'none';
	}
}

function showQ3(){
	const field = document.getElementById("Q3").value;
	const card = document.getElementById("q3");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:flex; flex-direction:column; gap:10px;">
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Surgery Date</label>
				<input type="date" name="surgery_date" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Hospital Name</label>
				<input type="text" name="hospital_name" placeholder="Hospital name" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Body Part Affected</label>
				<input type="text" name="body_part" placeholder="What part of the body?" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
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
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:flex; flex-direction:column; gap:10px;">
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Medicine / Treatment Type</label>
				<input type="text" name="treatment_medicine" placeholder="Name of medicine or treatment" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Dosage Schedule</label>
				<input type="text" name="schedule_dosage" placeholder="e.g., 2x daily, morning/evening" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
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
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="1" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Tuberculosis</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" id="has_cancer" name="family_condition_type_id" value="2" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Cancer</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="3" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Diabetes Mellitus</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="4" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Hypertension</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="5" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Stroke / Heart attack</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="6" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Depression</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="7" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Kidney problems</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease; grid-column:span 2;">
				<input type="checkbox" id="has_other" name="family_condition_type_id" value="8" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Others</span>
			</label>
			<div id="otherBox" style="display:none; margin-top:8px; grid-column:span 2;">
				<input type="text" name="family_condition_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
			</div>
			<div id="cancerBox" style="display:none; margin-top:8px; grid-column:1;">
				<input type="text" name="family_condition_description" placeholder="Specify type of cancer" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
			</div>
		</div>
		`;
	}

	// Family History - Cancer
	const cancerCheckbox = document.getElementById('has_cancer');
	const cancerBox = document.getElementById('cancerBox');
	if (cancerCheckbox && cancerBox) {
		cancerCheckbox.addEventListener('change', function () {
			cancerBox.style.display = this.checked ? 'block' : 'none';
		});
		cancerBox.style.display = cancerCheckbox.checked ? 'block' : 'none';
	}

	// Family History - Others
	const otherCheckbox = document.getElementById('has_other');
	const otherBox = document.getElementById('otherBox');
	if (otherCheckbox && otherBox) {
		otherCheckbox.addEventListener('change', function () {
			otherBox.style.display = this.checked ? 'block' : 'none';
		});
		otherBox.style.display = otherCheckbox.checked ? 'block' : 'none';
	}
}


</script>
    <script>
    document.getElementById('visual_impairment').addEventListener('change', function() {
        document.getElementById('visualOptionsBox').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('special_health').addEventListener('change', function() {
        document.getElementById('healthOptionsBox').style.display = this.checked ? 'block' : 'none';
    });
    </script>
    <script>
        let current = 1;

        function goTo(n) {
            document.getElementById('panel-' + current).classList.remove('active');
            document.getElementById('s' + current).classList.remove('active');
            document.getElementById('s' + current).classList.add('done');

            current = n;

            document.getElementById('panel-' + n).classList.add('active');
            document.querySelectorAll('#stepper .step').forEach((s, i) => {
                if (i + 1 <  n) { s.classList.add('done');    s.classList.remove('active'); }
                if (i + 1 === n) { s.classList.add('active');  s.classList.remove('done');   }
                if (i + 1 >  n) { s.classList.remove('done', 'active'); }
            });

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function toggle(id, open) {
            document.getElementById(id).classList.toggle('open', open);
        }

        function toggleMotherTongueOther() {
            const select = document.getElementById('Mother_Tongue');
            const other  = document.getElementById('Mother_Tongue_Other');
            if (!select || !other) return;
            if (select.value === 'Other') {
                other.style.display = 'block';
            } else {
                other.style.display = 'none';
                other.value = '';
            }
        }

        function toggleIpOther() {
            const select = document.getElementById('IP_Group');
            const other  = document.getElementById('IP_Specify');
            if (!select || !other) return;
            if (select.value === 'Other') {
                other.style.display = 'block';
            } else {
                other.style.display = 'none';
                other.value = '';
            }
        }

        async function loadEnrollmentLookups() {
            const motherTongueSelect = document.getElementById('Mother_Tongue');
            const ipGroupSelect = document.getElementById('IP_Group');

            if (!motherTongueSelect || !ipGroupSelect || !API?.lookups) {
                return;
            }

            try {
                const response = await API.lookups.listAll();
                const motherTongues = response.data?.motherTongues || [];
                const indigenousGroups = response.data?.indigenousGroups || [];

                populateLookupSelect(motherTongueSelect, motherTongues);
                populateLookupSelect(ipGroupSelect, indigenousGroups);
            } catch (error) {
                console.error('Failed to load lookup values:', error);
            }
        }

        function populateLookupSelect(select, values) {
            const otherOption = Array.from(select.options).find(option => option.value === 'Other');
            select.querySelectorAll('option').forEach(option => {
                if (option.value !== '' && option.value !== 'Other') {
                    option.remove();
                }
            });

            values.forEach(value => {
                if (!value) return;
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                select.insertBefore(option, otherOption || null);
            });
        }

        function setAutoSchoolYear() {
            const startInput = document.querySelector('input[name="year_start"]');
            const endInput = document.querySelector('input[name="year_end"]');
            if (!startInput || !endInput) {
                return;
            }

            const now = new Date();
            const month = now.getMonth();
            const year = now.getFullYear();
            const startYear = month >= 5 ? year : year - 1;
            const endYear = startYear + 1;

            if (!startInput.value) {
                startInput.value = startYear;
            }
            if (!endInput.value) {
                endInput.value = endYear;
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                loadEnrollmentLookups();
                setAutoSchoolYear();
            });
        } else {
            loadEnrollmentLookups();
            setAutoSchoolYear();
        }

        function sameAddr(yes) {
            document.getElementById('permBox').style.opacity       = yes ? '.4'    : '1';
            document.getElementById('permBox').style.pointerEvents = yes ? 'none'  : 'auto';
        }

        // Auto-calculate Age when Birth Date changes — replaces enroll.js birthDate listener
        document.getElementById('birthDate').addEventListener('change', function () {
            const birthDate = new Date(this.value);
            const today     = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            document.getElementById('ageField').value = age;
        });

        function addNestedValue(target, name, value) {
            const parts = name.split('[').map(part => part.replace(/\]$/, ''));
            let current = target;

            parts.forEach((part, index) => {
                const isLast = index === parts.length - 1;
                const nextPart = parts[index + 1];
                const nextPartIsNumeric = /^\d+$/.test(nextPart);

                if (part === '') {
                    if (isLast) {
                        current.push(value);
                    } else {
                        if (!Array.isArray(current)) {
                            current = [];
                        }
                        if (current.length === 0) {
                            current.push(nextPartIsNumeric ? {} : []);
                        }
                        current = current[current.length - 1];
                    }
                } else {
                    if (isLast) {
                        // For keyed arrays (numeric keys), use object notation; otherwise use array
                        const isNumericKey = /^\d+$/.test(part);
                        if (isNumericKey) {
                            if (typeof current[part] !== 'object' || current[part] === null) {
                                current[part] = value;
                            }
                        } else {
                            if (current[part] === undefined) {
                                current[part] = [];
                            }
                            if (!Array.isArray(current[part])) {
                                current[part] = [current[part]];
                            }
                            current[part].push(value);
                        }
                    } else {
                        if (current[part] === undefined) {
                            current[part] = nextPartIsNumeric ? {} : [];
                        }
                        current = current[part];
                    }
                }
            });
        }

        function serializeForm(form) {
            const formData = new FormData(form);
            const data = {};

            for (const [name, value] of formData.entries()) {
                if (name === 'next') {
                    continue;
                }

                if (name.includes('[')) {
                    addNestedValue(data, name, value);
                    continue;
                }

                if (data[name] !== undefined) {
                    if (!Array.isArray(data[name])) {
                        data[name] = [data[name]];
                    }
                    data[name].push(value);
                } else {
                    data[name] = value;
                }
            }

            if (data.same_address === 'Yes') {
                data.Permanent_House_No          = data.Current_House_No;
                data.Permanent_Street_Name       = data.Current_Street_Name;
                data.Permanent_Barangay          = data.Current_Barangay;
                data.Permanent_Municipality_City = data.Current_Municipality_City;
                data.Permanent_Province          = data.Current_Province;
                data.Permanent_Country           = data.Current_Country;
                data.Permanent_Zip_Code          = data.Current_Zip_Code;
            }

            return data;
        }

        function escapeHtml(text) {
            if (text === undefined || text === null) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function showMessage(type, message) {
            const container = document.getElementById('formMessage');
            container.className = `message ${type}`;
            container.textContent = message;
        }

        async function generateEnrollmentPdf(studentId) {
            const url = new URL('pdf.php', window.location.href);
            url.searchParams.set('student_id', studentId);
            url.searchParams.set('type', 'combined');

            const response = await fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const text = await response.text();
                throw new Error('PDF generation failed: ' + text);
            }
        }

        document.getElementById('enrollmentForm').addEventListener('submit', async function (event) {
            event.preventDefault();
            showConfirmation(event.target);
        });

        function generateConfirmationSummary(form) {
            const data = serializeForm(form);

            function safe(v) { return (v === undefined || v === null || (typeof v === 'string' && v.trim() === '')) ? null : v; }

            const studentName = [safe(data.Learner_First_Name), safe(data.Learner_Last_Name)].filter(Boolean).join(' ');
            const dob = safe(data.Birth_Date);
            const grade = safe(data.Grade_Level) || safe(data.Returning_Grade_Level);

            const addressParts = [];
            if (safe(data.Current_Street_Name)) addressParts.push(safe(data.Current_Street_Name));
            if (safe(data.Current_Barangay)) addressParts.push(safe(data.Current_Barangay));
            if (safe(data.Current_Municipality_City)) addressParts.push(safe(data.Current_Municipality_City));
            if (safe(data.Current_Province)) addressParts.push(safe(data.Current_Province));
            if (safe(data.Current_Zip_Code)) addressParts.push(safe(data.Current_Zip_Code));
            const address = addressParts.length ? addressParts.join(', ') : null;

            const fatherName = [safe(data.father_first_name), safe(data.father_last_name)].filter(Boolean).join(' ');
            const motherName = [safe(data.mother_first_name), safe(data.mother_last_name)].filter(Boolean).join(' ');
            const guardianName = [safe(data.guardian_first_name), safe(data.guardian_last_name)].filter(Boolean).join(' ');

            const fatherPhone = safe(data.father_contact_number);
            const motherPhone = safe(data.mother_contact_number);
            const guardianPhone = safe(data.guardian_contact_number);

            let summary = '<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">';

            if (studentName) summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Learner</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(studentName)}</span></div>`;
            if (dob) summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Date of Birth</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(dob)}</span></div>`;
            if (grade) summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Grade Level</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(grade)}</span></div>`;
            if (address) summary += `<div style="grid-column:1 / -1"><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Address</strong><span style="font-size:13px; color:var(--text);">${escapeHtml(address)}</span></div>`;

            if (fatherName || fatherPhone) {
                let val = fatherName ? escapeHtml(fatherName) : '';
                if (fatherPhone) val += (val ? ' — ' : '') + escapeHtml(fatherPhone);
                summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Father</strong><span style="font-size:13px; color:var(--text);">${val}</span></div>`;
            }

            if (motherName || motherPhone) {
                let val = motherName ? escapeHtml(motherName) : '';
                if (motherPhone) val += (val ? ' — ' : '') + escapeHtml(motherPhone);
                summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Mother</strong><span style="font-size:13px; color:var(--text);">${val}</span></div>`;
            }

            if (guardianName || guardianPhone) {
                let val = guardianName ? escapeHtml(guardianName) : '';
                if (guardianPhone) val += (val ? ' — ' : '') + escapeHtml(guardianPhone);
                summary += `<div><strong style="display:block; font-size:12px; color:var(--muted); margin-bottom:2px;">Guardian</strong><span style="font-size:13px; color:var(--text);">${val}</span></div>`;
            }

            summary += '</div>';
            return summary;
        }

        function showConfirmation(form) {
            const modal = document.getElementById('confirmationModal');
            const summary = document.getElementById('confirmationSummary');
            summary.innerHTML = generateConfirmationSummary(form);
            modal.style.display = 'flex';
            modal.style.background = 'rgba(0,0,0,0.5)';
        }

        function cancelConfirmation() {
            const modal = document.getElementById('confirmationModal');
            modal.style.display = 'none';
        }

        async function confirmSubmission() {
            const modal = document.getElementById('confirmationModal');
            modal.style.display = 'none';
            
            const form = document.getElementById('enrollmentForm');
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            showMessage('', '');

            try {
                const payload = serializeForm(form);
                const response = await API.enroll.create(payload);
                await generateEnrollmentPdf(response.student_id);

                showMessage('success', 'Enrollment submitted successfully. Student ID: ' + response.student_id + (response.enrollment_id ? ', Enrollment ID: ' + response.enrollment_id : '') + '. Form PDF generated. Redirecting to teacher dashboard...');
                
                // Redirect to teacher dashboard after 2 seconds
                setTimeout(() => {
                    window.location.href = '../../dashboard/teacher_dashboard/teacher_dashboard.php';
                }, 2000);
                
                form.reset();
                goTo(1);
                document.getElementById('ageField').value = '';
                document.getElementById('permBox').style.opacity = '1';
                document.getElementById('permBox').style.pointerEvents = 'auto';
            } catch (error) {
                showMessage('error', error.message || 'Enrollment submission failed.');
            } finally {
                submitButton.disabled = false;
            }
        }
    </script>

</body>
</html>