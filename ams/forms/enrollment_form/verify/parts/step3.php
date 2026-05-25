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

            <div class="grid-2" style="margin-top:14px; gap:12px;">
                <div class="field">
                    <label>Occupation</label>
                    <input type="text" name="father_occupation" placeholder="Teacher, Driver, etc.">
                </div>
                <div class="field">
                    <label>Relationship Status</label>
                    <input type="text" name="father_relationship_status" placeholder="Married, Single, Widowed">
                </div>
            </div>
            <div class="field" style="margin-top:14px;">
                <label>Facebook / Messenger</label>
                <input type="text" name="father_facebook_messenger" placeholder="fb.com/username">
            </div>

            <div class="sec-divider"><span>Mother's Information</span></div>

            <div class="grid-3">
                <div class="field">
                    <label>Maiden Last Name</label>
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

            <div class="grid-2" style="margin-top:14px; gap:12px;">
                <div class="field">
                    <label>Occupation</label>
                    <input type="text" name="mother_occupation" placeholder="Teacher, Driver, etc.">
                </div>
                <div class="field">
                    <label>Relationship Status</label>
                    <input type="text" name="mother_relationship_status" placeholder="Married, Single, Widowed">
                </div>
            </div>
            <div class="field" style="margin-top:14px;">
                <label>Facebook / Messenger</label>
                <input type="text" name="mother_facebook_messenger" placeholder="fb.com/username">
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

            <div class="grid-2" style="margin-top:14px; gap:12px;">
                <div class="field">
                    <label>Occupation</label>
                    <input type="text" name="guardian_occupation" placeholder="Teacher, Driver, etc.">
                </div>
                <div class="field">
                    <label>Relationship Status</label>
                    <input type="text" name="guardian_relationship_status" placeholder="Married, Single, Widowed">
                </div>
            </div>
            <div class="field" style="margin-top:14px;">
                <label>Facebook / Messenger</label>
                <input type="text" name="guardian_facebook_messenger" placeholder="fb.com/username">
            </div>


            <div class="sec-divider"><span>Emergency Contact</span></div>

            <div class="field">
                <label>Who should we contact first in case of emergency?</label>
                <div class="radio-group" style="margin-top:8px;">
                    <label class="radio-pill">
                        <input type="radio" name="emergency_contact" value="father"> Father
                    </label>
                    <label class="radio-pill">
                        <input type="radio" name="emergency_contact" value="mother"> Mother
                    </label>
                    <label class="radio-pill">
                        <input type="radio" name="emergency_contact" value="guardian"> Guardian
                    </label>
                </div>
            </div>

        </div><!-- /.card-body -->

        <div class="card-foot">
            <button type="button" class="btn btn-ghost"   onclick="goTo(2)">← Back</button>
            <span class="step-count">Step 3 of 5</span>
            <button type="button" class="btn btn-primary" onclick="goTo(4)"> Next: Medical →</button>
        </div>

    </div><!-- /.card -->
</div><!-- /#panel-3 -->