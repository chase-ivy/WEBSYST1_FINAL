<!-- ═══════════════════════════════════════════════════════
     STEP 4 — MEDICAL INFORMATION
════════════════════════════════════════════════════════ -->
<div class="panel" id="panel-5">
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
            <button type="button" class="btn btn-ghost"  onclick="goTo(4)">← Back</button>
            <span class="step-count">Step 5 of 6</span>
            <button type="button" class="btn btn-primary" onclick="goTo(6)">Next: Review →</button>
        </div>

    </div>
</div>
