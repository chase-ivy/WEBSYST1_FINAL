<?php
require_once __DIR__ . '/../../../login/auth.php';
require_role(['teacher']);
$currentUserId = $_SESSION['user_id'];
require_once __DIR__ . '/../../../config/config.php';

// Load active sections for assignment dropdown
$sections = [];
try {
    $stmt = $pdo->prepare('SELECT section_id, school_year, grade_level, name FROM sections WHERE is_active = 1 ORDER BY school_year DESC, grade_level, name');
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // fallback to empty list
    $sections = [];
}

// Load lookup tables for mother tongue and IP groups
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
}
?>
<!-- Inject sections array for client-side filtering -->
<script>
    window.SECTIONS = <?php echo json_encode($sections); ?>;
    window.MOTHER_TONGUES = <?php echo json_encode($motherTongues); ?>;
    window.INDIGENOUS_GROUPS = <?php echo json_encode($indigenousGroups); ?>;
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Re-enroll Students · Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../enrollment.css" rel="stylesheet">
    <script>
        window.CURRENT_USER_ID = <?php echo intval($currentUserId); ?>;
    </script>
</head>
<body>

    <!-- TOP NAV -->
    <div class="topbar">
        <div class="topbar-logo">
            <img src="../../../style/logo.png" alt="Logo">
            <span>Gibraltar Elementary School</span>
        </div>
    </div>

    <div class="wrap">
        <div class="card">
            <div class="card-head">
                <h2>Re-enroll Students</h2>
                <p>Select a student to re-enroll for the new school year. Their previous enrollment data will be loaded and can be modified as needed.</p>
            </div>
            <div class="card-body">
                <div class="grid-2" style="align-items:flex-end; gap:16px;">
                    <div class="field span-2">
                        <label>Search and select student</label>
                        <input type="text" id="studentSearch" placeholder="Search by name or LRN…">
                        <select id="enrollmentSelect"><option value="">Loading…</option></select>
                    </div>
                    <div class="field">
                        <label>Filter by previous year</label>
                        <select id="schoolYearFilter">
                            <option value="">All years</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button id="clearBtn" class="btn btn-ghost" style="align-self:flex-end;">Clear</button>
                    </div>
                </div>
                <div id="veMessage" class="message" style="display:block; margin-top:12px;"></div>
            </div>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <h2>Modify Enrollment Details</h2>
                <p>Review and update enrollment data in the stepper below, then submit to create the new enrollment.</p>
            </div>
            <div class="card-body">
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
                        <span class="step-label">Medical</span>
                    </div>
                    <div class="step" id="s5">
                        <div class="step-dot">5</div>
                        <span class="step-label">Review</span>
                    </div>
                </div>
                <form id="enrollmentForm" novalidate>
                <input type="hidden" name="student_id" id="studentIdInput" value="0">
                <input type="hidden" name="enrollment_id" id="enrollmentIdInput" value="0">

                <?php include 'parts/step1.php'; ?>
                <?php include 'parts/step2.php'; ?>
                <?php include 'parts/step3.php'; ?>
                <?php include 'parts/step4.php'; ?>
                <?php include 'parts/step5.php'; ?>
                </form>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2026 <strong>Gibraltar Elementary School — AMES</strong>.
    </footer>

    <script src='../../../api/client.js'></script>
    <script src="../enrollment.js"></script>
    <script src="verify_enrollment.js"></script>
</body>
</html>