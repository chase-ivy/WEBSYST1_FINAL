<?php

// ================================================================
// enroll_process.php
// Include this at the top of enrollment.php.
// Handles all INSERT logic when the form is submitted.
// Requires $pdo from config.php to be already included.
// ================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── SCHOOL YEAR ───────────────────────────────────────────────
    $school_year = ($_POST['year_start'] ?? '') . '-' . ($_POST['year_end'] ?? '');

    // ── INDIGENOUS GROUP ──────────────────────────────────────────
    $ip       = $_POST['ip'] ?? 'No';
    $ip_value = ($ip === 'Yes') ? ($_POST['IP_Specify'] ?? '') : 'No';

    // ── 4Ps BENEFICIARY ───────────────────────────────────────────
    $fourps       = $_POST['fourps'] ?? 'No';
    $fourps_value = ($fourps === 'Yes') ? ($_POST['FourPs_Specify'] ?? '') : 'No';

    // ── DISABILITY ────────────────────────────────────────────────
    $disability = $_POST['disability'] ?? 'No';

    if ($disability === 'Yes' && !empty($_POST['disability_type'])) {
        $disability_value = implode(',', $_POST['disability_type']);
    } else {
        $disability_value = '0';
    }

    // ── STUDENTS ──────────────────────────────────────────────────
    $state = $pdo->prepare('INSERT INTO students (
        school_year, grade_level, with_lrn, `returning`,
        psa_bcn, lrn, last_name, first_name, middle_name,
        extension_name, birth_date, sex, place_of_birth, age,
        mother_tongue, indigenous_group, `4p_beneficiary`, is_learner_with_disability
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

    $state->execute([
        $school_year,
        $_POST['Grade_Level'] ?? '',

        $_POST['with_lrn'] ?? 'No',
        $_POST['returning'] ?? 'No',

        $_POST['PSA_Birth_Certificate_No'] ?? '',
        $_POST['Learner_Reference_No'] ?? '',

        $_POST['Learner_Last_Name'] ?? '',
        $_POST['Learner_First_Name'] ?? '',
        $_POST['Learner_Middle_Name'] ?? '',

        $_POST['Learner_Extension_Name'] ?? '',
        $_POST['Birth_Date'] ?? null,

        $_POST['sex'] ?? '',

        $_POST['Place_of_Birth'] ?? '',
        $_POST['Age'] ?? 0,

        $_POST['Mother_Tongue'] ?? '',

        $ip_value ?? 'No',
        $fourps_value ?? 'No',
        $disability_value ?? '0'
    ]);

    $student_id = $pdo->lastInsertId();

    // ── CURRENT ADDRESS ───────────────────────────────────────────
    $state1 = $pdo->prepare('INSERT INTO current_address (
        student_id, house_no, street_name, barangay,
        municipality_city, province, country, zip_code
    ) VALUES (?,?,?,?,?,?,?,?)');

    $state1->execute([
        $student_id,
        $_POST['Current_House_No'] ?? 0,
        $_POST['Current_Street_Name'] ?? '',
        $_POST['Current_Barangay'] ?? '',
        $_POST['Current_Municipality_City'] ?? '',
        $_POST['Current_Province'] ?? '',
        $_POST['Current_Country'] ?? '',
        $_POST['Current_Zip_Code'] ?? 0
    ]);

    // ── PERMANENT ADDRESS ─────────────────────────────────────────
    if (isset($_POST['same_address']) && $_POST['same_address'] === 'Yes') {
        $perm_house    = $_POST['Current_House_No'] ?? 0;
        $perm_street   = $_POST['Current_Street_Name'] ?? '';
        $perm_barangay = $_POST['Current_Barangay'] ?? '';
        $perm_city     = $_POST['Current_Municipality_City'] ?? '';
        $perm_province = $_POST['Current_Province'] ?? '';
        $perm_country  = $_POST['Current_Country'] ?? '';
        $perm_zip      = $_POST['Current_Zip_Code'] ?? 0;
    } else {
        $perm_house    = $_POST['Permanent_House_No'] ?? 0;
        $perm_street   = $_POST['Permanent_Street_Name'] ?? '';
        $perm_barangay = $_POST['Permanent_Barangay'] ?? '';
        $perm_city     = $_POST['Permanent_Municipality_City'] ?? '';
        $perm_province = $_POST['Permanent_Province'] ?? '';
        $perm_country  = $_POST['Permanent_Country'] ?? '';
        $perm_zip      = $_POST['Permanent_Zip_Code'] ?? 0;
    }

    $state2 = $pdo->prepare('INSERT INTO permanent_address (
        student_id, house_no, street_name, barangay,
        municipality_city, province, country, zip_code
    ) VALUES (?,?,?,?,?,?,?,?)');

    $state2->execute([
        $student_id,
        $perm_house ?? 0,
        $perm_street ?? '',
        $perm_barangay ?? '',
        $perm_city ?? '',
        $perm_province ?? '',
        $perm_country ?? '',
        $perm_zip ?? 0
    ]);

    // ── PARENT / GUARDIAN ─────────────────────────────────────────
    function insertParent($pdo, $student_id, $type, $last, $first, $middle, $contact) {
        $last = trim($last);
        $first = trim($first);
        $middle = trim($middle);
        $contact = trim($contact);

        // Skip if all parent fields are empty
        if ($last === '' && $first === '' && $middle === '' && $contact === '') {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO parents (last_name, first_name, middle_name, contact_number, parent_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$last, $first, $middle, $contact, $type]);

        $parent_id = $pdo->lastInsertId();
        if (!$parent_id) {
            $lookup = $pdo->prepare("SELECT parent_id FROM parents WHERE last_name = ? AND first_name = ? AND middle_name = ? AND contact_number = ? AND parent_type = ? ORDER BY parent_id DESC LIMIT 1");
            $lookup->execute([$last, $first, $middle, $contact, $type]);
            $parent_id = $lookup->fetchColumn();
        }

        if ($parent_id) {
            $link = $pdo->prepare("INSERT INTO student_parents (student_id, parent_id) VALUES (?, ?)");
            $link->execute([$student_id, $parent_id]);
        }
    }

    insertParent($pdo, $student_id, 'father',
        $_POST['father_last_name'] ?? '',
        $_POST['father_first_name'] ?? '',
        $_POST['father_middle_name'] ?? '',
        $_POST['father_contact_number'] ?? ''
    );

    insertParent($pdo, $student_id, 'mother',
        $_POST['mother_last_name'] ?? '',
        $_POST['mother_first_name'] ?? '',
        $_POST['mother_middle_name'] ?? '',
        $_POST['mother_contact_number'] ?? ''
    );

    insertParent($pdo, $student_id, 'guardian',
        $_POST['guardian_last_name'] ?? '',
        $_POST['guardian_first_name'] ?? '',
        $_POST['guardian_middle_name'] ?? '',
        $_POST['guardian_contact_number'] ?? ''
    );

    // ── RETURNING LEARNER ─────────────────────────────────────────
    $state4 = $pdo->prepare('INSERT INTO returning_learner_information (
        student_id, last_grade_level_completed,
        last_school_attended, last_school_year_completed
    ) VALUES (?,?,?,?)');

    $state4->execute([
        $student_id,
        $_POST['Returning_Grade_Level'] ?? '',
        $_POST['Last_School_Attended'] ?? '',
        $_POST['Last_School_Year_Completed'] ?? ''
    ]);

    // ── REDIRECT after all inserts are done ───────────────────────
    header('Location: medical.php');
    exit;
}