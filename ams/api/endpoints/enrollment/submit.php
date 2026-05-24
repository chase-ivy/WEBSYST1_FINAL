<?php
// ============================================================
// endpoints/enrollment/submit.php
// Handles the full enrollment form submission as one transaction.
// Writes to: enrollments, enrollment_disabilities,
//   enrollment_returning_learners, enrollment_medical_information,
//   enrollment_medical_allergies, enrollment_medical_conditions,
//   enrollment_medical_surgeries, enrollment_medical_treatments,
//   enrollment_family_medical_history, student_addresses,
//   student_parent_guardians
//
// Does NOT write to student_school_records or
// student_medical_records. Those are created at verification.
//
// Accessible by: any logged-in user (student, staff, admin)
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

if (!is_logged_in()) {
    sendJson(['success' => false, 'error' => 'Unauthorized'], 401);
}

requireMethod('POST');

// ── Helpers ──────────────────────────────────────────────────

function normalizeCheckbox($value): int {
    return in_array((string)$value, ['1', 'true', 'yes', 'on', 'Yes'], true) ? 1 : 0;
}

function parseIds($value): array {
    if (is_array($value)) {
        return array_values(array_filter(array_map('intval', $value), fn($v) => $v > 0));
    }
    if ($value === null || $value === '') return [];
    $value = trim((string)$value);
    return $value === '' ? [] : [intval($value)];
}

function strOrNull($value): ?string {
    if (is_array($value)) {
        $value = implode(', ', array_filter(array_map('trim', $value), fn($v) => $v !== ''));
    }
    $value = trim((string)($value ?? ''));
    return $value === '' ? null : $value;
}

function resolveOrCreateLookup(PDO $pdo, string $table, string $idCol, string $nameCol, ?string $name): ?int {
    $name = trim((string)($name ?? ''));
    if ($name === '') return null;
    $stmt = $pdo->prepare("SELECT $idCol FROM $table WHERE LOWER($nameCol) = LOWER(?) AND is_active = 1 LIMIT 1");
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    if ($row) return intval($row[$idCol]);
    $ins = $pdo->prepare("INSERT INTO $table ($nameCol, is_active) VALUES (?, 1)");
    $ins->execute([$name]);
    return intval($pdo->lastInsertId());
}

function ownershipType($value): ?string {
    $map = [
        'rental'                => 'rented',
        'rented'                => 'rented',
        'owned'                 => 'owned',
        'living with relatives' => 'living_with_relatives',
        'living_with_relatives' => 'living_with_relatives',
        'inherited'             => 'inherited',
    ];
    if ($value === null) return null;
    $val = strtolower(trim((string)$value));
    return $map[$val] ?? null;
}

function insertGuardian(PDO $pdo, int $studentId, int $typeId, array $data, string $prefix): void {
    $lastName    = trim((string)($data["{$prefix}_last_name"]           ?? ''));
    $firstName   = trim((string)($data["{$prefix}_first_name"]          ?? ''));
    $middleName  = trim((string)($data["{$prefix}_middle_name"]         ?? ''));
    $contact     = trim((string)($data["{$prefix}_contact_number"]      ?? ''));
    $occupation  = strOrNull($data["{$prefix}_occupation"]              ?? null);
    $relStatus   = strOrNull($data["{$prefix}_relationship_status"]     ?? null);
    $fb          = strOrNull($data["{$prefix}_facebook_messenger"]      ?? null);
    $isEmergency = normalizeCheckbox($data["{$prefix}_is_emergency_contact"] ?? 0);
    $priority    = isset($data["{$prefix}_contact_priority"]) ? intval($data["{$prefix}_contact_priority"]) : null;
    $isVisible   = isset($data["{$prefix}_is_contact_visible"]) ? normalizeCheckbox($data["{$prefix}_is_contact_visible"]) : 1;

    if ($lastName === '' && $firstName === '' && $contact === '' && $occupation === null) return;

    $pdo->prepare('
        INSERT INTO student_parent_guardians
            (student_id, parent_guardian_type_id, last_name, first_name, middle_name,
             contact_number, occupation, relationship_status, facebook_messenger,
             is_emergency_contact, contact_priority, is_contact_visible)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $studentId, $typeId,
        $lastName, $firstName, $middleName, $contact,
        $occupation, $relStatus, $fb,
        $isEmergency, $priority, $isVisible,
    ]);
}

