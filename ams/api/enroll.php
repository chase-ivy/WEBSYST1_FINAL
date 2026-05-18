<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

if (!ob_get_level()) {
    ob_start();
}

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

// ─────────────────────────────────────────────────────────────────────────────
// Helper functions
// ─────────────────────────────────────────────────────────────────────────────

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

/**
 * Insert a parent/guardian into student_parent_guardians.
 * parent_guardian_type_id: 1=Father, 2=Mother, 3=Guardian
 */
function insertParentGuardian(PDO $pdo, int $studentId, int $typeId, string $lastName, string $firstName, string $middleName, string $contactNumber): void {
    $lastName    = trim($lastName);
    $firstName   = trim($firstName);
    $middleName  = trim($middleName);
    $contactNumber = trim($contactNumber);

    // Skip entirely empty records
    if ($lastName === '' && $firstName === '' && $middleName === '' && $contactNumber === '') {
        return;
    }

    $stmt = $pdo->prepare('
        INSERT INTO student_parent_guardians
            (student_id, parent_guardian_type_id, last_name, first_name, middle_name, contact_number)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$studentId, $typeId, $lastName, $firstName, $middleName, $contactNumber]);
}

/**
 * Create a users row for the new student and return the user_id.
 * The student row must already exist so we can link it afterwards.
 */
function createUserForStudent(PDO $pdo, string $firstName, string $lastName, string $lrn, string $email = '', string $password = ''): ?int {
    $base = strtolower(preg_replace('/[^a-z0-9._-]/i', '', str_replace(' ', '.', "$firstName.$lastName")));
    $username = $base;
    $suffix   = 1;
    while (true) {
        $chk = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $chk->execute([$username]);
        if ((int)$chk->fetchColumn() === 0) break;
        $username = $base . $suffix++;
    }

    if ($email === '') {
        $email = $username . '@student.local';
    }
    if ($password === '') {
        $password = ($lrn !== '') ? $lrn : bin2hex(random_bytes(4));
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), 'student']);
        $id = intval($pdo->lastInsertId());
        return $id > 0 ? $id : null;
    } catch (Exception $e) {
        return null;
    }
}

function normalizeCheckboxValue($value): int {
    return in_array((string)$value, ['1', 'true', 'yes', 'on', 'Yes'], true) ? 1 : 0;
}

function parseIdsValue($value): array {
    if (is_array($value)) {
        return array_values(array_filter(array_map('intval', $value), fn($v) => $v > 0));
    }
    if ($value === null || $value === '') return [];
    return [intval($value)];
}

function getStringValue($value): ?string {
    if (is_array($value)) {
        $value = implode(', ', array_filter(array_map('trim', $value), fn($v) => $v !== ''));
    }
    $value = trim((string)($value ?? ''));
    return $value === '' ? null : $value;
}

/**
 * Resolve a free-text mother tongue name to its mother_tongue_id.
 * If the name is not found, insert it and return the new ID.
 * Returns null if the incoming name is blank.
 */
