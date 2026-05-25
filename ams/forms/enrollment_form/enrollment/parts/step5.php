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

                <div style="background:var(--brand); color:#fff; padding:10px 16px; font-size:13px; font-weight:600;">
                    Consent for Data Collection and Usage
                </div>

                <div style="padding:16px; font-size:13px; color:#333; line-height:1.7; background:#fff;">
                    <p>I hereby consent to the collection and usage of my personal data for the purpose of enrollment and academic administration.</p>
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
            <button type="submit" class="btn btn-primary">Submit Enrollment</button>
        </div>

    </div><!-- /.card -->
</div><!-- /#panel-5 -->
