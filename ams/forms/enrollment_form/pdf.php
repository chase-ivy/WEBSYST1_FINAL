<?php
require_once __DIR__ . '/../../pdf/vendor/autoload.php';

use Classes\GeneratePDF;

require_once __DIR__ . '/../../config/config.php';

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

function fetchMedicalInfo($pdo, $enrollment_id) {
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
    $stmt = $pdo->prepare("SELECT * FROM returning_learners WHERE enrollment_id = ? LIMIT 1");
    $stmt->execute([$enrollment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchStudentAllergies($pdo, $allergy_group_id) {
    $stmt = $pdo->prepare("
        SELECT sa.*, at.name as allergy_type_name
        FROM student_allergies sa
        JOIN allergy_types at ON sa.allergy_type_id = at.allergy_type_id
        WHERE sa.allergy_group_id = ?
    ");
    $stmt->execute([$allergy_group_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchStudentConditions($pdo, $condition_group_id) {
    $stmt = $pdo->prepare("
        SELECT sc.*, ct.name as condition_name
        FROM student_conditions sc
        JOIN condition_types ct ON sc.condition_type_id = ct.condition_type_id
        WHERE sc.condition_group_id = ?
    ");
    $stmt->execute([$condition_group_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchStudentFamilyConditions($pdo, $family_history_id) {
    $stmt = $pdo->prepare("
        SELECT sfc.*, fct.name as condition_name
        FROM student_family_conditions sfc
        JOIN family_condition_types fct ON sfc.family_condition_type_id = fct.family_condition_type_id
        WHERE sfc.family_history_id = ?
    ");
    $stmt->execute([$family_history_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function computeAge($birth_date) {
    if (empty($birth_date)) return '';
    return (new DateTime($birth_date))->diff(new DateTime('today'))->y;
}

function safeUpper($value) {
    return strtoupper(trim($value ?? ''));
}

function chk($condition) {
    return $condition ? 'Yes' : 'Off';
}

// ---------------------------------------------------------------------------
// Fetch all shared data (single DB pass for both forms)
// ---------------------------------------------------------------------------

$student       = fetchStudent($pdo, $student_id);
$enrollment    = fetchLatestEnrollment($pdo, $student_id);
$enrollment_id = $enrollment['enrollment_id'] ?? 0;

$parents           = fetchParents($pdo, $enrollment_id);
$current           = fetchAddress($pdo, $enrollment_id, 'current');
$permanent         = fetchAddress($pdo, $enrollment_id, 'permanent');
$returning_learner = fetchReturningLearner($pdo, $enrollment_id);

$medical_info = fetchMedicalInfo($pdo, $enrollment_id);
$medical_id   = $medical_info['medical_id'] ?? 0;

$allergies  = fetchByMedicalId($pdo, 'medical_allergies',      $medical_id);
$conditions = fetchByMedicalId($pdo, 'medical_conditions',     $medical_id);
$surgeries  = fetchByMedicalId($pdo, 'medical_surgeries',      $medical_id);
$treatments = fetchByMedicalId($pdo, 'medical_treatments',     $medical_id);
$histories  = fetchByMedicalId($pdo, 'family_medical_history', $medical_id);

// Allergy detail rows
$allergy_group_id  = $allergies['allergy_group_id'] ?? 0;
$student_allergies = $allergy_group_id ? fetchStudentAllergies($pdo, $allergy_group_id) : [];

$allergyByType = [];
foreach ($student_allergies as $a) {
    $allergyByType[strtolower($a['allergy_type_name'])] = $a;
}

// Condition detail rows
$condition_group_id = $conditions['condition_group_id'] ?? 0;
$student_conditions = $condition_group_id ? fetchStudentConditions($pdo, $condition_group_id) : [];

$conditionByName = [];
foreach ($student_conditions as $c) {
    $conditionByName[strtolower($c['condition_name'])] = $c;
}

// Family condition detail rows
$family_history_id    = $histories['family_history_id'] ?? 0;
$student_family_conds = $family_history_id ? fetchStudentFamilyConditions($pdo, $family_history_id) : [];

$familyCondByName = [];
$familyCancerDesc = '';
$familyOtherDesc  = '';
foreach ($student_family_conds as $fc) {
    $key = strtolower($fc['condition_name']);
    $familyCondByName[$key] = $fc;
    if (str_contains($key, 'cancer')) $familyCancerDesc = $fc['description'] ?? '';
    if (str_contains($key, 'other'))  $familyOtherDesc  = $fc['description'] ?? '';
}

// Grade map
$gradeMap = [
    'Kinder'  => 'KD',
    'Grade 1' => '01', 'Grade 2' => '02', 'Grade 3' => '03',
    'Grade 4' => '04', 'Grade 5' => '05', 'Grade 6' => '06',
];
$formattedGrade           = $gradeMap[trim($enrollment['grade_level'] ?? '')] ?? '';
$returning_formattedGrade = $gradeMap[trim($returning_learner['last_grade_level_completed'] ?? '')] ?? '';

// Address arrays
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

// Full address string (single-line, used by medical form)
$fullAddress = implode(', ', array_filter([
    $currentAddr['house_no'],
    $currentAddr['street_name'],
    $currentAddr['barangay'],
    $currentAddr['municipality'],
    $currentAddr['province'],
]));

// Guardian display (prefer guardian → mother → father)
$guardian     = $parents['guardian'] ?? $parents['mother'] ?? $parents['father'] ?? [];
$guardianName = trim(
    safeUpper($guardian['last_name']   ?? '') . ', ' .
    safeUpper($guardian['first_name']  ?? '') . ' ' .
    safeUpper($guardian['middle_name'] ?? '')
);

// Surgery detail string
$surgery_detail = '';
if (!empty($surgeries['has_surgery'])) {
    $parts = array_filter([
        $surgeries['surgery_date']  ?? '',
        $surgeries['hospital_name'] ?? '',
        $surgeries['body_part']     ?? '',
    ]);
    $surgery_detail = implode(' / ', $parts);
}

// ===========================================================================
// BUILD DATA: ENROLLMENT FORM
// ===========================================================================

$enrollmentData = [
    // --- Student name ---
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
    'age'            => computeAge($student['birth_date']),
    'sex'            => $student['sex'] ?? '',
    'sex_male'       => (($student['sex'] ?? '') === 'Male')   ? 'Yes' : '',
    'sex_female'     => (($student['sex'] ?? '') === 'Female') ? 'Yes' : '',
    'place_of_birth' => safeUpper($student['place_of_birth']),

    // --- Grade level ---
    'grade_level'    => $formattedGrade,

    // --- Enrollment flags ---
    'psa_bcn'          => trim($enrollment['psa_bcn'] ?? ''),
    'with_lrn_yes'     => ($enrollment['with_lrn'] ?? 0) == 1 ? 'Yes' : '',
    'with_lrn_no'      => ($enrollment['with_lrn'] ?? 0) == 0 ? 'Yes' : '',
    'returning_yes'    => ($enrollment['is_returning_learner'] ?? 0) == 1 ? 'Yes' : '',
    'returning_no'     => ($enrollment['is_returning_learner'] ?? 0) == 0 ? 'Yes' : '',
    'mother_tongue'    => safeUpper($enrollment['mother_tongue']),
    '4ps_beneficiary'  => trim($enrollment['four_ps_household_id'] ?? ''),
    '4ps_yes'          => ($enrollment['is_four_ps_beneficiary'] ?? 0) == 1 ? 'Yes' : '',
    '4ps_no'           => ($enrollment['is_four_ps_beneficiary'] ?? 0) == 0 ? 'Yes' : '',
    'indigenous_group' => safeUpper($enrollment['indigenous_group']),
    'ip_yes'           => ($enrollment['is_indigenous'] ?? 0) == 1 ? 'Yes' : '',
    'ip_no'            => ($enrollment['is_indigenous'] ?? 0) == 0 ? 'Yes' : '',
    'learner_with_disability' => ($enrollment['is_learner_with_disability'] ?? 0) == 1 ? 'Yes' : '',

    // --- Returning learner ---
    'returning_grade_level'      => $returning_formattedGrade,
    'last_school_attended'       => safeUpper($returning_learner['last_school_attended']  ?? ''),
    'last_school_year_completed' => trim($returning_learner['last_school_year_completed'] ?? ''),
    'returning_school_id'        => trim($returning_learner['school_id']                  ?? ''),

    // --- Current address ---
    'current_house_no'     => $currentAddr['house_no'],
    'current_street'       => $currentAddr['street_name'],
    'current_barangay'     => $currentAddr['barangay'],
    'current_municipality' => $currentAddr['municipality'],
    'current_province'     => $currentAddr['province'],
    'current_country'      => $currentAddr['country'],
    'current_zip'          => $currentAddr['zip_code'],

    // --- Permanent address ---
    'same_address'           => $sameAddress ? 'Yes' : '',
    'permanent_house_no'     => $permanentAddr['house_no'],
    'permanent_street'       => $permanentAddr['street_name'],
    'permanent_barangay'     => $permanentAddr['barangay'],
    'permanent_municipality' => $permanentAddr['municipality'],
    'permanent_province'     => $permanentAddr['province'],
    'permanent_country'      => $permanentAddr['country'],
    'permanent_zip'          => $permanentAddr['zip_code'],

    // --- Father ---
    'father_last_name'      => safeUpper($parents['father']['last_name']   ?? ''),
    'father_first_name'     => safeUpper($parents['father']['first_name']  ?? ''),
    'father_middle_name'    => safeUpper($parents['father']['middle_name'] ?? ''),
    'father_contact_number' => trim($parents['father']['contact_number']   ?? ''),

    // --- Mother ---
    'mother_last_name'      => safeUpper($parents['mother']['last_name']   ?? ''),
    'mother_first_name'     => safeUpper($parents['mother']['first_name']  ?? ''),
    'mother_middle_name'    => safeUpper($parents['mother']['middle_name'] ?? ''),
    'mother_contact_number' => trim($parents['mother']['contact_number']   ?? ''),

    // --- Guardian ---
    'guardian_last_name'      => safeUpper($parents['guardian']['last_name']   ?? ''),
    'guardian_first_name'     => safeUpper($parents['guardian']['first_name']  ?? ''),
    'guardian_middle_name'    => safeUpper($parents['guardian']['middle_name'] ?? ''),
    'guardian_contact_number' => trim($parents['guardian']['contact_number']   ?? ''),
];

// ===========================================================================
// BUILD DATA: MEDICAL FORM
// ===========================================================================

$medicalData = [
    // --- Header info ---
    'lrn'        => trim($student['lrn'] ?? ''),
    'first_name' => safeUpper($student['first_name']),
    'last_name'  => safeUpper($student['last_name']),
    'full_name'                      => safeUpper($student['last_name']) . ', '
                                      . safeUpper($student['first_name']) . ' '
                                      . safeUpper($student['middle_name']) . ' '
                                      . safeUpper($student['extension_name']),
    'grade_level'                    => $formattedGrade,
    'birth_date'                     => $student['birth_date'] ?? '',
    'age'                            => computeAge($student['birth_date']),
    'sex'                            => $student['sex'] ?? '',
    'full_address'                   => $fullAddress,
    'parent_guardian_name'           => $guardianName,
    'parent_guardian_contact_number' => trim($guardian['contact_number'] ?? ''),

    // --- 1. Allergies ---
    'has_allergy_yes'          => chk(($allergies['has_allergies'] ?? 0) == 1),
    'has_allergy_no'           => chk(($allergies['has_allergies'] ?? 0) == 0),
    'medicine_allergy'         => chk(isset($allergyByType['medicine'])),
    'medicine_allergy_specify' => $allergyByType['medicine']['description'] ?? '',
    'pollen_allergy'           => chk(isset($allergyByType['pollen'])),
    'food_allergy'             => chk(isset($allergyByType['food'])),
    'food_allergy_specify'     => $allergyByType['food']['description'] ?? '',
    'other_allergy'            => chk(isset($allergyByType['other'])),
    'other_allergy_specify'    => $allergyByType['other']['description'] ?? '',

    // --- 2. Medical conditions ---
    'has_medical_condition_yes' => chk(($conditions['has_conditions'] ?? 0) == 1),
    'has_medical_condition_no'  => chk(($conditions['has_conditions'] ?? 0) == 0),
    'error_of_refraction'       => chk(isset($conditionByName['error of refraction (eye ailment)'])),
    'asthma'                    => chk(isset($conditionByName['asthma (lung ailment)'])),
    'seizure'                   => chk(isset($conditionByName['seizure (convulsions)'])),
    'anemia'                    => chk(isset($conditionByName['anemia'])),
    'bleeding_disorder'         => chk(isset($conditionByName['bleeding disorder'])),
    'fracture_dislocation'      => chk(isset($conditionByName['fracture / dislocation'])),
    'other_condition'           => chk(isset($conditionByName['other'])),
    'other_condition_specify'   => $conditionByName['other']['description'] ?? '',

    // --- 3. Surgery / hospitalization ---
    'has_surgery_hospitalization_yes' => chk(($surgeries['has_surgery'] ?? 0) == 1),
    'has_surgery_hospitalization_no'  => chk(($surgeries['has_surgery'] ?? 0) == 0),
    'surgery_hospitalization_detail'  => $surgery_detail,

    // --- 4. Treatment / medicines ---
    'is_currently_taking_treatment_yes' => chk(($treatments['is_taking_treatment'] ?? 0) == 1),
    'is_currently_taking_treatment_no'  => chk(($treatments['is_taking_treatment'] ?? 0) == 0),
    'treatment_medicine'                => trim($treatments['treatment_medicine'] ?? ''),
    'schedule_dosage'                   => trim($treatments['schedule_dosage']    ?? ''),

    // --- 5. Family medical history ---
    'tuberculosis'         => chk(isset($familyCondByName['tuberculosis'])),
    'cancer'               => chk(isset($familyCondByName['cancer'])),
    'cancer_type'          => $familyCancerDesc,
    'diabetes_mellitus'    => chk(isset($familyCondByName['diabetes mellitus'])),
    'hypertension'         => chk(isset($familyCondByName['hypertension'])),
    'stroke_heart_attack'  => chk(isset($familyCondByName['stroke / heart attack'])),
    'depression'           => chk(isset($familyCondByName['depression'])),
    'other_family_history' => $familyOtherDesc,

    // --- 6 & 7. Cigarette exposure & other info ---
    'exposed_to_cigarette_vape_smoke_yes' => chk(($medical_info['exposed_to_cigarette_vape_smoke'] ?? 0) == 1),
    'other_pertinent_information'         => trim($medical_info['other_pertinent_information'] ?? ''),
];

// ===========================================================================
// Generate both PDFs separately
// ===========================================================================

$generator = new GeneratePDF;
$results   = [];
$errors    = [];

try {
    $results['enrollment'] = $generator->generate($enrollmentData, 'enrollment');
} catch (Throwable $e) {
    $errors['enrollment'] = $e->getMessage();
}

try {
    $results['medical'] = $generator->generate($medicalData, 'medical');
} catch (Throwable $e) {
    $errors['medical'] = $e->getMessage();
}

foreach ($results as $type => $path) {
    echo ucfirst($type) . " PDF generated: {$path}\n";
}
foreach ($errors as $type => $msg) {
    echo ucfirst($type) . " PDF generation failed: {$msg}\n";
}
?>