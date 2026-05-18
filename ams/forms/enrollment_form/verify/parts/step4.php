<div class="panel" id="panel-4">
    <div class="sec-divider"><span>Medical Information</span></div>
    <div class="grid-2">
        <div class="field">
            <label>Exposed to cigarette/vape smoke</label>
            <select name="exposed_to_cigarette_vape_smoke" id="exposed_to_cigarette_vape_smoke">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        <div class="field">
            <label>Other pertinent information</label>
            <input type="text" name="other_pertinent_information" id="other_pertinent_information" placeholder="Additional medical notes">
        </div>
    </div>
    <div class="field">
        <label>Allergies</label>
        <textarea name="allergies" id="allergies" rows="3" placeholder="One item per line"></textarea>
    </div>
    <div class="field">
        <label>Medical conditions</label>
        <textarea name="conditions" id="conditions" rows="3" placeholder="One item per line"></textarea>
    </div>
    <div class="field">
        <label>Surgeries</label>
        <textarea name="surgeries" id="surgeries" rows="3" placeholder="One item per line"></textarea>
    </div>
    <div class="field">
        <label>Treatments</label>
        <textarea name="treatments" id="treatments" rows="3" placeholder="One item per line"></textarea>
    </div>
    <div class="field">
        <label>Family medical history</label>
        <textarea name="family_medical_history" id="family_medical_history" rows="3" placeholder="One item per line"></textarea>
    </div>
    <div class="grid-2" style="margin-top:24px;">
        <button type="button" class="btn btn-ghost" onclick="goTo(3)">Back</button>
        <button type="button" class="btn btn-primary" onclick="goToAndReview(5)">Next</button>
    </div>
</div>
