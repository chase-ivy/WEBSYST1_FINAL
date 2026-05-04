<?php
require_once __DIR__ . '/../../config/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── SCHOOL YEAR ───────────────────────────────────────────────
    $school_year = ($_POST['year_start'] ?? '') . '-' . ($_POST['year_end'] ?? '');

    // ── INDIGENOUS GROUP ──────────────────────────────────────────
    $ip       = $_POST['ip'] ?? 'No';
    $ip_value = ($ip === 'Yes') ? ($_POST['IP_Specify'] ?? '') : null;

    // ── 4Ps BENEFICIARY ───────────────────────────────────────────
    $fourps       = $_POST['fourps'] ?? 'No';
    $fourps_value = ($fourps === 'Yes') ? ($_POST['FourPs_Specify'] ?? '') : null;

    // ── DISABILITY ────────────────────────────────────────────────
    $disability       = $_POST['disability'] ?? 'No';
    $disability_types = [];

    if ($disability === 'Yes' && !empty($_POST['disability_type'])) {
        $disability_types = array_map('intval', $_POST['disability_type']);
    }

    $birth_date = $_POST['Birth_Date'] ?? null;
    $age = null;
    if ($birth_date) {
        $birth = new DateTime($birth_date);
        $today = new DateTime();
        $age = $today->diff($birth)->y;
    }

    $with_lrn = (($_POST['with_lrn'] ?? 'No') === 'Yes') ? 1 : 0;
    $returning = (($_POST['returning'] ?? 'No') === 'Yes') ? 1 : 0;
    $sex = strtolower($_POST['sex'] ?? '');
    $fourps_value = ($fourps === 'Yes') ? ($_POST['FourPs_Specify'] ?? '') : 'No';
    $learner_with_disability = !empty($disability_types) ? 'Yes' : 'No';

    // ── STUDENTS ──────────────────────────────────────────────────
    $state = $pdo->prepare('INSERT INTO students (
        school_year, grade_level, with_lrn, `returning`,
        psa_bcn, lrn, last_name, first_name, middle_name,
        extension_name, birth_date, sex, place_of_birth,
        age, mother_tongue, indigenous_group, `4p_beneficiary`,
        is_learner_with_disability
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

    $state->execute([
        $school_year,
        $_POST['Grade_Level']               ?? '',
        $with_lrn,
        $returning,
        $_POST['PSA_Birth_Certificate_No']  ?? '',
        $_POST['Learner_Reference_No']      ?? '',
        $_POST['Learner_Last_Name']         ?? '',
        $_POST['Learner_First_Name']        ?? '',
        $_POST['Learner_Middle_Name']       ?? null,
        $_POST['Learner_Extension_Name']    ?? null,
        $birth_date,
        $sex,
        $_POST['Place_of_Birth']            ?? '',
        $age ?? 0,
        $_POST['Mother_Tongue']             ?? '',
        $ip_value,
        $fourps_value,
        $learner_with_disability
    ]);

    $student_id = $pdo->lastInsertId();

    // ── STUDENT DISABILITIES ──────────────────────────────────────
    if (!empty($disability_types)) {
        $dis_stmt = $pdo->prepare('INSERT INTO student_disabilities (student_id, disability_type_id) VALUES (?, ?)');
        foreach ($disability_types as $type_id) {
            $dis_stmt->execute([$student_id, $type_id]);
        }
    }

    // ── CURRENT ADDRESS ───────────────────────────────────────────
    $state1 = $pdo->prepare('INSERT INTO current_address (
        student_id, house_no, street_name, barangay,
        municipality_city, province, country, zip_code
    ) VALUES (?,?,?,?,?,?,?,?)');

    $state1->execute([
        $student_id,
        $_POST['Current_House_No']          ?? null,
        $_POST['Current_Street_Name']       ?? '',
        $_POST['Current_Barangay']          ?? '',
        $_POST['Current_Municipality_City'] ?? '',
        $_POST['Current_Province']          ?? '',
        $_POST['Current_Country']           ?? 'Philippines',
        $_POST['Current_Zip_Code']          ?? null
    ]);

    // ── PERMANENT ADDRESS ─────────────────────────────────────────
    if (isset($_POST['same_address']) && $_POST['same_address'] === 'Yes') {
        $perm_house    = $_POST['Current_House_No']          ?? null;
        $perm_street   = $_POST['Current_Street_Name']       ?? '';
        $perm_barangay = $_POST['Current_Barangay']          ?? '';
        $perm_city     = $_POST['Current_Municipality_City'] ?? '';
        $perm_province = $_POST['Current_Province']          ?? '';
        $perm_country  = $_POST['Current_Country']           ?? 'Philippines';
        $perm_zip      = $_POST['Current_Zip_Code']          ?? null;
    } else {
        $perm_house    = $_POST['Permanent_House_No']          ?? null;
        $perm_street   = $_POST['Permanent_Street_Name']       ?? '';
        $perm_barangay = $_POST['Permanent_Barangay']          ?? '';
        $perm_city     = $_POST['Permanent_Municipality_City'] ?? '';
        $perm_province = $_POST['Permanent_Province']          ?? '';
        $perm_country  = $_POST['Permanent_Country']           ?? 'Philippines';
        $perm_zip      = $_POST['Permanent_Zip_Code']          ?? null;
    }

    $state2 = $pdo->prepare('INSERT INTO permanent_address (
        student_id, house_no, street_name, barangay,
        municipality_city, province, country, zip_code
    ) VALUES (?,?,?,?,?,?,?,?)');

    $state2->execute([
        $student_id,
        $perm_house, $perm_street, $perm_barangay,
        $perm_city,  $perm_province, $perm_country, $perm_zip
    ]);

    // ── PARENT / GUARDIAN ─────────────────────────────────────────
    $parent_id = $pdo->lastInsertId();

        if ($parent_id) {
            $link = $pdo->prepare('INSERT INTO student_parents (student_id, parent_id) VALUES (?,?)');
            $link->execute([$student_id, $parent_id]);
        }
            return $parent_id;
        
        $father_id = insertParent(
        $pdo, $student_id, 'father',
        $_POST['father_last_name'] ?? '',
        $_POST['father_first_name'] ?? '',
        $_POST['father_middle_name'] ?? '',
        $_POST['father_contact_number'] ?? ''
        );

        $mother_id = insertParent(
        $pdo, $student_id, 'mother',
        $_POST['mother_last_name'] ?? '',
        $_POST['mother_first_name'] ?? '',
        $_POST['mother_middle_name'] ?? '',
        $_POST['mother_contact_number'] ?? ''
        );

        $guardian_id = insertParent(
        $pdo, $student_id, 'guardian',
        $_POST['guardian_last_name'] ?? '',
        $_POST['guardian_first_name'] ?? '',
        $_POST['guardian_middle_name'] ?? '',
        $_POST['guardian_contact_number'] ?? ''
        );

        $parent_id = $father_id ?? $mother_id ?? $guardian_id;

        if (!$parent_id) {
            $parent_id = null;
        }


    $father_id   = insertParent($pdo, $student_id, 'father',
        $_POST['father_last_name']    ?? '', $_POST['father_first_name']    ?? '',
        $_POST['father_middle_name']  ?? '', $_POST['father_contact_number'] ?? ''
    );
    $mother_id   = insertParent($pdo, $student_id, 'mother',
        $_POST['mother_last_name']    ?? '', $_POST['mother_first_name']    ?? '',
        $_POST['mother_middle_name']  ?? '', $_POST['mother_contact_number'] ?? ''
    );
    $guardian_id = insertParent($pdo, $student_id, 'guardian',
        $_POST['guardian_last_name']    ?? '', $_POST['guardian_first_name']    ?? '',
        $_POST['guardian_middle_name']  ?? '', $_POST['guardian_contact_number'] ?? ''
    );

    $linked_parent_id = $father_id ?? $mother_id ?? $guardian_id;

    // ── RETURNING LEARNER ─────────────────────────────────────────
    $is_returning = $_POST['returning'] ?? 'No';

    if ($is_returning === 'Yes') {
        $state4 = $pdo->prepare('INSERT INTO returning_learner_information (
            student_id, last_grade_level_completed,
            last_school_attended, last_school_year_completed, school_id
        ) VALUES (?,?,?,?,?)');

        $state4->execute([
            $student_id,
            $_POST['Returning_Grade_Level']      ?? '',
            $_POST['Last_School_Attended']       ?? '',
            $_POST['Last_School_Year_Completed'] ?? '',
            $_POST['School_ID']                  ?? null
        ]);
    }
    //Medical Part
    $med_state = $pdo->prepare('INSERT INTO medical_information (student_id, parent_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?,?,?,?)');
    $med_state->execute([
        $student_id,
        $parent_id,
        $_POST['exposed_to_cigarette_vape_smoke'] ?? '',
        $_POST['other_pertinent_information'] ?? ''
    ]);

    $medical_id = $pdo->lastInsertId();

    $med_state1 = $pdo->prepare('INSERT INTO medical_allergies (medical_id, has_allergies, medicine_allergy, pollen_allergy, food_allergy, other_allergy) VALUES (?,?,?,?,?,?)');
    $med_state1->execute([
        $medical_id,
        $_POST['has_allergies'] ?? '',
        $_POST['medicine_allergy'] ?? NULL,
        $_POST['pollen_allergy'] ?? 0,
        $_POST['food_allergy'] ?? '',
        $_POST['other_allergy'] ?? ''
    ]);

    $med_state2 = $pdo->prepare('INSERT INTO medical_conditions (medical_id, has_medical_condition, error_of_refraction, asthma, seizure, heart_illness, anemia, bleeding_disorder, fracture_dislocation, other_condition) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $med_state2->execute([
        $medical_id,
        $_POST['has_medical_condition'] ?? 0,
        $_POST['error_of_refraction'] ?? 0,
        $_POST['asthma'] ?? 0,
        $_POST['seizure'] ?? 0,
        $_POST['heart_illness'] ?? 0,
        $_POST['anemia'] ?? 0,
        $_POST['bleeding_disorder'] ?? 0,
        $_POST['fracture_dislocation'] ?? 0,
        $_POST['other_condition'] ?? ''
    ]);

    $med_state3 = $pdo->prepare('INSERT INTO medical_surgery_hospitalization (medical_id, has_surgery_hospitalization, surgery_date, hospital_name, body_part) VALUES (?,?,?,?,?)');
    $med_state3->execute([  
        $medical_id,
        $_POST['has_surgery_hospitalization'] ?? '',
        $_POST['surgery_date'] ?? '',
        $_POST['hospital_name'] ?? '',
        $_POST['body_part'] ?? ''
    ]);

    $med_state4 = $pdo->prepare('INSERT INTO medical_treatment_medicines (medical_id, is_currently_taking_treatment, treatment_medicine, schedule_dosage) VALUES (?,?,?,?)');
    $med_state4->execute([          
        $medical_id,
        $_POST['is_currently_taking_treatment'] ?? '',
        $_POST['treatment_medicine'] ?? '',
        $_POST['schedule_dosage'] ?? ''
    ]);

    $med_state5 = $pdo->prepare('INSERT INTO family_medical_history (medical_id, has_family_medical_history, tuberculosis, cancer, cancer_type, diabetes_mellitus, hypertension, stroke_heart_attack, depression, kidney_problems, other_condition) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $med_state5->execute([              
        $medical_id,
        $_POST['has_family_medical_history'] ?? 0,
        $_POST['tuberculosis'] ?? 0,
        $_POST['cancer'] ?? 0,
        $_POST['cancer_type'] ?? 0,
        $_POST['diabetes_mellitus'] ?? 0,
        $_POST['hypertension'] ?? 0,
        $_POST['stroke_heart_attack'] ?? 0,
        $_POST['depression'] ?? 0,
        $_POST['kidney_problems'] ?? 0,
        $_POST['other_condition'] ?? ''
    ]);


    // // ── STORE student_id in session for medical.php ───────────────
    // session_start();
    // $_SESSION['student_id'] = $student_id;
    // $_SESSION['linked_parent_id'] = $linked_parent_id;

    // header('Location: medical.php');
    // exit;
} 