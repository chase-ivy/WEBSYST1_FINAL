<div class="panel active" id="panel-1">
    <div class="sec-divider"><span>Learner Information</span></div>
    <div class="grid-2">
        <div class="field">
            <label>School year</label>
            <input type="text" name="school_year" id="school_year" placeholder="2025-2026">
        </div>
        <div class="field">
            <label>Grade level</label>
            <input type="text" name="grade_level" id="grade_level" placeholder="Grade 1">
        </div>
    </div>
    <div class="grid-3">
        <div class="field">
            <label>Last name</label>
            <input type="text" name="last_name" id="last_name" placeholder="Last name">
        </div>
        <div class="field">
            <label>First name</label>
            <input type="text" name="first_name" id="first_name" placeholder="First name">
        </div>
        <div class="field">
            <label>Middle name</label>
            <input type="text" name="middle_name" id="middle_name" placeholder="Middle name">
        </div>
    </div>
    <div class="grid-2">
        <div class="field">
            <label>Extension name</label>
            <input type="text" name="extension_name" id="extension_name" placeholder="Jr., Sr., III">
        </div>
        <div class="field">
            <label>LRN</label>
            <input type="text" name="lrn" id="lrn" placeholder="Learner Reference Number">
        </div>
    </div>
    <div class="grid-3">
        <div class="field">
            <label>Birth date</label>
            <input type="date" name="birth_date" id="birth_date">
        </div>
        <div class="field">
            <label>Sex</label>
            <select name="sex" id="sex">
                <option value="">Select sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="field">
            <label>Place of birth</label>
            <input type="text" name="place_of_birth" id="place_of_birth" placeholder="City, province">
        </div>
    </div>
    <div class="grid-2">
        <div class="field">
            <label>Mother tongue</label>
            <select name="mother_tongue" id="Mother_Tongue" onchange="toggleMotherTongueOther()">
                <option value="">Select mother tongue</option>
                <option value="Filipino">Filipino</option>
                <option value="English">English</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="field" id="Mother_Tongue_Other" style="display:none;">
            <label>Other mother tongue</label>
            <input type="text" name="mother_tongue_other" id="mother_tongue_other" placeholder="Specify other mother tongue">
        </div>
    </div>
    <div class="grid-2">
        <div class="field">
            <label>Indigenous group</label>
            <select name="indigenous_group" id="IP_Group" onchange="toggleIpOther()">
                <option value="">Select indigenous group</option>
                <option value="None">None</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="field" id="IP_Specify" style="display:none;">
            <label>Specify indigenous group</label>
            <input type="text" name="indigenous_group_other" id="indigenous_group_other" placeholder="Specify indigenous group">
        </div>
    </div>
    <div class="grid-2">
        <div class="field">
            <label>4Ps household ID</label>
            <input type="text" name="four_ps_household_id" id="four_ps_household_id" placeholder="Household ID">
        </div>
        <div class="field" style="padding-top:28px;">
            <label class="checkbox-label"><input type="checkbox" name="is_learner_with_disability" id="is_learner_with_disability" value="1"> Learner with disability</label>
            <label class="checkbox-label"><input type="checkbox" name="is_returning_learner" id="is_returning_learner" value="1"> Returning learner</label>
        </div>
    </div>
    <div class="grid-2" style="margin-top:24px;">
        <button type="button" class="btn btn-primary" onclick="goTo(2)">Next</button>
    </div>
</div>
