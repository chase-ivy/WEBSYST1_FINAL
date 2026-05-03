<?php
require_once __DIR__ . '/../../pdf/vendor/autoload.php';

use Classes\GeneratePDF;

require_once __DIR__ . '/../../config/config.php';

// if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $student_id = $_GET['student_id'] ?? 1;

    function fetchParents($pdo, $student_id) {
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM parents p
            JOIN student_parents sp ON p.parent_id = sp.parent_id
            WHERE sp.student_id = ?
        ");
        $stmt->execute([$student_id]);
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['parent_type']] = $row;
        }
        return $result;
    }

    function fetchOne($pdo, $table, $student_id) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE student_id = ? LIMIT 1");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    $students = fetchOne($pdo, 'students', $student_id);
    $parents = fetchParents($pdo, $student_id);  
    $allergies = fetchOne($pdo, 'medical_allergies', $student_id);
    $addresses = fetchOne($pdo, 'current_address', $student_id);
    $conditions = fetchOne($pdo, 'medical_conditions', $student_id);
    $surguries = fetchOne($pdo, 'medical_surgery_hospitalization', $student_id);
    $treatments = fetchOne($pdo, 'medical_treatment_medicines', $student_id);
    $histories = fetchOne($pdo, 'family_medical_history', $student_id);
    $informations = fetchOne($pdo, 'medical_information', $student_id);

    echo '<pre>';
    echo "STUDENTs:\n";
    print_r($students);

    echo "\nPARENTS:\n";
    print_r($parents);

    echo "\nALLERGIES:\n";
    print_r($allergies);

    echo "\nADDRESS:\n";
    print_r($addresses);

    echo "\nCONDITIONS:\n";
    print_r($conditions);

    echo "\nSURGURIES:\n";
    print_r($surguries);
    
    echo "\nTREATMENTS:\n";
    print_r($treatments);
    echo '</pre>';

    echo "\nHISTORIES:\n";
    print_r($histories);
    echo '</pre>';

    echo "\nINFORMATIONS:\n";
    print_r($informations);
    echo '</pre>';

    } else {
        echo "No student ID provided.";
    }
    //parent child relationship
    $has = array_flip($mental_disability);

    $gradeMap = [
        'Kinder'  => 'KD',
        'Grade 1' => '01',
        'Grade 2' => '02',
        'Grade 3' => '03',
        'Grade 4' => '04',
        'Grade 5' => '05',
        'Grade 6' => '06',
    ];
    $grade = trim($student['grade_level'] ?? '');
    $formattedGrade = $gradeMap[$grade] ?? '';

    $returning_grade = trim($returning_learner['last_grade_level_completed'] ?? '');
    $returning_formattedGrade = $gradeMap[$returning_grade] ?? '';

    $currentAddr = [
        'house_no'     => trim($current['house_no'] ?? ''),
        'street_name'  => trim($current['street_name'] ?? ''),
        'barangay'     => trim($current['barangay'] ?? ''),
        'municipality' => trim($current['municipality'] ?? ''),
        'province'     => trim($current['province'] ?? ''),
        'country'      => trim($current['country'] ?? ''),
        'zip_code'     => trim($current['zip_code'] ?? ''),
    ];
    $permanentAddr = [
        'house_no'     => trim($permanent['house_no'] ?? ''),
        'street_name'  => trim($permanent['street_name'] ?? ''),
        'barangay'     => trim($permanent['barangay'] ?? ''),
        'municipality' => trim($permanent['municipality'] ?? ''),
        'province'     => trim($permanent['province'] ?? ''),
        'country'      => trim($permanent['country'] ?? ''),
        'zip_code'     => trim($permanent['zip_code'] ?? ''),
    ];
    $sameAddress = ($currentAddr === $permanentAddr);

    $data = [
        // '' => $(''),
        'full_name' => strtoupper($students['last_name']) . ', ' . strtoupper($students['first_name']) . ' ' . strtoupper($students['middle_name']) . ' ' . strtoupper($students['extension_name']),
        'grade_level' => $formattedGrade,
        'birth_date' => $students['birth_date'],
        'age' => $students['age'],
        'sex' => $students['sex'],
        //parentguardian name anc contact number needs an arr list

        'with_lrn_yes' => $allergies['has_allergies'] == 1 ? 'Yes' : '',
        'with_lrn_no' => $allergies['has_allergies'] == 0 ? 'Yes' : '',
        
        'returning_yes' => $student['returning'] == 1 ? 'Yes' : '',
        'returning_no' => $student['returning'] == 0 ? 'Yes' : '',
        'psa_bcn' => $student['psa_bcn'],
        'last_name' => strtoupper($student['last_name']),
        'first_name' => strtoupper($student['first_name']),
        'middle_name' => strtoupper($student['middle_name']),
        'extension_name' => strtoupper($student['extension_name']),
        'sex_male' => $student['sex'] == 'male' ? 'Yes' : '',
        'sex_female' => $student['sex'] == 'female' ? 'Yes' : '',
        'place_of_birth' => strtoupper($student['place_of_birth']),
        'age' => $student['age'],
        'mother_tongue' => strtoupper($student['mother_tongue'])   ,
        '4ps_benificiary' => strtoupper($student['4p_benificiary']),
        '4ps_yes' => !empty($student['4p_benificiary']) ? 'Yes' : '',
        '4ps_no' => empty($student['4p_benificiary']) ? 'Yes' : '',
        'indigenous_group' => strtoupper($student['indigenous_group']),
        'ip_yes' => !empty($student['indigenous_group']) ? 'Yes' : '',
        'ip_no' => empty($student['indigenous_group']) ? 'Yes' : '',

        'father_last_name' => strtoupper($parents['father']['last_name']),
        'father_first_name' => strtoupper($parents['father']['first_name']),
        'father_middle_name' => strtoupper($parents['father']['middle_name']),
        'father_contact_number' => $parents['father']['contact_number'],

        'mother_last_name' => strtoupper($parents['mother']['last_name']),
        'mother_first_name' => strtoupper($parents['mother']['first_name']),
        'mother_middle_name' => strtoupper($parents['mother']['middle_name']),
        'mother_contact_number' => $parents['mother']['contact_number'],

        'guardian_last_name' => strtoupper($parents['guardian']['last_name']),
        'guardian_first_name' => strtoupper($parents['guardian']['first_name']),
        'guardian_middle_name' => strtoupper($parents['guardian']['middle_name']),
        'guardian_contact_number' => $parents['guardian']['contact_number']

    ];

    $pdf = new GeneratePDF;
    try {
        $response = $pdf->generate($data);
        echo "PDF generated: {$response}";
    } catch (Throwable $e) {
        echo 'PDF generation failed: ' . $e->getMessage();
    }
?>