<?php
require_once __DIR__ . '/../../pdf/vendor/autoload.php';

use Classes\GeneratePDF;

include "enroll_config.php";

// if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $student_id = $_GET['student_id'] ?? 1;

    function fetchOne($pdo, $table, $student_id) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE student_id = ? LIMIT 1");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    $student = fetchOne($pdo, 'students', $student_id);
    $enrollment = fetchOne($pdo, 'enrollments', $student_id);
    $current = fetchOne($pdo, 'current_address', $student_id);
    $permanent = fetchOne($pdo, 'permanent_address', $student_id);
    $parents = fetchOne($pdo, 'parent_guardian_information', $student_id);
    $returning_learner = fetchOne($pdo, 'returning_learner_information', $student_id);

    $mental_disability = array_map('trim', explode(',', $student['is_learner_with_disability']));

echo '<pre>';
echo "STUDENT:\n";
print_r($student);

echo "\nSELECTED DISABILITIES:\n";
print_r($mental_disability);

echo "\nENROLLMENT:\n";
print_r($enrollment);

echo "\nCURRENT ADDRESS:\n";
print_r($current);

echo "\nPERMANENT ADDRESS:\n";
print_r($permanent);

echo "\nPARENTS:\n";
print_r($parents);

echo "\nRETURNING LEARNER:\n";
print_r($returning_learner);
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
    'lrn' => $student['lrn'],
    'school_year' => $student['school_year'],

    'grade_level' => $formattedGrade,

    'with_lrn_yes' => $student['with_lrn'] == 1 ? 'Yes' : '',
    'with_lrn_no' => $student['with_lrn'] == 0 ? 'Yes' : '',
    'returning_yes' => $student['returning'] == 1 ? 'Yes' : '',
    'returning_no' => $student['returning'] == 0 ? 'Yes' : '',
    'psa_bcn' => $student['psa_bcn'],
    'last_name' => strtoupper($student['last_name']),
    'first_name' => strtoupper($student['first_name']),
    'middle_name' => strtoupper($student['middle_name']),
    'extension_name' => strtoupper($student['extension_name']),
    'birth_date' => $student['birth_date'],
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
    'is_learner_with_disability_yes' => !empty($student['mental_disability']) ? 'Yes' : '',
    'is_learner_with_disability_no' => empty($student['mental_disability']) ? 'Yes' : '',

    // 'disab_12' => in_array('12', $mental_disability) ? 'Yes' : '',
    'low_vision'        => isset($has['3']) ? 'Yes' : '',
    'blind'             => isset($has['2']) ? 'Yes' : '',
    'visual_impairment' => (isset($has['1']) || isset($has['3'])) ? 'Yes' : '',

    'hearing_impairment' => isset($has['4']) ? 'Yes' : '',
    'autism_spectrum_disorder' => isset($has['5']) ? 'Yes' : '',
    'speech_language_disorder' => isset($has['6']) ? 'Yes' : '',
    'learning_disorder' => isset($has['7']) ? 'Yes' : '',
    'emotional_behavioral_disorder' => isset($has['8']) ? 'Yes' : '',
    'cerebral_palsy' => isset($has['9']) ? 'Yes' : '',
    'intellectual_disorder' => isset($has['10']) ? 'Yes' : '',
    'orthopedic_physical_handicap' => isset($has['11']) ? 'Yes' : '',

    'cancer' => isset($has['13']) ? 'Yes' : '',
    'special_health_problem' => (isset($has['12']) || isset($has['13'])) ? 'Yes' : '',

    'multiple_disorder' => count($mental_disability) > 1 ? 'Yes' : '',

    'same_address_yes' => $sameAddress ? 'Yes' : '',
    'same_address_no' => !$sameAddress ? 'Yes' : '',
    'house_no' => $current['house_no'],
    'street_name' => strtoupper($current['street_name']),
    'barangay' => strtoupper($current['barangay']),
    'municipality_city' => strtoupper($current['municipality_city']),
    'province' => strtoupper($current['province']),
    'country' => strtoupper($current['country']),
    'zip_code' => $current['zip_code'],

    'house_nop' => $permanent['house_no'],
    'street_namep' => strtoupper($permanent['street_name']),
    'barangayp' => strtoupper($permanent['barangay']),
    'municipality_cityp' => strtoupper($permanent['municipality_city']),
    'provincep' => strtoupper($permanent['province']),
    'countryp' => strtoupper($permanent['country']),
    'zip_codep' => $permanent['zip_code'],

    'father_last_name' => strtoupper($parents['father_last_name']),
    'father_first_name' => strtoupper($parents['father_first_name']),
    'father_middle_name' => strtoupper($parents['father_middle_name']),
    'father_contact_number' => $parents['father_contact_number'],

    'mother_last_name' => strtoupper($parents['mother_last_name']),
    'mother_first_name' => strtoupper($parents['mother_first_name']),
    'mother_middle_name' => strtoupper($parents['mother_middle_name']),
    'mother_contact_number' => $parents['mother_contact_number'],

    'guardian_last_name' => strtoupper($parents['guardian_last_name']),
    'guardian_first_name' => strtoupper($parents['guardian_first_name']),
    'guardian_middle_name' => strtoupper($parents['guardian_middle_name']),
    'guardian_contact_number' => $parents['guardian_contact_number'],

    'last_grade_level_completed' => $returning_formattedGrade,
    'last_school_attended' => strtoupper($returning_learner['last_school_attended']),
    'last_school_year_completed' => $returning_learner['last_school_year_completed'],
    'school_id' => $returning_learner['school_id']
];

$pdf = new GeneratePDF;
try {
    $response = $pdf->generate($data);
    echo "PDF generated: {$response}";
} catch (Throwable $e) {
    echo 'PDF generation failed: ' . $e->getMessage();
}
?>