function resolveMotherTongueId(PDO $pdo, ?string $name): ?int {
    $name = trim((string)$name);
    if ($name === '') return null;

    $stmt = $pdo->prepare('SELECT mother_tongue_id FROM mother_tongues WHERE LOWER(name) = LOWER(?) AND is_active = 1 LIMIT 1');
    $stmt->execute([$name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return (int)$row['mother_tongue_id'];
    }

    $insert = $pdo->prepare('INSERT INTO mother_tongues (name, is_active) VALUES (?, 1)');
    $insert->execute([$name]);
    return intval($pdo->lastInsertId());
}

/**
 * Resolve a free-text indigenous group name to its indigenous_group_id.
 * If the name is not found, insert it and return the new ID.
 * Returns null if the incoming name is blank.
 */
function resolveIndigenousGroupId(PDO $pdo, ?string $name): ?int {
    $name = trim((string)$name);
    if ($name === '') return null;

    $stmt = $pdo->prepare('SELECT indigenous_group_id FROM indigenous_groups WHERE LOWER(name) = LOWER(?) AND is_active = 1 LIMIT 1');
    $stmt->execute([$name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return (int)$row['indigenous_group_id'];
    }

    $insert = $pdo->prepare('INSERT INTO indigenous_groups (name, is_active) VALUES (?, 1)');
    $insert->execute([$name]);
    return intval($pdo->lastInsertId());
}

// ─────────────────────────────────────────────────────────────────────────────
// Main transaction
// ─────────────────────────────────────────────────────────────────────────────

try {
    $pdo->beginTransaction();

    // ── Derived values ────────────────────────────────────────────────────────
    $schoolYear  = trim(($data['year_start'] ?? '') . '-' . ($data['year_end'] ?? ''));
    $withLrn     = !empty($data['with_lrn'])  && in_array((string)$data['with_lrn'],  ['1','Yes'], true) ? 1 : 0;
    $returning   = !empty($data['returning']) && in_array((string)$data['returning'], ['1','Yes'], true) ? 1 : 0;
    $isIp        = (isset($data['ip'])     && $data['ip']     === 'Yes') ? 1 : 0;
    $isFourPs    = (isset($data['fourps']) && $data['fourps'] === 'Yes') ? 1 : 0;
    $fourPsId    = $isFourPs ? trim($data['FourPs_Specify'] ?? '') : null;
    $isDisabled  = !empty($data['disabilityDetails']) ? 1 : 0;

    $lrnValue    = trim($data['Learner_Reference_No'] ?? '') ?: null;
    $motherTongueName  = trim($data['Mother_Tongue'] ?? '');
    if ($motherTongueName === 'Other') {
        $motherTongueName = trim($data['Mother_Tongue_Other'] ?? '');
    }

    $indigenousName = null;
    if ($isIp) {
        $selectedIpGroup = trim($data['IP_Group'] ?? '');
        $indigenousName  = ($selectedIpGroup === 'Other') ? trim($data['IP_Specify'] ?? '') : $selectedIpGroup;
    }

    // Resolve FK IDs from lookup tables
    $motherTongueId   = resolveMotherTongueId($pdo, $motherTongueName);
    $indigenousGroupId = resolveIndigenousGroupId($pdo, $indigenousName);

    // ── 1. Create user account first (students.user_id is NOT NULL) ───────────
    $firstName    = trim($data['Learner_First_Name']  ?? '');
    $lastName     = trim($data['Learner_Last_Name']   ?? '');
    $userEmail    = trim($data['user_email']    ?? '');
    $userPassword = trim($data['user_password'] ?? '');

    $userId = createUserForStudent($pdo, $firstName, $lastName, $lrnValue ?? '', $userEmail, $userPassword);
    if ($userId === null) {
        throw new Exception('Failed to create user account for student.');
    }

    // ── 2. Insert student ─────────────────────────────────────────────────────
    $stmt = $pdo->prepare('
        INSERT INTO students
            (user_id, lrn, last_name, first_name, middle_name, extension_name,
             birth_date, sex, place_of_birth)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $userId,
        $lrnValue,
        $lastName,
        $firstName,
        trim($data['Learner_Middle_Name']    ?? '') ?: null,
        trim($data['Learner_Extension_Name'] ?? '') ?: null,
        trim($data['Birth_Date']             ?? ''),
        trim($data['sex']                    ?? ''),
        trim($data['Place_of_Birth']         ?? '') ?: null,
    ]);
    $studentId = intval($pdo->lastInsertId());

    // ── 3. Insert enrollment ──────────────────────────────────────────────────
    $enrollStmt = $pdo->prepare('
        INSERT INTO enrollments
            (student_id, school_year, mother_tongue_id,
             is_indigenous, indigenous_group_id,
             is_four_ps_beneficiary, four_ps_household_id,
             is_learner_with_disability, is_returning_learner)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $enrollStmt->execute([
        $studentId,
        $schoolYear,
        $motherTongueId,
        $isIp,
        $indigenousGroupId,
        $isFourPs,
        $fourPsId,
        $isDisabled,
        $returning,
    ]);
    $enrollmentId = intval($pdo->lastInsertId());

    // ── 4. Insert student_school_records (snapshot) ───────────────────────────
    $gradeLevel = trim($data['Grade_Level'] ?? '');
    $ssrStmt = $pdo->prepare('
        INSERT INTO student_school_records
            (enrollment_id, student_id, school_year, grade_level,
             lrn, last_name, first_name, middle_name, extension_name,
             birth_date, sex, place_of_birth,
             mother_tongue, indigenous_group, four_ps_household_id,
             is_learner_with_disability, is_returning_learner)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $ssrStmt->execute([
        $enrollmentId,
        $studentId,
        $schoolYear,
        $gradeLevel,
        $lrnValue,
        $lastName,
        $firstName,
        trim($data['Learner_Middle_Name']    ?? '') ?: null,
        trim($data['Learner_Extension_Name'] ?? '') ?: null,
        trim($data['Birth_Date']             ?? ''),
        trim($data['sex']                    ?? ''),
        trim($data['Place_of_Birth']         ?? '') ?: null,
        $motherTongueName ?: null,
        $indigenousName,
        $fourPsId,
        $isDisabled,
        $returning,
    ]);
    $schoolRecordId = intval($pdo->lastInsertId());

    // ── 5. Addresses → student_addresses (keyed by student_id) ───────────────
    $permHouse    = trim($data['Permanent_House_No']          ?? '');
    $permStreet   = trim($data['Permanent_Street_Name']       ?? '');
    $permBarangay = trim($data['Permanent_Barangay']          ?? '');
    $permCity     = trim($data['Permanent_Municipality_City'] ?? '');
    $permProvince = trim($data['Permanent_Province']          ?? '');
    $permCountry  = trim($data['Permanent_Country']           ?? '');
    $permZip      = trim($data['Permanent_Zip_Code']          ?? '');

    if (isset($data['same_address']) && $data['same_address'] === 'Yes') {
        $permHouse    = trim($data['Current_House_No']          ?? '');
        $permStreet   = trim($data['Current_Street_Name']       ?? '');
        $permBarangay = trim($data['Current_Barangay']          ?? '');
        $permCity     = trim($data['Current_Municipality_City'] ?? '');
        $permProvince = trim($data['Current_Province']          ?? '');
        $permCountry  = trim($data['Current_Country']           ?? '');
        $permZip      = trim($data['Current_Zip_Code']          ?? '');
    }

    $addrStmt = $pdo->prepare('
        INSERT INTO student_addresses
            (student_id, address_type, house_no, street_name, barangay,
             municipality_city, province, country, zip_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $addrStmt->execute([
        $studentId, 'current',
        trim($data['Current_House_No']          ?? ''),
        trim($data['Current_Street_Name']       ?? ''),
        trim($data['Current_Barangay']          ?? ''),
        trim($data['Current_Municipality_City'] ?? ''),
        trim($data['Current_Province']          ?? ''),
        trim($data['Current_Country']           ?? '') ?: 'Philippines',
        trim($data['Current_Zip_Code']          ?? ''),
    ]);
    $addrStmt->execute([
        $studentId, 'permanent',
        $permHouse, $permStreet, $permBarangay,
        $permCity, $permProvince,
        $permCountry ?: 'Philippines',
        $permZip,
    ]);

    // ── 6. Medical information → enrollment_medical_information ──────────────
    $exposedToSmoke        = normalizeCheckboxValue($data['exposed_to_cigarette_vape_smoke'] ?? 0);
    $otherPertinentInfo    = getStringValue($data['other_pertinent_information'] ?? null);

    $medStmt = $pdo->prepare('
        INSERT INTO enrollment_medical_information
            (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information)
        VALUES (?, ?, ?)
    ');
    $medStmt->execute([$enrollmentId, $exposedToSmoke, $otherPertinentInfo]);
    $medicalInfoId = intval($pdo->lastInsertId());

    // ── 7. Allergies → enrollment_medical_allergies ───────────────────────────
    $allergyTypeIds     = parseIdsValue($data['medicine_allergy'] ?? []);
    $allergyDescriptions = $data['allergy_description'] ?? [];
    if (!is_array($allergyDescriptions)) {
        $allergyDescriptions = ['default' => trim((string)$allergyDescriptions)];
    }
    if (!empty($allergyTypeIds)) {
        $allergyStmt = $pdo->prepare('
            INSERT INTO enrollment_medical_allergies
                (medical_information_id, allergy_type_id, description)
            VALUES (?, ?, ?)
        ');
        foreach ($allergyTypeIds as $typeId) {
            $desc = trim((string)($allergyDescriptions[$typeId] ?? $allergyDescriptions['default'] ?? ''));
            $allergyStmt->execute([$medicalInfoId, $typeId, $desc !== '' ? $desc : null]);
        }
    }

    // ── 8. Medical conditions → enrollment_medical_conditions ─────────────────
    $conditionTypeIds   = parseIdsValue($data['condition_type_id'] ?? []);
    $conditionDesc      = getStringValue($data['condition_description'] ?? null);
    if (!empty($conditionTypeIds)) {
        $condStmt = $pdo->prepare('
            INSERT INTO enrollment_medical_conditions
                (medical_information_id, condition_type_id, description)
            VALUES (?, ?, ?)
        ');
        foreach ($conditionTypeIds as $typeId) {
            $condStmt->execute([$medicalInfoId, $typeId, $conditionDesc]);
        }
    }

    // ── 9. Surgery → enrollment_medical_surgeries ────────────────────────────
    $hasSurgery   = normalizeCheckboxValue($data['has_surgery_hospitalization'] ?? 0);
    $surgeryDate  = getStringValue($data['surgery_date']   ?? null);
    $hospitalName = getStringValue($data['hospital_name']  ?? null);
    $bodyPart     = getStringValue($data['body_part']       ?? null);
    if ($hasSurgery || $surgeryDate || $hospitalName || $bodyPart) {
        $surgStmt = $pdo->prepare('
            INSERT INTO enrollment_medical_surgeries
                (medical_information_id, surgery_date, hospital_name, body_part)
            VALUES (?, ?, ?, ?)
        ');
        $surgStmt->execute([$medicalInfoId, $surgeryDate, $hospitalName, $bodyPart]);
    }

    // ── 10. Treatments → enrollment_medical_treatments ───────────────────────
    $isTakingTreatment = normalizeCheckboxValue($data['is_taking_treatment'] ?? 0);
    $treatmentMedicine = getStringValue($data['treatment_medicine'] ?? null);
    $scheduleDosage    = getStringValue($data['schedule_dosage']    ?? null);
    if ($isTakingTreatment || $treatmentMedicine || $scheduleDosage) {
        $treatStmt = $pdo->prepare('
            INSERT INTO enrollment_medical_treatments
                (medical_information_id, treatment_medicine, schedule_dosage)
            VALUES (?, ?, ?)
        ');
        $treatStmt->execute([$medicalInfoId, $treatmentMedicine, $scheduleDosage]);
    }

    // ── 11. Family medical history → enrollment_family_medical_history ────────
    $familyConditionTypeIds = parseIdsValue($data['family_condition_type_id'] ?? []);
    $familyConditionDesc    = getStringValue($data['family_condition_description'] ?? null);
    if (!empty($familyConditionTypeIds)) {
        $famStmt = $pdo->prepare('
            INSERT INTO enrollment_family_medical_history
                (medical_information_id, family_history_type_id, description)
            VALUES (?, ?, ?)
        ');
        foreach ($familyConditionTypeIds as $typeId) {
            $famStmt->execute([$medicalInfoId, $typeId, $familyConditionDesc]);
        }
    }

    // ── 12. Disabilities → enrollment_disabilities ────────────────────────────
    $disabilityRows = parseDisabilityRows($data);
    if (!empty($disabilityRows)) {
        $disStmt = $pdo->prepare('
            INSERT INTO enrollment_disabilities
                (enrollment_id, disability_type_id, disability_subtype_id)
            VALUES (?, ?, ?)
        ');
        foreach ($disabilityRows as $row) {
            $disStmt->execute([$enrollmentId, $row['type_id'], $row['subtype_id']]);
        }
    }

    // ── 13. Parents/guardians → student_parent_guardians ─────────────────────
    // parent_guardian_type_id: 1=Father, 2=Mother, 3=Guardian
    insertParentGuardian($pdo, $studentId, 1,
        $data['father_last_name']    ?? '', $data['father_first_name']    ?? '',
        $data['father_middle_name']  ?? '', $data['father_contact_number'] ?? '');

    insertParentGuardian($pdo, $studentId, 2,
        $data['mother_last_name']    ?? '', $data['mother_first_name']    ?? '',
        $data['mother_middle_name']  ?? '', $data['mother_contact_number'] ?? '');

    insertParentGuardian($pdo, $studentId, 3,
        $data['guardian_last_name']   ?? '', $data['guardian_first_name']   ?? '',
        $data['guardian_middle_name'] ?? '', $data['guardian_contact_number'] ?? '');

    // ── 14. Returning learner → enrollment_returning_learners ─────────────────
    if ($returning === 1) {
        $retStmt = $pdo->prepare('
            INSERT INTO enrollment_returning_learners
                (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed)
            VALUES (?, ?, ?, ?)
        ');
        $retStmt->execute([
            $enrollmentId,
            trim($data['Returning_Grade_Level']      ?? '') ?: null,
            trim($data['Last_School_Attended']        ?? '') ?: null,
            trim($data['Last_School_Year_Completed']  ?? '') ?: null,
        ]);
    }

    // ── 15. student_medical_records (JSON snapshot linked to school record) ───
    $allergiesJson = null;
    if (!empty($allergyTypeIds)) {
        $allergiesJson = json_encode(array_map(fn($id) => [
            'allergy_type_id' => $id,
            'description'     => trim((string)($allergyDescriptions[$id] ?? $allergyDescriptions['default'] ?? '')) ?: null,
        ], $allergyTypeIds));
    }
    $conditionsJson = null;
    if (!empty($conditionTypeIds)) {
        $conditionsJson = json_encode(array_map(fn($id) => [
            'condition_type_id' => $id,
            'description'       => $conditionDesc,
        ], $conditionTypeIds));
    }
    $surgeriesJson = ($hasSurgery || $surgeryDate) ? json_encode([[
        'surgery_date'  => $surgeryDate,
        'hospital_name' => $hospitalName,
        'body_part'     => $bodyPart,
    ]]) : null;
    $treatmentsJson = ($isTakingTreatment || $treatmentMedicine) ? json_encode([[
        'treatment_medicine' => $treatmentMedicine,
        'schedule_dosage'    => $scheduleDosage,
    ]]) : null;
    $familyHistoryJson = null;
    if (!empty($familyConditionTypeIds)) {
        $familyHistoryJson = json_encode(array_map(fn($id) => [
            'family_history_type_id' => $id,
            'description'            => $familyConditionDesc,
        ], $familyConditionTypeIds));
    }

    $smrStmt = $pdo->prepare('
        INSERT INTO student_medical_records
            (school_record_id, exposed_to_cigarette_vape_smoke, other_pertinent_information,
             allergies, conditions, surgeries, treatments, family_medical_history)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $smrStmt->execute([
        $schoolRecordId,
        $exposedToSmoke,
        $otherPertinentInfo,
        $allergiesJson,
        $conditionsJson,
        $surgeriesJson,
        $treatmentsJson,
        $familyHistoryJson,
    ]);

    // ── Commit ────────────────────────────────────────────────────────────────
    $pdo->commit();

    if (ob_get_length() !== false) ob_end_clean();

    echo json_encode([
        'success'       => true,
        'student_id'    => $studentId,
        'enrollment_id' => $enrollmentId,
        'user_id'       => $userId,
        'message'       => 'Student enrolled successfully.',
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (ob_get_length() !== false) ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}