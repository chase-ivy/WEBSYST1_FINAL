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

function insertParentGuardian(PDO $pdo, int $studentId, int $typeId, string $lastName, string $firstName, string $middleName, string $contactNumber, ?string $occupation = null, ?string $relationshipStatus = null, ?string $facebookMessenger = null, int $isEmergencyContact = 0): void {
    $lastName = trim($lastName);
    $firstName = trim($firstName);
    $middleName = trim($middleName);
    $contactNumber = trim($contactNumber);
    $occupation = trim((string)($occupation ?? '')) ?: null;
    $relationshipStatus = trim((string)($relationshipStatus ?? '')) ?: null;
    $facebookMessenger = trim((string)($facebookMessenger ?? '')) ?: null;

    if ($lastName === '' && $firstName === '' && $middleName === '' && $contactNumber === '' && $occupation === null && $relationshipStatus === null && $facebookMessenger === null && $isEmergencyContact === 0) {
        return;
    }

    $stmt = $pdo->prepare('
        INSERT INTO student_parent_guardians
            (student_id, parent_guardian_type_id, last_name, first_name, middle_name, contact_number, occupation, relationship_status, face_book_messenger, is_emergency_contact)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$studentId, $typeId, $lastName, $firstName, $middleName, $contactNumber, $occupation, $relationshipStatus, $facebookMessenger, $isEmergencyContact]);
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
        
        // First, collect all disability types that have subtypes (from disability_sub)
        $processedTypes = [];
        if (!empty($data['disability_sub']) && is_array($data['disability_sub'])) {
            foreach ($data['disability_sub'] as $typeId => $values) {
                $typeId = intval($typeId);
                if ($typeId === 0 || !is_array($values)) {
                    continue;
                }
                $processedTypes[$typeId] = true;
                $subtypeIds = array_map('intval', array_filter($values, fn($v) => $v !== ''));
                if (empty($subtypeIds)) {
                    // Type selected but no subtype chosen
                    $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => null];
                } else {
                    // Type selected with specific subtypes
                    foreach (array_unique($subtypeIds) as $subtypeId) {
                        $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => $subtypeId];
                    }
                }
            }
        }
        
        // Then, collect disability types without subtypes (from disabilityDetails)
        // These are types that were checked but don't have subtype options
        if (!empty($data['disabilityDetails']) && is_array($data['disabilityDetails'])) {
            foreach ($data['disabilityDetails'] as $typeId => $values) {
                $typeId = intval($typeId);
                // Skip if we already processed this type (it had subtypes)
                if ($typeId === 0 || !is_array($values) || isset($processedTypes[$typeId])) {
                    continue;
                }
                // Add this type without a subtype
                $disabilityRows[] = ['type_id' => $typeId, 'subtype_id' => null];
            }
        }
        if (!empty($disabilityRows)) {
            $disabilityInsert = $pdo->prepare('INSERT INTO enrollment_disabilities (enrollment_id, disability_type_id, disability_subtype_id) VALUES (?, ?, ?)');
            foreach ($disabilityRows as $row) {
                $disabilityInsert->execute([$enrollmentId, $row['type_id'], $row['subtype_id']]);
            }
        }

        insertParentGuardian(
            $pdo,
            $studentId,
            1,
            $data['father_last_name'] ?? '',
            $data['father_first_name'] ?? '',
            $data['father_middle_name'] ?? '',
            $data['father_contact_number'] ?? '',
            $data['father_occupation'] ?? null,
            $data['father_relationship_status'] ?? null,
            $data['father_face_book_messenger'] ?? null,
            normalizeCheckboxValue($data['father_is_emergency_contact'] ?? 0)
        );

        insertParentGuardian(
            $pdo,
            $studentId,
            2,
            $data['mother_last_name'] ?? '',
            $data['mother_first_name'] ?? '',
            $data['mother_middle_name'] ?? '',
            $data['mother_contact_number'] ?? '',
            $data['mother_occupation'] ?? null,
            $data['mother_relationship_status'] ?? null,
            $data['mother_face_book_messenger'] ?? null,
            normalizeCheckboxValue($data['mother_is_emergency_contact'] ?? 0)
        );

        insertParentGuardian(
            $pdo,
            $studentId,
            3,
            $data['guardian_last_name'] ?? '',
            $data['guardian_first_name'] ?? '',
            $data['guardian_middle_name'] ?? '',
            $data['guardian_contact_number'] ?? '',
            $data['guardian_occupation'] ?? null,
            $data['guardian_relationship_status'] ?? null,
            $data['guardian_face_book_messenger'] ?? null,
            normalizeCheckboxValue($data['guardian_is_emergency_contact'] ?? 0)
        );

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

