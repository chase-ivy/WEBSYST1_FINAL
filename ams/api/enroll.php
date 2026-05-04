<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
if ($action !== 'create') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

function parseDisabilityRows(array $data): array {
    $rows = [];
    if (empty($data['disabilityDetails']) || !is_array($data['disabilityDetails'])) {
        return $rows;
    }

    foreach ($data['disabilityDetails'] as $typeId => $values) {
        $typeId = intval($typeId);
        if ($typeId === 0 || !is_array($values)) {
            continue;
        }

        $subtypes = [];
        if (!empty($data['disability_sub'][$typeId]) && is_array($data['disability_sub'][$typeId])) {
            foreach ($data['disability_sub'][$typeId] as $subId) {
                $subtypes[] = intval($subId);
            }
        }

        if (!empty($subtypes)) {
            foreach (array_unique($subtypes) as $subId) {
                $rows[] = ['type_id' => $typeId, 'subtype_id' => $subId];
            }
        } else {
            $rows[] = ['type_id' => $typeId, 'subtype_id' => null];
        }
    }

    return $rows;
}

function insertParent(PDO $pdo, int $enrollmentId, string $relationship, string $lastName, string $firstName, string $middleName, string $contactNumber): ?int {
    $lastName = trim($lastName);
    $firstName = trim($firstName);
    $middleName = trim($middleName);
    $contactNumber = trim($contactNumber);

    if ($lastName === '' && $firstName === '' && $middleName === '' && $contactNumber === '') {
        return null;
    }

    $stmt = $pdo->prepare('INSERT INTO parents (last_name, first_name, middle_name, contact_number) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $lastName,
        $firstName,
        $middleName,
        $contactNumber
    ]);

    $parentId = intval($pdo->lastInsertId());
    if ($parentId > 0) {
        $link = $pdo->prepare('INSERT INTO enrollment_parents (enrollment_id, parent_id, relationship) VALUES (?, ?, ?)');
        $link->execute([$enrollmentId, $parentId, $relationship]);
    }

    return $parentId > 0 ? $parentId : null;
}

