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
                <span>By submitting this re-enrollment, you create a new enrollment record for the student for the new school year. The student's previous enrollment remains unchanged.</span>
            </div>

            <div id="reviewSummary" style="background:#fff; border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; min-height:180px; color:var(--text);">
                <em>Select a student to populate the form and show the review summary.</em>
            </div>

        </div><!-- /.card-body -->

        <div class="card-foot" style="gap:8px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; padding:18px;">
            <button type="button" class="btn btn-ghost"  onclick="goTo(4)">← Back</button>
            <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; align-items:center;">
                <button type="button" class="btn btn-ghost" id="clearFormBtn">Clear & Close</button>
                <button type="button" class="btn btn-ghost" id="saveChangesBtn">Save Changes</button>
                <button type="button" class="btn btn-primary" id="submitRenrollBtn">Submit Re-enrollment</button>
            </div>
        </div>

    </div><!-- /.card -->
</div><!-- /#panel-5 -->
