<?php
// DEPRECATED: This endpoint implementation is out of sync with the current gems_db schema.
// It has been moved to ams/api/deprecated/students.php to avoid accidental use.
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in() || !in_array($_SESSION['role'], ['staff', 'admin'], true)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

function fetchOne($pdo, $table, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE student_id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchOneBy($pdo, $table, $column, $id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $column = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchParents($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT e.enrollment_id FROM enrollments e WHERE e.student_id = ? ORDER BY e.enrollment_id DESC LIMIT 1");
    $stmt->execute([$student_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($enrollment['enrollment_id'])) {
        return [];
    }

    $stmt = $pdo->prepare("SELECT p.*, ep.relationship FROM parents p JOIN enrollment_parents ep ON p.parent_id = ep.parent_id WHERE ep.enrollment_id = ?");
    $stmt->execute([$enrollment['enrollment_id']]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['relationship'] ?? 'parent'] = $row;
    }
    return $result;
}

function fetchDisabilities($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT disability_type_id FROM student_disabilities WHERE enrollment_id = (SELECT enrollment_id FROM enrollments WHERE student_id = ? ORDER BY enrollment_id DESC LIMIT 1)");
    $stmt->execute([$student_id]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'disability_type_id');
}

function fetchMedical($pdo, $enrollment_id) {
    $medical = fetchOneBy($pdo, 'medical_information', 'enrollment_id', $enrollment_id);
    if (empty($medical)) {
        return [];
    }
    $medical_id = $medical['medical_id'];
    $allergies = fetchOneBy($pdo, 'medical_allergies', 'medical_id', $medical_id);
    $conditions = fetchOneBy($pdo, 'medical_conditions', 'medical_id', $medical_id);
    $surgeries = fetchOneBy($pdo, 'medical_surgeries', 'medical_id', $medical_id);
    $treatments = fetchOneBy($pdo, 'medical_treatments', 'medical_id', $medical_id);
    $family_history = fetchOneBy($pdo, 'family_medical_history', 'medical_id', $medical_id);
    return array_merge($medical, [
        'allergies' => $allergies ?: [],
        'conditions' => $conditions ?: [],
        'surgeries' => $surgeries ?: [],
        'treatments' => $treatments ?: [],
        'family_history' => $family_history ?: []
    ]);
}

function fetchLatestEnrollment($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? ORDER BY enrollment_id DESC LIMIT 1");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchEnrollmentAddress($pdo, $enrollment_id, $type) {
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE enrollment_id = ? AND address_type = ? LIMIT 1");
    $stmt->execute([$enrollment_id, $type]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function upsertEnrollmentAddress($pdo, $enrollment_id, $type, $data) {
    $stmt = $pdo->prepare("UPDATE addresses SET house_no = ?, street_name = ?, barangay = ?, municipality_city = ?, province = ?, country = ?, zip_code = ? WHERE enrollment_id = ? AND address_type = ?");
    $stmt->execute([
        $data['house_no'] ?? '',
        $data['street_name'] ?? '',
        $data['barangay'] ?? '',
        $data['municipality_city'] ?? '',
        $data['province'] ?? '',
        $data['country'] ?? '',
        $data['zip_code'] ?? '',
        $enrollment_id,
        $type
    ]);

    if ($stmt->rowCount() === 0) {
        $insert = $pdo->prepare("INSERT INTO addresses (enrollment_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([
            $enrollment_id,
            $type,
            $data['house_no'] ?? '',
            $data['street_name'] ?? '',
            $data['barangay'] ?? '',
            $data['municipality_city'] ?? '',
            $data['province'] ?? '',
            $data['country'] ?? '',
            $data['zip_code'] ?? ''
        ]);
    }
}

function updateOrInsertParent($pdo, $enrollment_id, $relationship, $parentData) {
    $stmt = $pdo->prepare("UPDATE parents p JOIN enrollment_parents ep ON p.parent_id = ep.parent_id SET p.last_name = ?, p.first_name = ?, p.middle_name = ?, p.contact_number = ? WHERE ep.enrollment_id = ? AND ep.relationship = ?");
    $stmt->execute([
        $parentData['last_name'] ?? '',
        $parentData['first_name'] ?? '',
        $parentData['middle_name'] ?? '',
        $parentData['contact_number'] ?? '',
        $enrollment_id,
        $relationship
    ]);

    if ($stmt->rowCount() === 0) {
        $insertParent = $pdo->prepare("INSERT INTO parents (last_name, first_name, middle_name, contact_number) VALUES (?, ?, ?, ?)");
        $insertParent->execute([
            $parentData['last_name'] ?? '',
            $parentData['first_name'] ?? '',
            $parentData['middle_name'] ?? '',
            $parentData['contact_number'] ?? ''
        ]);
        $parentId = $pdo->lastInsertId();
        $link = $pdo->prepare("INSERT INTO enrollment_parents (enrollment_id, parent_id, relationship) VALUES (?, ?, ?)");
        $link->execute([$enrollment_id, $parentId, $relationship]);
    }
}

function updateReturningLearner($pdo, $enrollment_id, $isReturning, $returningData) {
    if ($isReturning) {
        $stmt = $pdo->prepare("SELECT enrollment_id FROM returning_learners WHERE enrollment_id = ? LIMIT 1");
        $stmt->execute([$enrollment_id]);
        if ($stmt->fetch()) {
            $update = $pdo->prepare("UPDATE returning_learners SET last_grade_level_completed = ?, last_school_attended = ?, last_school_year_completed = ?, school_id = ? WHERE enrollment_id = ?");
            $update->execute([
                $returningData['last_grade_level_completed'] ?? '',
                $returningData['last_school_attended'] ?? '',
                $returningData['last_school_year_completed'] ?? '',
                $returningData['school_id'] ?? '',
                $enrollment_id
            ]);
        } else {
            $insert = $pdo->prepare("INSERT INTO returning_learners (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed, school_id) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([
                $enrollment_id,
                $returningData['last_grade_level_completed'] ?? '',
                $returningData['last_school_attended'] ?? '',
                $returningData['last_school_year_completed'] ?? '',
                $returningData['school_id'] ?? ''
            ]);
        }
    } else {
        $delete = $pdo->prepare("DELETE FROM returning_learners WHERE enrollment_id = ?");
        $delete->execute([$enrollment_id]);
    }
}

function updateDisabilities($pdo, $enrollment_id, $disabilities) {
    $delete = $pdo->prepare("DELETE FROM student_disabilities WHERE enrollment_id = ?");
    $delete->execute([$enrollment_id]);

    if (is_array($disabilities)) {
        $insert = $pdo->prepare("INSERT INTO student_disabilities (enrollment_id, disability_type_id, disability_subtype_id) VALUES (?, ?, ?)");
        foreach ($disabilities as $typeId) {
            $insert->execute([$enrollment_id, $typeId, null]);
        }
    }
}

function updateMedical($pdo, $enrollment_id, $medicalData) {
    // First, check if medical record exists
    $existing = fetchOneBy($pdo, 'medical_information', 'enrollment_id', $enrollment_id);
    if (empty($existing)) {
        // Insert new
        $insert = $pdo->prepare("INSERT INTO medical_information (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?, ?, ?)");
        $insert->execute([
            $enrollment_id,
            $medicalData['exposed_to_cigarette_vape_smoke'] ?? 0,
            $medicalData['other_pertinent_information'] ?? ''
        ]);
        $medical_id = $pdo->lastInsertId();
    } else {
        // Update existing
        $update = $pdo->prepare("UPDATE medical_information SET exposed_to_cigarette_vape_smoke = ?, other_pertinent_information = ? WHERE medical_id = ?");
        $update->execute([
            $medicalData['exposed_to_cigarette_vape_smoke'] ?? 0,
            $medicalData['other_pertinent_information'] ?? '',
            $existing['medical_id']
        ]);
        $medical_id = $existing['medical_id'];
    }

    // Update allergies
    $hasAllergies = !empty($medicalData['has_allergies']) ? 1 : 0;
    $allergiesStmt = $pdo->prepare("INSERT INTO medical_allergies (medical_id, has_allergies) VALUES (?, ?) ON DUPLICATE KEY UPDATE has_allergies = VALUES(has_allergies)");
    $allergiesStmt->execute([$medical_id, $hasAllergies]);

    // Update conditions
    $hasConditions = !empty($medicalData['has_med_condition']) ? 1 : 0;
    $conditionsStmt = $pdo->prepare("INSERT INTO medical_conditions (medical_id, has_conditions) VALUES (?, ?) ON DUPLICATE KEY UPDATE has_conditions = VALUES(has_conditions)");
    $conditionsStmt->execute([$medical_id, $hasConditions]);

    // Update surgeries
    $hasSurgery = !empty($medicalData['has_surgery_hospitalization']) ? 1 : 0;
    $surgeryStmt = $pdo->prepare("INSERT INTO medical_surgeries (medical_id, has_surgery) VALUES (?, ?) ON DUPLICATE KEY UPDATE has_surgery = VALUES(has_surgery)");
    $surgeryStmt->execute([$medical_id, $hasSurgery]);

    // Update treatments
    $isTakingTreatment = !empty($medicalData['is_taking_treatment']) ? 1 : 0;
    $treatmentStmt = $pdo->prepare("INSERT INTO medical_treatments (medical_id, is_taking_treatment) VALUES (?, ?) ON DUPLICATE KEY UPDATE is_taking_treatment = VALUES(is_taking_treatment)");
    $treatmentStmt->execute([$medical_id, $isTakingTreatment]);

    // Update family history
    $familyHistory = !empty($medicalData['family_medical_history']) ? 1 : 0;
    $familyStmt = $pdo->prepare("INSERT INTO family_medical_history (medical_id, has_family_history) VALUES (?, ?) ON DUPLICATE KEY UPDATE has_family_history = VALUES(has_family_history)");
    $familyStmt->execute([$medical_id, $familyHistory]);
}

function getValue(array $data, string $key, $default = null) {
    return array_key_exists($key, $data) ? $data[$key] : $default;
}

function getBoolValue(array $data, string $key, $default = null) {
    if (!array_key_exists($key, $data)) {
        return $default;
    }
    $value = $data[$key];
    return in_array((string)$value, ['1', 'Yes', 'yes', 'true', 'on'], true) ? 1 : 0;
}

$action = $_GET['action'] ?? '';

try {

    if ($action === 'list') {
        $stmt = $pdo->query("SELECT s.student_id, s.lrn, s.first_name, s.last_name, s.middle_name, s.sex, s.place_of_birth,
            e.grade_level, e.school_year
            FROM students s
            INNER JOIN enrollments e ON s.student_id = e.student_id
            ORDER BY s.last_name ASC, s.first_name ASC");

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

       //GET FULL STUDENT DATA

    if ($action === 'get') {

        $student_id = intval($_GET['student_id'] ?? 0);

        $student   = fetchOne($pdo, 'students', $student_id);
        $latestEnrollment = fetchLatestEnrollment($pdo, $student_id);
        $current   = [];
        $permanent = [];

        if (!empty($latestEnrollment['enrollment_id'])) {
            $current = fetchEnrollmentAddress($pdo, $latestEnrollment['enrollment_id'], 'current');
            $permanent = fetchEnrollmentAddress($pdo, $latestEnrollment['enrollment_id'], 'permanent');
        }

        $parents   = fetchParents($pdo, $student_id);
        $returning = [];
        if (!empty($latestEnrollment['enrollment_id'])) {
            $returning = fetchOneBy($pdo, 'returning_learners', 'enrollment_id', $latestEnrollment['enrollment_id']);
        }
        $disability_ids = fetchDisabilities($pdo, $student_id);
        $medical = [];
        if (!empty($latestEnrollment['enrollment_id'])) {
            $medical = fetchMedical($pdo, $latestEnrollment['enrollment_id']);
        }

        $schoolYearStart = '';
        $schoolYearEnd = '';
        if (!empty($latestEnrollment['school_year']) && strpos($latestEnrollment['school_year'], '-') !== false) {
            [$schoolYearStart, $schoolYearEnd] = explode('-', $latestEnrollment['school_year'], 2);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'student' => $student,
                'latest_enrollment' => array_merge($latestEnrollment, [
                    'year_start' => $schoolYearStart,
                    'year_end' => $schoolYearEnd,
                ]),
                'current_address' => $current,
                'permanent_address' => $permanent,
                'parents' => $parents,
                'returning' => $returning,
                'disabilities' => $disability_ids,
                'medical' => $medical
            ]
        ]);
        exit;
    }

       //CREATE FULL PROFILE
    if ($action === 'create') {

        $data = json_decode(file_get_contents("php://input"), true);

        // 1. student
        $stmt = $pdo->prepare("
            INSERT INTO students (lrn, first_name, last_name, middle_name, birth_date, sex, place_of_birth)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['lrn'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['birth_date'],
            $data['sex'],
            $data['place_of_birth']
        ]);

        $student_id = $pdo->lastInsertId();

        // 2. addresses
        $pdo->prepare("INSERT INTO current_address (student_id) VALUES (?)")
            ->execute([$student_id]);

        $pdo->prepare("INSERT INTO permanent_address (student_id) VALUES (?)")
            ->execute([$student_id]);

        echo json_encode(['success' => true, 'student_id' => $student_id]);
        exit;
    }

       //UPDATE FULL PROFILE
    if ($action === 'update') {

        $data = json_decode(file_get_contents("php://input"), true);
        $student_id = intval($data['student_id']);
        $currentStudent = fetchOne($pdo, 'students', $student_id);
        $latest = fetchLatestEnrollment($pdo, $student_id);

        // 1. students
        $pdo->prepare("
            UPDATE students
            SET lrn=?, first_name=?, last_name=?, middle_name=?, extension_name=?, birth_date=?, sex=?, place_of_birth=?
            WHERE student_id=?
        ")->execute([
            getValue($data, 'Learner_Reference_No', $currentStudent['lrn'] ?? ''),
            getValue($data, 'Learner_First_Name', $currentStudent['first_name'] ?? ''),
            getValue($data, 'Learner_Last_Name', $currentStudent['last_name'] ?? ''),
            getValue($data, 'Learner_Middle_Name', $currentStudent['middle_name'] ?? ''),
            getValue($data, 'Learner_Extension_Name', $currentStudent['extension_name'] ?? ''),
            getValue($data, 'Birth_Date', $currentStudent['birth_date'] ?? ''),
            getValue($data, 'sex', $currentStudent['sex'] ?? ''),
            getValue($data, 'Place_of_Birth', $currentStudent['place_of_birth'] ?? ''),
            $student_id
        ]);

        if (!empty($latest['enrollment_id'])) {
            $schoolYear = null;
            if (array_key_exists('year_start', $data) || array_key_exists('year_end', $data)) {
                $schoolYear = trim((string) getValue($data, 'year_start', '') . '-' . (string) getValue($data, 'year_end', ''));
                if ($schoolYear === '-') {
                    $schoolYear = $latest['school_year'];
                }
            } else {
                $schoolYear = $latest['school_year'];
            }

            $withLrn = getBoolValue($data, 'with_lrn', $latest['with_lrn'] ?? 0);
            $isIndigenous = getBoolValue($data, 'ip', $latest['is_indigenous'] ?? 0);
            $isFourPs = getBoolValue($data, 'fourps', $latest['is_four_ps_beneficiary'] ?? 0);
            $isLearnerWithDisability = array_key_exists('disabilities', $data) ? (!empty($data['disabilities']) ? 1 : 0) : ($latest['is_learner_with_disability'] ?? 0);
            $isReturning = array_key_exists('returning', $data) ? getBoolValue($data, 'returning', $latest['is_returning_learner'] ?? 0) : ($latest['is_returning_learner'] ?? 0);
            $age = array_key_exists('Birth_Date', $data) && trim((string) $data['Birth_Date']) !== '' ? (int) floor((time() - strtotime($data['Birth_Date'])) / 31557600) : ($latest['age'] ?? null);

            $pdo->prepare("UPDATE enrollments SET school_year = ?, grade_level = ?, with_lrn = ?, psa_bcn = ?, age = ?, mother_tongue = ?, is_indigenous = ?, indigenous_group = ?, is_four_ps_beneficiary = ?, four_ps_household_id = ?, is_learner_with_disability = ?, is_returning_learner = ? WHERE enrollment_id = ?")
                ->execute([
                    $schoolYear,
                    getValue($data, 'Grade_Level', $latest['grade_level'] ?? ''),
                    $withLrn,
                    getValue($data, 'psa_bcn', $latest['psa_bcn'] ?? ''),
                    $age,
                    getValue($data, 'Mother_Tongue', $latest['mother_tongue'] ?? ''),
                    $isIndigenous,
                    getValue($data, 'IP_Specify', $latest['indigenous_group'] ?? ''),
                    $isFourPs,
                    getValue($data, 'FourPs_Specify', $latest['four_ps_household_id'] ?? ''),
                    $isLearnerWithDisability,
                    $isReturning,
                    $latest['enrollment_id']
                ]);

            $currentAddress = fetchEnrollmentAddress($pdo, $latest['enrollment_id'], 'current');
            $permanentAddress = fetchEnrollmentAddress($pdo, $latest['enrollment_id'], 'permanent');

            if (array_key_exists('Current_House_No', $data) || array_key_exists('Current_Street_Name', $data) || array_key_exists('Current_Barangay', $data) || array_key_exists('Current_Municipality_City', $data) || array_key_exists('Current_Province', $data) || array_key_exists('Current_Country', $data) || array_key_exists('Current_Zip_Code', $data)) {
                upsertEnrollmentAddress($pdo, $latest['enrollment_id'], 'current', [
                    'house_no' => getValue($data, 'Current_House_No', $currentAddress['house_no'] ?? ''),
                    'street_name' => getValue($data, 'Current_Street_Name', $currentAddress['street_name'] ?? ''),
                    'barangay' => getValue($data, 'Current_Barangay', $currentAddress['barangay'] ?? ''),
                    'municipality_city' => getValue($data, 'Current_Municipality_City', $currentAddress['municipality_city'] ?? ''),
                    'province' => getValue($data, 'Current_Province', $currentAddress['province'] ?? ''),
                    'country' => getValue($data, 'Current_Country', $currentAddress['country'] ?? ''),
                    'zip_code' => getValue($data, 'Current_Zip_Code', $currentAddress['zip_code'] ?? '')
                ]);
            }

            if (array_key_exists('Permanent_House_No', $data) || array_key_exists('Permanent_Street_Name', $data) || array_key_exists('Permanent_Barangay', $data) || array_key_exists('Permanent_Municipality_City', $data) || array_key_exists('Permanent_Province', $data) || array_key_exists('Permanent_Country', $data) || array_key_exists('Permanent_Zip_Code', $data)) {
                upsertEnrollmentAddress($pdo, $latest['enrollment_id'], 'permanent', [
                    'house_no' => getValue($data, 'Permanent_House_No', $permanentAddress['house_no'] ?? ''),
                    'street_name' => getValue($data, 'Permanent_Street_Name', $permanentAddress['street_name'] ?? ''),
                    'barangay' => getValue($data, 'Permanent_Barangay', $permanentAddress['barangay'] ?? ''),
                    'municipality_city' => getValue($data, 'Permanent_Municipality_City', $permanentAddress['municipality_city'] ?? ''),
                    'province' => getValue($data, 'Permanent_Province', $permanentAddress['province'] ?? ''),
                    'country' => getValue($data, 'Permanent_Country', $permanentAddress['country'] ?? ''),
                    'zip_code' => getValue($data, 'Permanent_Zip_Code', $permanentAddress['zip_code'] ?? '')
                ]);
            }

            if (array_key_exists('father_last_name', $data) || array_key_exists('father_first_name', $data) || array_key_exists('father_middle_name', $data) || array_key_exists('father_contact_number', $data)) {
                updateOrInsertParent($pdo, $latest['enrollment_id'], 'father', [
                    'last_name' => getValue($data, 'father_last_name', ''),
                    'first_name' => getValue($data, 'father_first_name', ''),
                    'middle_name' => getValue($data, 'father_middle_name', ''),
                    'contact_number' => getValue($data, 'father_contact_number', '')
                ]);
            }

            if (array_key_exists('mother_last_name', $data) || array_key_exists('mother_first_name', $data) || array_key_exists('mother_middle_name', $data) || array_key_exists('mother_contact_number', $data)) {
                updateOrInsertParent($pdo, $latest['enrollment_id'], 'mother', [
                    'last_name' => getValue($data, 'mother_last_name', ''),
                    'first_name' => getValue($data, 'mother_first_name', ''),
                    'middle_name' => getValue($data, 'mother_middle_name', ''),
                    'contact_number' => getValue($data, 'mother_contact_number', '')
                ]);
            }

            if (array_key_exists('guardian_last_name', $data) || array_key_exists('guardian_first_name', $data) || array_key_exists('guardian_middle_name', $data) || array_key_exists('guardian_contact_number', $data)) {
                updateOrInsertParent($pdo, $latest['enrollment_id'], 'guardian', [
                    'last_name' => getValue($data, 'guardian_last_name', ''),
                    'first_name' => getValue($data, 'guardian_first_name', ''),
                    'middle_name' => getValue($data, 'guardian_middle_name', ''),
                    'contact_number' => getValue($data, 'guardian_contact_number', '')
                ]);
            }

            if (array_key_exists('returning', $data)) {
                updateReturningLearner($pdo, $latest['enrollment_id'], $isReturning, [
                    'last_grade_level_completed' => getValue($data, 'Returning_Grade_Level', ''),
                    'last_school_attended' => getValue($data, 'Last_School_Attended', ''),
                    'last_school_year_completed' => getValue($data, 'Last_School_Year_Completed', ''),
                    'school_id' => getValue($data, 'school_ID', '')
                ]);
            }

            if (array_key_exists('disabilities', $data)) {
                updateDisabilities($pdo, $latest['enrollment_id'], $data['disabilities']);
            }

            if (array_key_exists('exposed_to_cigarette_vape_smoke', $data) || array_key_exists('other_pertinent_information', $data) || array_key_exists('has_allergies', $data) || array_key_exists('has_med_condition', $data) || array_key_exists('has_surgery_hospitalization', $data) || array_key_exists('is_taking_treatment', $data) || array_key_exists('family_medical_history', $data)) {
                updateMedical($pdo, $latest['enrollment_id'], $data);
            }
        }

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = intval($data['student_id'] ?? 0);

        if ($student_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
            exit;
        }

        $pdo->beginTransaction();
        // Get user_id from student
        $userStmt = $pdo->prepare('SELECT user_id FROM students WHERE student_id = ?');
        $userStmt->execute([$student_id]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        $enrollmentIds = $pdo->prepare('SELECT enrollment_id FROM enrollments WHERE student_id = ?');
        $enrollmentIds->execute([$student_id]);
        $enrollments = $enrollmentIds->fetchAll(PDO::FETCH_COLUMN, 0);

        foreach ($enrollments as $enrollmentId) {
            $medicalStmt = $pdo->prepare('SELECT medical_id FROM medical_information WHERE enrollment_id = ? LIMIT 1');
            $medicalStmt->execute([$enrollmentId]);
            $medicalRow = $medicalStmt->fetch(PDO::FETCH_ASSOC);
            if ($medicalRow) {
                $medicalId = intval($medicalRow['medical_id']);
                $allergyStmt = $pdo->prepare('SELECT allergy_group_id FROM medical_allergies WHERE medical_id = ? LIMIT 1');
                $allergyStmt->execute([$medicalId]);
                $allergyRow = $allergyStmt->fetch(PDO::FETCH_ASSOC);
                if ($allergyRow) {
                    $pdo->prepare('DELETE FROM student_allergies WHERE allergy_group_id = ?')->execute([intval($allergyRow['allergy_group_id'])]);
                }
                $conditionStmt = $pdo->prepare('SELECT condition_group_id FROM medical_conditions WHERE medical_id = ? LIMIT 1');
                $conditionStmt->execute([$medicalId]);
                $conditionRow = $conditionStmt->fetch(PDO::FETCH_ASSOC);
                if ($conditionRow) {
                    $pdo->prepare('DELETE FROM student_conditions WHERE condition_group_id = ?')->execute([intval($conditionRow['condition_group_id'])]);
                }
                $familyStmt = $pdo->prepare('SELECT family_history_id FROM family_medical_history WHERE medical_id = ? LIMIT 1');
                $familyStmt->execute([$medicalId]);
                $familyRow = $familyStmt->fetch(PDO::FETCH_ASSOC);
                if ($familyRow) {
                    $pdo->prepare('DELETE FROM student_family_conditions WHERE family_history_id = ?')->execute([intval($familyRow['family_history_id'])]);
                }
                $pdo->prepare('DELETE FROM medical_allergies WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_conditions WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_surgeries WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_treatments WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM family_medical_history WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_information WHERE medical_id = ?')->execute([$medicalId]);
            }
        }
        
        $deleteActivityScores = $pdo->prepare('DELETE FROM activity_scores WHERE class_student_id IN (SELECT class_student_id FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?))');
        $deleteActivityScores->execute([$student_id]);

        $deleteAttendance = $pdo->prepare('DELETE FROM attendance WHERE class_student_id IN (SELECT class_student_id FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?))');
        $deleteAttendance->execute([$student_id]);

        $deleteGrades = $pdo->prepare('DELETE FROM grades WHERE class_student_id IN (SELECT class_student_id FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?))');
        $deleteGrades->execute([$student_id]);
        
        $deleteClassStudents = $pdo->prepare('DELETE FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteClassStudents->execute([$student_id]);
        
        $deleteAddresses = $pdo->prepare('DELETE FROM addresses WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteAddresses->execute([$student_id]);
        
        $deleteEnrollmentParents = $pdo->prepare('DELETE FROM enrollment_parents WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteEnrollmentParents->execute([$student_id]);
        
        $deleteReturningLearners = $pdo->prepare('DELETE FROM returning_learners WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteReturningLearners->execute([$student_id]);
        
        $deleteDisabilities = $pdo->prepare('DELETE FROM student_disabilities WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteDisabilities->execute([$student_id]);
        
        $deleteEnrollments = $pdo->prepare('DELETE FROM enrollments WHERE student_id = ?');
        $deleteEnrollments->execute([$student_id]);
        $deleteStudent = $pdo->prepare('DELETE FROM students WHERE student_id = ?');
        $deleteStudent->execute([$student_id]);
        
        if ($user && $user['user_id']) {
            // Delete associated user account after student record is removed
            $deleteUser = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
            $deleteUser->execute([$user['user_id']]);
        }
        $pdo->commit();

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}