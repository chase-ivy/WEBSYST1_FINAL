<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
// This API was originally protected by session-based auth. For environments
// where the verification UI is used inside the app (same origin) and during
// initial deployment/testing, we allow access without requiring login.
// WARNING: Removing auth makes this endpoint accessible to anyone who can
// reach the server. Reinstate `require_once __DIR__ . '/../login/auth.php'`
// and the `is_logged_in()` guard in production if needed.

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

function updateEnrollmentAddress(PDO $pdo, int $enrollment_id, string $type, array $address) {
    try {
        $stmt = $pdo->prepare('UPDATE addresses SET house_no = ?, street_name = ?, barangay = ?, municipality_city = ?, province = ?, country = ?, zip_code = ? WHERE enrollment_id = ? AND address_type = ?');
        $stmt->execute([
            $address['house_no'] ?? '',
            $address['street_name'] ?? '',
            $address['barangay'] ?? '',
            $address['municipality_city'] ?? '',
            $address['province'] ?? '',
            $address['country'] ?? '',
            $address['zip_code'] ?? '',
            $enrollment_id,
            $type,
        ]);
        if ($stmt->rowCount() === 0) {
            $insert = $pdo->prepare('INSERT INTO addresses (enrollment_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insert->execute([
                $enrollment_id,
                $type,
                $address['house_no'] ?? '',
                $address['street_name'] ?? '',
                $address['barangay'] ?? '',
                $address['municipality_city'] ?? '',
                $address['province'] ?? '',
                $address['country'] ?? '',
                $address['zip_code'] ?? '',
            ]);
        }
    } catch (Exception $e) {
        // Fallback for schemas that store student addresses separately
        try {
            $stmt = $pdo->prepare('UPDATE student_addresses SET house_no = ?, street_name = ?, barangay = ?, municipality_city = ?, province = ?, country = ?, zip_code = ? WHERE student_id = (SELECT student_id FROM enrollments WHERE enrollment_id = ? LIMIT 1) AND address_type = ?');
            $stmt->execute([
                $address['house_no'] ?? '',
                $address['street_name'] ?? '',
                $address['barangay'] ?? '',
                $address['municipality_city'] ?? '',
                $address['province'] ?? '',
                $address['country'] ?? '',
                $address['zip_code'] ?? '',
                $enrollment_id,
                $type,
            ]);
            if ($stmt->rowCount() === 0) {
                $insert = $pdo->prepare('INSERT INTO student_addresses (student_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES ((SELECT student_id FROM enrollments WHERE enrollment_id = ? LIMIT 1), ?, ?, ?, ?, ?, ?, ?, ?)');
                $insert->execute([
                    $enrollment_id,
                    $type,
                    $address['house_no'] ?? '',
                    $address['street_name'] ?? '',
                    $address['barangay'] ?? '',
                    $address['municipality_city'] ?? '',
                    $address['province'] ?? '',
                    $address['country'] ?? '',
                    $address['zip_code'] ?? '',
                ]);
            }
        } catch (Exception $inner) {
            // Ignore fallback if schema differs.
        }
    }
}

