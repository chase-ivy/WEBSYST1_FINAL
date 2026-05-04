<?php
require_once __DIR__ . '/../../pdf/vendor/autoload.php';

use Classes\GeneratePDF;

include __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $student_id = $_GET['student_id'] ?? 12;

    // ── FETCH HELPERS ─────────────────────────────────────────────

    function fetchOne($pdo, $table, $student_id) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE student_id = ? LIMIT 1");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // Parents keyed by parent_type via junction table
    function fetchParents($pdo, $student_id) {
        $stmt = $pdo->prepare("
            SELECT p.*
            FROM parents p
            JOIN student_parents sp ON p.parent_id = sp.parent_id
            AND sp.student_id = ?
        ");
        $stmt->execute([$student_id]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['parent_type']] = $row;
        }
        return $result;
    }

    // Disabilities as flat array of disability_type_ids
    // Replaces the old comma-string explode on students.is_learner_with_disability
    function fetchDisabilities($pdo, $student_id) {
        $stmt = $pdo->prepare("SELECT disability_type_id FROM student_disabilities WHERE student_id = ?");
        $stmt->execute([$student_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'disability_type_id');
    }

    // age computed from birth_date — not stored
    function computeAge($birth_date) {
        if (empty($birth_date)) return '';
        return (new DateTime($birth_date))->diff(new DateTime('today'))->y;
    }

    // ── FETCH ALL ─────────────────────────────────────────────────

    $student          = fetchOne($pdo, 'students', $student_id);
    $current          = fetchOne($pdo, 'current_address', $student_id);
    $permanent        = fetchOne($pdo, 'permanent_address', $student_id);
    $parents          = fetchParents($pdo, $student_id);
    $returning_learner = fetchOne($pdo, 'returning_learner_information', $student_id);
    $disability_ids   = fetchDisabilities($pdo, $student_id);

    // Build a lookup for O(1) disability checks — replaces array_flip on comma string
    $has = array_flip($disability_ids);

    // Debug output to verify data fetching before PDF generation
    echo '<pre>';
    echo "STUDENTs:\n";
    print_r($student);

    echo "\nCURRENT:\n";
    print_r($current);

    echo "\nPERMANENT:\n";
    print_r($permanent);

    echo "\nPARENTS:\n";
    print_r($parents);

    echo "\nRETURNING:\n";
    print_r($returning_learner);

    echo "\nDISABILITIES:\n";
    print_r($disability_ids);
    echo '</pre>';

    // ── GRADE MAP ─────────────────────────────────────────────────

    $gradeMap = [
        'Kinder'  => 'KD',
        'Grade 1' => '01', 'Grade 2' => '02', 'Grade 3' => '03',
        'Grade 4' => '04', 'Grade 5' => '05', 'Grade 6' => '06',
    ];

    $grade              = trim($student['grade_level'] ?? '');
    $formattedGrade     = $gradeMap[$grade] ?? '';
    $returning_grade    = trim($returning_learner['last_grade_level_completed'] ?? '');
    $returning_formattedGrade = $gradeMap[$returning_grade] ?? '';

    // ── ADDRESS COMPARISON ────────────────────────────────────────

    $currentAddr = [
        'house_no'    => trim($current['house_no']          ?? ''),
        'street_name' => trim($current['street_name']       ?? ''),
        'barangay'    => trim($current['barangay']          ?? ''),
        'municipality'=> trim($current['municipality_city'] ?? ''),
        'province'    => trim($current['province']          ?? ''),
        'country'     => trim($current['country']           ?? ''),
        'zip_code'    => trim($current['zip_code']          ?? ''),
    ];
    $permanentAddr = [
        'house_no'    => trim($permanent['house_no']          ?? ''),
        'street_name' => trim($permanent['street_name']       ?? ''),
        'barangay'    => trim($permanent['barangay']          ?? ''),
        'municipality'=> trim($permanent['municipality_city'] ?? ''),
        'province'    => trim($permanent['province']          ?? ''),
        'country'     => trim($permanent['country']           ?? ''),
        'zip_code'    => trim($permanent['zip_code']          ?? ''),
    ];
    $sameAddress = ($currentAddr === $permanentAddr);

    // ── DATA MAP ──────────────────────────────────────────────────

    $data = [
        'lrn'         => $student['lrn'],
        'school_year' => $student['school_year'],
        'grade_level' => $formattedGrade,

        'with_lrn_yes'   => $student['with_lrn']  == 1 ? 'Yes' : '',
        'with_lrn_no'    => $student['with_lrn']  == 0 ? 'Yes' : '',
        'returning_yes'  => $student['returning']  == 1 ? 'Yes' : '',
        'returning_no'   => $student['returning']  == 0 ? 'Yes' : '',

        'psa_bcn'        => $student['psa_bcn'],
        'last_name'      => strtoupper($student['last_name']),
        'first_name'     => strtoupper($student['first_name']),
        'middle_name'    => strtoupper($student['middle_name']    ?? ''),
        'extension_name' => strtoupper($student['extension_name'] ?? ''),
        'birth_date'     => $student['birth_date'],
        'sex_male'       => $student['sex'] === 'male'   ? 'Yes' : '',
        'sex_female'     => $student['sex'] === 'female' ? 'Yes' : '',
        'place_of_birth' => strtoupper($student['place_of_birth']),

        // Computed age — not read from DB column
        'age' => computeAge($student['birth_date']),

        'mother_tongue'    => strtoupper($student['mother_tongue'] ?? ''),

        // 4Ps — null means No
        '4ps_benificiary'  => strtoupper($student['4p_beneficiary'] ?? ''),
        '4ps_yes'          => !empty($student['4p_beneficiary']) ? 'Yes' : '',
        '4ps_no'           => empty($student['4p_beneficiary'])  ? 'Yes' : '',

        // IP — null means No
        'indigenous_group' => strtoupper($student['indigenous_group'] ?? ''),
        'ip_yes'           => !empty($student['indigenous_group']) ? 'Yes' : '',
        'ip_no'            => empty($student['indigenous_group'])  ? 'Yes' : '',

        // Disability — now driven by student_disabilities rows
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
        'same_address_yes' => $sameAddress  ? 'Yes' : '',
        'same_address_no'  => !$sameAddress ? 'Yes' : '',

        'house_no'          => $current['house_no']          ?? '',
        'street_name'       => strtoupper($current['street_name']       ?? ''),
        'barangay'          => strtoupper($current['barangay']          ?? ''),
        'municipality_city' => strtoupper($current['municipality_city'] ?? ''),
        'province'          => strtoupper($current['province']          ?? ''),
        'country'           => strtoupper($current['country']           ?? ''),
        'zip_code'          => $current['zip_code'] ?? '',

        'house_nop'          => $permanent['house_no']          ?? '',
        'street_namep'       => strtoupper($permanent['street_name']       ?? ''),
        'barangayp'          => strtoupper($permanent['barangay']          ?? ''),
        'municipality_cityp' => strtoupper($permanent['municipality_city'] ?? ''),
        'provincep'          => strtoupper($permanent['province']          ?? ''),
        'countryp'           => strtoupper($permanent['country']           ?? ''),
        'zip_codep'          => $permanent['zip_code'] ?? '',

        // Parents — keyed by parent_type from parents table
        'father_last_name'       => strtoupper($parents['father']['last_name']      ?? ''),
        'father_first_name'      => strtoupper($parents['father']['first_name']     ?? ''),
        'father_middle_name'     => strtoupper($parents['father']['middle_name']    ?? ''),
        'father_contact_number'  => $parents['father']['contact_number']            ?? '',

        'mother_last_name'       => strtoupper($parents['mother']['last_name']      ?? ''),
        'mother_first_name'      => strtoupper($parents['mother']['first_name']     ?? ''),
        'mother_middle_name'     => strtoupper($parents['mother']['middle_name']    ?? ''),
        'mother_contact_number'  => $parents['mother']['contact_number']            ?? '',

        'guardian_last_name'     => strtoupper($parents['guardian']['last_name']    ?? ''),
        'guardian_first_name'    => strtoupper($parents['guardian']['first_name']   ?? ''),
        'guardian_middle_name'   => strtoupper($parents['guardian']['middle_name']  ?? ''),
        'guardian_contact_number'=> $parents['guardian']['contact_number']          ?? '',

        // Returning learner
        'last_grade_level_completed' => $returning_formattedGrade,
        'last_school_attended'       => strtoupper($returning_learner['last_school_attended']       ?? ''),
        'last_school_year_completed' => $returning_learner['last_school_year_completed']            ?? '',
        'school_id'                  => $returning_learner['school_id']                             ?? '',
    ];

    $pdf = new GeneratePDF;
    try {
        $response = $pdf->generate($data);
        echo "PDF generated: {$response}";
    } catch (Throwable $e) {
        echo 'PDF generation failed: ' . $e->getMessage();
    }

} else {
    echo "No student ID provided.";
}