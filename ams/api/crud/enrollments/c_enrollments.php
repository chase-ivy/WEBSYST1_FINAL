<?php
require_once __DIR__ . "/../../crud_base.php";

function normalizeCheckboxValue($value): int {
    return in_array((string)$value, ['1', 'true', 'yes', 'on', 'Yes'], true) ? 1 : 0;
}

function parseIdsValue($value): array {
    if (is_array($value)) {
        return array_values(array_filter(array_map('intval', $value), fn($v) => $v > 0));
    }
    if ($value === null || $value === '') {
        return [];
    }
    $value = trim((string)$value);
    if ($value === '') {
        return [];
    }
    return [intval($value)];
}

function getStringValue($value): ?string {
    if (is_array($value)) {
        $value = implode(', ', array_filter(array_map('trim', $value), fn($v) => $v !== ''));
    }
    $value = trim((string)($value ?? ''));
    return $value === '' ? null : $value;
}

function resolveLookupId(PDO $pdo, string $table, string $idColumn, string $nameColumn, ?string $name): ?int {
    $name = trim((string)($name ?? ''));
    if ($name === '') {
        return null;
    }

    $stmt = $pdo->prepare("SELECT $idColumn FROM $table WHERE LOWER($nameColumn) = LOWER(?) AND is_active = 1 LIMIT 1");
    $stmt->execute([$name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return intval($row[$idColumn]);
    }

    $insert = $pdo->prepare("INSERT INTO $table ($nameColumn, is_active) VALUES (?, 1)");
    $insert->execute([$name]);
    return intval($pdo->lastInsertId());
}

function resolveMotherTongueId(PDO $pdo, ?string $name): ?int {
    return resolveLookupId($pdo, 'mother_tongues', 'mother_tongue_id', 'name', $name);
}

function resolveIndigenousGroupId(PDO $pdo, ?string $name): ?int {
    return resolveLookupId($pdo, 'indigenous_groups', 'indigenous_group_id', 'name', $name);
}

function insertParentGuardian(PDO $pdo, int $studentId, int $typeId, string $lastName, string $firstName, string $middleName, string $contactNumber): void {
    $lastName = trim($lastName);
    $firstName = trim($firstName);
    $middleName = trim($middleName);
    $contactNumber = trim($contactNumber);

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

function getOwnershipType($value): ?string {
    $map = [
        'Rental' => 'rented',
        'Rented' => 'rented',
        'rental' => 'rented',
        'rented' => 'rented',
        'Owned' => 'owned',
        'owned' => 'owned',
        'Living with Relatives' => 'living_with_relatives',
        'living with relatives' => 'living_with_relatives',
        'living_with_relatives' => 'living_with_relatives',
        'Inherited' => 'inherited',
        'inherited' => 'inherited',
    ];
    if ($value === null) {
        return null;
    }
    $val = trim((string)$value);
    if ($val === '') {
        return null;
    }
    return $map[$val] ?? strtolower(str_replace(' ', '_', $val));
}

function handleEnrollmentCreate(PDO $pdo): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
    }

    $data = getJsonInput();
    $studentId = intval($data['student_id'] ?? 0);
    if ($studentId <= 0) {
        sendJson(['success' => false, 'error' => 'student_id is required'], 400);
    }

    $studentCheck = $pdo->prepare('SELECT student_id FROM students WHERE student_id = ? LIMIT 1');
    $studentCheck->execute([$studentId]);
    if (!$studentCheck->fetch()) {
        sendJson(['success' => false, 'error' => 'Invalid student_id provided'], 400);
    }

    $yearStart = trim($data['year_start'] ?? '');
    $yearEnd = trim($data['year_end'] ?? '');
    $schoolYear = ($yearStart !== '' && $yearEnd !== '') ? ($yearStart . '-' . $yearEnd) : trim($data['school_year'] ?? '');
    if ($schoolYear === '') {
        sendJson(['success' => false, 'error' => 'School year is required'], 400);
    }

    $gradeLevel = trim($data['Grade_Level'] ?? '');
    $motherTongueValue = trim((string)($data['Mother_Tongue'] ?? '')); 
    $motherTongueId = null;
    if ($motherTongueValue !== '' && strcasecmp($motherTongueValue, 'Other') !== 0) {
        $motherTongueId = intval($motherTongueValue) ?: null;
    }
    if ($motherTongueId === null && strcasecmp($motherTongueValue, 'Other') === 0) {
        $motherTongueId = resolveMotherTongueId($pdo, trim($data['Mother_Tongue_Other'] ?? ''));
    }

    $isIp = normalizeCheckboxValue($data['ip'] ?? 0);
    $isFourPs = normalizeCheckboxValue($data['fourps'] ?? 0);
    $isDisabled = !empty($data['disabilityDetails']) ? 1 : 0;
    $isReturning = trim((string)($data['Returning_Grade_Level'] ?? '')) !== '' ? 1 : 0;

    $indigenousGroupValue = trim((string)($data['IP_Group'] ?? ''));
    $indigenousGroupId = null;
    if ($indigenousGroupValue !== '' && strcasecmp($indigenousGroupValue, 'Other') !== 0) {
        $indigenousGroupId = intval($indigenousGroupValue) ?: null;
    }
    if ($indigenousGroupId === null && $isIp && strcasecmp($indigenousGroupValue, 'Other') === 0) {
        $indigenousGroupId = resolveIndigenousGroupId($pdo, trim($data['IP_Specify'] ?? ''));
    }

    $fourPsId = $isFourPs ? trim((string)($data['FourPs_Specify'] ?? '')) : null;
    $enrollmentStatus = trim((string)($data['enrollment_status'] ?? 'pending')) ?: 'pending';
    $lrnValue = trim((string)($data['Learner_Reference_No'] ?? '')) ?: null;
    $motherTongueText = $motherTongueValue !== 'Other' ? ($motherTongueValue ?: null) : trim((string)($data['Mother_Tongue_Other'] ?? ''));
    $indigenousText = $isIp ? ($indigenousGroupValue !== 'Other' ? ($indigenousGroupValue ?: null) : trim((string)($data['IP_Specify'] ?? ''))) : null;

    try {
        $pdo->beginTransaction();

        $insertEnrollment = $pdo->prepare('INSERT INTO enrollments (student_id, school_year, grade_level, enrollment_status, mother_tongue_id, is_indigenous, indigenous_group_id, is_four_ps_beneficiary, four_ps_household_id, is_learner_with_disability, is_returning_learner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insertEnrollment->execute([
            $studentId,
            $schoolYear,
            $gradeLevel !== '' ? $gradeLevel : null,
            $enrollmentStatus,
            $motherTongueId,
            $isIp,
            $indigenousGroupId,
            $isFourPs,
            $fourPsId !== '' ? $fourPsId : null,
            $isDisabled,
            $isReturning,
        ]);
        $enrollmentId = intval($pdo->lastInsertId());

        $ssrInsert = $pdo->prepare('INSERT INTO student_school_records (enrollment_id, student_id, school_year, grade_level, lrn, last_name, first_name, middle_name, extension_name, birth_date, sex, place_of_birth, mother_tongue, indigenous_group, four_ps_household_id, is_learner_with_disability, is_returning_learner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $ssrInsert->execute([
            $enrollmentId,
            $studentId,
            $schoolYear,
            $gradeLevel !== '' ? $gradeLevel : null,
            $lrnValue,
            trim((string)($data['Learner_Last_Name'] ?? '')) ?: null,
            trim((string)($data['Learner_First_Name'] ?? '')) ?: null,
            trim((string)($data['Learner_Middle_Name'] ?? '')) ?: null,
            trim((string)($data['Learner_Extension_Name'] ?? '')) ?: null,
            trim((string)($data['Birth_Date'] ?? '')) ?: null,
            trim((string)($data['sex'] ?? '')) ?: null,
            trim((string)($data['Place_of_Birth'] ?? '')) ?: null,
            $motherTongueText,
            $indigenousText,
            $fourPsId !== '' ? $fourPsId : null,
            $isDisabled,
            $isReturning,
        ]);
        $schoolRecordId = intval($pdo->lastInsertId());

        $currentOwnership = getOwnershipType($data['Current_Address_Status'] ?? null);
        $permanentOwnership = isset($data['same_address']) && $data['same_address'] === 'Yes'
            ? $currentOwnership
            : getOwnershipType($data['Permanent_Address_Status'] ?? null);

        $addressInsert = $pdo->prepare('INSERT INTO student_addresses (student_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code, ownership_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $addressInsert->execute([
            $studentId, 'current',
            trim((string)($data['Current_House_No'] ?? '')) ?: null,
            trim((string)($data['Current_Street_Name'] ?? '')) ?: null,
            trim((string)($data['Current_Barangay'] ?? '')) ?: null,
            trim((string)($data['Current_Municipality_City'] ?? '')) ?: null,
            trim((string)($data['Current_Province'] ?? '')) ?: null,
            trim((string)($data['Current_Country'] ?? '')) ?: 'Philippines',
            trim((string)($data['Current_Zip_Code'] ?? '')) ?: null,
            $currentOwnership,
        ]);

        $permHouse = trim((string)($data['Permanent_House_No'] ?? ''));
        $permStreet = trim((string)($data['Permanent_Street_Name'] ?? ''));
        $permBarangay = trim((string)($data['Permanent_Barangay'] ?? ''));
        $permCity = trim((string)($data['Permanent_Municipality_City'] ?? ''));
        $permProvince = trim((string)($data['Permanent_Province'] ?? ''));
        $permCountry = trim((string)($data['Permanent_Country'] ?? ''));
        $permZip = trim((string)($data['Permanent_Zip_Code'] ?? ''));
        if (isset($data['same_address']) && $data['same_address'] === 'Yes') {
            $permHouse = trim((string)($data['Current_House_No'] ?? ''));
            $permStreet = trim((string)($data['Current_Street_Name'] ?? ''));
            $permBarangay = trim((string)($data['Current_Barangay'] ?? ''));
            $permCity = trim((string)($data['Current_Municipality_City'] ?? ''));
            $permProvince = trim((string)($data['Current_Province'] ?? ''));
            $permCountry = trim((string)($data['Current_Country'] ?? ''));
            $permZip = trim((string)($data['Current_Zip_Code'] ?? ''));
        }

        $addressInsert->execute([
            $studentId, 'permanent',
            $permHouse ?: null,
            $permStreet ?: null,
            $permBarangay ?: null,
            $permCity ?: null,
            $permProvince ?: null,
            $permCountry ?: 'Philippines',
            $permZip ?: null,
            $permanentOwnership,
        ]);

        $exposedToSmoke = normalizeCheckboxValue($data['exposed_to_cigarette_vape_smoke'] ?? 0);
        $otherPertinentInfo = getStringValue($data['other_pertinent_information'] ?? null);
        $medicalInfoInsert = $pdo->prepare('INSERT INTO enrollment_medical_information (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?, ?, ?)');
        $medicalInfoInsert->execute([$enrollmentId, $exposedToSmoke, $otherPertinentInfo]);
        $medicalInfoId = intval($pdo->lastInsertId());

        $allergyTypeIds = parseIdsValue($data['medicine_allergy'] ?? []);
        $allergyDescriptions = $data['allergy_description'] ?? [];
        if (!is_array($allergyDescriptions)) {
            $allergyDescriptions = ['default' => trim((string)$allergyDescriptions)];
        }
        if (!empty($allergyTypeIds)) {
            $allergyInsert = $pdo->prepare('INSERT INTO enrollment_medical_allergies (medical_information_id, allergy_type_id, description) VALUES (?, ?, ?)');
            foreach ($allergyTypeIds as $typeId) {
                $description = trim((string)($allergyDescriptions[$typeId] ?? $allergyDescriptions['default'] ?? '')) ?: null;
                $allergyInsert->execute([$medicalInfoId, $typeId, $description]);
            }
        }

        $conditionTypeIds = parseIdsValue($data['condition_type_id'] ?? []);
        $conditionDescription = getStringValue($data['condition_description'] ?? null);
        if (!empty($conditionTypeIds)) {
            $conditionInsert = $pdo->prepare('INSERT INTO enrollment_medical_conditions (medical_information_id, condition_type_id, description) VALUES (?, ?, ?)');
            foreach ($conditionTypeIds as $typeId) {
                $conditionInsert->execute([$medicalInfoId, $typeId, $conditionDescription]);
            }
        }

        $hasSurgery = normalizeCheckboxValue($data['has_surgery_hospitalization'] ?? 0);
        $surgeryDate = getStringValue($data['surgery_date'] ?? null);
        $hospitalName = getStringValue($data['hospital_name'] ?? null);
        $bodyPart = getStringValue($data['body_part'] ?? null);
        if ($hasSurgery || $surgeryDate || $hospitalName || $bodyPart) {
            $surgeryInsert = $pdo->prepare('INSERT INTO enrollment_medical_surgeries (medical_information_id, surgery_date, hospital_name, body_part) VALUES (?, ?, ?, ?)');
            $surgeryInsert->execute([$medicalInfoId, $surgeryDate, $hospitalName, $bodyPart]);
        }

        $isTakingTreatment = normalizeCheckboxValue($data['is_taking_treatment'] ?? 0);
        $treatmentMedicine = getStringValue($data['treatment_medicine'] ?? null);
        $scheduleDosage = getStringValue($data['schedule_dosage'] ?? null);
        if ($isTakingTreatment || $treatmentMedicine || $scheduleDosage) {
            $treatmentInsert = $pdo->prepare('INSERT INTO enrollment_medical_treatments (medical_information_id, treatment_medicine, schedule_dosage) VALUES (?, ?, ?)');
            $treatmentInsert->execute([$medicalInfoId, $treatmentMedicine, $scheduleDosage]);
        }

        $familyConditionTypeIds = parseIdsValue($data['family_condition_type_id'] ?? []);
        $familyConditionDescription = getStringValue($data['family_condition_description'] ?? null);
        if (!empty($familyConditionTypeIds)) {
            $familyHistoryInsert = $pdo->prepare('INSERT INTO enrollment_family_medical_history (medical_information_id, family_history_type_id, description) VALUES (?, ?, ?)');
            foreach ($familyConditionTypeIds as $typeId) {
                $familyHistoryInsert->execute([$medicalInfoId, $typeId, $familyConditionDescription]);
            }
        }

        $disabilityRows = [];
        if (!empty($data['disabilityDetails']) && is_array($data['disabilityDetails'])) {
            foreach ($data['disabilityDetails'] as $typeId => $values) {
                $typeId = intval($typeId);
                if ($typeId === 0 || !is_array($values)) {
                    continue;
                }
                $subtypeIds = array_map('intval', array_filter($values, fn($v) => $v !== ''));
                if (empty($subtypeIds)) {
                    $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => null];
                } else {
                    foreach (array_unique($subtypeIds) as $subtypeId) {
                        $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => $subtypeId];
                    }
                }
            }
        }
        if (!empty($disabilityRows)) {
            $disabilityInsert = $pdo->prepare('INSERT INTO enrollment_disabilities (enrollment_id, disability_type_id, disability_subtype_id) VALUES (?, ?, ?)');
            foreach ($disabilityRows as $row) {
                $disabilityInsert->execute([$enrollmentId, $row['type_id'], $row['subtype_id']]);
            }
        }

        insertParentGuardian($pdo, $studentId, 1, $data['father_last_name'] ?? '', $data['father_first_name'] ?? '', $data['father_middle_name'] ?? '', $data['father_contact_number'] ?? '');
        insertParentGuardian($pdo, $studentId, 2, $data['mother_last_name'] ?? '', $data['mother_first_name'] ?? '', $data['mother_middle_name'] ?? '', $data['mother_contact_number'] ?? '');
        insertParentGuardian($pdo, $studentId, 3, $data['guardian_last_name'] ?? '', $data['guardian_first_name'] ?? '', $data['guardian_middle_name'] ?? '', $data['guardian_contact_number'] ?? '');

        if ($isReturning) {
            $returningInsert = $pdo->prepare('INSERT INTO enrollment_returning_learners (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed) VALUES (?, ?, ?, ?)');
            $returningInsert->execute([
                $enrollmentId,
                getStringValue($data['Returning_Grade_Level'] ?? null),
                getStringValue($data['Last_School_Attended'] ?? null),
                getStringValue($data['Last_School_Year_Completed'] ?? null),
            ]);
        }

        $allergiesJson = null;
        if (!empty($allergyTypeIds)) {
            $allergiesJson = json_encode(array_map(fn($id) => [
                'allergy_type_id' => $id,
                'description' => trim((string)($allergyDescriptions[$id] ?? $allergyDescriptions['default'] ?? '')) ?: null,
            ], $allergyTypeIds));
        }
        $conditionsJson = !empty($conditionTypeIds) ? json_encode(array_map(fn($id) => [
            'condition_type_id' => $id,
            'description' => $conditionDescription,
        ], $conditionTypeIds)) : null;
        $surgeriesJson = ($hasSurgery || $surgeryDate || $hospitalName || $bodyPart) ? json_encode([[
            'surgery_date' => $surgeryDate,
            'hospital_name' => $hospitalName,
            'body_part' => $bodyPart,
        ]]) : null;
        $treatmentsJson = ($isTakingTreatment || $treatmentMedicine || $scheduleDosage) ? json_encode([[
            'treatment_medicine' => $treatmentMedicine,
            'schedule_dosage' => $scheduleDosage,
        ]]) : null;
        $familyHistoryJson = !empty($familyConditionTypeIds) ? json_encode(array_map(fn($id) => [
            'family_history_type_id' => $id,
            'description' => $familyConditionDescription,
        ], $familyConditionTypeIds)) : null;

        $studentMedicalInsert = $pdo->prepare('INSERT INTO student_medical_records (school_record_id, exposed_to_cigarette_vape_smoke, other_pertinent_information, allergies, conditions, surgeries, treatments, family_medical_history) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $studentMedicalInsert->execute([
            $schoolRecordId,
            $exposedToSmoke,
            $otherPertinentInfo,
            $allergiesJson,
            $conditionsJson,
            $surgeriesJson,
            $treatmentsJson,
            $familyHistoryJson,
        ]);

        $pdo->commit();

        sendJson([
            'success' => true,
            'student_id' => $studentId,
            'enrollment_id' => $enrollmentId,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendJson(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

handleEnrollmentCreate($pdo);

