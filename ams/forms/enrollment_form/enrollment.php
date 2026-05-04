<?php    
    include '../../config/config.php';
    include 'enroll.php';
    // include '../../functions/oop.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Form · Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>

        /* ── DESIGN TKENS ───────────────────────────────────────── */
        :root {
    --brand: #4e0303;
    --brand-dark: #ec3f3f;
    --brand-light: #e8f0f7;
    --border: #d1d5db;
    --text: #000000;
    --muted: #6b7280;
    --surface: #ffffff;
    --canvas: #f5f7fa;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04);
    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 14px;
    --radius-xl: 20px;
    --transition: 180ms ease;
}

/* ── RESET ───────────────────────────────────────────────── */
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


html, body { height: 100%; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--canvas);
    color: var(--text);
    display: flex;
    flex-direction: column;
}

a { text-decoration: none; color: inherit; }

/* ── TOP NAV ─────────────────────────────────────────────── */
.topbar {
    background: #4e0303;
    padding: 12px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.topbar-logo {
    display: flex;
    align-items: center;
    gap: 9px;
}

.topbar-logo img {
    width: 36px;
    height: 36px;
    object-fit: contain;
}

.topbar-logo span {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
}

.topbar a {
    font-size: 12px;
    color: rgba(255,255,255,.65);
    border: 1px solid rgba(255,255,255,.2);
    padding: 6px 13px;
    border-radius: var(--radius-sm);
    transition: background var(--t), color var(--t);
}

.topbar a:hover {
    background: rgba(255,255,255,.1);
    color: #fff;
}

/* ── PROGRESS STEPPER ────────────────────────────────────── */
.stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    padding: 28px 20px 0;
    max-width: 700px;
    margin: 0 auto;
    width: 100%;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    flex: 1;
    position: relative;
}

.step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 16px;
    left: calc(50% + 18px);
    right: calc(-50% + 18px);
    height: 2px;
    background: var(--border);
    transition: background var(--t);
}

.step.done:not(:last-child)::after { background: var(--brand); }

.step-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid var(--border);
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    z-index: 1;
    transition: all var(--t);
}

.step.active .step-dot {
    border-color: var(--brand);
    background: var(--brand);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(21,96,168,.15);
}

.step.done .step-dot {
    border-color: var(--brand);
    background: var(--brand);
    color: #fff;
}

.step-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    letter-spacing: .3px;
    white-space: nowrap;
}

.step.active .step-label,
.step.done   .step-label { color: var(--brand); }

/* ── MAIN WRAPPER / CARD ─────────────────────────────────── */
.wrap {
    max-width: 700px;
    width: 100%;
    margin: 24px auto 40px;
    padding: 0 20px;
}

.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.card-head {
    padding: 24px 32px 20px;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(160deg, #f0f5ff, #e8f0fb);
}

.card-head h2 {
    font-size: 18px;
    font-weight: 700;
    color: var(--brand);
    margin-bottom: 2px;
}

.card-head p { font-size: 13px; color: var(--muted); }

.card-body { padding: 28px 32px; }

/* ── STEP PANELS ─────────────────────────────────────────── */
.panel         { display: none; }
.panel.active  { display: block; }

/* ── FORM GRID ───────────────────────────────────────────── */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr;       gap: 16px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr;   gap: 16px; }
.span-2 { grid-column: span 2; }

.field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.field label {
    font-size: 11px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}

.field input,
.field select {
    width: 100%;
    padding: 10px 13px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--text);
    background: var(--canvas);
    outline: none;
    transition: border-color var(--t), box-shadow var(--t), background var(--t);
}

.field input:focus,
.field select:focus {
    border-color: var(--brand);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(21,96,168,.10);
}

.field input::placeholder      { color: #b0b8c4; }
.field select option[value=""] { color: var(--muted); }

/* ── RADIO PILLS ─────────────────────────────────────────── */
.radio-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.radio-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1.5px solid var(--border);
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: all var(--t);
}

.radio-pill input { display: none; }

.radio-pill:has(input:checked) {
    border-color: var(--brand);
    background: var(--brand-light);
    color: var(--brand);
}

