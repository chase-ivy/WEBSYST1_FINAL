<!-- ═══════════════════════════════════════════════════════
     STEP 2 — ADDRESS INFORMATION
════════════════════════════════════════════════════════ -->
<div class="panel" id="panel-3">
    <div class="card">

        <div class="card-head">
            <h2>Address Information</h2>
            <p>Current and permanent address of the learner</p>
        </div>

        <div class="card-body">

            <div class="sec-divider"><span>Current Address</span></div>

            <div class="grid-2">
                <div class="field">
                    <label>House No.</label>
                    <input type="number" name="Current_House_No" placeholder="123">
                </div>
                <div class="field">
                    <label>Street Name</label>
                    <input type="text" name="Current_Street_Name" placeholder="Rizal St.">
                </div>
                <div class="field">
                    <label>Barangay</label>
                    <input type="text" name="Current_Barangay" placeholder="Brgy. Name">
                </div>
                <div class="field">
                    <label>Municipality / City</label>
                    <input type="text" name="Current_Municipality_City" placeholder="Baguio City">
                </div>
                <div class="field">
                    <label>Province</label>
                    <input type="text" name="Current_Province" placeholder="Benguet">
                </div>
                <div class="field">
                    <label>Country</label>
                    <input type="text" name="Current_Country" placeholder="Philippines">
                </div>
                <div class="field">
                    <label>Zip Code</label>
                    <input type="number" name="Current_Zip_Code" placeholder="2600">
                </div>
                <div class="field">
                    <label>Address Status</label>
                    <select name="Current_Address_Status">
                        <option value="">Select status</option>
                        <option value="Rental">Rental</option>
                        <option value="Owned">Owned</option>
                        <option value="Living with Relatives">Living with Relatives</option>
                        <option value="Inherited">Inherited</option>
                    </select>
                </div>
            </div>

            <div class="sec-divider"><span>Permanent Address</span></div>

            <!-- Same as current toggle -->
            <div class="field" style="margin-bottom:14px;">
                <label>Same as Current Address?</label>
                <div class="radio-group">
                    <label class="radio-pill">
                        <input type="radio" name="same_address" value="Yes" onchange="sameAddr(true)"> Yes
                    </label>
                    <label class="radio-pill">
                        <input type="radio" name="same_address" value="No"  onchange="sameAddr(false)"> No
                    </label>
                </div>
            </div>

            <div class="collapse open" id="permBox">
                <div class="grid-2">
                    <div class="field">
                        <label>House No.</label>
                        <input type="number" name="Permanent_House_No" placeholder="123">
                    </div>
                    <div class="field">
                        <label>Street Name</label>
                        <input type="text" name="Permanent_Street_Name" placeholder="Rizal St.">
                    </div>
                    <div class="field">
                        <label>Barangay</label>
                        <input type="text" name="Permanent_Barangay" placeholder="Brgy. Name">
                    </div>
                    <div class="field">
                        <label>Municipality / City</label>
                        <input type="text" name="Permanent_Municipality_City" placeholder="Baguio City">
                    </div>
                    <div class="field">
                        <label>Province</label>
                        <input type="text" name="Permanent_Province" placeholder="Benguet">
                    </div>
                    <div class="field">
                        <label>Country</label>
                        <input type="text" name="Permanent_Country" placeholder="Philippines">
                    </div>
                    <div class="field">
                        <label>Zip Code</label>
                        <input type="number" name="Permanent_Zip_Code" placeholder="2600">
                    </div>
                    <div class="field">
                        <label>Address Status</label>
                        <select name="Permanent_Address_Status">
                            <option value="">Select status</option>
                            <option value="Rental">Rental</option>
                            <option value="Owned">Owned</option>
                            <option value="Living with Relatives">Living with Relatives</option>
                            <option value="Inherited">Inherited</option>
                        </select>
                    </div>
                </div>
            </div><!-- /#permBox -->

        </div><!-- /.card-body -->

        <div class="card-foot">
            <button type="button" class="btn btn-ghost"   onclick="goTo(2)">← Back</button>
            <span class="step-count">Step 3 of 6</span>
            <button type="button" class="btn btn-primary" onclick="goTo(4)">Next: Parents →</button>
        </div>

    </div><!-- /.card -->
</div><!-- /#panel-2 -->
