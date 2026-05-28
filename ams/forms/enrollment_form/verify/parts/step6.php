<!-- ═══════════════════════════════════════════════════════
     STEP 6 — REVIEW AND SUBMIT
════════════════════════════════════════════════════════ -->
<div class="panel" id="panel-6">
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
                <span>By saving or verifying this enrollment, you confirm that the review data matches the enrollment record stored in the database.</span>
            </div>

            <div id="reviewSummary" style="background:#fff; border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; min-height:180px; color:var(--text);">
                <em>Select a pending enrollment to populate the form and show the review summary.</em>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label for="rejectReason">Rejection reason <small>(optional)</small></label>
                <textarea id="rejectReason" rows="3" placeholder="Explain why this enrollment is being rejected" style="width:100%; min-height:72px; padding:10px; border:1px solid var(--border); border-radius:var(--radius-sm);"></textarea>
            </div>

        </div><!-- /.card-body -->

        <div class="card-foot" style="gap:8px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; padding:18px;">
            <button type="button" class="btn btn-ghost"  onclick="goTo(5)">← Back</button>
            <div style="display:flex; gap:8px; align-items:center;">
                <label for="assignSectionSelect" style="margin-right:8px;">Assign to section</label>
                <select id="assignSectionSelect" name="assignSectionSelect" disabled>
                    <option value="">Choose a section…</option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?php echo intval($sec['section_id']); ?>"><?php echo htmlspecialchars(trim($sec['school_year'] . ' · ' . $sec['grade_level'] . ' · ' . $sec['name']), ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-secondary" id="assignSectionBtn" disabled style="margin-left:8px;">Assign</button>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; align-items:center;">
                <button type="button" class="btn btn-danger" id="rejectSubmitBtn">Reject Enrollment</button>
                <button type="button" class="btn btn-ghost" id="saveChangesBtn">Save Changes</button>
                <button type="button" class="btn btn-primary" id="verifySubmitBtn">Verify & Archive</button>
            </div>
        </div>

    </div><!-- /.card -->
</div><!-- /#panel-6 -->