try {
    $pdo->beginTransaction();

    $schoolYear = trim(($data['year_start'] ?? '') . '-' . ($data['year_end'] ?? ''));
    $withLrn = (!empty($data['with_lrn']) && $data['with_lrn'] === 'Yes') ? 1 : 0;
    $returning = (!empty($data['returning']) && $data['returning'] === 'Yes') ? 1 : 0;
    $ipValue = (isset($data['ip']) && $data['ip'] === 'Yes') ? trim($data['IP_Specify'] ?? '') : null;
    $fourpsValue = (isset($data['fourps']) && $data['fourps'] === 'Yes') ? trim($data['FourPs_Specify'] ?? '') : null;
    $isLearnerWithDisability = !empty($data['disabilityDetails']) ? 1 : 0;
    $age = isset($data['Birth_Date']) && trim($data['Birth_Date']) !== '' ? (int) floor((time() - strtotime($data['Birth_Date'])) / 31557600) : null;

    $stmt = $pdo->prepare('INSERT INTO students (
        lrn, last_name, first_name, middle_name, extension_name,
        birth_date, sex, place_of_birth
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');

    $stmt->execute([
        trim($data['Learner_Reference_No'] ?? ''),
        trim($data['Learner_Last_Name'] ?? ''),
        trim($data['Learner_First_Name'] ?? ''),
        trim($data['Learner_Middle_Name'] ?? ''),
        trim($data['Learner_Extension_Name'] ?? ''),
        trim($data['Birth_Date'] ?? ''),
        trim($data['sex'] ?? ''),
        trim($data['Place_of_Birth'] ?? '')
    ]);

    $studentId = intval($pdo->lastInsertId());

    $enrollmentStmt = $pdo->prepare('INSERT INTO enrollments (
        student_id, school_year, grade_level, with_lrn, psa_bcn,
        age, mother_tongue, is_indigenous, indigenous_group, is_four_ps_beneficiary,
        four_ps_household_id, is_learner_with_disability, is_returning_learner
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

    $enrollmentStmt->execute([
        $studentId,
        $schoolYear,
        trim($data['Grade_Level'] ?? ''),
        $withLrn,
        trim($data['psa_bcn'] ?? ''),
        $age,
        trim($data['Mother_Tongue'] ?? ''),
        !empty($ipValue) ? 1 : 0,
        $ipValue,
        !empty($fourpsValue) ? 1 : 0,
        trim($data['FourPs_Specify'] ?? ''),
        $isLearnerWithDisability,
        $returning
    ]);

    $enrollmentId = intval($pdo->lastInsertId());

    $permHouse = trim($data['Permanent_House_No'] ?? '');
    $permStreet = trim($data['Permanent_Street_Name'] ?? '');
    $permBarangay = trim($data['Permanent_Barangay'] ?? '');
    $permCity = trim($data['Permanent_Municipality_City'] ?? '');
    $permProvince = trim($data['Permanent_Province'] ?? '');
    $permCountry = trim($data['Permanent_Country'] ?? '');
    $permZip = trim($data['Permanent_Zip_Code'] ?? '');

    if (isset($data['same_address']) && $data['same_address'] === 'Yes') {
        $permHouse = trim($data['Current_House_No'] ?? '');
        $permStreet = trim($data['Current_Street_Name'] ?? '');
        $permBarangay = trim($data['Current_Barangay'] ?? '');
        $permCity = trim($data['Current_Municipality_City'] ?? '');
        $permProvince = trim($data['Current_Province'] ?? '');
        $permCountry = trim($data['Current_Country'] ?? '');
        $permZip = trim($data['Current_Zip_Code'] ?? '');
    }

    $addressStmt = $pdo->prepare('INSERT INTO addresses (enrollment_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $addressStmt->execute([
        $enrollmentId,
        'current',
        trim($data['Current_House_No'] ?? ''),
        trim($data['Current_Street_Name'] ?? ''),
        trim($data['Current_Barangay'] ?? ''),
        trim($data['Current_Municipality_City'] ?? ''),
        trim($data['Current_Province'] ?? ''),
        trim($data['Current_Country'] ?? ''),
        trim($data['Current_Zip_Code'] ?? '')
    ]);

    $addressStmt->execute([
        $enrollmentId,
        'permanent',
        $permHouse,
        $permStreet,
        $permBarangay,
        $permCity,
        $permProvince,
        $permCountry,
        $permZip
    ]);

    $disabilityRows = parseDisabilityRows($data);
    if (!empty($disabilityRows)) {
        $disabilityStmt = $pdo->prepare('INSERT INTO student_disabilities (enrollment_id, disability_type_id, disability_subtype_id) VALUES (?, ?, ?)');
        foreach ($disabilityRows as $disabilityRow) {
            $disabilityStmt->execute([
                $enrollmentId,
                $disabilityRow['type_id'],
                $disabilityRow['subtype_id']
            ]);
        }
    }

    insertParent($pdo, $enrollmentId, 'father', $data['father_last_name'] ?? '', $data['father_first_name'] ?? '', $data['father_middle_name'] ?? '', $data['father_contact_number'] ?? '');
    insertParent($pdo, $enrollmentId, 'mother', $data['mother_last_name'] ?? '', $data['mother_first_name'] ?? '', $data['mother_middle_name'] ?? '', $data['mother_contact_number'] ?? '');
    insertParent($pdo, $enrollmentId, 'guardian', $data['guardian_last_name'] ?? '', $data['guardian_first_name'] ?? '', $data['guardian_middle_name'] ?? '', $data['guardian_contact_number'] ?? '');

    if ($returning === 1) {
        $returningStmt = $pdo->prepare('INSERT INTO returning_learners (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed, school_id) VALUES (?, ?, ?, ?, ?)');
        $returningStmt->execute([
            $enrollmentId,
            trim($data['Returning_Grade_Level'] ?? ''),
            trim($data['Last_School_Attended'] ?? ''),
            trim($data['Last_School_Year_Completed'] ?? ''),
            trim($data['school_ID'] ?? '')
        ]);
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'student_id' => $studentId,
        'enrollment_id' => $enrollmentId
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
