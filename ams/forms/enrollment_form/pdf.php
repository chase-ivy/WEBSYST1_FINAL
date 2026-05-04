<?php
require_once __DIR__ . '/../../pdf/vendor/autoload.php';

use Classes\GeneratePDF;

require_once __DIR__ . '/../../config/config.php';

// if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_GET['student_id'])) {
//     echo "No student ID provided.";
//     exit;
// }

// $student_id = intval($_GET['student_id']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo "No student ID provided.";
    exit;
}

$student_id = intval($_GET['student_id'] ?? 2);

// ── FETCH HELPERS ─────────────────────────────────────────────

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

function fetchReturningLearner($pdo, $enrollment_id) {
    $stmt = $pdo->prepare("SELECT * FROM returning_learners WHERE enrollment_id = ? LIMIT 1");
    $stmt->execute([$enrollment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchDisabilities($pdo, $enrollment_id) {
    $stmt = $pdo->prepare("SELECT disability_type_id FROM student_disabilities WHERE enrollment_id = ?");
    $stmt->execute([$enrollment_id]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'disability_type_id');
}

// FIX: medical_information links by enrollment_id, not student_id
function fetchMedicalInfo($pdo, $enrollment_id) {
    $stmt = $pdo->prepare("SELECT * FROM medical_information WHERE enrollment_id = ? LIMIT 1");
    $stmt->execute([$enrollment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// FIX: age is NOT stored in the new DB — compute it from birth_date
function computeAge($birth_date) {
    if (empty($birth_date)) return '';
    return (new DateTime($birth_date))->diff(new DateTime('today'))->y;
}

// ── FETCH ALL ─────────────────────────────────────────────────

$student          = fetchOne($pdo, 'students', $student_id);
$enrollment       = fetchLatestEnrollment($pdo, $student_id);
$enrollment_id    = $enrollment['enrollment_id'] ?? 0;

$current          = fetchAddress($pdo, $enrollment_id, 'current');
$permanent        = fetchAddress($pdo, $enrollment_id, 'permanent');
$parents          = fetchParents($pdo, $enrollment_id);
$returning_learner = fetchReturningLearner($pdo, $enrollment_id);
$disability_ids   = fetchDisabilities($pdo, $enrollment_id);
$medical          = fetchMedicalInfo($pdo, $enrollment_id);

$has = array_flip($disability_ids);

print_r($student);
print_r($enrollment);
print_r($enrollment_id);
print_r($current);
print_r($permanent);
print_r($parents);
print_r($returning_learner);
print_r($disability_ids);
print_r($medical);
print_r($has);

// ── GRADE MAP ─────────────────────────────────────────────────

$gradeMap = [
    'Kinder'  => 'KD',
    'Grade 1' => '01', 'Grade 2' => '02', 'Grade 3' => '03',
    'Grade 4' => '04', 'Grade 5' => '05', 'Grade 6' => '06',
];

$grade                    = trim($enrollment['grade_level'] ?? '');
$formattedGrade           = $gradeMap[$grade] ?? '';
$returning_grade          = trim($returning_learner['last_grade_level_completed'] ?? '');
$returning_formattedGrade = $gradeMap[$returning_grade] ?? '';

// ── ADDRESS COMPARISON ────────────────────────────────────────

// FIX: column is municipality_city, not municipality
$currentAddr = [
    'house_no'     => trim($current['house_no']          ?? ''),
    'street_name'  => trim($current['street_name']       ?? ''),
    'barangay'     => trim($current['barangay']          ?? ''),
    'municipality' => trim($current['municipality_city'] ?? ''),
    'province'     => trim($current['province']          ?? ''),
    'country'      => trim($current['country']           ?? ''),
    'zip_code'     => trim($current['zip_code']          ?? ''),
];
$permanentAddr = [
    'house_no'     => trim($permanent['house_no']          ?? ''),
    'street_name'  => trim($permanent['street_name']       ?? ''),
    'barangay'     => trim($permanent['barangay']          ?? ''),
    'municipality' => trim($permanent['municipality_city'] ?? ''),
    'province'     => trim($permanent['province']          ?? ''),
    'country'      => trim($permanent['country']           ?? ''),
    'zip_code'     => trim($permanent['zip_code']          ?? ''),
];
$sameAddress = ($currentAddr === $permanentAddr);

// ── DATA MAP ──────────────────────────────────────────────────

$data = [
    'lrn'         => $student['lrn']              ?? '',
    'school_year' => $enrollment['school_year']   ?? '',
    'grade_level' => $formattedGrade,

    // FIX: use enrollment flags, not student table
    'with_lrn_yes'  => ($enrollment['with_lrn'] ?? 0) == 1 ? 'Yes' : '',
    'with_lrn_no'   => ($enrollment['with_lrn'] ?? 0) == 0 ? 'Yes' : '',
    'returning_yes' => ($enrollment['is_returning_learner'] ?? 0) == 1 ? 'Yes' : '',
    'returning_no'  => ($enrollment['is_returning_learner'] ?? 0) == 0 ? 'Yes' : '',

    'psa_bcn'        => $enrollment['psa_bcn']            ?? '',
    'last_name'      => strtoupper($student['last_name']   ?? ''),
    'first_name'     => strtoupper($student['first_name']  ?? ''),
    'middle_name'    => strtoupper($student['middle_name'] ?? ''),
    'extension_name' => strtoupper($student['extension_name'] ?? ''),
    'birth_date'     => $student['birth_date'] ?? '',

    // FIX: sex enum is 'Male'/'Female' (capitalized) in new DB
    'sex_male'   => ($student['sex'] ?? '') === 'Male'   ? 'Yes' : '',
    'sex_female' => ($student['sex'] ?? '') === 'Female' ? 'Yes' : '',

    'place_of_birth' => strtoupper($student['place_of_birth'] ?? ''),

    // FIX: age is computed, not stored
    'age' => computeAge($student['birth_date'] ?? ''),

    // FIX: mother_tongue is on enrollments, not students
    'mother_tongue' => strtoupper($enrollment['mother_tongue'] ?? ''),

    // FIX: 4Ps fields are on enrollments
    '4ps_benificiary'      => !empty($enrollment['is_four_ps_beneficiary']) ? 'Yes' : 'No',
    '4ps_yes'              => !empty($enrollment['is_four_ps_beneficiary']) ? 'Yes' : '',
    '4ps_no'               => empty($enrollment['is_four_ps_beneficiary'])  ? 'Yes' : '',
    'four_ps_household_id' => strtoupper($enrollment['four_ps_household_id'] ?? ''),

    // FIX: IP fields are on enrollments
    'indigenous_group' => strtoupper($enrollment['indigenous_group'] ?? ''),
    'ip_yes'           => !empty($enrollment['is_indigenous']) ? 'Yes' : '',
    'ip_no'            => empty($enrollment['is_indigenous'])  ? 'Yes' : '',

    // Disabilities — from student_disabilities table
    'is_learner_with_disability_yes' => !empty($disability_ids) ? 'Yes' : '',
    'is_learner_with_disability_no'  => empty($disability_ids)  ? 'Yes' : '',
    'visual_impairment'              => (isset($has[1]) || isset($has[2]) || isset($has[3])) ? 'Yes' : '',
    'blind'                          => isset($has[2])  ? 'Yes' : '',
    'low_vision'                     => isset($has[3])  ? 'Yes' : '',
    'hearing_impairment'             => isset($has[4])  ? 'Yes' : '',
    'autism_spectrum_disorder'       => isset($has[5])  ? 'Yes' : '',
    'speech_language_disorder'       => isset($has[6])  ? 'Yes' : '',
    'learning_disorder'              => isset($has[7])  ? 'Yes' : '',
    'emotional_behavioral_disorder'  => isset($has[8])  ? 'Yes' : '',
    'cerebral_palsy'                 => isset($has[9])  ? 'Yes' : '',
    'intellectual_disorder'          => isset($has[10]) ? 'Yes' : '',
    'orthopedic_physical_handicap'   => isset($has[11]) ? 'Yes' : '',
    'special_health_problem'         => (isset($has[12]) || isset($has[13])) ? 'Yes' : '',
    'cancer'                         => isset($has[13]) ? 'Yes' : '',
    'multiple_disorder'              => count($disability_ids) > 1 ? 'Yes' : '',

    // Address
    'same_address_yes'  => $sameAddress  ? 'Yes' : '',
    'same_address_no'   => !$sameAddress ? 'Yes' : '',
    'house_no'          => $current['house_no']                    ?? '',
    'street_name'       => strtoupper($current['street_name']      ?? ''),
    'barangay'          => strtoupper($current['barangay']         ?? ''),
    'municipality_city' => strtoupper($current['municipality_city'] ?? ''),
    'province'          => strtoupper($current['province']         ?? ''),
    'country'           => strtoupper($current['country']          ?? ''),
    'zip_code'          => $current['zip_code']                    ?? '',

    'house_nop'          => $permanent['house_no']                    ?? '',
    'street_namep'       => strtoupper($permanent['street_name']      ?? ''),
    'barangayp'          => strtoupper($permanent['barangay']         ?? ''),
    'municipality_cityp' => strtoupper($permanent['municipality_city'] ?? ''),
    'provincep'          => strtoupper($permanent['province']         ?? ''),
    'countryp'           => strtoupper($permanent['country']          ?? ''),
    'zip_codep'          => $permanent['zip_code']                    ?? '',

    // Parents
    'father_last_name'        => strtoupper($parents['father']['last_name']    ?? ''),
    'father_first_name'       => strtoupper($parents['father']['first_name']   ?? ''),
    'father_middle_name'      => strtoupper($parents['father']['middle_name']  ?? ''),
    'father_contact_number'   => $parents['father']['contact_number']          ?? '',

    'mother_last_name'        => strtoupper($parents['mother']['last_name']    ?? ''),
    'mother_first_name'       => strtoupper($parents['mother']['first_name']   ?? ''),
    'mother_middle_name'      => strtoupper($parents['mother']['middle_name']  ?? ''),
    'mother_contact_number'   => $parents['mother']['contact_number']          ?? '',

    'guardian_last_name'      => strtoupper($parents['guardian']['last_name']  ?? ''),
    'guardian_first_name'     => strtoupper($parents['guardian']['first_name'] ?? ''),
    'guardian_middle_name'    => strtoupper($parents['guardian']['middle_name'] ?? ''),
    'guardian_contact_number' => $parents['guardian']['contact_number']        ?? '',

    // Returning learner
    'last_grade_level_completed' => $returning_formattedGrade,
    'last_school_attended'       => strtoupper($returning_learner['last_school_attended']    ?? ''),
    'last_school_year_completed' => $returning_learner['last_school_year_completed']         ?? '',
    'school_id'                  => $returning_learner['school_id']                          ?? '',
];

$pdf = new GeneratePDF;
try {
    $response = $pdf->generate($data);
    echo "PDF generated: {$response}";
} catch (Throwable $e) {
    echo 'PDF generation failed: ' . $e->getMessage();
}