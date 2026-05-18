<?php
/**
 * pdf.php — Gibraltar Elementary School
 * Generates enrollment and/or medical PDF forms for a given student.
 *
 * GET params:
 *   student_id  (int, required)  — the student to generate for
 *   type        (string, opt)    — 'enrollment' | 'medical' | 'combined'
 *                                  omit to generate all three and print paths
 *   debug       (any, opt)       — dump assembled data as JSON instead of PDF
 */

declare(strict_types=1);

require_once __DIR__ . '/../../pdf/vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

if (!class_exists(Classes\GeneratePDF::class, false)) {
    require_once __DIR__ . '/../../pdf/GeneratePDF.php';
}

use Classes\GeneratePDF;

// ---------------------------------------------------------------------------
// Bootstrap checks
// ---------------------------------------------------------------------------

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Database connection not initialised. Check config.php.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed.');
}

$student_id = intval($_GET['student_id'] ?? 6);
if ($student_id <= 0) {
    http_response_code(400);
    exit('A valid student_id is required.');
}

$requestedType = isset($_GET['type']) ? trim($_GET['type']) : null;
$validTypes    = ['enrollment', 'medical', 'combined'];
if ($requestedType !== null && !in_array($requestedType, $validTypes, true)) {
    http_response_code(400);
    exit('Invalid type. Must be one of: ' . implode(', ', $validTypes));
}

// ---------------------------------------------------------------------------
// Pure helper functions
// ---------------------------------------------------------------------------

function up(string $value): string
{
    return strtoupper(trim($value));
}

function safe(mixed $value): string
{
    return trim((string)($value ?? ''));
}

function safeUp(mixed $value): string
{
    return up(safe($value));
}

/** Returns 'Yes' when $condition is true, 'Off' otherwise (PDF checkbox convention). */
function chk(bool $condition): string
{
    return $condition ? 'Yes' : 'Off';
}

function computeAge(mixed $birthDate): string
{
    if (empty($birthDate)) {
        return '';
    }
    try {
        return (string)(new DateTime($birthDate))->diff(new DateTime('today'))->y;
    } catch (Exception) {
        return '';
    }
}

// ---------------------------------------------------------------------------
// DB fetch functions — aligned to the actual schema
// ---------------------------------------------------------------------------

