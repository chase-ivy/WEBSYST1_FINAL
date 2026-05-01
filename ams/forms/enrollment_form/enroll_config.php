<?php
include '../../config/config.php';

// Enrollment form helper functions and insert logic can use the shared $pdo from master config.

?>

<?php
    $year_start = $_POST['year_start'] ?? '';
    $year_end   = $_POST['year_end'] ?? '';


    $school_year = $year_start . '-' . $year_end;

    // This is for the IP field.
    $ip = $_POST['ip'] ?? null;

    if ($ip === "Yes") {
        $ip_value = $_POST['IP_Specify'] ?? null;
    } else {
        $ip_value = "No";
    }


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
        $disability_value = implode(',', $_POST['disabilityDetails']);
    } else {
        $disability_value = "0";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $state = $pdo->prepare('INSERT INTO students (school_year, grade_level, 
        with_lrn, `returning`, psa_bcn, lrn, last_name, first_name, middle_name,
        extension_name, birth_date, sex, place_of_birth, age, mother_tongue, indigenous_group,
        `4p_benificiary`, is_learner_with_disability) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        
        $state->execute([
        $school_year,
        $_POST['Grade_Level'] ?? null,
        $_POST['with_lrn'] ?? null,
        $_POST['returning'] ?? null,
        $_POST['PSA_Birth_Certificate_No'] ?? null,
        $_POST['Learner_Reference_No'] ?? null,
        $_POST['Learner_Last_Name'] ?? null,
        $_POST['Learner_First_Name'] ?? null,
        $_POST['Learner_Middle_Name'] ?? null,
        $_POST['Learner_Extension_Name'] ?? null,
        $_POST['Birth_Date'] ?? null,
        $_POST['sex'] ?? null,
        $_POST['Place_of_Birth'] ?? null,
        $_POST['Age'] ?? null,
        $_POST['Mother_Tongue'] ?? null,
        $ip_value,
        $fourps_value,
        $disability_value
        ]);
        
        $student_id = $pdo->lastInsertId();

        $state1 =$pdo->prepare('INSERT INTO current_address (student_id, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?,?,?,?,?,?,?,?)');

        $state1->execute([
            $student_id,
            $_POST['Current_House_No'] ?? null,
            $_POST['Current_Street_Name'] ?? null,
            $_POST['Current_Barangay'] ?? null,
            $_POST['Current_Municipality_City'] ?? null,
            $_POST['Current_Province'] ?? null,
            $_POST['Current_Country'] ?? null,
            $_POST['Current_Zip_Code'] ?? null
        ]);

        if (isset($_POST['same_address']) && $_POST['same_address'] === 'Yes') {

            $permanent_house   = $_POST['Current_House_No'] ?? null;
            $permanent_street  = $_POST['Current_Street_Name'] ?? null;
            $permanent_barangay= $_POST['Current_Barangay'] ?? null;
            $permanent_city    = $_POST['Current_Municipality_City'] ?? null;
            $permanent_province= $_POST['Current_Province'] ?? null;
            $permanent_country  = $_POST['Current_Country'] ?? null;
            $permanent_zip     = $_POST['Current_Zip_Code'] ?? null;

        } else {

            $permanent_house   = $_POST['Permanent_House_No'] ?? null;
            $permanent_street  = $_POST['Permanent_Street_Name'] ?? null;
            $permanent_barangay= $_POST['Permanent_Barangay'] ?? null;
            $permanent_city    = $_POST['Permanent_Municipality_City'] ?? null;
            $permanent_province= $_POST['Permanent_Province'] ?? null;
            $permanent_country  = $_POST['Permanent_Country'] ?? null;
            $permanent_zip     = $_POST['Permanent_Zip_Code'] ?? null;
        }

        $state2 = $pdo->prepare('INSERT INTO permanent_address (student_id, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?,?,?,?,?,?,?,?)');       

        $state2->execute([
            $student_id,
            $permanent_house,
            $permanent_street,
            $permanent_barangay,
            $permanent_city,
            $permanent_province,
            $permanent_country,
            $permanent_zip
        ]);


        $state3 = $pdo->prepare('INSERT INTO parent_guardian_information (student_id, father_last_name, father_first_name, father_middle_name, father_contact_number, mother_last_name, mother_first_name, mother_middle_name, mother_contact_number) VALUES (?,?,?,?,?,?,?,?,?)');

        $state3->execute([
            $student_id,
            $_POST['Father_Last_Name'] ?? null,
            $_POST['Father_First_Name'] ?? null,
            $_POST['Father_Middle_Name'] ?? null, 
            $_POST['Father_Contact_Number'] ?? null,
            $_POST['Mother_Last_Name'] ?? null,
            $_POST['Mother_First_Name'] ?? null,
            $_POST['Mother_Middle_Name'] ?? null,
            $_POST['Mother_Contact_Number'] ?? null
        ]); 

        $state4 = $pdo->prepare('INSERT INTO returning_learner_information (student_id, last_grade_level_completed, last_school_attended, last_school_year_completed, school_id) VALUES (?,?,?,?,?)');

        $state4->execute([
            $student_id,
            $_POST['Last_Grade_Level_Completed'] ?? null,
            $_POST['Last_School_Attended'] ?? null,
            $_POST['Last_School_Year_Completed'] ?? null,
            $_POST['school_ID'] ?? null
        ]);
    }       
    

?>
