<?php
require_once __DIR__ . '/../../pdf/vendor/autoload.php';

use Classes\GeneratePDF;

include "enroll_config.php";

// if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // $student_id = 20187546;

    // $student_id = $_GET['student_id'];

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

$data = [
    // '' => $(''),
    'lrn' => $student['student_id'],
    'school_year' => $student['school_year'],
    'grade_level' => $student['grade_level'],
    'with_lrn_yes' => $student['with_lrn'] == 1 ? 'Yes' : '',
    'with_lrn_no' => $student['with_lrn'] == 0 ? 'Yes' : '',
    'returning_yes' => $student['returning'] == 1 ? 'Yes' : '',
    'returning_no' => $student['returning'] == 0 ? 'Yes' : '',
    'psa_bcn' => $student['psa_bcn'],
    'lrn' => $student['lrn'],
    'last_name' => $student['last_name'],
    'first_name' => $student['first_name'],
    'middle_name' => $student['middle_name'],
    'extension_name' => $student['extension_name'],
    'birth_date' => $student['birth_date'],
    'sex_male' => $student['sex'] == 'Male' ? 'Yes' : '',
    'sex_female' => $student['sex'] == 'Female' ? 'Yes' : '',
    'place_of_birth' => $student['place_of_birth'],
    'age' => $student['age'],
    'mother_tongue' => $student['mother_tongue'],
    '4p_benificiary' => !empty($student['4p_benificiary']) ? 'Yes' : '',
    '4p_yes' => !empty($student['4p_benificiary']) ? 'Yes' : '',
    '4p_no' => empty($student['4p_benificiary']) ? 'Yes' : '',
    'indigenous_group' => $student['indigenous_group'],
    'indigenous_group_yes' => !empty($student['indigenous_group']) ? 'Yes' : '',
    'indigenous_group_no' => empty($student['indigenous_group']) ? 'Yes' : '',

    // 'disab_12' => in_array('12', $mental_disability) ? 'Yes' : '',
];

$pdf = new GeneratePDF;
try {
    $response = $pdf->generate($data);
    echo "PDF generated: {$response}";
} catch (Throwable $e) {
    echo 'PDF generation failed: ' . $e->getMessage();
}
?>
