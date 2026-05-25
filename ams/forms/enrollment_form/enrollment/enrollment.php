<?php
require_once __DIR__ . '/../../../config/config.php';

$motherTongues = [];
$indigenousGroups = [];
try {
    $stmt = $pdo->prepare('SELECT mother_tongue_id AS id, name FROM mother_tongues ORDER BY name');
    $stmt->execute();
    $motherTongues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT indigenous_group_id AS id, name FROM indigenous_groups ORDER BY name');
    $stmt->execute();
    $indigenousGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $motherTongues = [];
    $indigenousGroups = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Form · Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../enrollment.css" rel="stylesheet">
    <script>
        window.MOTHER_TONGUES = <?php echo json_encode($motherTongues); ?>;
        window.INDIGENOUS_GROUPS = <?php echo json_encode($indigenousGroups); ?>;
    </script>
</head>
<body>

    <!-- TOP NAV -->
    <div class="topbar">
        <div class="topbar-logo">
            <img src="../../../style/logo.png" alt="Logo">
            <span>Gibraltar Elementary School</span>
        </div>
        <a href="../../../dashboard/teacher_dashboard/teacher_dashboard.php" class="topbar-back">
            ← Back to Dashboard
        </a>
    </div>

    <!-- PROGRESS STEPPER -->
    <div class="stepper" id="stepper">
        <div class="step active" id="s1">
            <div class="step-dot">1</div>
            <span class="step-label">Learner Info</span>
        </div>
        <div class="step" id="s2">
            <div class="step-dot">2</div>
            <span class="step-label">Address</span>
        </div>
        <div class="step" id="s3">
            <div class="step-dot">3</div>
            <span class="step-label">Parents</span>
        </div>
        <div class="step" id="s4">
            <div class="step-dot">4</div>
            <span class="step-label">Medical Form</span>
        </div>
        <div class="step" id="s5">
            <div class="step-dot">5</div>
            <span class="step-label">Review</span>
        </div>
    </div>

    <div class="wrap">
        <div id="formMessage" class="message" aria-live="polite"></div>
        <form id="enrollmentForm" novalidate>

        <?php require __DIR__ . '/parts/step1.php'; ?>
        <?php require __DIR__ . '/parts/step2.php'; ?>
        <?php require __DIR__ . '/parts/step3.php'; ?>
        <?php require __DIR__ . '/parts/step4.php'; ?>
        <?php require __DIR__ . '/parts/step5.php'; ?>
        
    </form>
    
    <!-- CONFIRMATION MODAL -->
    <div id="confirmationModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:12px; padding:32px; max-width:500px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h2 style="margin-bottom:12px; font-size:20px; font-weight:700; color:var(--text);">Verify Enrollment Information</h2>
            <p style="margin-bottom:24px; font-size:14px; color:var(--muted);">Please review the information below before submitting your enrollment.</p>
            
            <div id="confirmationSummary" style="background:var(--canvas); border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:24px; max-height:300px; overflow-y:auto; font-size:13px;">
            </div>
            
            <div style="display:flex; gap:8px;">
                <button type="button" onclick="cancelConfirmation()" style="flex:1; padding:10px 16px; background:var(--canvas); color:var(--text); border:1px solid var(--border); border-radius:6px; font-weight:600; cursor:pointer; transition:background var(--transition);" onmouseover="this.style.background='#efefef'" onmouseout="this.style.background='var(--canvas)'">Cancel</button>
                <button type="button" onclick="confirmSubmission()" style="flex:1; padding:10px 16px; background:var(--brand); color:white; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition:background var(--transition);" onmouseover="this.style.background='var(--brand-dark)'" onmouseout="this.style.background='var(--brand)'">Confirm & Submit</button>
            </div>
        </div>
    </div>
</div><!-- /.wrap -->

    <footer>
        &copy; 2025 <strong>Gibraltar Elementary School — AMES</strong>. All rights reserved.
    </footer>

    <script src="../../../api/client.js"></script>
<script>
function showField(){
	const field = document.getElementById("field").value;
	const card = document.getElementById("fieldDetails");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
<div style="background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius-md); padding:14px 16px; display:flex; flex-direction:column; gap:12px;">

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="medicine_allergy_checkbox" name="medicine_allergy[]" value="1" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Medicine
    </label>
    <div id="medicineAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[1]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="pollen_allergy_checkbox" name="medicine_allergy[]" value="2" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Pollen
    </label>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="food_allergy_checkbox" name="medicine_allergy[]" value="3" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Food
    </label>
    <div id="foodAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[3]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:6px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); cursor:pointer;">
      <input type="checkbox" id="other_allergy_checkbox" name="medicine_allergy[]" value="4" style="width:15px; height:15px; accent-color:var(--brand); flex-shrink:0;">
      Others
    </label>
    <div id="otherAllergyBox" style="display:none; margin-left:23px;">
      <input type="text" name="allergy_description[4]" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%; background:var(--canvas);">
    </div>
  </div>

</div>
`;

		const attachToggle = (checkboxId, boxId) => {
			const checkbox = document.getElementById(checkboxId);
			const box = document.getElementById(boxId);
			if (!checkbox || !box) return;
			checkbox.addEventListener('change', () => {
				box.style.display = checkbox.checked ? 'block' : 'none';
			});
			box.style.display = checkbox.checked ? 'block' : 'none';
		};

		attachToggle('medicine_allergy_checkbox', 'medicineAllergyBox');
		attachToggle('food_allergy_checkbox', 'foodAllergyBox');
		attachToggle('other_allergy_checkbox', 'otherAllergyBox');
	}
}

function showQ2(){
	const field = document.getElementById("Q2").value;
	const card = document.getElementById("q2");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="1" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Error of refraction (Eye Ailment)</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="2" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Asthma (Lung Ailment)</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="3" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Seizure (Convulsions)</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="4" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Heart Illness</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="5" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Anemia</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="6" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Bleeding disorder</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="condition_type_id" value="7" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Fracture / Dislocation</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease; grid-column:span 2;">
				<input type="checkbox" id="has_medical_condition" name="condition_type_id" value="8" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Others</span>
			</label>
			<div id="medical_condition_details" style="display:none; margin-top:8px; grid-column:span 2;">
				<input type="text" name="condition_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
			</div>
		</div>
		`;
	}
    // Medical Condition Others
	const medicalCheckbox = document.getElementById('has_medical_condition');
	const medicalBox = document.getElementById('medical_condition_details');
	if (medicalCheckbox && medicalBox) {
		medicalCheckbox.addEventListener('change', function () {
			medicalBox.style.display = this.checked ? 'block' : 'none';
		});
		medicalBox.style.display = medicalCheckbox.checked ? 'block' : 'none';
	}
}

