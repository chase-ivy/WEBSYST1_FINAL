<?php
require_once __DIR__ . '/../../config/config.php';
 $year_start = $_POST['year_start'] ?? '';
    $year_end   = $_POST['year_end'] ?? '';


    $school_year = $year_start . '-' . $year_end;

    // This is for the IP field.
    $ip = $_POST['ip'] ?? null; 
    if ($ip === "Yes") { $ip_value = $_POST['IP_Specify'] ?? null; 
    } else { 
        $ip_value = "No"; 
    }

    // $ip = $_POST['ip'] ?? 'No';

    // $is_indigenous = ($ip === "Yes") ? 1 : 0;
    // $indigenous_group = ($ip === "Yes") ? ($_POST['IP_Specify'] ?? null) : null;


    // This is for the 4Ps Beneficiary field.
    $fourps = $_POST['fourps'] ?? null;

    if ($fourps === "Yes") {
        $fourps_value = $_POST['FourPs_Specify'] ?? null;
    } else {
        $fourps_value = "No";
    }

    // This is for the Learner with Disability field.
    $disability = $_POST['disability'] ?? 'No';

    if ($disability === "Yes" && !empty($_POST['disabilityDetails'])) {

        $flat = [];
        foreach ($_POST['disabilityDetails'] as $arr) {
            foreach ($arr as $val) {
                $flat[] = $val;
            }
        }

        $disability_value = implode(',', $flat);

    } else {
        $disability_value = "0";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){

        $state1 = $pdo->prepare('INSERT INTO students (lrn, last_name, first_name, middle_name, extension_name, birth_date, sex, place_of_birth) VALUES (?,?,?,?,?,?,?,?)');  
        $state1->execute([
            $_POST['Learner_Reference_No'] ?? null,
            $_POST['Learner_Last_Name'] ?? null,
            $_POST['Learner_First_Name'] ?? null,
            $_POST['Learner_Middle_Name'] ?? null,
            $_POST['Learner_Extension_Name'] ?? null,
            $_POST['Birth_Date'] ?? null,
            $_POST['sex'] ?? null,
            $_POST['Place_of_Birth'] ?? null            
        ]);
        
        $student_id = $pdo->lastInsertId();

        $state = $pdo->prepare('INSERT INTO enrollments (student_id,school_year, grade_level, with_lrn, psa_bcn, age, mother_tongue, is_indigenous, indigenous_group, is_four_ps_beneficiary, four_ps_household_id, is_learner_with_disability, is_returning_learner)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $state->execute([
        $student_id,
        $school_year,
        $_POST['Grade_Level'] ?? null,
        $_POST['with_lrn'] ?? null,
        $_POST['psa_bcn'] ?? null,
        $_POST['Age'] ?? null,
        $_POST['Mother_Tongue'] ?? null,
        isset($_POST['ip']) && $_POST['ip'] === 'Yes' ? 1 : 0,
        $_POST['IP_Specify'] ?? null,
        isset($_POST['fourps']) && $_POST['fourps'] === 'Yes' ? 1 : 0,
        $_POST['FourPs_Specify'] ?? null,
        isset($_POST['disability']) && $_POST['disability'] === 'Yes' ? 1 : 0,
        isset($_POST['returning']) && $_POST['returning'] === '1' ? 1 : 0,

        ]);



        $enrollment_id = $pdo->lastInsertId();

        $state2 = $pdo->prepare('INSERT INTO returning_learners (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed, school_id) VALUES (?,?,?,?,?)');
        $state2->execute([
            $enrollment_id,
            $_POST['Returning_Grade_Level'] ?? null,
            $_POST['Last_School_Attended'] ?? null,
            $_POST['Last_School_Year_Completed'] ?? null,
            $_POST['school_ID'] ?? null
        ]);

       
        foreach ($_POST['disabilityDetails'] as $type_id => $values) {

            foreach ($values as $val) {

                if (isset($_POST['disability_sub'][$type_id])) {
                    foreach ($_POST['disability_sub'][$type_id] as $sub_id) {

                        $stmt = $pdo->prepare("
                            INSERT INTO student_disabilities 
                            (enrollment_id, disability_type_id, disability_subtype_id)
                            VALUES (?, ?, ?)
                        ");

                        $stmt->execute([$enrollment_id, $type_id, $sub_id]);
                    }

                } else {

                    $stmt = $pdo->prepare("
                        INSERT INTO student_disabilities 
                        (enrollment_id, disability_type_id, disability_subtype_id)
                        VALUES (?, ?, NULL)
                    ");

                    $stmt->execute([$enrollment_id, $type_id]);
                }
            }
        }

        $state4 = $pdo->prepare('INSERT INTO addresses (enrollment_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?,?,?,?,?,?,?,?,?)');
        $state4->execute([
            $enrollment_id,
            'current',
            $_POST['Current_House_No'] ?? null,
            $_POST['Current_Street_Name'] ?? null,
            $_POST['Current_Barangay'] ?? null,
            $_POST['Current_Municipality_City'] ?? null,
            $_POST['Current_Province'] ?? null,
            $_POST['Current_Country'] ?? null,
            $_POST['Current_Zip_Code'] ?? null
        ]);
        if ($_POST['same_address'] === 'Yes') {
        $perm = [
            $_POST['Current_House_No'] ?? null,
            $_POST['Current_Street_Name'] ?? null,
            $_POST['Current_Barangay'] ?? null,
            $_POST['Current_Municipality_City'] ?? null,
            $_POST['Current_Province'] ?? null,
            $_POST['Current_Country'] ?? null,
            $_POST['Current_Zip_Code'] ?? null
        ];
        } else {
        $perm = [
            $_POST['Permanent_House_No'] ?? null,
            $_POST['Permanent_Street_Name'] ?? null,
            $_POST['Permanent_Barangay'] ?? null,
            $_POST['Permanent_Municipality_City'] ?? null,
            $_POST['Permanent_Province'] ?? null,
            $_POST['Permanent_Country'] ?? null,
            $_POST['Permanent_Zip_Code'] ?? null
        ];
        }
        $state5 = $pdo->prepare('INSERT INTO addresses (enrollment_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?,?,?,?,?,?,?,?,?)');
        $state5->execute([
            $enrollment_id,
            'permanent',
            $perm[0],
            $perm[1],
            $perm[2],
            $perm[3],
            $perm[4],
            $perm[5],
            $perm[6]
        ]);


        $state7 =$pdo->prepare('INSERT INTO parents (last_name, first_name, middle_name, contact_number) VALUES (?,?,?,?)');   
        $state7->execute([
            $_POST['father_last_name'] ?? null,
            $_POST['father_first_name'] ?? null,
            $_POST['father_middle_name'] ?? null, 
            $_POST['father_contact_number'] ?? null
        ]);


        $state8 = $pdo->prepare('INSERT INTO parents ( last_name, first_name, middle_name, contact_number) VALUES (?,?,?,?)');
        $state8->execute([
            $_POST['mother_last_name'] ?? null,
            $_POST['mother_first_name'] ?? null,
            $_POST['mother_middle_name'] ?? null,
            $_POST['mother_contact_number'] ?? null 
        ]);
        $state9 = $pdo->prepare('INSERT INTO parents ( last_name, first_name, middle_name, contact_number) VALUES (?,?,?,?)');  
        $state9->execute([
            $_POST['guardian_last_name'] ?? null,
            $_POST['guardian_first_name'] ?? null,
            $_POST['guardian_middle_name'] ?? null,
            $_POST['guardian_contact_number'] ?? null
        ]);

        $parent_id = $pdo->lastInsertId();

                
        // //MEDICAL PART
        // $med_state = $pdo->prepare('INSERT INTO medical_allergies (alley_group_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?,?,?)');
        // $med_state->execute([
        //     $allergy_group_id,
        //     $_POST['exposed_to_cigarette_vape_smoke'] ?? null,
        //     $_POST['other_pertinent_information'] ?? null
        // ]);
        
        // $medical_id = $pdo->lastInsertId();

        // $med_state1 = $pdo->prepare('INSERT INTO medical_allergies (medical_id, has_allergies) VALUES (?,?)');
        // $med_state1->execute([
        //     $medical_id,
        //     $_POST['has_allergies'] ?? 0
        // ]);

        // $allergy_group_id = $pdo->lastInsertId();

        // $med_state2 = $pdo->prepare('INSERT INTO student_allergies (allergy_group_id, allergy_type_id, description) VALUES (?,?,?)');
        // if ($_POST['has_allergies'] == "1") {

        //     if (!empty($_POST['medicine_allergy'])) {
        //         $med_state2->execute([
        //             $allergy_group_id,
        //             1,
        //             $_POST['medicine_allergy']
        //         ]);
        //     }

        //     if (isset($_POST['pollen_allergy']) && $_POST['pollen_allergy'] == "1") {
        //         $med_state2->execute([
        //             $allergy_group_id,
        //             2,
        //             'Yes'
        //         ]);
        //     }

        //     if (!empty($_POST['food_allergy'])) {
        //         $med_state2->execute([
        //             $allergy_group_id,
        //             3,
        //             $_POST['food_allergy']
        //         ]);
        //     }

        //     if (!empty($_POST['other_allergy'])) {
        //         $med_state2->execute([
        //             $allergy_group_id,
        //             4,
        //             $_POST['other_allergy_text'] ?? null
        //         ]);
        //     }

        // }

        // $med_state3 = $pdo->prepare('INSERT INTO family_medical_history (medical_id, has_family_history) VALUES (?,?)');
        // $med_state3->execute([
        //     $medical_id,
        //     $_POST['family_medical_history'] ?? 0
        // ]);

        // $family_history_id = $pdo->lastInsertId();

        // $med_state4 = $pdo->prepare('INSERT INTO student_family_conditions (family_history_id, family_condition_type_id, description) VALUES (?,?,?)');
        // if ($_POST['family_medical_history'] == "1") {

        //     if (!empty($_POST['tuberculosis'])) {
        //         $med_state4->execute([$family_history_id, 1, null]);
        //     }

        //     if (!empty($_POST['has_cancer'])) {
        //         $med_state4->execute([
        //             $family_history_id,
        //             2,
        //             $_POST['cancer_type'] ?? null
        //         ]);
        //     }

        //     if (!empty($_POST['diabetes_mellitus'])) {
        //         $med_state4->execute([$family_history_id, 3, null]);
        //     }

        //     if (!empty($_POST['hypertension'])) {
        //         $med_state4->execute([$family_history_id, 4, null]);
        //     }

        //     if (!empty($_POST['stroke_heart_attack'])) {
        //         $med_state4->execute([$family_history_id, 5, null]);
        //     }

        //     if (!empty($_POST['depression'])) {
        //         $med_state4->execute([$family_history_id, 6, null]);
        //     }

        //     if (!empty($_POST['kidney_problems'])) {
        //         $med_state4->execute([$family_history_id, 7, null]);
        //     }

        //     if (!empty($_POST['other_condition_check'])) {
        //         $med_state4->execute([
        //             $family_history_id,
        //             8,
        //             $_POST['other_condition'] ?? null
        //         ]);
        //     }

        // }

        // $med_state5 = $pdo->prepare('INSERT INTO medical_conditions (medical_id, has_conditions) VALUES (?,?)');
        // $med_state5->execute([
        //     $medical_id,
        //     $_POST['has_medical_condition'] ?? 0
        // ]);

        // $condtion_group_id = $pdo->lastInsertId();
        
        // $med_state6 = $pdo->prepare('INSERT INTO student_conditions (condition_group_id, condition_type_id, description) VALUES (?,?,?)');
        // if ($_POST['has_medical_condition'] == "1") {

        //     if (!empty($_POST['error_of_refraction'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             1,
        //             $_POST['error_of_refraction']
        //         ]);
        //     }

        //     if (!empty($_POST['asthma'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             2,
        //             $_POST['asthma']
        //         ]);
        //     }

        //     if (!empty($_POST['seizure'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             3,
        //             $_POST['seizure']
        //         ]);
        //     }

        //     if (!empty($_POST['heart_illness'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             4,
        //             $_POST['heart_illness']
        //         ]);
        //     }

        //     if (!empty($_POST['anemia'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             5,
        //             $_POST['anemia']
        //         ]);
        //     }

        //     if (!empty($_POST['bleeding_disorder'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             6,
        //             $_POST['bleeding_disorder']
        //         ]);
        //     }

        //     if (!empty($_POST['fracture_dislocation'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             7,
        //             $_POST['fracture_dislocation']
        //         ]);
        //     }

        //     if (!empty($_POST['other_medical_condition'])) {
        //         $med_state6->execute([
        //             $condtion_group_id,
        //             8,
        //             $_POST['other_medical_condition_text'] ?? null
        //         ]);
        //     }

        // }

        // $med_state7 = $pdo->prepare('INSERT INTO medical_surgeries (medical_id, has_surgery, surgery_date, hospital_name, body_part) VALUES (?,?,?,?,?)');  
        // $med_state7->execute([
        //     $medical_id,
        //     $_POST['has_surgery_hospitalization'] ?? 0,
        //     $_POST['surgery_date'] ?? null,
        //     $_POST['hospital_name'] ?? null,
        //     $_POST['body_part'] ?? null
        // ]);

        // $med_state8 = $pdo->prepare('INSERT INTO medical_treatments (medical_id, is_taking_treatment, treatment_medicine, schedule_dosage) VALUES (?,?,?,?)');
        // $med_state8->execute([
        //     $medical_id,
        //     $_POST['is_taking_treatment'] ?? 0,
        //     $_POST['treatment_medicine'] ?? null,
        //     $_POST['schedule_dosage'] ?? null
        // ]);

    }       


 // $state1 =$pdo->prepare('INSERT INTO current_address (student_id, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?,?,?,?,?,?,?,?)');

        // $state1->execute([
        //     $student_id,
        //     $_POST['Current_House_No'] ?? null,
        //     $_POST['Current_Street_Name'] ?? null,
        //     $_POST['Current_Barangay'] ?? null,
        //     $_POST['Current_Municipality_City'] ?? null,
        //     $_POST['Current_Province'] ?? null,
        //     $_POST['Current_Country'] ?? null,
        //     $_POST['Current_Zip_Code'] ?? null
        // ]);

        // if (isset($_POST['same_address']) && $_POST['same_address'] === 'Yes') {

        //     $permanent_house   = $_POST['Current_House_No'] ?? null;
        //     $permanent_street  = $_POST['Current_Street_Name'] ?? null;
        //     $permanent_barangay= $_POST['Current_Barangay'] ?? null;
        //     $permanent_city    = $_POST['Current_Municipality_City'] ?? null;
        //     $permanent_province= $_POST['Current_Province'] ?? null;
        //     $permanent_country  = $_POST['Current_Country'] ?? null;
        //     $permanent_zip     = $_POST['Current_Zip_Code'] ?? null;

        // } else {

        //     $permanent_house   = $_POST['Permanent_House_No'] ?? null;
        //     $permanent_street  = $_POST['Permanent_Street_Name'] ?? null;
        //     $permanent_barangay= $_POST['Permanent_Barangay'] ?? null;
        //     $permanent_city    = $_POST['Permanent_Municipality_City'] ?? null;
        //     $permanent_province= $_POST['Permanent_Province'] ?? null;
        //     $permanent_country  = $_POST['Permanent_Country'] ?? null;
        //     $permanent_zip     = $_POST['Permanent_Zip_Code'] ?? null;
        // }

        // $state2 = $pdo->prepare('INSERT INTO permanent_address (student_id, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?,?,?,?,?,?,?,?)');       

        // $state2->execute([
        //     $student_id,
        //     $permanent_house,
        //     $permanent_street,
        //     $permanent_barangay,
        //     $permanent_city,
        //     $permanent_province,
        //     $permanent_country,
        //     $permanent_zip
        // ]);


        // $state3 = $pdo->prepare('INSERT INTO parent_guardian_information (student_id, father_last_name, father_first_name, father_middle_name, father_contact_number, mother_last_name, mother_first_name, mother_middle_name, mother_contact_number, guardian_last_name, guardian_first_name, guardian_middle_name, guardian_contact_number) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');

        // $state3->execute([
        //     $student_id,
        //     $_POST['Father_Last_Name'] ?? null,
        //     $_POST['Father_First_Name'] ?? null,
        //     $_POST['Father_Middle_Name'] ?? null, 
        //     $_POST['Father_Contact_Number'] ?? null,
        //     $_POST['Mother_Last_Name'] ?? null,
        //     $_POST['Mother_First_Name'] ?? null,
        //     $_POST['Mother_Middle_Name'] ?? null,
        //     $_POST['Mother_Contact_Number'] ?? null,
        //     $_POST['Guardian_Last_Name'] ?? null,
        //     $_POST['Guardian_First_Name'] ?? null,      
        //     $_POST['Guardian_Middle_Name'] ?? null,
        //     $_POST['Guardian_Contact_Number'] ?? null
        // ]);     

        // $state4 = $pdo->prepare('INSERT INTO returning_learner_information (student_id, last_grade_level_completed, last_school_attended, last_school_year_completed, school_id) VALUES (?,?,?,?,?)');

        // $state4->execute([
        //     $student_id,
        //     $_POST['Last_Grade_Level_Completed'] ?? null,
        //     $_POST['Last_School_Attended'] ?? null,
        //     $_POST['Last_School_Year_Completed'] ?? null,
        //     $_POST['school_ID'] ?? null
        // ]);

        // //Medical Part
        // $med_state = $pdo->prepare('INSERT INTO medical_information (student_id, parent_guardian_name, contact_number, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?,?,?,?,?)');
        // $med_state->execute([
        //     $student_id,
        //     $_POST['parent_guardian_name'] ?? null,
        //     $_POST['contact_number'] ?? null,
        //     $_POST['exposed_to_cigarette_vape_smoke'] ?? null,
        //     $_POST['other_pertinent_information'] ?? null
        // ]);

        // $medical_id = $pdo->lastInsertId();

        // $med_state1 = $pdo->prepare('INSERT INTO medical_allergies (medical_id, has_allergies, medicine_allergy, pollen_allergy, food_allergy, other_allergy) VALUES (?,?,?,?,?,?)');
        // $med_state1->execute([
        //     $medical_id,
        //     $_POST['has_allergies'] ?? 0,
        //     $_POST['medicine_allergy'] ?? '',
        //     $_POST['pollen_allergy'] ?? 0,
        //     $_POST['food_allergy'] ?? '',
        //     $_POST['other_allergy'] ?? ''
        // ]);

        // $med_state2 = $pdo->prepare('INSERT INTO medical_conditions (medical_id, has_medical_condition, error_of_refraction, asthma, seizure, heart_illness, anemia, bleeding_disorder, fracture_dislocation, other_condition) VALUES (?,?,?,?,?,?,?,?,?,?)');
        // $med_state2->execute([
        //     $medical_id,
        //     $_POST['has_medical_condition'] ?? 0,
        //     $_POST['error_of_refraction'] ?? 0,
        //     $_POST['asthma'] ?? 0,
        //     $_POST['seizure'] ?? 0,
        //     $_POST['heart_illness'] ?? 0,
        //     $_POST['anemia'] ?? 0,
        //     $_POST['bleeding_disorder'] ?? 0,
        //     $_POST['fracture_dislocation'] ?? 0,
        //     $_POST['other_condition'] ?? ''
        // ]);

        // $med_state3 = $pdo->prepare('INSERT INTO medical_surgery_hospitalization (medical_id, has_surgery_hospitalization, surgery_date, hospital_name, body_part) VALUES (?,?,?,?,?)');
        // $med_state3->execute([  
        //     $medical_id,
        //     $_POST['has_surgery_hospitalization'] ?? 0,
        //     $_POST['surgery_date'] ?? '',
        //     $_POST['hospital_name'] ?? '',
        //     $_POST['body_part'] ?? ''
        // ]);

        // $med_state4 = $pdo->prepare('INSERT INTO medical_treatment_medicines (medical_id, is_currently_taking_treatment, treatment_medicine, schedule_dosage) VALUES (?,?,?,?)');
        // $med_state4->execute([          
        //     $medical_id,
        //     $_POST['is_currently_taking_treatment'] ?? 0,
        //     $_POST['treatment_medicine'] ?? 0,
        //     $_POST['schedule_dosage'] ?? ''
        // ]);

        // $med_state5 = $pdo->prepare('INSERT INTO family_medical_history (medical_id, has_family_medical_history, tuberculosis, cancer, cancer_type, diabetes_mellitus, hypertension, stroke_heart_attack, depression, kidney_problems, other_condition) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        // $med_state5->execute([              
        //     $medical_id,
        //     $_POST['has_family_medical_history'] ?? 0,
        //     $_POST['tuberculosis'] ?? 0,
        //     $_POST['cancer'] ?? 0,
        //     $_POST['cancer_type'] ?? 0,
        //     $_POST['diabetes_mellitus'] ?? 0,
        //     $_POST['hypertension'] ?? 0,
        //     $_POST['stroke_heart_attack'] ?? 0,
        //     $_POST['depression'] ?? 0,
        //     $_POST['kidney_problems'] ?? 0,
        //     $_POST['other_condition'] ?? ''
        // ]);





// if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//     // ── SCHOOL YEAR ───────────────────────────────────────────────
//     $school_year = ($_POST['year_start'] ?? '') . '-' . ($_POST['year_end'] ?? '');

//     // ── INDIGENOUS GROUP ──────────────────────────────────────────
//     $ip       = $_POST['ip'] ?? 'No';
//     $ip_value = ($ip === 'Yes') ? ($_POST['IP_Specify'] ?? '') : null;

//     // ── 4Ps BENEFICIARY ───────────────────────────────────────────
//     $fourps       = $_POST['fourps'] ?? 'No';
//     $fourps_value = ($fourps === 'Yes') ? ($_POST['FourPs_Specify'] ?? '') : null;

//     // ── DISABILITY ────────────────────────────────────────────────
//     $disability       = $_POST['disability'] ?? 'No';
//     $disability_types = [];

//     if ($disability === 'Yes' && !empty($_POST['disability_type'])) {
//         $disability_types = array_map('intval', $_POST['disability_type']);
//     }

//     $birth_date = $_POST['Birth_Date'] ?? null;
//     $age = null;
//     if ($birth_date) {
//         $birth = new DateTime($birth_date);
//         $today = new DateTime();
//         $age = $today->diff($birth)->y;
//     }

//     $with_lrn = (($_POST['with_lrn'] ?? 'No') === 'Yes') ? 1 : 0;
//     $returning = (($_POST['returning'] ?? 'No') === 'Yes') ? 1 : 0;
//     $sex = strtolower($_POST['sex'] ?? '');
//     $fourps_value = ($fourps === 'Yes') ? ($_POST['FourPs_Specify'] ?? '') : 'No';
//     $learner_with_disability = !empty($disability_types) ? 'Yes' : 'No';

//     // ── STUDENTS ──────────────────────────────────────────────────
//     $state = $pdo->prepare('INSERT INTO students (
//         school_year, grade_level, with_lrn, `returning`,
//         psa_bcn, lrn, last_name, first_name, middle_name,
//         extension_name, birth_date, sex, place_of_birth,
//         age, mother_tongue, indigenous_group, `4p_beneficiary`,
//         is_learner_with_disability
//     ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

//     $state->execute([
//         $school_year,
//         $_POST['Grade_Level']               ?? '',
//         $with_lrn,
//         $returning,
//         $_POST['PSA_Birth_Certificate_No']  ?? '',
//         $_POST['Learner_Reference_No']      ?? '',
//         $_POST['Learner_Last_Name']         ?? '',
//         $_POST['Learner_First_Name']        ?? '',
//         $_POST['Learner_Middle_Name']       ?? null,
//         $_POST['Learner_Extension_Name']    ?? null,
//         $birth_date,
//         $sex,
//         $_POST['Place_of_Birth']            ?? '',
//         $age ?? 0,
//         $_POST['Mother_Tongue']             ?? '',
//         $ip_value,
//         $fourps_value,
//         $learner_with_disability
//     ]);

//     $student_id = $pdo->lastInsertId();

//     // ── STUDENT DISABILITIES ──────────────────────────────────────
//     if (!empty($disability_types)) {
//         $dis_stmt = $pdo->prepare('INSERT INTO student_disabilities (student_id, disability_type_id) VALUES (?, ?)');
//         foreach ($disability_types as $type_id) {
//             $dis_stmt->execute([$student_id, $type_id]);
//         }
//     }

//     // ── CURRENT ADDRESS ───────────────────────────────────────────
//     $state1 = $pdo->prepare('INSERT INTO current_address (
//         student_id, house_no, street_name, barangay,
//         municipality_city, province, country, zip_code
//     ) VALUES (?,?,?,?,?,?,?,?)');

//     $state1->execute([
//         $student_id,
//         $_POST['Current_House_No']          ?? null,
//         $_POST['Current_Street_Name']       ?? '',
//         $_POST['Current_Barangay']          ?? '',
//         $_POST['Current_Municipality_City'] ?? '',
//         $_POST['Current_Province']          ?? '',
//         $_POST['Current_Country']           ?? 'Philippines',
//         $_POST['Current_Zip_Code']          ?? null
//     ]);

//     // ── PERMANENT ADDRESS ─────────────────────────────────────────
//     if (isset($_POST['same_address']) && $_POST['same_address'] === 'Yes') {
//         $perm_house    = $_POST['Current_House_No']          ?? null;
//         $perm_street   = $_POST['Current_Street_Name']       ?? '';
//         $perm_barangay = $_POST['Current_Barangay']          ?? '';
//         $perm_city     = $_POST['Current_Municipality_City'] ?? '';
//         $perm_province = $_POST['Current_Province']          ?? '';
//         $perm_country  = $_POST['Current_Country']           ?? 'Philippines';
//         $perm_zip      = $_POST['Current_Zip_Code']          ?? null;
//     } else {
//         $perm_house    = $_POST['Permanent_House_No']          ?? null;
//         $perm_street   = $_POST['Permanent_Street_Name']       ?? '';
//         $perm_barangay = $_POST['Permanent_Barangay']          ?? '';
//         $perm_city     = $_POST['Permanent_Municipality_City'] ?? '';
//         $perm_province = $_POST['Permanent_Province']          ?? '';
//         $perm_country  = $_POST['Permanent_Country']           ?? 'Philippines';
//         $perm_zip      = $_POST['Permanent_Zip_Code']          ?? null;
//     }

//     $state2 = $pdo->prepare('INSERT INTO permanent_address (
//         student_id, house_no, street_name, barangay,
//         municipality_city, province, country, zip_code
//     ) VALUES (?,?,?,?,?,?,?,?)');

//     $state2->execute([
//         $student_id,
//         $perm_house, $perm_street, $perm_barangay,
//         $perm_city,  $perm_province, $perm_country, $perm_zip
//     ]);

//     // ── PARENT / GUARDIAN ─────────────────────────────────────────
//     $parent_id = $pdo->lastInsertId();

//         if ($parent_id) {
//             $link = $pdo->prepare('INSERT INTO student_parents (student_id, parent_id) VALUES (?,?)');
//             $link->execute([$student_id, $parent_id]);
//         }
//             return $parent_id;
        
//         $father_id = insertParent(
//         $pdo, $student_id, 'father',
//         $_POST['father_last_name'] ?? '',
//         $_POST['father_first_name'] ?? '',
//         $_POST['father_middle_name'] ?? '',
//         $_POST['father_contact_number'] ?? ''
//         );

//         $mother_id = insertParent(
//         $pdo, $student_id, 'mother',
//         $_POST['mother_last_name'] ?? '',
//         $_POST['mother_first_name'] ?? '',
//         $_POST['mother_middle_name'] ?? '',
//         $_POST['mother_contact_number'] ?? ''
//         );

//         $guardian_id = insertParent(
//         $pdo, $student_id, 'guardian',
//         $_POST['guardian_last_name'] ?? '',
//         $_POST['guardian_first_name'] ?? '',
//         $_POST['guardian_middle_name'] ?? '',
//         $_POST['guardian_contact_number'] ?? ''
//         );

//         $parent_id = $father_id ?? $mother_id ?? $guardian_id;

//         if (!$parent_id) {
//             $parent_id = null;
//         }


//     $father_id   = insertParent($pdo, $student_id, 'father',
//         $_POST['father_last_name']    ?? '', $_POST['father_first_name']    ?? '',
//         $_POST['father_middle_name']  ?? '', $_POST['father_contact_number'] ?? ''
//     );
//     $mother_id   = insertParent($pdo, $student_id, 'mother',
//         $_POST['mother_last_name']    ?? '', $_POST['mother_first_name']    ?? '',
//         $_POST['mother_middle_name']  ?? '', $_POST['mother_contact_number'] ?? ''
//     );
//     $guardian_id = insertParent($pdo, $student_id, 'guardian',
//         $_POST['guardian_last_name']    ?? '', $_POST['guardian_first_name']    ?? '',
//         $_POST['guardian_middle_name']  ?? '', $_POST['guardian_contact_number'] ?? ''
//     );

//     $linked_parent_id = $father_id ?? $mother_id ?? $guardian_id;

//     // ── RETURNING LEARNER ─────────────────────────────────────────
//     $is_returning = $_POST['returning'] ?? 'No';

//     if ($is_returning === 'Yes') {
//         $state4 = $pdo->prepare('INSERT INTO returning_learner_information (
//             student_id, last_grade_level_completed,
//             last_school_attended, last_school_year_completed, school_id
//         ) VALUES (?,?,?,?,?)');

//         $state4->execute([
//             $student_id,
//             $_POST['Returning_Grade_Level']      ?? '',
//             $_POST['Last_School_Attended']       ?? '',
//             $_POST['Last_School_Year_Completed'] ?? '',
//             $_POST['School_ID']                  ?? null
//         ]);
//     }
//     //Medical Part
//     $med_state = $pdo->prepare('INSERT INTO medical_information (student_id, parent_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?,?,?,?)');
//     $med_state->execute([
//         $student_id,
//         $parent_id,
//         $_POST['exposed_to_cigarette_vape_smoke'] ?? '',
//         $_POST['other_pertinent_information'] ?? ''
//     ]);

//     $medical_id = $pdo->lastInsertId();

//     $med_state1 = $pdo->prepare('INSERT INTO medical_allergies (medical_id, has_allergies, medicine_allergy, pollen_allergy, food_allergy, other_allergy) VALUES (?,?,?,?,?,?)');
//     $med_state1->execute([
//         $medical_id,
//         $_POST['has_allergies'] ?? '',
//         $_POST['medicine_allergy'] ?? NULL,
//         $_POST['pollen_allergy'] ?? 0,
//         $_POST['food_allergy'] ?? '',
//         $_POST['other_allergy'] ?? ''
//     ]);

//     $med_state2 = $pdo->prepare('INSERT INTO medical_conditions (medical_id, has_medical_condition, error_of_refraction, asthma, seizure, heart_illness, anemia, bleeding_disorder, fracture_dislocation, other_condition) VALUES (?,?,?,?,?,?,?,?,?,?)');
//     $med_state2->execute([
//         $medical_id,
//         $_POST['has_medical_condition'] ?? 0,
//         $_POST['error_of_refraction'] ?? 0,
//         $_POST['asthma'] ?? 0,
//         $_POST['seizure'] ?? 0,
//         $_POST['heart_illness'] ?? 0,
//         $_POST['anemia'] ?? 0,
//         $_POST['bleeding_disorder'] ?? 0,
//         $_POST['fracture_dislocation'] ?? 0,
//         $_POST['other_condition'] ?? ''
//     ]);

//     $med_state3 = $pdo->prepare('INSERT INTO medical_surgery_hospitalization (medical_id, has_surgery_hospitalization, surgery_date, hospital_name, body_part) VALUES (?,?,?,?,?)');
//     $med_state3->execute([  
//         $medical_id,
//         $_POST['has_surgery_hospitalization'] ?? '',
//         $_POST['surgery_date'] ?? '',
//         $_POST['hospital_name'] ?? '',
//         $_POST['body_part'] ?? ''
//     ]);

//     $med_state4 = $pdo->prepare('INSERT INTO medical_treatment_medicines (medical_id, is_currently_taking_treatment, treatment_medicine, schedule_dosage) VALUES (?,?,?,?)');
//     $med_state4->execute([          
//         $medical_id,
//         $_POST['is_currently_taking_treatment'] ?? '',
//         $_POST['treatment_medicine'] ?? '',
//         $_POST['schedule_dosage'] ?? ''
//     ]);

//     $med_state5 = $pdo->prepare('INSERT INTO family_medical_history (medical_id, has_family_medical_history, tuberculosis, cancer, cancer_type, diabetes_mellitus, hypertension, stroke_heart_attack, depression, kidney_problems, other_condition) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
//     $med_state5->execute([              
//         $medical_id,
//         $_POST['has_family_medical_history'] ?? 0,
//         $_POST['tuberculosis'] ?? 0,
//         $_POST['cancer'] ?? 0,
//         $_POST['cancer_type'] ?? 0,
//         $_POST['diabetes_mellitus'] ?? 0,
//         $_POST['hypertension'] ?? 0,
//         $_POST['stroke_heart_attack'] ?? 0,
//         $_POST['depression'] ?? 0,
//         $_POST['kidney_problems'] ?? 0,
//         $_POST['other_condition'] ?? ''
//     ]);


//     ── STORE student_id in session for medical.php ───────────────
//     session_start();
//     $_SESSION['student_id'] = $student_id;
//     $_SESSION['linked_parent_id'] = $linked_parent_id;

//     header('Location: medical.php');
//     exit;
// } 