function fetchStudent(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ? LIMIT 1');
    $stmt->execute([$studentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Returns the latest enrollment for a student, with grade_level and
 * mother_tongue / indigenous_group resolved from their lookup tables.
 */
function fetchLatestEnrollment(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare("
        SELECT
            e.*,
            mt.name  AS mother_tongue,
            ig.name  AS indigenous_group
        FROM enrollments e
        LEFT JOIN mother_tongues   mt ON mt.mother_tongue_id   = e.mother_tongue_id
        LEFT JOIN indigenous_groups ig ON ig.indigenous_group_id = e.indigenous_group_id
        WHERE e.student_id = ?
        ORDER BY e.enrollment_id DESC
        LIMIT 1
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Fetches the school record linked to an enrollment.
 * This is the denormalised snapshot stored at enrolment time.
 */
function fetchSchoolRecord(PDO $pdo, int $enrollmentId): array
{
    $stmt = $pdo->prepare('
        SELECT * FROM student_school_records
        WHERE enrollment_id = ? LIMIT 1
    ');
    $stmt->execute([$enrollmentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Returns ['father' => [...], 'mother' => [...], 'guardian' => [...]]
 * keyed by normalised relationship name.
 */
function fetchParents(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare("
        SELECT spg.*, pgt.name AS relationship
        FROM   student_parent_guardians spg
        LEFT JOIN parent_guardian_types pgt
               ON pgt.parent_guardian_type_id = spg.parent_guardian_type_id
        WHERE  spg.student_id = ?
    ");
    $stmt->execute([$studentId]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rel = strtolower(trim($row['relationship'] ?? ''));
        if ($rel === '') continue;

        // Normalise to father / mother / guardian
        if (str_contains($rel, 'father'))   $rel = 'father';
        elseif (str_contains($rel, 'mother'))   $rel = 'mother';
        elseif (str_contains($rel, 'guardian')) $rel = 'guardian';

        $result[$rel] = $row;
    }
    return $result;
}

/**
 * Fetches current and permanent addresses for a student.
 * Returns ['current' => [...], 'permanent' => [...]]
 */
function fetchAddresses(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare("
        SELECT * FROM student_addresses
        WHERE student_id = ? AND address_type IN ('current','permanent')
    ");
    $stmt->execute([$studentId]);

    $result = ['current' => [], 'permanent' => []];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['address_type']] = $row;
    }
    return $result;
}

function fetchReturningLearner(PDO $pdo, int $enrollmentId): array
{
    $stmt = $pdo->prepare('
        SELECT * FROM enrollment_returning_learners
        WHERE enrollment_id = ? LIMIT 1
    ');
    $stmt->execute([$enrollmentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Returns disability rows with type/subtype names joined.
 * Uses enrollment_disabilities (correct table).
 */
function fetchDisabilities(PDO $pdo, int $enrollmentId): array
{
    $stmt = $pdo->prepare("
        SELECT
            ed.*,
            dt.name  AS disability_type,
            dst.name AS disability_subtype
        FROM enrollment_disabilities ed
        LEFT JOIN disability_types    dt  ON dt.disability_type_id    = ed.disability_type_id
        LEFT JOIN disability_subtypes dst ON dst.disability_subtype_id = ed.disability_subtype_id
        WHERE ed.enrollment_id = ?
    ");
    $stmt->execute([$enrollmentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** Returns the single medical_information row for an enrollment. */
function fetchMedicalInfo(PDO $pdo, int $enrollmentId): array
{
    $stmt = $pdo->prepare('
        SELECT * FROM enrollment_medical_information
        WHERE enrollment_id = ? LIMIT 1
    ');
    $stmt->execute([$enrollmentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Returns allergy rows keyed by lowercase type name.
 * e.g. ['medicine' => [...], 'food' => [...]]
 */
function fetchAllergies(PDO $pdo, int $medicalInfoId): array
{
    if (!$medicalInfoId) return [];

    $stmt = $pdo->prepare("
        SELECT ema.*, mat.name AS allergy_type_name
        FROM   enrollment_medical_allergies ema
        JOIN   medical_allergy_types mat ON mat.allergy_type_id = ema.allergy_type_id
        WHERE  ema.medical_information_id = ?
    ");
    $stmt->execute([$medicalInfoId]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = strtolower(trim($row['allergy_type_name'] ?? ''));
        if ($key !== '') $result[$key] = $row;
    }
    return $result;
}

/**
 * Returns condition rows keyed by lowercase type name.
 */
function fetchConditions(PDO $pdo, int $medicalInfoId): array
{
    if (!$medicalInfoId) return [];

    $stmt = $pdo->prepare("
        SELECT emc.*, mct.name AS condition_name
        FROM   enrollment_medical_conditions emc
        JOIN   medical_condition_types mct ON mct.condition_type_id = emc.condition_type_id
        WHERE  emc.medical_information_id = ?
    ");
    $stmt->execute([$medicalInfoId]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = strtolower(trim($row['condition_name'] ?? ''));
        if ($key !== '') $result[$key] = $row;
    }
    return $result;
}

/** Returns the surgery record for a medical_information row (at most one). */
function fetchSurgery(PDO $pdo, int $medicalInfoId): array
{
    if (!$medicalInfoId) return [];

    $stmt = $pdo->prepare('
        SELECT * FROM enrollment_medical_surgeries
        WHERE medical_information_id = ? LIMIT 1
    ');
    $stmt->execute([$medicalInfoId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/** Returns the treatment record for a medical_information row (at most one). */
function fetchTreatment(PDO $pdo, int $medicalInfoId): array
{
    if (!$medicalInfoId) return [];

    $stmt = $pdo->prepare('
        SELECT * FROM enrollment_medical_treatments
        WHERE medical_information_id = ? LIMIT 1
    ');
    $stmt->execute([$medicalInfoId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Returns family history rows keyed by lowercase type name.
 */
function fetchFamilyHistory(PDO $pdo, int $medicalInfoId): array
{
    if (!$medicalInfoId) return [];

    $stmt = $pdo->prepare("
        SELECT efmh.*, fmht.name AS condition_name
        FROM   enrollment_family_medical_history efmh
        JOIN   family_medical_history_types fmht
               ON fmht.family_history_type_id = efmh.family_history_type_id
        WHERE  efmh.medical_information_id = ?
    ");
    $stmt->execute([$medicalInfoId]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = strtolower(trim($row['condition_name'] ?? ''));
        if ($key !== '') $result[$key] = $row;
    }
    return $result;
}

// ---------------------------------------------------------------------------
// Grade display map
// ---------------------------------------------------------------------------

const GRADE_MAP = [
    'Kinder'  => 'KD',
    'Grade 1' => '01', 'Grade 2' => '02', 'Grade 3' => '03',
    'Grade 4' => '04', 'Grade 5' => '05', 'Grade 6' => '06',
];

function formatGrade(string $raw): string
{
    return GRADE_MAP[trim($raw)] ?? '';
}

// ---------------------------------------------------------------------------
// Fetch all data
// ---------------------------------------------------------------------------

$student       = fetchStudent($pdo, $student_id);
if (empty($student)) {
    http_response_code(404);
    exit("Student ID {$student_id} not found.");
}

$enrollment    = fetchLatestEnrollment($pdo, $student_id);
$enrollment_id = intval($enrollment['enrollment_id'] ?? 0);

$schoolRecord     = fetchSchoolRecord($pdo, $enrollment_id);
$parents          = fetchParents($pdo, $student_id);
$addresses        = fetchAddresses($pdo, $student_id);
$returningLearner = fetchReturningLearner($pdo, $enrollment_id);
$disabilities     = fetchDisabilities($pdo, $enrollment_id);

$medicalInfo   = fetchMedicalInfo($pdo, $enrollment_id);
$medicalInfoId = intval($medicalInfo['medical_information_id'] ?? 0);

$allergyByType    = fetchAllergies($pdo, $medicalInfoId);
$conditionByName  = fetchConditions($pdo, $medicalInfoId);
$surgery          = fetchSurgery($pdo, $medicalInfoId);
$treatment        = fetchTreatment($pdo, $medicalInfoId);
$familyCondByName = fetchFamilyHistory($pdo, $medicalInfoId);

// ---------------------------------------------------------------------------
// Derived shared values
// ---------------------------------------------------------------------------

$disabilityTypes    = array_map(
    fn($d) => strtolower(trim((string)($d['disability_type'] ?? ''))),
    $disabilities
);
$disabilitySubtypes = array_map(
    fn($d) => strtolower(trim((string)($d['disability_subtype'] ?? ''))),
    $disabilities
);

$currentAddr = [
    'house_no'     => safe($addresses['current']['house_no']          ?? ''),
    'street_name'  => safe($addresses['current']['street_name']       ?? ''),
    'barangay'     => safe($addresses['current']['barangay']          ?? ''),
    'municipality' => safe($addresses['current']['municipality_city'] ?? ''),
    'province'     => safe($addresses['current']['province']          ?? ''),
    'country'      => safe($addresses['current']['country']           ?? ''),
    'zip_code'     => safe($addresses['current']['zip_code']          ?? ''),
];

$permanentAddr = [
    'house_no'     => safe($addresses['permanent']['house_no']          ?? ''),
    'street_name'  => safe($addresses['permanent']['street_name']       ?? ''),
    'barangay'     => safe($addresses['permanent']['barangay']          ?? ''),
    'municipality' => safe($addresses['permanent']['municipality_city'] ?? ''),
    'province'     => safe($addresses['permanent']['province']          ?? ''),
    'country'      => safe($addresses['permanent']['country']           ?? ''),
    'zip_code'     => safe($addresses['permanent']['zip_code']          ?? ''),
];

$sameAddress = ($currentAddr === $permanentAddr);

$fullAddress = implode(', ', array_filter([
    $currentAddr['house_no'],
    $currentAddr['street_name'],
    $currentAddr['barangay'],
    $currentAddr['municipality'],
    $currentAddr['province'],
]));

// Preferred contact: guardian → mother → father
$guardian     = $parents['guardian'] ?? $parents['mother'] ?? $parents['father'] ?? [];
$guardianName = trim(
    safeUp($guardian['last_name']   ?? '') . ', ' .
    safeUp($guardian['first_name']  ?? '') . ' ' .
    safeUp($guardian['middle_name'] ?? '')
);

// Surgery detail string
$surgeryDetail = '';
if (!empty($surgery)) {
    $parts = array_filter([
        !empty($surgery['surgery_date'])
            ? date('m/d/Y', strtotime($surgery['surgery_date']))
            : '',
        safe($surgery['hospital_name'] ?? ''),
        safe($surgery['body_part']     ?? ''),
    ]);
    $surgeryDetail = implode(' / ', $parts);
}

// Family history extras
$familyCancerDesc = safe($familyCondByName['cancer']['description'] ?? '');
$familyOtherDesc  = safe($familyCondByName['other']['description']  ?? '');

$formattedGrade          = formatGrade(safe($enrollment['grade_level']                           ?? ''));
$returningFormattedGrade = formatGrade(safe($returningLearner['last_grade_level_completed']      ?? ''));

// ---------------------------------------------------------------------------
// Build enrollment data array
// ---------------------------------------------------------------------------

$enrollmentData = [
    // Student name
    'full_name'      => safeUp($student['last_name'])  . ', '
                      . safeUp($student['first_name']) . ' '
                      . safeUp($student['middle_name']   ?? '') . ' '
                      . safeUp($student['extension_name'] ?? ''),
    'last_name'      => safeUp($student['last_name']),
    'first_name'     => safeUp($student['first_name']),
    'middle_name'    => safeUp($student['middle_name']   ?? ''),
    'extension_name' => safeUp($student['extension_name'] ?? ''),

    // Basic student info
    'lrn'            => safe($student['lrn']            ?? ''),
    'birth_date'     => safe($student['birth_date']     ?? ''),
    'age'            => computeAge($student['birth_date'] ?? ''),
    'sex'            => safe($student['sex']             ?? ''),
    'sex_male'       => safe($student['sex'] ?? '') === 'Male'   ? 'Yes' : '',
    'sex_female'     => safe($student['sex'] ?? '') === 'Female' ? 'Yes' : '',
    'place_of_birth' => safeUp($student['place_of_birth'] ?? ''),

    // Grade / school year
    'grade_level' => $formattedGrade,
    'school_year' => safe($enrollment['school_year'] ?? ''),

    // Enrollment flags
    'psa_bcn'         => safe($enrollment['psa_bcn']                ?? ''),
    'with_lrn_yes'    => ($enrollment['with_lrn']            ?? 0) == 1 ? 'Yes' : '',
    'with_lrn_no'     => ($enrollment['with_lrn']            ?? 0) == 0 ? 'Yes' : '',
    'returning_yes'   => ($enrollment['is_returning_learner'] ?? 0) == 1 ? 'Yes' : '',
    'returning_no'    => ($enrollment['is_returning_learner'] ?? 0) == 0 ? 'Yes' : '',
    'mother_tongue'   => safeUp($enrollment['mother_tongue']   ?? ''),
    '4ps_benificiary' => safe($enrollment['four_ps_household_id'] ?? ''),
    '4ps_yes'         => ($enrollment['is_four_ps_beneficiary'] ?? 0) == 1 ? 'Yes' : '',
    '4ps_no'          => ($enrollment['is_four_ps_beneficiary'] ?? 0) == 0 ? 'Yes' : '',
    'indigenous_group' => safeUp($enrollment['indigenous_group'] ?? ''),
    'ip_yes'           => ($enrollment['is_indigenous']           ?? 0) == 1 ? 'Yes' : '',
    'ip_no'            => ($enrollment['is_indigenous']           ?? 0) == 0 ? 'Yes' : '',
    'is_learner_with_disability_yes' => ($enrollment['is_learner_with_disability'] ?? 0) == 1 ? 'Yes' : '',
    'is_learner_with_disability_no'  => ($enrollment['is_learner_with_disability'] ?? 0) == 0 ? 'Yes' : '',

    // Disability checkboxes
    'visual_impairment'             => chk(in_array('visual impairment',                     $disabilityTypes,    true)),
    'blind'                         => chk(in_array('blind',                                 $disabilitySubtypes, true)),
    'low_vision'                    => chk(in_array('low vision',                            $disabilitySubtypes, true)),
    'hearing_impairment'            => chk(in_array('hearing impairment',                    $disabilityTypes,    true)),
    'autism_spectrum_disorder'      => chk(in_array('autism spectrum disorder',              $disabilityTypes,    true)),
    'speech_language_disorder'      => chk(in_array('speech / language disorder',           $disabilityTypes,    true)),
    'learning_disability'           => chk(in_array('learning disability',                   $disabilityTypes,    true)),
    'emotional_behavioral_disorder' => chk(in_array('emotional / behavioral disorder',      $disabilityTypes,    true)),
    'cerebral_palsy'                => chk(in_array('cerebral palsy',                        $disabilityTypes,    true)),
    'intellectual_disability'       => chk(in_array('intellectual disability',               $disabilityTypes,    true)),
    'orthopedic_physical_handicap'  => chk(in_array('orthopedic / physical handicap',       $disabilityTypes,    true)),
    'social_health_problem'         => chk(in_array('chronic illness',                       $disabilityTypes,    true)),
    'multiple_disorder'             => chk(in_array('others',                                $disabilityTypes,    true)),

    // Current address
    'house_no'           => $currentAddr['house_no'],
    'street_name'        => $currentAddr['street_name'],
    'barangay'           => $currentAddr['barangay'],
    'municipality_city'  => $currentAddr['municipality'],
    'province'           => $currentAddr['province'],
    'country'            => $currentAddr['country'],
    'zip_code'           => $currentAddr['zip_code'],

    // Permanent address
    'same_address_yes' => $sameAddress ? 'Yes' : '',
    'same_address_no'  => $sameAddress ? '' : 'Yes',
    'house_nop'          => $permanentAddr['house_no'],
    'street_namep'       => $permanentAddr['street_name'],
    'barangayp'          => $permanentAddr['barangay'],
    'municipality_cityp' => $permanentAddr['municipality'],
    'provincep'          => $permanentAddr['province'],
    'countryp'           => $permanentAddr['country'],
    'zip_codep'          => $permanentAddr['zip_code'],

    // Father
    'father_last_name'      => safeUp($parents['father']['last_name']    ?? ''),
    'father_first_name'     => safeUp($parents['father']['first_name']   ?? ''),
    'father_middle_name'    => safeUp($parents['father']['middle_name']  ?? ''),
    'father_contact_number' => safe($parents['father']['contact_number'] ?? ''),

    // Mother
    'mother_last_name'      => safeUp($parents['mother']['last_name']    ?? ''),
    'mother_first_name'     => safeUp($parents['mother']['first_name']   ?? ''),
    'mother_middle_name'    => safeUp($parents['mother']['middle_name']  ?? ''),
    'mother_contact_number' => safe($parents['mother']['contact_number'] ?? ''),

    // Guardian
    'guardian_last_name'      => safeUp($parents['guardian']['last_name']    ?? ''),
    'guardian_first_name'     => safeUp($parents['guardian']['first_name']   ?? ''),
    'guardian_middle_name'    => safeUp($parents['guardian']['middle_name']  ?? ''),
    'guardian_contact_number' => safe($parents['guardian']['contact_number'] ?? ''),

    // Returning learner section
    'returning_grade_level'      => $returningFormattedGrade,
    'last_school_attended'       => safeUp($returningLearner['last_school_attended']   ?? ''),
    'last_school_year_completed' => safe($returningLearner['last_school_year_completed'] ?? ''),
    'school_id'                  => safe($returningLearner['school_id']                  ?? ''),
];

// ---------------------------------------------------------------------------
// Build medical data array
// ---------------------------------------------------------------------------

$hasSurgery   = !empty($surgery);
$hasTreatment = !empty($treatment);

$medicalData = [
    // Header
    'lrn'                            => safe($student['lrn'] ?? ''),
    'first_name'                     => safeUp($student['first_name']),
    'last_name'                      => safeUp($student['last_name']),
    'full_name'                      => safeUp($student['last_name'])      . ', '
                                      . safeUp($student['first_name'])     . ' '
                                      . safeUp($student['middle_name']     ?? '') . ' '
                                      . safeUp($student['extension_name']  ?? ''),
    'grade_level'                    => $formattedGrade,
    'school_year'                    => safe($enrollment['school_year'] ?? ''),
    'birth_date'                     => safe($student['birth_date']     ?? ''),
    'age'                            => computeAge($student['birth_date'] ?? ''),
    'sex'                            => safe($student['sex']             ?? ''),
    'full_address'                   => $fullAddress,
    'parent_guardian_name'           => $guardianName,
    'parent_guardian_contact_number' => safe($guardian['contact_number'] ?? ''),

    // 1. Allergies
    'has_allergy_yes'          => chk(!empty($allergyByType)),
    'has_allergy_no'           => chk(empty($allergyByType)),
    'medicine_allergy'         => chk(isset($allergyByType['medicine'])),
    'medicine_allergy_specify' => safe($allergyByType['medicine']['description'] ?? ''),
    'pollen_allergy'           => chk(isset($allergyByType['pollen'])),
    'food_allergy'             => chk(isset($allergyByType['food'])),
    'food_allergy_specify'     => safe($allergyByType['food']['description'] ?? ''),
    'other_allergy'            => chk(isset($allergyByType['other'])),
    'other_allergy_specify'    => safe($allergyByType['other']['description'] ?? ''),

    // 2. Medical conditions
    'has_medical_condition_yes' => chk(!empty($conditionByName)),
    'has_medical_condition_no'  => chk(empty($conditionByName)),
    'error_of_refraction'       => chk(isset($conditionByName['error of refraction'])),
    'asthma'                    => chk(isset($conditionByName['asthma'])),
    'seizure'                   => chk(isset($conditionByName['seizure'])),
    'heart_illness'             => chk(isset($conditionByName['heart illness'])),
    'anemia'                    => chk(isset($conditionByName['anemia'])),
    'bleeding_disorder'         => chk(isset($conditionByName['bleeding disorder'])),
    'fracture_dislocation'      => chk(isset($conditionByName['fracture / dislocation'])),
    'other_condition'           => chk(isset($conditionByName['other'])),
    'other_condition_specify'   => safe($conditionByName['other']['description'] ?? ''),

    // 3. Surgery / hospitalisation
    'has_surgery_hospitalization_yes' => chk($hasSurgery),
    'has_surgery_hospitalization_no'  => chk(!$hasSurgery),
    'surgery_hospitalization_detail'  => $surgeryDetail,

    // 4. Treatment / medicines
    'is_currently_taking_treatment_yes' => chk($hasTreatment),
    'is_currently_taking_treatment_no'  => chk(!$hasTreatment),
    'treatment_medicine'                => safe($treatment['treatment_medicine'] ?? ''),
    'schedule_dosage'                   => safe($treatment['schedule_dosage']    ?? ''),

    // 5. Family medical history
    'tuberculosis'         => chk(isset($familyCondByName['tuberculosis'])),
    'cancer'               => chk(isset($familyCondByName['cancer'])),
    'cancer_type'          => $familyCancerDesc,
    'diabetes_mellitus'    => chk(isset($familyCondByName['diabetes mellitus'])),
    'hypertension'         => chk(isset($familyCondByName['hypertension'])),
    'stroke_heart_attack'  => chk(isset($familyCondByName['stroke / heart attack'])),
    'depression'           => chk(isset($familyCondByName['depression'])),
    'kidney_problems'      => chk(isset($familyCondByName['kidney problems'])),
    'other_family_history' => $familyOtherDesc,

    // 6 & 7. Cigarette exposure & other info
    'exposed_to_cigarette_vape_smoke_yes' => chk(($medicalInfo['exposed_to_cigarette_vape_smoke'] ?? 0) == 1),
    'exposed_to_cigarette_vape_smoke_no'  => chk(($medicalInfo['exposed_to_cigarette_vape_smoke'] ?? 0) == 0),
    'other_pertinent_information'         => safe($medicalInfo['other_pertinent_information'] ?? ''),
];

$combinedData = array_merge($enrollmentData, $medicalData);

// ---------------------------------------------------------------------------
// Debug mode — dump all assembled data as JSON
// ---------------------------------------------------------------------------

if (!empty($_GET['debug'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'student'         => $student,
        'enrollment'      => $enrollment,
        'schoolRecord'    => $schoolRecord,
        'enrollmentData'  => $enrollmentData,
        'medicalInfo'     => $medicalInfo,
        'medicalData'     => $medicalData,
        'combinedData'    => $combinedData,
        'derived' => [
            'disabilities'    => $disabilities,
            'allergyByType'   => $allergyByType,
            'conditionByName' => $conditionByName,
            'surgery'         => $surgery,
            'treatment'       => $treatment,
            'familyHistory'   => $familyCondByName,
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------------------------------------------------------------------------
// Generate PDF(s)
// ---------------------------------------------------------------------------

$generator = new GeneratePDF();

// Single-type mode — stream directly to the browser
if ($requestedType !== null) {
    $data = match ($requestedType) {
        'enrollment' => $enrollmentData,
        'medical'    => $medicalData,
        'combined'   => $combinedData,
    };

    try {
        $path = $generator->generate($data, $requestedType);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        readfile($path);
    } catch (Throwable $e) {
        http_response_code(500);
        echo 'Error generating PDF: ' . htmlspecialchars($e->getMessage());
    }
    exit;
}

// No type specified — generate all three and report results
$tasks = [
    'enrollment' => $enrollmentData,
    'medical'    => $medicalData,
    'combined'   => $combinedData,
];

foreach ($tasks as $formType => $data) {
    try {
        $path = $generator->generate($data, $formType);
        echo ucfirst($formType) . " PDF generated: {$path}\n";
    } catch (Throwable $e) {
        echo ucfirst($formType) . " PDF generation failed: " . $e->getMessage() . "\n";
    }
}