<!-- ═══════════════════════════════════════════════════════
     STEP 5 — SPECIAL NEEDS EDUCATION PROGRAM
════════════════════════════════════════════════════════ -->
<div class="panel" id="panel-6">
    <div class="card">

        <div class="card-head">
            <h2>Special Needs Education Program</h2>
            <p>Information about special education services and accommodations</p>
        </div>

        <div class="card-body">

            <!-- Question 5: Is the Learner under the Special Needs Education Program? -->
            <div class="field" style="margin-bottom: 20px;">
                <label style="font-size: 14px; font-weight: 600;">5. Is the Learner under the Special Needs Education Program?</label>
                <div class="radio-group" style="margin-top: 10px;">
                    <label class="radio-pill">
                        <input type="radio" name="has_special_needs" value="1" onchange="toggle('specialNeedsDetails', true)"> Yes
                    </label>
                    <label class="radio-pill">
                        <input type="radio" name="has_special_needs" value="0" onchange="toggle('specialNeedsDetails', false)"> No
                    </label>
                </div>
            </div>

            <!-- Special Needs Details (collapsible) -->
            <div class="collapse" id="specialNeedsDetails" style="border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; background: var(--canvas);">
                
                <!-- a1. With Diagnosis from Licensed Medical Specialist -->
                <div style="margin-bottom: 24px;">
                    <label style="font-size: 13px; font-weight: 700; color: var(--text); display: block; margin-bottom: 12px;">a1. With Diagnosis from Licensed Medical Specialist:</label>
                    <div id="diagnosisTypes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- a2. With Manifestations -->
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 13px; font-weight: 700; color: var(--text); display: block; margin-bottom: 12px;">a2. With Manifestations:</label>
                    <div id="manifestationTypes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- Question b: Does the Learner have a PWD ID? -->
            <div class="field" style="margin-top: 24px; margin-bottom: 20px;">
                <label style="font-size: 14px; font-weight: 600;">b. Does the Learner have a PWD ID?</label>
                <div class="radio-group" style="margin-top: 10px;">
                    <label class="radio-pill">
                        <input type="radio" name="has_pwd_id" value="1" onchange="toggle('pwdIdBox', true)"> Yes
                    </label>
                    <label class="radio-pill">
                        <input type="radio" name="has_pwd_id" value="0" onchange="toggle('pwdIdBox', false)"> No
                    </label>
                </div>
            </div>

            <!-- PWD ID Number (collapsible) -->
            <div class="collapse" id="pwdIdBox">
                <div class="field">
                    <label>PWD ID Number</label>
                    <input type="text" name="pwd_id_number" placeholder="Enter PWD ID number">
                </div>
            </div>

        </div>

        <div class="card-foot">
            <button type="button" class="btn btn-ghost" onclick="goTo(5)">← Back</button>
            <span class="step-count">Step 6 of 7</span>
            <button type="button" class="btn btn-primary" onclick="goTo(7)">Next: Review →</button>
        </div>

    </div>
</div>
