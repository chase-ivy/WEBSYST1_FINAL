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