function handleEnrollmentVerify(PDO $pdo): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use POST.'], 405);
    }

    $data = getJsonInput();
    $enrollmentId = intval($data['enrollment_id'] ?? 0);
    $verifiedBy = intval($data['verified_by'] ?? 0);
    
    if ($enrollmentId <= 0) {
        sendJson(['success' => false, 'error' => 'enrollment_id is required'], 400);
    }
    if ($verifiedBy <= 0) {
        sendJson(['success' => false, 'error' => 'verified_by (user_id) is required'], 400);
    }

    try {
        // Fetch enrollment and student data
        $enrollmentStmt = $pdo->prepare('
            SELECT e.*, s.lrn, s.psa_bcn, s.last_name, s.first_name, s.middle_name, 
                   s.extension_name, s.birth_date, s.sex, s.place_of_birth
            FROM enrollments e
            JOIN students s ON e.student_id = s.student_id
            WHERE e.enrollment_id = ? LIMIT 1
        ');
        $enrollmentStmt->execute([$enrollmentId]);
        $enrollment = $enrollmentStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enrollment) {
            sendJson(['success' => false, 'error' => 'Enrollment not found'], 404);
            return;
        }

        // Verify enrollment is in pending state
        if ($enrollment['enrollment_status'] !== 'pending') {
            sendJson(['success' => false, 'error' => 'Only pending enrollments can be verified'], 400);
            return;
        }

        $pdo->beginTransaction();

        // Step 1: Update enrollment status to verified (without verified_by/verified_at yet)
        $updateEnrollmentStatus = $pdo->prepare('UPDATE enrollments SET enrollment_status = ? WHERE enrollment_id = ?');
        $updateEnrollmentStatus->execute(['verified', $enrollmentId]);

        // Step 2: Fetch and resolve disability data
        $disabilityStmt = $pdo->prepare('
            SELECT ed.disability_type_id, ed.disability_subtype_id,
                   dt.name as type_name, ds.name as subtype_name
            FROM enrollment_disabilities ed
            LEFT JOIN disability_types dt ON ed.disability_type_id = dt.disability_type_id
            LEFT JOIN disability_subtypes ds ON ed.disability_subtype_id = ds.disability_subtype_id
            WHERE ed.enrollment_id = ?
        ');
        $disabilityStmt->execute([$enrollmentId]);
        $disabilityRecords = $disabilityStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $disabilityJson = null;
        if (!empty($disabilityRecords)) {
            $disabilityArray = array_map(function($record) {
                return [
                    'type' => $record['type_name'],
                    'subtype' => $record['subtype_name'],
                ];
            }, $disabilityRecords);
            $disabilityJson = json_encode($disabilityArray);
        }

        // Step 3: Fetch and flatten medical data
        $medicalInfoStmt = $pdo->prepare('
            SELECT medical_information_id, exposed_to_cigarette_vape_smoke, other_pertinent_information
            FROM enrollment_medical_information
            WHERE enrollment_id = ?
            LIMIT 1
        ');
        $medicalInfoStmt->execute([$enrollmentId]);
        $medicalInfo = $medicalInfoStmt->fetch(PDO::FETCH_ASSOC);

        $allergiesJson = null;
        $conditionsJson = null;
        $surgeriesJson = null;
        $treatmentsJson = null;
        $familyHistoryJson = null;

        if ($medicalInfo) {
            $medicalInfoId = intval($medicalInfo['medical_information_id']);

            // Fetch allergy details
            $allergyDetailsStmt = $pdo->prepare("
                SELECT ema.allergy_type_id, mat.name as allergy_name, ema.description
                FROM enrollment_medical_allergies ema
                JOIN medical_allergy_types mat ON ema.allergy_type_id = mat.allergy_type_id
                WHERE ema.medical_information_id = ?
            ");
            $allergyDetailsStmt->execute([$medicalInfoId]);
            $allergyDetails = $allergyDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($allergyDetails)) {
                $allergiesJson = json_encode($allergyDetails);
            }

            // Fetch condition details
            $conditionDetailsStmt = $pdo->prepare("
                SELECT emc.condition_type_id, mct.name as condition_name, emc.description
                FROM enrollment_medical_conditions emc
                JOIN medical_condition_types mct ON emc.condition_type_id = mct.condition_type_id
                WHERE emc.medical_information_id = ?
            ");
            $conditionDetailsStmt->execute([$medicalInfoId]);
            $conditionDetails = $conditionDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($conditionDetails)) {
                $conditionsJson = json_encode($conditionDetails);
            }

            // Fetch surgery details
            $surgeryDetailsStmt = $pdo->prepare("
                SELECT surgery_date, hospital_name, body_part
                FROM enrollment_medical_surgeries
                WHERE medical_information_id = ?
            ");
            $surgeryDetailsStmt->execute([$medicalInfoId]);
            $surgeryDetails = $surgeryDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($surgeryDetails)) {
                $surgeriesJson = json_encode($surgeryDetails);
            }

            // Fetch treatment details
            $treatmentDetailsStmt = $pdo->prepare("
                SELECT treatment_medicine, schedule_dosage
                FROM enrollment_medical_treatments
                WHERE medical_information_id = ?
            ");
            $treatmentDetailsStmt->execute([$medicalInfoId]);
            $treatmentDetails = $treatmentDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($treatmentDetails)) {
                $treatmentsJson = json_encode($treatmentDetails);
            }

            // Fetch family history details
            $familyHistoryDetailsStmt = $pdo->prepare("
                SELECT emfh.family_history_type_id, fht.name as family_history_name, emfh.description
                FROM enrollment_family_medical_history emfh
                JOIN family_medical_history_types fht ON emfh.family_history_type_id = fht.family_history_type_id
                WHERE emfh.medical_information_id = ?
            ");
            $familyHistoryDetailsStmt->execute([$medicalInfoId]);
            $familyHistoryDetails = $familyHistoryDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($familyHistoryDetails)) {
                $familyHistoryJson = json_encode($familyHistoryDetails);
            }
        }

        // Step 4: Update student_school_records with verified data
        // First, fetch the school_record_id from the initial enrollment
        $schoolRecordStmt = $pdo->prepare('SELECT school_record_id FROM student_school_records WHERE enrollment_id = ? LIMIT 1');
        $schoolRecordStmt->execute([$enrollmentId]);
        $schoolRecord = $schoolRecordStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schoolRecord) {
            throw new Exception('Student school record not found for this enrollment');
        }
        
        $schoolRecordId = intval($schoolRecord['school_record_id']);

        // Resolve mother_tongue_id and indigenous_group_id to names
        $motherTongueName = null;
        if ($enrollment['mother_tongue_id']) {
            $mtStmt = $pdo->prepare('SELECT name FROM mother_tongues WHERE mother_tongue_id = ? LIMIT 1');
            $mtStmt->execute([$enrollment['mother_tongue_id']]);
            $mtRow = $mtStmt->fetch(PDO::FETCH_ASSOC);
            $motherTongueName = $mtRow['name'] ?? null;
        }

        $indigenousGroupName = null;
        if ($enrollment['indigenous_group_id']) {
            $igStmt = $pdo->prepare('SELECT name FROM indigenous_groups WHERE indigenous_group_id = ? LIMIT 1');
            $igStmt->execute([$enrollment['indigenous_group_id']]);
            $igRow = $igStmt->fetch(PDO::FETCH_ASSOC);
            $indigenousGroupName = $igRow['name'] ?? null;
        }

        $updateSchoolRecord = $pdo->prepare('
            UPDATE student_school_records
            SET mother_tongue = ?, indigenous_group = ?, disabilities = ?,
                verified_by = ?, verified_at = NOW(), academic_status = ?
            WHERE school_record_id = ?
        ');
        $updateSchoolRecord->execute([
            $motherTongueName,
            $indigenousGroupName,
            $disabilityJson,
            $verifiedBy,
            'active',
            $schoolRecordId,
        ]);

        // Step 5: Insert into student_medical_records
        $insertMedicalRecord = $pdo->prepare('
            INSERT INTO student_medical_records 
            (school_record_id, exposed_to_cigarette_vape_smoke, other_pertinent_information, 
             allergies, conditions, surgeries, treatments, family_medical_history)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                exposed_to_cigarette_vape_smoke = VALUES(exposed_to_cigarette_vape_smoke),
                other_pertinent_information = VALUES(other_pertinent_information),
                allergies = VALUES(allergies),
                conditions = VALUES(conditions),
                surgeries = VALUES(surgeries),
                treatments = VALUES(treatments),
                family_medical_history = VALUES(family_medical_history)
        ');
        $insertMedicalRecord->execute([
            $schoolRecordId,
            $medicalInfo['exposed_to_cigarette_vape_smoke'] ?? 0,
            $medicalInfo['other_pertinent_information'] ?? null,
            $allergiesJson,
            $conditionsJson,
            $surgeriesJson,
            $treatmentsJson,
            $familyHistoryJson,
        ]);

        // Step 6: Update enrollment with verified_by and verified_at
        $finalUpdateEnrollment = $pdo->prepare('
            UPDATE enrollments 
            SET verified_by = ?, verified_at = NOW()
            WHERE enrollment_id = ?
        ');
        $finalUpdateEnrollment->execute([$verifiedBy, $enrollmentId]);

        $pdo->commit();

        sendJson([
            'success' => true,
            'enrollment_id' => $enrollmentId,
            'school_record_id' => $schoolRecordId,
            'message' => 'Enrollment verified and permanent records created',
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendJson(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// Route the request based on action parameter
$action = $_GET['action'] ?? $_POST['action'] ?? 'create';
if ($action === 'verify') {
    handleEnrollmentVerify($pdo);
} else {
    handleEnrollmentCreate($pdo);
}

