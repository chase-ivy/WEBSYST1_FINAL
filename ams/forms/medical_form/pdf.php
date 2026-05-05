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

$student_id = intval($_GET['student_id'] ?? 1);

// ---------------------------------------------------------------------------
// Helper functions
// ---------------------------------------------------------------------------

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

function fetchStudent($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
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

function fetchByEnrollment($pdo, $table, $enrollment_id) {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE enrollment_id = ? LIMIT 1");
    $stmt->execute([$enrollment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchMedicalByEnrollment($pdo, $enrollment_id) {
    // medical_information is the parent; fetch it first, then join children by medical_id
    $stmt = $pdo->prepare("SELECT * FROM medical_information WHERE enrollment_id = ? LIMIT 1");
    $stmt->execute([$enrollment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchByMedicalId($pdo, $table, $medical_id) {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE medical_id = ? LIMIT 1");
    $stmt->execute([$medical_id]);
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

function safeUpper($value) {
    return strtoupper(trim($value ?? ''));
}

// ---------------------------------------------------------------------------
// Fetch all data
// ---------------------------------------------------------------------------

$student      = fetchStudent($pdo, $student_id);                          // FIX: was $students / used wrong var name
$enrollment   = fetchLatestEnrollment($pdo, $student_id);
$enrollment_id = $enrollment['enrollment_id'] ?? 0;

$parents          = fetchParents($pdo, $enrollment_id);
$current          = fetchAddress($pdo, $enrollment_id, 'current');
$permanent        = fetchAddress($pdo, $enrollment_id, 'permanent');
$returning_learner = fetchReturningLearner($pdo, $enrollment_id);

// Medical chain: information → allergies / conditions / surgeries / treatments / family history
$medical_info  = fetchMedicalByEnrollment($pdo, $enrollment_id);          // FIX: was wrong table + wrong key
$medical_id    = $medical_info['medical_id'] ?? 0;

$allergies     = fetchByMedicalId($pdo, 'medical_allergies',        $medical_id);  // FIX: now uses medical_id
$conditions    = fetchByMedicalId($pdo, 'medical_conditions',       $medical_id);  // FIX: now uses medical_id
$surgeries     = fetchByMedicalId($pdo, 'medical_surgeries',        $medical_id);  // FIX: correct table name
$treatments    = fetchByMedicalId($pdo, 'medical_treatments',       $medical_id);  // FIX: correct table name
$histories     = fetchByMedicalId($pdo, 'family_medical_history',   $medical_id);  // FIX: now uses medical_id

// ---------------------------------------------------------------------------
// Grade level map
// ---------------------------------------------------------------------------

$gradeMap = [
    'Kinder'  => 'KD',
    'Grade 1' => '01',
    'Grade 2' => '02',
    'Grade 3' => '03',
    'Grade 4' => '04',
    'Grade 5' => '05',
    'Grade 6' => '06',
];

$grade          = trim($enrollment['grade_level'] ?? '');
$formattedGrade = $gradeMap[$grade] ?? '';

$returning_grade          = trim($returning_learner['last_grade_level_completed'] ?? '');
$returning_formattedGrade = $gradeMap[$returning_grade] ?? '';

// ---------------------------------------------------------------------------
// Address helpers
// ---------------------------------------------------------------------------

$currentAddr = [
    'house_no'     => trim($current['house_no']          ?? ''),
    'street_name'  => trim($current['street_name']       ?? ''),
    'barangay'     => trim($current['barangay']          ?? ''),
    'municipality' => trim($current['municipality_city'] ?? ''),   // FIX: correct column name
    'province'     => trim($current['province']          ?? ''),
    'country'      => trim($current['country']           ?? ''),
    'zip_code'     => trim($current['zip_code']          ?? ''),
];

$permanentAddr = [
    'house_no'     => trim($permanent['house_no']          ?? ''),
    'street_name'  => trim($permanent['street_name']       ?? ''),
    'barangay'     => trim($permanent['barangay']          ?? ''),
    'municipality' => trim($permanent['municipality_city'] ?? ''),  // FIX: correct column name
    'province'     => trim($permanent['province']          ?? ''),
    'country'      => trim($permanent['country']           ?? ''),
    'zip_code'     => trim($permanent['zip_code']          ?? ''),
];

$sameAddress = ($currentAddr === $permanentAddr);

// ---------------------------------------------------------------------------
// Build $data array for PDF
// ---------------------------------------------------------------------------

$data = [
    // --- Student name (full and split) ---
    'full_name'      => safeUpper($student['last_name']) . ', '
                      . safeUpper($student['first_name']) . ' '
                      . safeUpper($student['middle_name']) . ' '
                      . safeUpper($student['extension_name']),
    'last_name'      => safeUpper($student['last_name']),
    'first_name'     => safeUpper($student['first_name']),
    'middle_name'    => safeUpper($student['middle_name']),
    'extension_name' => safeUpper($student['extension_name']),

    // --- Basic student info ---
    'lrn'            => trim($student['lrn'] ?? ''),
    'birth_date'     => $student['birth_date'] ?? '',
    'age'            => computeAge($student['birth_date']),              // FIX: use computeAge(); students table has no age column
    'sex'            => $student['sex'] ?? '',
    'sex_male'       => (($student['sex'] ?? '') === 'Male')   ? 'Yes' : '',   // FIX: DB stores 'Male'/'Female' (capitalised)
    'sex_female'     => (($student['sex'] ?? '') === 'Female') ? 'Yes' : '',
    'place_of_birth' => safeUpper($student['place_of_birth']),

    // --- Grade level ---
    'grade_level'    => $formattedGrade,

    // --- Enrollment flags (from enrollments table, not students) ---
    'psa_bcn'        => trim($enrollment['psa_bcn'] ?? ''),             // FIX: lives in enrollments

    'with_lrn_yes'   => ($enrollment['with_lrn'] ?? 0) == 1 ? 'Yes' : '',  // FIX: was checking allergies by mistake
    'with_lrn_no'    => ($enrollment['with_lrn'] ?? 0) == 0 ? 'Yes' : '',

    'returning_yes'  => ($enrollment['is_returning_learner'] ?? 0) == 1 ? 'Yes' : '',  // FIX: correct column
    'returning_no'   => ($enrollment['is_returning_learner'] ?? 0) == 0 ? 'Yes' : '',

    'mother_tongue'  => safeUpper($enrollment['mother_tongue']),         // FIX: lives in enrollments

    '4ps_beneficiary'=> trim($enrollment['four_ps_household_id'] ?? ''),
    '4ps_yes'        => ($enrollment['is_four_ps_beneficiary'] ?? 0) == 1 ? 'Yes' : '',  // FIX: correct column
    '4ps_no'         => ($enrollment['is_four_ps_beneficiary'] ?? 0) == 0 ? 'Yes' : '',

    'indigenous_group' => safeUpper($enrollment['indigenous_group']),    // FIX: lives in enrollments
    'ip_yes'         => ($enrollment['is_indigenous'] ?? 0) == 1 ? 'Yes' : '',
    'ip_no'          => ($enrollment['is_indigenous'] ?? 0) == 0 ? 'Yes' : '',

    'learner_with_disability' => ($enrollment['is_learner_with_disability'] ?? 0) == 1 ? 'Yes' : '',

    // --- Returning learner ---
    'returning_grade_level'      => $returning_formattedGrade,
    'last_school_attended'       => safeUpper($returning_learner['last_school_attended']     ?? ''),
    'last_school_year_completed' => trim($returning_learner['last_school_year_completed']    ?? ''),
    'returning_school_id'        => trim($returning_learner['school_id']                     ?? ''),

    // --- Current address ---
    'current_house_no'    => $currentAddr['house_no'],
    'current_street'      => $currentAddr['street_name'],
    'current_barangay'    => $currentAddr['barangay'],
    'current_municipality'=> $currentAddr['municipality'],
    'current_province'    => $currentAddr['province'],
    'current_country'     => $currentAddr['country'],
    'current_zip'         => $currentAddr['zip_code'],

    // --- Permanent address ---
    'same_address'          => $sameAddress ? 'Yes' : '',
    'permanent_house_no'    => $permanentAddr['house_no'],
    'permanent_street'      => $permanentAddr['street_name'],
    'permanent_barangay'    => $permanentAddr['barangay'],
    'permanent_municipality'=> $permanentAddr['municipality'],
    'permanent_province'    => $permanentAddr['province'],
    'permanent_country'     => $permanentAddr['country'],
    'permanent_zip'         => $permanentAddr['zip_code'],

    // --- Father ---
    'father_last_name'      => safeUpper($parents['father']['last_name']      ?? ''),
    'father_first_name'     => safeUpper($parents['father']['first_name']     ?? ''),
    'father_middle_name'    => safeUpper($parents['father']['middle_name']    ?? ''),
    'father_contact_number' => trim($parents['father']['contact_number']      ?? ''),

    // --- Mother ---
    'mother_last_name'      => safeUpper($parents['mother']['last_name']      ?? ''),
    'mother_first_name'     => safeUpper($parents['mother']['first_name']     ?? ''),
    'mother_middle_name'    => safeUpper($parents['mother']['middle_name']    ?? ''),
    'mother_contact_number' => trim($parents['mother']['contact_number']      ?? ''),

    // --- Guardian ---
    'guardian_last_name'      => safeUpper($parents['guardian']['last_name']    ?? ''),
    'guardian_first_name'     => safeUpper($parents['guardian']['first_name']   ?? ''),
    'guardian_middle_name'    => safeUpper($parents['guardian']['middle_name']  ?? ''),
    'guardian_contact_number' => trim($parents['guardian']['contact_number']    ?? ''),

    // --- Medical: allergies ---
    'has_allergies_yes' => ($allergies['has_allergies'] ?? 0) == 1 ? 'Yes' : '',
    'has_allergies_no'  => ($allergies['has_allergies'] ?? 0) == 0 ? 'Yes' : '',

    // --- Medical: conditions ---
    'has_conditions_yes' => ($conditions['has_conditions'] ?? 0) == 1 ? 'Yes' : '',
    'has_conditions_no'  => ($conditions['has_conditions'] ?? 0) == 0 ? 'Yes' : '',

    // --- Medical: surgery ---
    'has_surgery_yes'   => ($surgeries['has_surgery'] ?? 0) == 1 ? 'Yes' : '',
    'has_surgery_no'    => ($surgeries['has_surgery'] ?? 0) == 0 ? 'Yes' : '',
    'surgery_date'      => $surgeries['surgery_date']   ?? '',
    'hospital_name'     => safeUpper($surgeries['hospital_name'] ?? ''),
    'surgery_body_part' => safeUpper($surgeries['body_part']     ?? ''),

    // --- Medical: treatment ---
    'is_taking_treatment_yes' => ($treatments['is_taking_treatment'] ?? 0) == 1 ? 'Yes' : '',
    'is_taking_treatment_no'  => ($treatments['is_taking_treatment'] ?? 0) == 0 ? 'Yes' : '',
    'treatment_medicine'      => safeUpper($treatments['treatment_medicine'] ?? ''),
    'schedule_dosage'         => trim($treatments['schedule_dosage']         ?? ''),

    // --- Medical: family history ---
    'has_family_history_yes' => ($histories['has_family_history'] ?? 0) == 1 ? 'Yes' : '',
    'has_family_history_no'  => ($histories['has_family_history'] ?? 0) == 0 ? 'Yes' : '',

    // --- Medical: other info ---
    'exposed_to_smoke'          => ($medical_info['exposed_to_cigarette_vape_smoke'] ?? 0) == 1 ? 'Yes' : '',
    'other_pertinent_info'      => trim($medical_info['other_pertinent_information'] ?? ''),
];

// ---------------------------------------------------------------------------
// Generate PDF
// ---------------------------------------------------------------------------

$pdf = new GeneratePDF;
try {
    $response = $pdf->generate($data, 'medical');
    echo "PDF generated: {$response}";
} catch (Throwable $e) {
    echo 'PDF generation failed: ' . $e->getMessage();
}
?>