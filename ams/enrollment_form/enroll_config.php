<?php

// PLEASE DO NOT REMOVE THIS.
$host = "localhost";
$db = "gems_db";
$user = "root";
$pass = "";
$char = "utf8mb4";


$dsn = "mysql:host=$host;dbname=$db;charset=$char";

$option = [
    PDO:: ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION,
    PDO:: ATTR_DEFAULT_FETCH_MODE => PDO:: FETCH_ASSOC,
    PDO:: ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO ($dsn, $user, $pass, $option);
}catch (PDOException $e){
    echo "Connection failed" . $e -> getMessage();
}

?>
//ENROLLMENT SIDE
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

        $state1 =$pdo->prepare('INSERT INTO current_address (student_id, house_no, street_name, barangay, municipality_city, province, zip_code) VALUES (?,?,?,?,?,?,?)');

        $state1->execute([
            $student_id,
            $_POST['Current_House_No'] ?? null,
            $_POST['Current_Street_Name'] ?? null,
            $_POST['Current_Barangay'] ?? null,
            $_POST['Current_Municipality_City'] ?? null,
            $_POST['Current_Province'] ?? null,
            $_POST['Current_Zip_Code'] ?? null
        ]);
    }       
    

?>