// ── Main ─────────────────────────────────────────────────────

$data = getJsonInput();

$studentId = intval($data['student_id'] ?? 0);
if ($studentId <= 0) {
    sendJson(['success' => false, 'error' => 'student_id is required'], 400);
}

$check = $pdo->prepare('SELECT student_id FROM students WHERE student_id = ? LIMIT 1');
$check->execute([$studentId]);
if (!$check->fetch()) {
    sendJson(['success' => false, 'error' => 'Invalid student_id'], 400);
}

$yearStart  = trim($data['year_start'] ?? '');
$yearEnd    = trim($data['year_end']   ?? '');
$schoolYear = ($yearStart !== '' && $yearEnd !== '')
    ? "$yearStart-$yearEnd"
    : trim($data['school_year'] ?? '');
if ($schoolYear === '') {
    sendJson(['success' => false, 'error' => 'School year is required'], 400);
}

$gradeLevel = strOrNull($data['Grade_Level'] ?? null);

// Mother tongue
$mtRaw        = trim((string)($data['Mother_Tongue'] ?? ''));
$motherTongueId = null;
if ($mtRaw !== '' && strcasecmp($mtRaw, 'Other') !== 0) {
    $motherTongueId = intval($mtRaw) ?: null;
} elseif (strcasecmp($mtRaw, 'Other') === 0) {
    $motherTongueId = resolveOrCreateLookup($pdo, 'mother_tongues', 'mother_tongue_id', 'name', $data['Mother_Tongue_Other'] ?? null);
}

// Indigenous
$isIp           = normalizeCheckbox($data['ip'] ?? 0);
$igRaw          = trim((string)($data['IP_Group'] ?? ''));
$indigenousGroupId = null;
if ($igRaw !== '' && strcasecmp($igRaw, 'Other') !== 0) {
    $indigenousGroupId = intval($igRaw) ?: null;
} elseif ($isIp && strcasecmp($igRaw, 'Other') === 0) {
    $indigenousGroupId = resolveOrCreateLookup($pdo, 'indigenous_groups', 'indigenous_group_id', 'name', $data['IP_Specify'] ?? null);
}

$isFourPs    = normalizeCheckbox($data['fourps'] ?? 0);
$fourPsId    = $isFourPs ? strOrNull($data['FourPs_Specify'] ?? null) : null;
$isDisabled  = (!empty($data['disabilityDetails']) || !empty($data['disability_sub'])) ? 1 : 0;
$isReturning = trim((string)($data['Returning_Grade_Level'] ?? '')) !== '' ? 1 : 0;

// Queue number per school year
$queueStmt = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE school_year = ?');
$queueStmt->execute([$schoolYear]);
$queueNumber = intval($queueStmt->fetchColumn()) + 1;

