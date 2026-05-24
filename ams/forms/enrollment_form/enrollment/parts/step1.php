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

            <!-- Returning -->
            <div class="grid-1" style="margin-top:16px;">
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
                        <option value="Ilocano[1][]">Ilocano</option>
                        <option value="Tagalog[2][]">Tagalog</option>
                        <option value="Kapampangan[3][]">Kapampangan</option>
                        <option value="Pangasinan[4][]">Pangasinan</option>
                        <option value="Cebuano[5][]">Cebuano</option>
                        <option value="English[6][]">English</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" id="Mother_Tongue_Other" name="Mother_Tongue_Other" placeholder="Please specify" style="display:none; margin-top:8px; padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
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
                        <option value="Ibalio[1][]">Ibalio</option>
                        <option value="Kankanaey[2][]">Kankanaey</option>
                        <option value="Ifugao[3][]">Ifugao</option>
                        <option value="Bontoc[4][]">Bontoc</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" id="IP_Specify" name="IP_Specify" placeholder="Please specify" style="display:none; margin-top:8px; padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
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
                        <input id="social_health" type="checkbox" name="disabilityDetails[9][]" value="9"> Social Health Problem/Chronic Disease
                    </label>
                    <div id="socialOptionsBox" style="display:none; margin-left: 18px; margin-top:6px;">
                        <!-- disability_subtypes for type 9 will be dynamically loaded here -->
                    </div>
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