/* ── SECTION DIVIDER ─────────────────────────────────────── */
.sec-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 24px 0 18px;
}

.sec-divider::before,
.sec-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.sec-divider span {
    font-size: 11px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .7px;
    white-space: nowrap;
}

/* ── COLLAPSIBLE SECTIONS ────────────────────────────────── */
.collapse {
    overflow: hidden;
    max-height: 0;
    transition: max-height .3s ease;
}

.collapse.open { max-height: 600px; }

/* ── DISABILITY CHECKBOX GRID ────────────────────────────── */
.disability-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 8px;
}

.check-item {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    color: var(--text);
    cursor: pointer;
    padding: 6px 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    transition: background var(--t), border-color var(--t);
}

.check-item input {
    width: 14px;
    height: 14px;
    accent-color: var(--brand);
    flex-shrink: 0;
}

.check-item:has(input:checked) {
    background: var(--brand-light);
    border-color: var(--brand);
    color: var(--brand);
}

/* ── CARD FOOTER / BUTTONS ───────────────────────────────── */
.card-foot {
    padding: 18px 32px;
    border-top: 1px solid var(--border);
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.btn {
    padding: 10px 22px;
    font-size: 14px;
    font-weight: 600;
    border-radius: var(--radius-sm);
    border: none;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    transition: all var(--t);
}

.btn-ghost {
    background: var(--surface);
    color: var(--muted);
    border: 1.5px solid var(--border);
}

.btn-ghost:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.btn-primary { background: var(--brand); color: #fff; }

.btn-primary:hover {
    background: var(--brand-dark);
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(21,96,168,.28);
}

.step-count {
    font-size: 12px;
    color: var(--muted);
    font-weight: 500;
}

/* ── PAGE FOOTER ─────────────────────────────────────────── */
footer {
    background: #4e0303;
    color: rgba(255,255,255,.5);
    text-align: center;
    padding: 18px;
    font-size: 12px;
    border-top: 3px solid var(--brand);
    margin-top: auto;
}

footer strong { color: rgba(255,255,255,.8); }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 580px) {
    .grid-2, .grid-3     { grid-template-columns: 1fr; }
    .span-2              { grid-column: span 1; }
    .card-body,
    .card-head,
    .card-foot           { padding: 20px; }
    .topbar              { padding: 10px 16px; }
}

    </style>
</head>
<body>

    <!-- TOP NAV -->
    <div class="topbar">
        <div class="topbar-logo">
            <img src="../../style/logo.png" alt="Logo">
            <span>Gibraltar Elementary School</span>
        </div>
        <a href="../../login/index.php">← Back to Home</a>
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
    <form method="POST" action="enrollment.php">
        <input type="hidden" name="next" value="1">

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
                        <input type="number" name="PSA_Birth_Certificate_No" placeholder="PSA number">
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
                            <input type="text" name="Mother_Tongue" placeholder="e.g. Ilocano">
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
                            <input type="text" name="IP_Specify" placeholder="Specify IP group">
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
                    <div class="collapse" id="disabilityBox">
                        <div class="disability-grid" style="margin-top:10px;">
                            <label class="check-item">
                                <input type="checkbox" id="visual_impairment" name="disabilityDetails[]" value="1"> Visual Impairment
                                <div id="visualOptions" style="display:none; margin-left:15px;">
                                    <input type="checkbox" name="disabilityDetails[]" value="2"> a. blind<br>
                                    <input type="checkbox" name="disabilityDetails[]" value="3"> b. low vision
                                </div>
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="4"> Hearing Impairment
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="5"> Autism Spectrum Disorder
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="6"> Speech / Language Disorder
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="7"> Learning Disability
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="8"> Emotional / Behavioral Disorder
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="9"> Cerebral Palsy
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="10"> Intellectual Disability
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="11"> Orthopedic / Physical Disability
                            </label>
                            <label class="check-item">
                                <input type="checkbox" id="special_health" name="disability_type[]" value="12"> Special Health Problem
                                <div id="healthOptions" style="display:none; margin-left:15px;">
                                    <input type="checkbox" name="disabilityDetails[]" value="13"> Cancer
                                </div>
                            </label>
                            <label class="check-item">
                                <input type="checkbox" name="disability_type[]" value="14"> Multiple Disabilities / Disorder

                            </label>
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
                    <button type="button" class="btn btn-primary" onclick="goTo(4)">Medical →</button>
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

                <div class="card-body">

                <!-- Parent Info -->

                 <!-- THIS IS NOT INCLUDED IN DB -->
                <!-- <div class="field">
                    <label>Parent / Guardian Name</label>
                    <input type="text" name="parent_name">
                </div>

                <div class="field">
                    <label>Contact Number</label>
                    <input type="text" name="contact">
                </div> -->

                <hr><br>
                <strong><p>Instruction: Please put a check (✅) on appropriate items and fill up blanks as indicated.</p></strong>
                <!-- Q1 -->
                <div class="field">
                <label>1. Does your child/ward have any allergies?</label>
					<select id="field" onchange="showField()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>

				<div id="fieldDetails"></div>
</div>

                <!-- Q2 -->
                <div class="field">
                <label>2. Does your child/ward have any ongoing medical condition?</label>
					<select id="Q2" onchange="showQ2()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>

				<div id="q2"></div>
                </div>


                <!-- Q3 -->
                <div class="field">
                <label>3. Did your child/ward ever have surgery / hospitalization?</label>
					<select id="Q3" onchange="showQ3()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>

				<div id="q3"></div>
                </div>


                <!-- Q4 -->
                <div class="field">
                <label>4. Is your  child currently taking treatment / medicines</label>
                    <select id="Q4" onchange="showQ4()" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>

				<div id="q4"></div>
</div>

                <!-- Q5 -->
                <div class="field">
                <label>5. Does your family have a history of the following conditions:</label>
                <!--SELECTION-->
                <div class="card">

				<div class="check">
				  <label>Tuberculosis
				    <input type="checkbox" name="tuberculosis" value="1">
				</label>
				</div>
				<hr>
				<div class="check">
				  <label>Cancer, what kind?
				    <input type="text" name="cancer_type" placeholder="Please Specify">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Diabetes Mellitus
				    <input type="checkbox" name="diabetes_mellitus" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Hypertension
				    <input type="checkbox" name="hypertension" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Stroke / Heart attack
				    <input type="checkbox" name="stroke_heart_attack" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Depression
				    <input type="checkbox" name="depression" value="1">
				  </label>
				</div>

				<hr>
				<div class="check">
				  <label>Kidney problems
				    <input class="toggle" type="checkbox" name="kidney_problems" value="1">
				  </label>
				</div>
				<hr>

				<div class="check">
				  <label>Others:
				    <input type="text" name="other_condition" placeholder="Please Specify">
				  </label>
				</div>

			    </div>
                </div>

                <!-- Q6 -->
                <div class="field">
                <label>6. Does your child/ward have exposure to cigarette/vape smoke at home?</label>
					<select name="exposed_to_cigarette_vape_smoke" style="width:100%;">
						<option hidden selected>Choose</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>

                </div>

                <!-- Q7 -->
                <div class="field">
                <label>7. Other pertinent learner information:</label>
                <input type="text" name="other_pertinent_information" placeholder="Optional">
                </div>

                </div>

            </div>




                <div class="card-foot">
                    <button type="button" class="btn btn-ghost"  onclick="goTo(3)">← Back</button>
                    <span class="step-count">Step 4 of 5</span>
                    <button type="button" class="btn btn-primary" onclick="goTo(5)">Submit →</button>
                </div>

            </div><!-- /.card -->
        </div><!-- /#panel-4 -->


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
</div><!-- /.wrap -->

    <footer>
        &copy; 2025 <strong>Gibraltar Elementary School — AMES</strong>. All rights reserved.
    </footer>

    <script src="medical.js"></script>
    <script>
        document.getElementById('visual_impairment').addEventListener('change', function() {
        document.getElementById('visualOptions').style.display = this.checked ? 'block' : 'none';
    });

        document.getElementById('special_health').addEventListener('change', function() {
        document.getElementById('healthOptions').style.display = this.checked ? 'block' : 'none';
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
    </script>

</body>
</html>