function showQ3(){
	const field = document.getElementById("Q3").value;
	const card = document.getElementById("q3");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:flex; flex-direction:column; gap:10px;">
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Surgery Date</label>
				<input type="date" name="surgery_date" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Hospital Name</label>
				<input type="text" name="hospital_name" placeholder="Hospital name" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Body Part Affected</label>
				<input type="text" name="body_part" placeholder="What part of the body?" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
		</div>
		`;
	}
}

function showQ4(){
	const field = document.getElementById("Q4").value;
	const card = document.getElementById("q4");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:flex; flex-direction:column; gap:10px;">
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Medicine / Treatment Type</label>
				<input type="text" name="treatment_medicine" placeholder="Name of medicine or treatment" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
			<div style="display:flex; flex-direction:column; gap:6px;">
				<label style="font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Dosage Schedule</label>
				<input type="text" name="schedule_dosage" placeholder="e.g., 2x daily, morning/evening" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif;">
			</div>
		</div>
		`;
	}
}

function showQ5(){
	const field = document.getElementById("Q5").value;
	const card = document.getElementById("q5");

	card.innerHTML = "";

	if (field === "1"){
		card.innerHTML = `
		<div style="background:var(--canvas); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="1" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Tuberculosis</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" id="has_cancer" name="family_condition_type_id" value="2" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Cancer</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="3" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Diabetes Mellitus</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="4" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Hypertension</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="5" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Stroke / Heart attack</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="6" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Depression</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease;">
				<input type="checkbox" name="family_condition_type_id" value="7" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Kidney problems</span>
			</label>
			<label style="display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; padding:6px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); transition:background .15s ease, border-color .15s ease; grid-column:span 2;">
				<input type="checkbox" id="has_other" name="family_condition_type_id" value="8" style="width:14px; height:14px; accent-color:var(--brand); flex-shrink:0;">
				<span>Others</span>
			</label>
			<div id="otherBox" style="display:none; margin-top:8px; grid-column:span 2;">
				<input type="text" name="family_condition_description" placeholder="Please specify" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
			</div>
			<div id="cancerBox" style="display:none; margin-top:8px; grid-column:1;">
				<input type="text" name="family_condition_description" placeholder="Specify type of cancer" style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:'DM Sans',sans-serif; width:100%;">
			</div>
		</div>
		`;
	}

	// Family History - Cancer
	const cancerCheckbox = document.getElementById('has_cancer');
	const cancerBox = document.getElementById('cancerBox');
	if (cancerCheckbox && cancerBox) {
		cancerCheckbox.addEventListener('change', function () {
			cancerBox.style.display = this.checked ? 'block' : 'none';
		});
		cancerBox.style.display = cancerCheckbox.checked ? 'block' : 'none';
	}

	// Family History - Others
	const otherCheckbox = document.getElementById('has_other');
	const otherBox = document.getElementById('otherBox');
	if (otherCheckbox && otherBox) {
		otherCheckbox.addEventListener('change', function () {
			otherBox.style.display = this.checked ? 'block' : 'none';
		});
		otherBox.style.display = otherCheckbox.checked ? 'block' : 'none';
	}
}


</script>
    <script src="enrollment.js"></script>

</body>
</html>