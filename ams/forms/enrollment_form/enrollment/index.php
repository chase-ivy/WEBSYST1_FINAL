<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Form · Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../enrollment.css" rel="stylesheet">
</head>
<body>

    <!-- TOP NAV -->
    <div class="topbar">
        <div class="topbar-logo">
            <img src="../../../style/logo.png" alt="Logo">
            <span>Gibraltar Elementary School</span>
        </div>
        <a href="../../../dashboard/teacher_dashboard/teacher_dashboard.php">? Back to Dashboard</a>
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
            <?php include __DIR__ . '/../parts/step1.php'; ?>
            <?php include __DIR__ . '/../parts/step2.php'; ?>
            <?php include __DIR__ . '/../parts/step3.php'; ?>
            <?php include __DIR__ . '/../parts/step4.php'; ?>
            <?php include __DIR__ . '/../parts/step5.php'; ?>
        </form>
    </div><!-- /.wrap -->

    <footer>
        &copy; 2025 <strong>Gibraltar Elementary School — AMES</strong>. All rights reserved.
    </footer>

    <script src="../../../api/client.js"></script>
    <script src="../enrollment.js"></script>
</body>
</html>