function updateEnrollmentMedical(PDO $pdo, int $enrollment_id, array $data) {
    $stmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
    $stmt->execute([$enrollment_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $update = $pdo->prepare('UPDATE enrollment_medical_information SET exposed_to_cigarette_vape_smoke = ?, other_pertinent_information = ? WHERE enrollment_id = ?');
        $update->execute([
            getBoolValue($data, 'exposed_to_cigarette_vape_smoke', $existing['exposed_to_cigarette_vape_smoke'] ?? 0),
            getValue($data, 'other_pertinent_information', $existing['other_pertinent_information'] ?? ''),
            $enrollment_id,
        ]);
    } else {
        $insert = $pdo->prepare('INSERT INTO enrollment_medical_information (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?, ?, ?)');
        $insert->execute([
            $enrollment_id,
            getBoolValue($data, 'exposed_to_cigarette_vape_smoke', 0),
            getValue($data, 'other_pertinent_information', ''),
        ]);
    }
}

function updateOrInsertParent(PDO $pdo, int $enrollment_id, string $relationship, array $parentData) {
    $stmt = $pdo->prepare('UPDATE parents p JOIN enrollment_parents ep ON p.parent_id = ep.parent_id SET p.last_name = ?, p.first_name = ?, p.middle_name = ?, p.contact_number = ? WHERE ep.enrollment_id = ? AND ep.relationship = ?');
    $stmt->execute([
        $parentData['last_name'] ?? '',
        $parentData['first_name'] ?? '',
        $parentData['middle_name'] ?? '',
        $parentData['contact_number'] ?? '',
        $enrollment_id,
        $relationship,
    ]);

    if ($stmt->rowCount() === 0) {
        $insert = $pdo->prepare('INSERT INTO parents (last_name, first_name, middle_name, contact_number) VALUES (?, ?, ?, ?)');
        $insert->execute([
            $parentData['last_name'] ?? '',
            $parentData['first_name'] ?? '',
            $parentData['middle_name'] ?? '',
            $parentData['contact_number'] ?? '',
        ]);
        $parentId = $pdo->lastInsertId();
        $link = $pdo->prepare('INSERT INTO enrollment_parents (enrollment_id, parent_id, relationship) VALUES (?, ?, ?)');
        $link->execute([$enrollment_id, $parentId, $relationship]);
    }
}

function updateReturningLearner(PDO $pdo, int $enrollment_id, bool $isReturning, array $returningData) {
    if ($isReturning) {
        $stmt = $pdo->prepare('SELECT enrollment_id FROM enrollment_returning_learners WHERE enrollment_id = ? LIMIT 1');
        $stmt->execute([$enrollment_id]);
        if ($stmt->fetch()) {
            $update = $pdo->prepare('UPDATE enrollment_returning_learners SET last_grade_level_completed = ?, last_school_attended = ?, last_school_year_completed = ? WHERE enrollment_id = ?');
            $update->execute([
                $returningData['last_grade_level_completed'] ?? '',
                $returningData['last_school_attended'] ?? '',
                $returningData['last_school_year_completed'] ?? '',
                $enrollment_id,
            ]);
        } else {
            $insert = $pdo->prepare('INSERT INTO enrollment_returning_learners (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed) VALUES (?, ?, ?, ?)');
            $insert->execute([
                $enrollment_id,
                $returningData['last_grade_level_completed'] ?? '',
                $returningData['last_school_attended'] ?? '',
                $returningData['last_school_year_completed'] ?? '',
            ]);
        }
    } else {
        $delete = $pdo->prepare('DELETE FROM enrollment_returning_learners WHERE enrollment_id = ?');
        $delete->execute([$enrollment_id]);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? ''; 

try {
    if ($action === 'list') {
        // Use explicit enrollment_status = 'pending' to find current enrollments
        // that need verification. This matches the application's workflow.
        $stmt = $pdo->prepare("SELECT e.enrollment_id, e.student_id, COALESCE(s.lrn, '') AS lrn, CONCAT(COALESCE(s.last_name,''), ', ', COALESCE(s.first_name,'')) AS student_name, COALESCE(ssr.grade_level, '') AS grade_level, e.school_year, e.enrollment_status FROM enrollments e LEFT JOIN students s ON s.student_id = e.student_id LEFT JOIN student_school_records ssr ON ssr.enrollment_id = e.enrollment_id WHERE e.enrollment_status = 'pending' ORDER BY e.enrollment_id DESC");
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'details') {
        $enrollment_id = intval($_GET['enrollment_id'] ?? 0);
        if ($enrollment_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'enrollment_id required']);
            exit;
        }

        $res = [];
        $stmt = $pdo->prepare('SELECT e.*, s.lrn, s.first_name, s.last_name, s.middle_name, s.birth_date, s.sex, s.place_of_birth, ssr.grade_level AS ssr_grade_level, ssr.mother_tongue AS ssr_mother_tongue, ssr.indigenous_group AS ssr_indigenous_group FROM enrollments e LEFT JOIN students s ON s.student_id = e.student_id LEFT JOIN student_school_records ssr ON ssr.enrollment_id = e.enrollment_id WHERE e.enrollment_id = ? LIMIT 1');
        $stmt->execute([$enrollment_id]);
        $res['enrollment'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($res['enrollment']) {
            if (!empty($res['enrollment']['ssr_grade_level'])) {
                $res['enrollment']['grade_level'] = $res['enrollment']['ssr_grade_level'];
            }
            if (!empty($res['enrollment']['ssr_mother_tongue'])) {
                $res['enrollment']['mother_tongue'] = $res['enrollment']['ssr_mother_tongue'];
            }
            if (!empty($res['enrollment']['ssr_indigenous_group'])) {
                $res['enrollment']['indigenous_group'] = $res['enrollment']['ssr_indigenous_group'];
            }
        }

        // Addresses: some schemas store addresses by enrollment_id in `addresses`,
        // others use `student_addresses` keyed by student_id. Try `addresses`
        // first and fall back to `student_addresses`.
        $res['addresses'] = [];
        try {
            $addrStmt = $pdo->prepare('SELECT * FROM addresses WHERE enrollment_id = ?');
            $addrStmt->execute([$enrollment_id]);
            $res['addresses'] = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $studentId = $res['enrollment']['student_id'] ?? 0;
            if ($studentId) {
                $addrStmt = $pdo->prepare('SELECT * FROM student_addresses WHERE student_id = ?');
                $addrStmt->execute([$studentId]);
                $res['addresses'] = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
        $medStmt->execute([$enrollment_id]);
        $medical = $medStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $res['medical'] = $medical;

        if ($medical) {
            $medical_id = $medical['medical_id'] ?? 0;
            $allergiesStmt = $pdo->prepare('SELECT * FROM enrollment_medical_allergies WHERE medical_information_id = ?');
            $allergiesStmt->execute([$medical_id]);
            $res['medical_allergies'] = $allergiesStmt->fetchAll(PDO::FETCH_ASSOC);

            $conditionsStmt = $pdo->prepare('SELECT * FROM enrollment_medical_conditions WHERE medical_information_id = ?');
            $conditionsStmt->execute([$medical_id]);
            $res['medical_conditions'] = $conditionsStmt->fetchAll(PDO::FETCH_ASSOC);

            $surgeriesStmt = $pdo->prepare('SELECT * FROM enrollment_medical_surgeries WHERE medical_information_id = ?');
            $surgeriesStmt->execute([$medical_id]);
            $res['medical_surgeries'] = $surgeriesStmt->fetchAll(PDO::FETCH_ASSOC);

            $treatStmt = $pdo->prepare('SELECT * FROM enrollment_medical_treatments WHERE medical_information_id = ?');
            $treatStmt->execute([$medical_id]);
            $res['medical_treatments'] = $treatStmt->fetchAll(PDO::FETCH_ASSOC);

            $familyStmt = $pdo->prepare('SELECT * FROM enrollment_family_medical_history WHERE medical_information_id = ?');
            $familyStmt->execute([$medical_id]);
            $res['medical_family'] = $familyStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['success' => true, 'data' => $res]);
        exit;
    }

    if ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = intval($data['student_id'] ?? 0);
        $enrollment_id = intval($data['enrollment_id'] ?? 0);

        if ($student_id <= 0 || $enrollment_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'student_id and enrollment_id are required']);
            exit;
        }

        $pdo->beginTransaction();

        $studentStmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ? LIMIT 1');
        $studentStmt->execute([$student_id]);
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Student not found']);
            exit;
        }

        $studentUpdate = $pdo->prepare('UPDATE students SET lrn = ?, first_name = ?, last_name = ?, middle_name = ?, extension_name = ?, birth_date = ?, sex = ?, place_of_birth = ? WHERE student_id = ?');
        $studentUpdate->execute([
            getValue($data, 'Learner_Reference_No', $student['lrn'] ?? ''),
            getValue($data, 'Learner_First_Name', $student['first_name'] ?? ''),
            getValue($data, 'Learner_Last_Name', $student['last_name'] ?? ''),
            getValue($data, 'Learner_Middle_Name', $student['middle_name'] ?? ''),
            getValue($data, 'Learner_Extension_Name', $student['extension_name'] ?? ''),
            getValue($data, 'Birth_Date', $student['birth_date'] ?? ''),
            getValue($data, 'sex', $student['sex'] ?? ''),
            getValue($data, 'Place_of_Birth', $student['place_of_birth'] ?? ''),
            $student_id,
        ]);

        $schoolYear = null;
        if (array_key_exists('year_start', $data) || array_key_exists('year_end', $data)) {
            $schoolYear = trim((string) getValue($data, 'year_start', '') . '-' . (string) getValue($data, 'year_end', ''));
            if ($schoolYear === '-') {
                $schoolYear = null;
            }
        }

        $enrollmentUpdate = $pdo->prepare('UPDATE enrollments SET school_year = ?, grade_level = ?, with_lrn = ?, psa_bcn = ?, age = ?, mother_tongue = ?, is_indigenous = ?, indigenous_group = ?, is_four_ps_beneficiary = ?, four_ps_household_id = ?, is_learner_with_disability = ?, is_returning_learner = ? WHERE enrollment_id = ?');
        $enrollmentUpdate->execute([
            $schoolYear,
            getValue($data, 'Grade_Level', null),
            getBoolValue($data, 'with_lrn', null),
            getValue($data, 'psa_bcn', null),
            array_key_exists('Age', $data) ? intval($data['Age']) : null,
            getValue($data, 'Mother_Tongue', null),
            getBoolValue($data, 'ip', null),
            getValue($data, 'IP_Specify', null),
            getBoolValue($data, 'fourps', null),
            getValue($data, 'FourPs_Specify', null),
            getBoolValue($data, 'disability', null),
            getBoolValue($data, 'returning', null),
            $enrollment_id,
        ]);

        $ssrUpdate = $pdo->prepare('UPDATE student_school_records SET grade_level = ?, mother_tongue = ?, indigenous_group = ? WHERE enrollment_id = ?');
        $ssrUpdate->execute([
            getValue($data, 'Grade_Level', null),
            getValue($data, 'Mother_Tongue', null),
            getValue($data, 'IP_Group', null),
            $enrollment_id,
        ]);

        $sameAddress = getValue($data, 'same_address', 'No') === 'Yes';

        $currentAddress = [
            'house_no' => getValue($data, 'Current_House_No', ''),
            'street_name' => getValue($data, 'Current_Street_Name', ''),
            'barangay' => getValue($data, 'Current_Barangay', ''),
            'municipality_city' => getValue($data, 'Current_Municipality_City', ''),
            'province' => getValue($data, 'Current_Province', ''),
            'country' => getValue($data, 'Current_Country', ''),
            'zip_code' => getValue($data, 'Current_Zip_Code', ''),
        ];

        $permanentAddress = $sameAddress ? $currentAddress : [
            'house_no' => getValue($data, 'Permanent_House_No', ''),
            'street_name' => getValue($data, 'Permanent_Street_Name', ''),
            'barangay' => getValue($data, 'Permanent_Barangay', ''),
            'municipality_city' => getValue($data, 'Permanent_Municipality_City', ''),
            'province' => getValue($data, 'Permanent_Province', ''),
            'country' => getValue($data, 'Permanent_Country', ''),
            'zip_code' => getValue($data, 'Permanent_Zip_Code', ''),
        ];

        updateEnrollmentAddress($pdo, $enrollment_id, 'current', $currentAddress);
        updateEnrollmentAddress($pdo, $enrollment_id, 'permanent', $permanentAddress);

        if (array_key_exists('father_last_name', $data) || array_key_exists('father_first_name', $data) || array_key_exists('father_middle_name', $data) || array_key_exists('father_contact_number', $data)) {
            updateOrInsertParent($pdo, $enrollment_id, 'father', [
                'last_name' => getValue($data, 'father_last_name', ''),
                'first_name' => getValue($data, 'father_first_name', ''),
                'middle_name' => getValue($data, 'father_middle_name', ''),
                'contact_number' => getValue($data, 'father_contact_number', ''),
            ]);
        }
        if (array_key_exists('mother_last_name', $data) || array_key_exists('mother_first_name', $data) || array_key_exists('mother_middle_name', $data) || array_key_exists('mother_contact_number', $data)) {
            updateOrInsertParent($pdo, $enrollment_id, 'mother', [
                'last_name' => getValue($data, 'mother_last_name', ''),
                'first_name' => getValue($data, 'mother_first_name', ''),
                'middle_name' => getValue($data, 'mother_middle_name', ''),
                'contact_number' => getValue($data, 'mother_contact_number', ''),
            ]);
        }
        if (array_key_exists('guardian_last_name', $data) || array_key_exists('guardian_first_name', $data) || array_key_exists('guardian_middle_name', $data) || array_key_exists('guardian_contact_number', $data)) {
            updateOrInsertParent($pdo, $enrollment_id, 'guardian', [
                'last_name' => getValue($data, 'guardian_last_name', ''),
                'first_name' => getValue($data, 'guardian_first_name', ''),
                'middle_name' => getValue($data, 'guardian_middle_name', ''),
                'contact_number' => getValue($data, 'guardian_contact_number', ''),
            ]);
        }

        if (array_key_exists('returning', $data)) {
            updateReturningLearner($pdo, $enrollment_id, getBoolValue($data, 'returning', false), [
                'last_grade_level_completed' => getValue($data, 'Returning_Grade_Level', ''),
                'last_school_attended' => getValue($data, 'Last_School_Attended', ''),
                'last_school_year_completed' => getValue($data, 'Last_School_Year_Completed', ''),
            ]);
        }

        updateEnrollmentMedical($pdo, $enrollment_id, $data);

        $pdo->commit();

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'verify') {
        $data = json_decode(file_get_contents('php://input'), true);
        $enrollment_id = intval($data['enrollment_id'] ?? 0);
        if ($enrollment_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'enrollment_id required']);
            exit;
        }

        $pdo->beginTransaction();

        // Check if already has school record
        $chk = $pdo->prepare('SELECT COUNT(*) FROM student_school_records WHERE enrollment_id = ?');
        $chk->execute([$enrollment_id]);
        if ((int)$chk->fetchColumn() > 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Enrollment already verified (snapshot exists).']);
            exit;
        }

        // Fetch enrollment + student
        $stmt = $pdo->prepare('SELECT e.*, s.lrn, s.first_name, s.last_name, s.middle_name, s.extension_name, s.birth_date, s.sex, s.place_of_birth, e.school_year, e.grade_level FROM enrollments e LEFT JOIN students s ON s.student_id = e.student_id WHERE e.enrollment_id = ? LIMIT 1');
        $stmt->execute([$enrollment_id]);
        $en = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$en) throw new Exception('Enrollment not found');

        // Insert student_school_records snapshot
        $ssrStmt = $pdo->prepare('INSERT INTO student_school_records (enrollment_id, student_id, school_year, grade_level, lrn, last_name, first_name, middle_name, extension_name, birth_date, sex, place_of_birth, mother_tongue, indigenous_group, four_ps_household_id, is_learner_with_disability, is_returning_learner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        // Some schemas keep grade_level elsewhere; default to empty string to satisfy NOT NULL.
        $gradeLevel = isset($en['grade_level']) ? $en['grade_level'] : '';
        $ssrStmt->execute([
            $enrollment_id,
            $en['student_id'] ?? null,
            $en['school_year'] ?? null,
            $gradeLevel,
            $en['lrn'] ?? null,
            $en['last_name'] ?? null,
            $en['first_name'] ?? null,
            $en['middle_name'] ?? null,
            $en['extension_name'] ?? null,
            $en['birth_date'] ?? null,
            $en['sex'] ?? null,
            $en['place_of_birth'] ?? null,
            $en['mother_tongue'] ?? null,
            $en['indigenous_group'] ?? null,
            $en['four_ps_household_id'] ?? null,
            $en['is_learner_with_disability'] ?? null,
            $en['is_returning_learner'] ?? null,
        ]);
        $schoolRecordId = intval($pdo->lastInsertId());

        // Build medical JSON from enrollment_medical_* tables
        $medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
        $medStmt->execute([$enrollment_id]);
        $med = $medStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $allergiesJson = null;
        $conditionsJson = null;
        $surgeriesJson = null;
        $treatmentsJson = null;
        $familyJson = null;

        if ($med) {
            $medical_id = $med['medical_id'] ?? 0;
            $allergies = $pdo->prepare('SELECT * FROM enrollment_medical_allergies WHERE medical_information_id = ?');
            $allergies->execute([$medical_id]);
            $a = $allergies->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($a)) $allergiesJson = json_encode($a);

            $conds = $pdo->prepare('SELECT * FROM enrollment_medical_conditions WHERE medical_information_id = ?');
            $conds->execute([$medical_id]);
            $c = $conds->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($c)) $conditionsJson = json_encode($c);

            $surg = $pdo->prepare('SELECT * FROM enrollment_medical_surgeries WHERE medical_information_id = ?');
            $surg->execute([$medical_id]);
            $su = $surg->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($su)) $surgeriesJson = json_encode($su);

            $treat = $pdo->prepare('SELECT * FROM enrollment_medical_treatments WHERE medical_information_id = ?');
            $treat->execute([$medical_id]);
            $tr = $treat->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tr)) $treatmentsJson = json_encode($tr);

            $fam = $pdo->prepare('SELECT * FROM enrollment_family_medical_history WHERE medical_information_id = ?');
            $fam->execute([$medical_id]);
            $fm = $fam->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($fm)) $familyJson = json_encode($fm);
        }

        $smrStmt = $pdo->prepare('INSERT INTO student_medical_records (school_record_id, exposed_to_cigarette_vape_smoke, other_pertinent_information, allergies, conditions, surgeries, treatments, family_medical_history) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $smrStmt->execute([
            $schoolRecordId,
            $med['exposed_to_cigarette_vape_smoke'] ?? null,
            $med['other_pertinent_information'] ?? null,
            $allergiesJson,
            $conditionsJson,
            $surgeriesJson,
            $treatmentsJson,
            $familyJson,
        ]);

        // Optionally mark enrollment verified if column exists
        try {
            // Prefer the schema's enrollment_status column; fall back to status if present
            $pdo->prepare('UPDATE enrollments SET enrollment_status = ? WHERE enrollment_id = ?')->execute(['verified', $enrollment_id]);
        } catch (Exception $e1) {
            try {
                $pdo->prepare('UPDATE enrollments SET status = ? WHERE enrollment_id = ?')->execute(['verified', $enrollment_id]);
            } catch (Exception $e2) {
                // ignore if neither column exists
            }
        }

        $pdo->commit();

        echo json_encode(['success' => true, 'school_record_id' => $schoolRecordId]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>