try {
    $pdo->beginTransaction();

    // 1. enrollments
    $pdo->prepare('
        INSERT INTO enrollments
            (student_id, school_year, grade_level, enrollment_status, queue_number,
             mother_tongue_id, is_indigenous, indigenous_group_id,
             is_four_ps_beneficiary, four_ps_household_id,
             is_learner_with_disability, is_returning_learner)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $studentId, $schoolYear, $gradeLevel, 'pending', $queueNumber,
        $motherTongueId, $isIp, $indigenousGroupId,
        $isFourPs, $fourPsId,
        $isDisabled, $isReturning,
    ]);
    $enrollmentId = intval($pdo->lastInsertId());

    // 2. current address
    $sameAddress = isset($data['same_address']) && $data['same_address'] === 'Yes';
    $addrStmt = $pdo->prepare('
        INSERT INTO student_addresses
            (student_id, address_type, house_no, street_name, barangay,
             municipality_city, province, country, zip_code, enrollment_id, ownership_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $addrStmt->execute([
        $studentId, 'current',
        strOrNull($data['Current_House_No']          ?? null),
        strOrNull($data['Current_Street_Name']       ?? null),
        strOrNull($data['Current_Barangay']          ?? null),
        strOrNull($data['Current_Municipality_City'] ?? null),
        strOrNull($data['Current_Province']          ?? null),
        strOrNull($data['Current_Country']           ?? null) ?? 'Philippines',
        strOrNull($data['Current_Zip_Code']          ?? null),
        $enrollmentId,
        ownershipType($data['Current_Address_Status'] ?? null),
    ]);

    // 3. permanent address (copy current if same_address)
    $permKeys = ['House_No', 'Street_Name', 'Barangay', 'Municipality_City', 'Province', 'Country', 'Zip_Code'];
    $permData = [];
    foreach ($permKeys as $key) {
        $permData[$key] = strOrNull($sameAddress ? ($data["Current_$key"] ?? null) : ($data["Permanent_$key"] ?? null));
    }

    $addrStmt->execute([
        $studentId, 'permanent',
        $permData['House_No'], $permData['Street_Name'], $permData['Barangay'],
        $permData['Municipality_City'], $permData['Province'],
        $permData['Country'] ?? 'Philippines',
        $permData['Zip_Code'],
        $enrollmentId,
        ownershipType($sameAddress ? ($data['Current_Address_Status'] ?? null) : ($data['Permanent_Address_Status'] ?? null)),
    ]);

    // 4. guardians (Father=1, Mother=2, Guardian=3)
    insertGuardian($pdo, $studentId, 1, $data, 'father');
    insertGuardian($pdo, $studentId, 2, $data, 'mother');
    insertGuardian($pdo, $studentId, 3, $data, 'guardian');

    // 5. medical information parent record
    $pdo->prepare('
        INSERT INTO enrollment_medical_information
            (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information)
        VALUES (?, ?, ?)
    ')->execute([
        $enrollmentId,
        normalizeCheckbox($data['exposed_to_cigarette_vape_smoke'] ?? 0),
        strOrNull($data['other_pertinent_information'] ?? null),
    ]);
    $medicalInfoId = intval($pdo->lastInsertId());

    // 6. allergies
    $allergyTypeIds  = parseIds($data['medicine_allergy'] ?? []);
    $allergyDescs    = $data['allergy_description'] ?? [];
    if (!is_array($allergyDescs)) {
        $allergyDescs = ['default' => trim((string)$allergyDescs)];
    }
    if (!empty($allergyTypeIds)) {
        $stmt = $pdo->prepare('INSERT INTO enrollment_medical_allergies (medical_information_id, allergy_type_id, description) VALUES (?, ?, ?)');
        foreach ($allergyTypeIds as $typeId) {
            $stmt->execute([
                $medicalInfoId, $typeId,
                strOrNull($allergyDescs[$typeId] ?? $allergyDescs['default'] ?? null),
            ]);
        }
    }

    // 7. conditions
    $conditionTypeIds = parseIds($data['condition_type_id'] ?? []);
    $conditionDesc    = strOrNull($data['condition_description'] ?? null);
    if (!empty($conditionTypeIds)) {
        $stmt = $pdo->prepare('INSERT INTO enrollment_medical_conditions (medical_information_id, condition_type_id, description) VALUES (?, ?, ?)');
        foreach ($conditionTypeIds as $typeId) {
            $stmt->execute([$medicalInfoId, $typeId, $conditionDesc]);
        }
    }

    // 8. surgeries
    $hasSurgery  = normalizeCheckbox($data['has_surgery_hospitalization'] ?? 0);
    $surgeryDate = strOrNull($data['surgery_date']  ?? null);
    $hospital    = strOrNull($data['hospital_name'] ?? null);
    $bodyPart    = strOrNull($data['body_part']     ?? null);
    if ($hasSurgery || $surgeryDate || $hospital || $bodyPart) {
        $pdo->prepare('INSERT INTO enrollment_medical_surgeries (medical_information_id, surgery_date, hospital_name, body_part) VALUES (?, ?, ?, ?)')
            ->execute([$medicalInfoId, $surgeryDate, $hospital, $bodyPart]);
    }

    // 9. treatments
    $hasTreatment = normalizeCheckbox($data['is_taking_treatment'] ?? 0);
    $medicine     = strOrNull($data['treatment_medicine'] ?? null);
    $dosage       = strOrNull($data['schedule_dosage']    ?? null);
    if ($hasTreatment || $medicine || $dosage) {
        $pdo->prepare('INSERT INTO enrollment_medical_treatments (medical_information_id, treatment_medicine, schedule_dosage) VALUES (?, ?, ?)')
            ->execute([$medicalInfoId, $medicine, $dosage]);
    }

    // 10. family medical history
    $familyTypeIds = parseIds($data['family_condition_type_id'] ?? []);
    $familyDesc    = strOrNull($data['family_condition_description'] ?? null);
    if (!empty($familyTypeIds)) {
        $stmt = $pdo->prepare('INSERT INTO enrollment_family_medical_history (medical_information_id, family_history_type_id, description) VALUES (?, ?, ?)');
        foreach ($familyTypeIds as $typeId) {
            $stmt->execute([$medicalInfoId, $typeId, $familyDesc]);
        }
    }

    // 11. disabilities
    $disabilityRows = [];
    $processedTypes = [];

    if (!empty($data['disability_sub']) && is_array($data['disability_sub'])) {
        foreach ($data['disability_sub'] as $typeId => $values) {
            $typeId = intval($typeId);
            if ($typeId === 0 || !is_array($values)) continue;
            $processedTypes[$typeId] = true;
            $subtypeIds = array_unique(array_map('intval', array_filter($values, fn($v) => $v !== '')));
            if (empty($subtypeIds)) {
                $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => null];
            } else {
                foreach ($subtypeIds as $subtypeId) {
                    $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => $subtypeId];
                }
            }
        }
    }

    if (!empty($data['disabilityDetails']) && is_array($data['disabilityDetails'])) {
        foreach ($data['disabilityDetails'] as $typeId => $values) {
            $typeId = intval($typeId);
            if ($typeId === 0 || !is_array($values) || isset($processedTypes[$typeId])) continue;
            $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => null];
        }
    }

    if (!empty($disabilityRows)) {
        $stmt = $pdo->prepare('INSERT INTO enrollment_disabilities (enrollment_id, disability_type_id, disability_subtype_id) VALUES (?, ?, ?)');
        foreach ($disabilityRows as $row) {
            $stmt->execute([$enrollmentId, $row['type_id'], $row['subtype_id']]);
        }
    }

    // 12. returning learner
    if ($isReturning) {
        $pdo->prepare('
            INSERT INTO enrollment_returning_learners
                (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed, school_id)
            VALUES (?, ?, ?, ?, ?)
        ')->execute([
            $enrollmentId,
            strOrNull($data['Returning_Grade_Level']      ?? null),
            strOrNull($data['Last_School_Attended']       ?? null),
            strOrNull($data['Last_School_Year_Completed'] ?? null),
            strOrNull($data['school_ID']                  ?? null),
        ]);
    }

    $pdo->commit();

    sendJson([
        'success'       => true,
        'enrollment_id' => $enrollmentId,
        'student_id'    => $studentId,
        'queue_number'  => $queueNumber,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}