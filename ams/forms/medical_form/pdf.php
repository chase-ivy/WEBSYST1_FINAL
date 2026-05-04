<?php
require_once __DIR__ . '/../../pdf/vendor/autoload.php';

use Classes\GeneratePDF;

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_GET['student_id'])) {
    echo "No student ID provided.";
    exit;
}

$student_id = intval($_GET['student_id']);

function fetchParents($pdo, $enrollment_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, ep.relationship
        FROM parents p
        JOIN enrollment_parents ep ON p.parent_id = ep.parent_id
        WHERE ep.enrollment_id = ?
    ");
    $stmt->execute([$enrollment_id]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['relationship']] = $row;
    }
    return $result;
}

function fetchOne($pdo, $table, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE student_id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchLatestEnrollment($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? ORDER BY enrollment_id DESC LIMIT 1");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchAddress($pdo, $enrollment_id, $type) {
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE enrollment_id = ? AND address_type = ? LIMIT 1");
    $stmt->execute([$enrollment_id, $type]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchReturningLearner($pdo, $enrollment_id) {
    $stmt = $pdo->prepare('SELECT * FROM returning_learners WHERE enrollment_id = ? LIMIT 1');
    $stmt->execute([$enrollment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function computeAge($birth_date) {
    if (empty($birth_date)) {
        return '';
    }
    return (new DateTime($birth_date))->diff(new DateTime('today'))->y;
}

$students = fetchOne($pdo, 'students', $student_id);
$enrollment = fetchLatestEnrollment($pdo, $student_id);
$parents = fetchParents($pdo, $enrollment['enrollment_id'] ?? 0);
$allergies = fetchOne($pdo, 'medical_allergies', $student_id);
$current = fetchAddress($pdo, $enrollment['enrollment_id'] ?? 0, 'current');
$permanent = fetchAddress($pdo, $enrollment['enrollment_id'] ?? 0, 'permanent');
$conditions = fetchOne($pdo, 'medical_conditions', $student_id);
$surguries = fetchOne($pdo, 'medical_surgery_hospitalization', $student_id);
$treatments = fetchOne($pdo, 'medical_treatment_medicines', $student_id);
$histories = fetchOne($pdo, 'family_medical_history', $student_id);
$informations = fetchOne($pdo, 'medical_information', $student_id);
$returning_learner = fetchReturningLearner($pdo, $enrollment['enrollment_id'] ?? 0);

$has = [];

    $gradeMap = [
        'Kinder'  => 'KD',
        'Grade 1' => '01',
        'Grade 2' => '02',
        'Grade 3' => '03',
        'Grade 4' => '04',
        'Grade 5' => '05',
        'Grade 6' => '06',
    ];
    $grade = trim($enrollment['grade_level'] ?? '');
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