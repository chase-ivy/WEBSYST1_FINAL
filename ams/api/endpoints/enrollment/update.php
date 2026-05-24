<?php
// ============================================================
// endpoints/enrollment/update.php
// Updates an existing enrollment record and related data (corrections before verify).
// Only staff/admin can update enrollments that are still pending.
//
// This handles updates to:
//   - enrollments table (grade_level, school_year, flags, etc.)
//   - students table (name, birth_date, sex, place_of_birth, etc.)
//   - student_addresses (current/permanent address)
//   - student_parent_guardians (parent/guardian data)
//   - enrollment_medical_* tables (allergies, conditions, surgeries, etc.)
//
// POST body:
//   enrollment_id (required)
//   All optional fields from the enrollment form will be updated
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

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

$data = getJsonInput();

$enrollmentId = intval($data['enrollment_id'] ?? 0);
if ($enrollmentId <= 0) {
    sendJson(['success' => false, 'error' => 'enrollment_id is required'], 400);
}

// Fetch the current enrollment + student
$stmt = $pdo->prepare('
    SELECT e.enrollment_id, e.enrollment_status, e.student_id,
           s.user_id, s.last_name, s.first_name, s.middle_name, s.extension_name,
           s.birth_date, s.sex, s.place_of_birth
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    WHERE e.enrollment_id = ? LIMIT 1
');
$stmt->execute([$enrollmentId]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    sendJson(['success' => false, 'error' => 'Enrollment not found'], 404);
}

if ($enrollment['enrollment_status'] !== 'pending') {
    sendJson(['success' => false, 'error' => 'Only pending enrollments can be updated'], 400);
}

$studentId = intval($enrollment['student_id']);

try {
    $pdo->beginTransaction();

    // 1. Update enrollments table
    $enrollmentUpdates = [];
    $enrollmentParams = [];
    
    $enrollmentFields = [
        'school_year', 'grade_level', 'is_returning_learner',
        'mother_tongue_id', 'is_indigenous', 'indigenous_group_id',
        'is_four_ps_beneficiary', 'four_ps_household_id',
        'is_learner_with_disability'
    ];
    
    foreach ($enrollmentFields as $field) {
        if (isset($data[$field])) {
            $enrollmentUpdates[] = "$field = ?";
            $enrollmentParams[] = $data[$field];
        }
    }
    
    if (count($enrollmentUpdates) > 0) {
        $enrollmentParams[] = $enrollmentId;
        $sql = 'UPDATE enrollments SET ' . implode(', ', $enrollmentUpdates) . ' WHERE enrollment_id = ?';
        $pdo->prepare($sql)->execute($enrollmentParams);
    }

    // 2. Update students table (name, DOB, sex, etc.)
    $studentUpdates = [];
    $studentParams = [];
    
    $studentFields = [
        'last_name' => 'Learner_Last_Name',
        'first_name' => 'Learner_First_Name',
        'middle_name' => 'Learner_Middle_Name',
        'extension_name' => 'Learner_Extension_Name',
        'birth_date' => 'Birth_Date',
        'sex' => 'sex',
        'place_of_birth' => 'Place_of_Birth',
        'psa_bcn' => 'psa_bcn'
    ];
    
    foreach ($studentFields as $dbField => $formField) {
        if (isset($data[$formField]) && $data[$formField] !== '') {
            $studentUpdates[] = "$dbField = ?";
            $studentParams[] = trim($data[$formField]);
        }
    }
    
    if (count($studentUpdates) > 0) {
        $studentParams[] = $studentId;
        $sql = 'UPDATE students SET ' . implode(', ', $studentUpdates) . ' WHERE student_id = ?';
        $pdo->prepare($sql)->execute($studentParams);
    }

    // 3. Update student_addresses (current and permanent)
    if (isset($data['Current_House_No']) || isset($data['Current_Street_Name']) || 
        isset($data['Current_Barangay']) || isset($data['Current_Municipality_City']) ||
        isset($data['Current_Province']) || isset($data['Current_Country']) || 
        isset($data['Current_Zip_Code'])) {
        
        $currentAddr = $pdo->prepare('
            SELECT address_id FROM student_addresses 
            WHERE student_id = ? AND address_type = ? LIMIT 1
        ');
        $currentAddr->execute([$studentId, 'current']);
        $currentAddressId = $currentAddr->fetchColumn();
        
        if ($currentAddressId) {
            $updates = [];
            $params = [];
            $addressMap = [
                'house_no' => 'Current_House_No',
                'street_name' => 'Current_Street_Name',
                'barangay' => 'Current_Barangay',
                'municipality_city' => 'Current_Municipality_City',
                'province' => 'Current_Province',
                'country' => 'Current_Country',
                'zip_code' => 'Current_Zip_Code'
            ];
            foreach ($addressMap as $dbCol => $formField) {
                if (isset($data[$formField])) {
                    $updates[] = "$dbCol = ?";
                    $params[] = trim($data[$formField]) ?: null;
                }
            }
            if (count($updates) > 0) {
                $params[] = $currentAddressId;
                $sql = 'UPDATE student_addresses SET ' . implode(', ', $updates) . ' WHERE address_id = ?';
                $pdo->prepare($sql)->execute($params);
            }
        }
    }
    
    if (isset($data['Permanent_House_No']) || isset($data['Permanent_Street_Name']) || 
        isset($data['Permanent_Barangay']) || isset($data['Permanent_Municipality_City']) ||
        isset($data['Permanent_Province']) || isset($data['Permanent_Country']) || 
        isset($data['Permanent_Zip_Code'])) {
        
        $permAddr = $pdo->prepare('
            SELECT address_id FROM student_addresses 
            WHERE student_id = ? AND address_type = ? LIMIT 1
        ');
        $permAddr->execute([$studentId, 'permanent']);
        $permAddressId = $permAddr->fetchColumn();
        
        if ($permAddressId) {
            $updates = [];
            $params = [];
            $addressMap = [
                'house_no' => 'Permanent_House_No',
                'street_name' => 'Permanent_Street_Name',
                'barangay' => 'Permanent_Barangay',
                'municipality_city' => 'Permanent_Municipality_City',
                'province' => 'Permanent_Province',
                'country' => 'Permanent_Country',
                'zip_code' => 'Permanent_Zip_Code'
            ];
            foreach ($addressMap as $dbCol => $formField) {
                if (isset($data[$formField])) {
                    $updates[] = "$dbCol = ?";
                    $params[] = trim($data[$formField]) ?: null;
                }
            }
            if (count($updates) > 0) {
                $params[] = $permAddressId;
                $sql = 'UPDATE student_addresses SET ' . implode(', ', $updates) . ' WHERE address_id = ?';
                $pdo->prepare($sql)->execute($params);
            }
        }
    }

    // 4. Update student_parent_guardians (if provided in the form)
    $guardians = [
        ['type_id' => 1, 'prefix' => 'father'],
        ['type_id' => 2, 'prefix' => 'mother'],
        ['type_id' => 3, 'prefix' => 'guardian'],
    ];
    foreach ($guardians as $guardian) {
        $lastName = trim((string)($data["{$guardian['prefix']}_last_name"] ?? ''));
        $firstName = trim((string)($data["{$guardian['prefix']}_first_name"] ?? ''));
        $middleName = trim((string)($data["{$guardian['prefix']}_middle_name"] ?? ''));
        $contact = trim((string)($data["{$guardian['prefix']}_contact_number"] ?? ''));

        $guardianStmt = $pdo->prepare('SELECT parent_guardian_id FROM student_parent_guardians WHERE student_id = ? AND parent_guardian_type_id = ? LIMIT 1');
        $guardianStmt->execute([$studentId, $guardian['type_id']]);
        $existingGuardian = $guardianStmt->fetch();

        if ($existingGuardian) {
            $pdo->prepare('UPDATE student_parent_guardians SET last_name = ?, first_name = ?, middle_name = ?, contact_number = ? WHERE parent_guardian_id = ?')
                ->execute([$lastName, $firstName, $middleName, $contact, $existingGuardian['parent_guardian_id']]);
        } elseif ($lastName !== '' || $firstName !== '' || $contact !== '') {
            $pdo->prepare('INSERT INTO student_parent_guardians (student_id, parent_guardian_type_id, last_name, first_name, middle_name, contact_number) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$studentId, $guardian['type_id'], $lastName, $firstName, $middleName, $contact]);
        }
    }

    // 5. Update medical information and related child records
    $medicalInfoStmt = $pdo->prepare('SELECT medical_information_id FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
    $medicalInfoStmt->execute([$enrollmentId]);
    $medicalInformationId = intval($medicalInfoStmt->fetchColumn());

    $medicalSmoke = normalizeCheckbox($data['exposed_to_cigarette_vape_smoke'] ?? 0);
    $medicalNotes = strOrNull($data['other_pertinent_information'] ?? null);

    if ($medicalInformationId > 0) {
        $pdo->prepare('UPDATE enrollment_medical_information SET exposed_to_cigarette_vape_smoke = ?, other_pertinent_information = ? WHERE medical_information_id = ?')
            ->execute([$medicalSmoke, $medicalNotes, $medicalInformationId]);
    } else {
        $pdo->prepare('INSERT INTO enrollment_medical_information (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?, ?, ?)')
            ->execute([$enrollmentId, $medicalSmoke, $medicalNotes]);
        $medicalInformationId = intval($pdo->lastInsertId());
    }

    if ($medicalInformationId > 0) {
        $pdo->prepare('DELETE FROM enrollment_medical_allergies WHERE medical_information_id = ?')->execute([$medicalInformationId]);
        $pdo->prepare('DELETE FROM enrollment_medical_conditions WHERE medical_information_id = ?')->execute([$medicalInformationId]);
        $pdo->prepare('DELETE FROM enrollment_medical_surgeries WHERE medical_information_id = ?')->execute([$medicalInformationId]);
        $pdo->prepare('DELETE FROM enrollment_medical_treatments WHERE medical_information_id = ?')->execute([$medicalInformationId]);
        $pdo->prepare('DELETE FROM enrollment_family_medical_history WHERE medical_information_id = ?')->execute([$medicalInformationId]);

        $allergyTypeIds = parseIds($data['medicine_allergy'] ?? []);
        $allergyDescs = $data['allergy_description'] ?? [];
        if (!is_array($allergyDescs)) {
            $allergyDescs = ['default' => trim((string)$allergyDescs)];
        }
        if (!empty($allergyTypeIds)) {
            $stmt = $pdo->prepare('INSERT INTO enrollment_medical_allergies (medical_information_id, allergy_type_id, description) VALUES (?, ?, ?)');
            foreach ($allergyTypeIds as $typeId) {
                $stmt->execute([$medicalInformationId, $typeId, strOrNull($allergyDescs[$typeId] ?? $allergyDescs['default'] ?? null)]);
            }
        }

        $conditionTypeIds = parseIds($data['condition_type_id'] ?? []);
        $conditionDesc = strOrNull($data['condition_description'] ?? null);
        if (!empty($conditionTypeIds)) {
            $stmt = $pdo->prepare('INSERT INTO enrollment_medical_conditions (medical_information_id, condition_type_id, description) VALUES (?, ?, ?)');
            foreach ($conditionTypeIds as $typeId) {
                $stmt->execute([$medicalInformationId, $typeId, $conditionDesc]);
            }
        }

        $hasSurgery = normalizeCheckbox($data['has_surgery_hospitalization'] ?? 0);
        $surgeryDate = strOrNull($data['surgery_date'] ?? null);
        $hospitalName = strOrNull($data['hospital_name'] ?? null);
        $bodyPart = strOrNull($data['body_part'] ?? null);
        if ($hasSurgery || $surgeryDate || $hospitalName || $bodyPart) {
            $pdo->prepare('INSERT INTO enrollment_medical_surgeries (medical_information_id, surgery_date, hospital_name, body_part) VALUES (?, ?, ?, ?)')
                ->execute([$medicalInformationId, $surgeryDate, $hospitalName, $bodyPart]);
        }

        $hasTreatment = normalizeCheckbox($data['is_taking_treatment'] ?? 0);
        $treatmentMedicine = strOrNull($data['treatment_medicine'] ?? null);
        $scheduleDosage = strOrNull($data['schedule_dosage'] ?? null);
        if ($hasTreatment || $treatmentMedicine || $scheduleDosage) {
            $pdo->prepare('INSERT INTO enrollment_medical_treatments (medical_information_id, treatment_medicine, schedule_dosage) VALUES (?, ?, ?)')
                ->execute([$medicalInformationId, $treatmentMedicine, $scheduleDosage]);
        }

        $familyTypeIds = parseIds($data['family_condition_type_id'] ?? []);
        $familyDesc = strOrNull($data['family_condition_description'] ?? null);
        if (!empty($familyTypeIds)) {
            $stmt = $pdo->prepare('INSERT INTO enrollment_family_medical_history (medical_information_id, family_history_type_id, description) VALUES (?, ?, ?)');
            foreach ($familyTypeIds as $typeId) {
                $stmt->execute([$medicalInformationId, $typeId, $familyDesc]);
            }
        }
    }

    $pdo->commit();

    sendJson([
        'success'       => true,
        'enrollment_id' => $enrollmentId,
        'student_id'    => $studentId,
        'message'       => 'Enrollment updated successfully',
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}

