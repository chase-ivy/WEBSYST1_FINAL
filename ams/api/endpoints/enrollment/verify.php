<?php
// ============================================================
// endpoints/enrollment/verify.php
// Verifies a pending enrollment. This is the moment the
// permanent school and medical records are created.
//
// Transaction order:
//   1. Check enrollment exists and is pending
//   2. INSERT student_school_records (snapshot from students + enrollments)
//   3. INSERT student_medical_records (snapshot from enrollment_medical_*)
//   4. UPDATE enrollments status, verified_by, verified_at
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();

$enrollmentId = intval($data['enrollment_id'] ?? 0);
if ($enrollmentId <= 0) {
    sendJson(['success' => false, 'error' => 'enrollment_id is required'], 400);
}

$verifiedBy = intval($_SESSION['user_id']);

// ── Fetch enrollment + student ────────────────────────────────

$stmt = $pdo->prepare('
    SELECT e.*, s.lrn, s.psa_bcn, s.last_name, s.first_name, s.middle_name,
           s.extension_name, s.birth_date, s.sex, s.place_of_birth
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    WHERE e.enrollment_id = ?
    LIMIT 1
');
$stmt->execute([$enrollmentId]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    sendJson(['success' => false, 'error' => 'Enrollment not found'], 404);
}

if ($enrollment['enrollment_status'] !== 'pending') {
    sendJson(['success' => false, 'error' => 'Only pending enrollments can be verified'], 400);
}

// Guard: grade_level is required for school records and section assignment.
// Old enrollments submitted before validation was enforced may have a null grade_level.
if (empty(trim((string)($enrollment['grade_level'] ?? '')))) {
    sendJson(['success' => false, 'error' => 'This enrollment is missing a grade level. Please edit and save the enrollment (Step 1) before verifying.'], 422);
}

// ── Resolve lookup names ──────────────────────────────────────

$motherTongueName = null;
if ($enrollment['mother_tongue_id']) {
    $mt = $pdo->prepare('SELECT name FROM mother_tongues WHERE mother_tongue_id = ? LIMIT 1');
    $mt->execute([$enrollment['mother_tongue_id']]);
    $motherTongueName = $mt->fetchColumn() ?: null;
}

$indigenousGroupName = null;
if ($enrollment['indigenous_group_id']) {
    $ig = $pdo->prepare('SELECT name FROM indigenous_groups WHERE indigenous_group_id = ? LIMIT 1');
    $ig->execute([$enrollment['indigenous_group_id']]);
    $indigenousGroupName = $ig->fetchColumn() ?: null;
}

$religionName = null;
if ($enrollment['religion_id']) {
    $r = $pdo->prepare('SELECT name FROM religions WHERE religion_id = ? LIMIT 1');
    $r->execute([$enrollment['religion_id']]);
    $religionName = $r->fetchColumn() ?: null;
}

// ── Resolve disabilities to JSON ──────────────────────────────

$disStmt = $pdo->prepare('
    SELECT dt.name AS type, ds.name AS subtype
    FROM enrollment_disabilities ed
    LEFT JOIN disability_types dt ON ed.disability_type_id = dt.disability_type_id
    LEFT JOIN disability_subtypes ds ON ed.disability_subtype_id = ds.disability_subtype_id
    WHERE ed.enrollment_id = ?
');
$disStmt->execute([$enrollmentId]);
$disabilityRows = $disStmt->fetchAll();
$disabilityJson = !empty($disabilityRows) ? json_encode($disabilityRows) : null;

// ── Resolve special needs to JSON ─────────────────────────────

$specialNeedsId     = null;
$hasSpecialNeeds    = 0;
$hasPwdId           = 0;
$pwdIdNumber        = null;
$diagnosesJson      = null;
$manifestationsJson = null;

$specNeedStmt = $pdo->prepare('SELECT * FROM enrollment_special_needs WHERE enrollment_id = ? LIMIT 1');
$specNeedStmt->execute([$enrollmentId]);
$specNeedRow = $specNeedStmt->fetch();

if ($specNeedRow) {
    $specialNeedsId  = intval($specNeedRow['special_needs_id']);
    $hasSpecialNeeds = intval($specNeedRow['has_special_needs']);
    $hasPwdId        = intval($specNeedRow['has_pwd_id']);
    $pwdIdNumber     = $specNeedRow['pwd_id_number'];
    
    // Fetch special needs types and categorize them
    $specTypeStmt = $pdo->prepare('
        SELECT snt.name, snt.category
        FROM enrollment_special_needs_details esnd
        JOIN special_needs_types snt ON esnd.special_needs_type_id = snt.special_needs_type_id
        WHERE esnd.special_needs_id = ?
    ');
    $specTypeStmt->execute([$specialNeedsId]);
    $specTypeRows = $specTypeStmt->fetchAll();
    
    $diagnoses      = [];
    $manifestations = [];
    foreach ($specTypeRows as $row) {
        if (strtolower($row['category']) === 'diagnosis') {
            $diagnoses[] = $row['name'];
        } elseif (strtolower($row['category']) === 'manifestation') {
            $manifestations[] = $row['name'];
        }
    }
    
    if (!empty($diagnoses)) $diagnosesJson = json_encode($diagnoses);
    if (!empty($manifestations)) $manifestationsJson = json_encode($manifestations);
}

// ── Resolve medical data to JSON ──────────────────────────────

$medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
$medStmt->execute([$enrollmentId]);
$medInfo = $medStmt->fetch();

$allergiesJson        = null;
$conditionsJson       = null;
$surgeriesJson        = null;
$treatmentsJson       = null;
$familyHistoryJson    = null;
$exposedToSmoke       = 0;
$otherPertinentInfo   = null;

if ($medInfo) {
    $medInfoId          = intval($medInfo['medical_information_id']);
    $exposedToSmoke     = intval($medInfo['exposed_to_cigarette_vape_smoke']);
    $otherPertinentInfo = $medInfo['other_pertinent_information'];

    $allergyStmt = $pdo->prepare('
        SELECT ema.allergy_type_id, mat.name AS allergy_name, ema.description
        FROM enrollment_medical_allergies ema
        JOIN medical_allergy_types mat ON ema.allergy_type_id = mat.allergy_type_id
        WHERE ema.medical_information_id = ?
    ');
    $allergyStmt->execute([$medInfoId]);
    $rows = $allergyStmt->fetchAll();
    if (!empty($rows)) $allergiesJson = json_encode($rows);

    $conditionStmt = $pdo->prepare('
        SELECT emc.condition_type_id, mct.name AS condition_name, emc.description
        FROM enrollment_medical_conditions emc
        JOIN medical_condition_types mct ON emc.condition_type_id = mct.condition_type_id
        WHERE emc.medical_information_id = ?
    ');
    $conditionStmt->execute([$medInfoId]);
    $rows = $conditionStmt->fetchAll();
    if (!empty($rows)) $conditionsJson = json_encode($rows);

    $surgeryStmt = $pdo->prepare('SELECT surgery_date, hospital_name, body_part FROM enrollment_medical_surgeries WHERE medical_information_id = ?');
    $surgeryStmt->execute([$medInfoId]);
    $rows = $surgeryStmt->fetchAll();
    if (!empty($rows)) $surgeriesJson = json_encode($rows);

    $treatmentStmt = $pdo->prepare('SELECT treatment_medicine, schedule_dosage FROM enrollment_medical_treatments WHERE medical_information_id = ?');
    $treatmentStmt->execute([$medInfoId]);
    $rows = $treatmentStmt->fetchAll();
    if (!empty($rows)) $treatmentsJson = json_encode($rows);

    $familyStmt = $pdo->prepare('
        SELECT emfh.family_history_type_id, fht.name AS family_history_name, emfh.description
        FROM enrollment_family_medical_history emfh
        JOIN family_medical_history_types fht ON emfh.family_history_type_id = fht.family_history_type_id
        WHERE emfh.medical_information_id = ?
    ');
    $familyStmt->execute([$medInfoId]);
    $rows = $familyStmt->fetchAll();
    if (!empty($rows)) $familyHistoryJson = json_encode($rows);
}

// ── Write permanent records ───────────────────────────────────

$existingSchoolRecord = $pdo->prepare('SELECT school_record_id FROM student_school_records WHERE enrollment_id = ? LIMIT 1');
$existingSchoolRecord->execute([$enrollmentId]);
$schoolRecordId = $existingSchoolRecord->fetchColumn();

try {
    $pdo->beginTransaction();

    if ($schoolRecordId) {
        $schoolRecordId = intval($schoolRecordId);

        $existingMedical = $pdo->prepare('SELECT 1 FROM student_medical_records WHERE school_record_id = ? LIMIT 1');
        $existingMedical->execute([$schoolRecordId]);
        $hasMedical = $existingMedical->fetchColumn() !== false;

        if (!$hasMedical) {
            $pdo->prepare('INSERT INTO student_medical_records
                (school_record_id, exposed_to_cigarette_vape_smoke, other_pertinent_information,
                 allergies, conditions, surgeries, treatments, family_medical_history)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([
                    $schoolRecordId,
                    $exposedToSmoke,
                    $otherPertinentInfo,
                    $allergiesJson,
                    $conditionsJson,
                    $surgeriesJson,
                    $treatmentsJson,
                    $familyHistoryJson,
                ]);
        }

        $pdo->prepare('UPDATE enrollments
            SET enrollment_status = ?, verified_by = ?, verified_at = NOW()
            WHERE enrollment_id = ?')
            ->execute(['verified', $verifiedBy, $enrollmentId]);

        $pdo->commit();

        sendJson([
            'success'         => true,
            'enrollment_id'   => $enrollmentId,
            'school_record_id'=> $schoolRecordId,
            'message'         => $hasMedical
                ? 'Enrollment already had permanent record; verification status repaired.'
                : 'Enrollment already had school record; medical record created and enrollment verified.',
        ]);
        return;
    }

    // 1. student_school_records — fresh INSERT (not update)
    $pdo->prepare('
        INSERT INTO student_school_records
            (enrollment_id, student_id, school_year, grade_level, academic_status,
             lrn, psa_bcn, last_name, first_name, middle_name, extension_name,
             birth_date, sex, place_of_birth,
             mother_tongue, religion, indigenous_group, four_ps_household_id,
             is_learner_with_disability, is_returning_learner, has_special_needs, has_pwd_id,
             disabilities, verified_by, verified_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ')->execute([
        $enrollmentId,
        $enrollment['student_id'],
        $enrollment['school_year'],
        $enrollment['grade_level'],
        'active',
        $enrollment['lrn'],
        $enrollment['psa_bcn'],
        $enrollment['last_name'],
        $enrollment['first_name'],
        $enrollment['middle_name'],
        $enrollment['extension_name'],
        $enrollment['birth_date'],
        $enrollment['sex'],
        $enrollment['place_of_birth'],
        $motherTongueName,
        $religionName,
        $indigenousGroupName,
        $enrollment['four_ps_household_id'],
        $enrollment['is_learner_with_disability'],
        $enrollment['is_returning_learner'],
        $hasSpecialNeeds,
        $hasPwdId,
        $disabilityJson,
        $verifiedBy,
    ]);
    $schoolRecordId = intval($pdo->lastInsertId());

    // 2. student_medical_records — fresh INSERT
    $pdo->prepare('
        INSERT INTO student_medical_records
            (school_record_id, exposed_to_cigarette_vape_smoke, other_pertinent_information,
             allergies, conditions, surgeries, treatments, family_medical_history)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $schoolRecordId,
        $exposedToSmoke,
        $otherPertinentInfo,
        $allergiesJson,
        $conditionsJson,
        $surgeriesJson,
        $treatmentsJson,
        $familyHistoryJson,
    ]);

    // 3. student_special_needs_records — fresh INSERT
    if ($hasSpecialNeeds || $hasPwdId) {
        $pdo->prepare('
            INSERT INTO student_special_needs_records
                (school_record_id, has_special_needs, has_pwd_id, pwd_id_number,
                 diagnoses, manifestations)
            VALUES (?, ?, ?, ?, ?, ?)
        ')->execute([
            $schoolRecordId,
            $hasSpecialNeeds,
            $hasPwdId,
            $pwdIdNumber,
            $diagnosesJson,
            $manifestationsJson,
        ]);
    }

    // 4. Update enrollment status
    $pdo->prepare('
        UPDATE enrollments
        SET enrollment_status = ?, verified_by = ?, verified_at = NOW()
        WHERE enrollment_id = ?
    ')->execute(['verified', $verifiedBy, $enrollmentId]);

    $pdo->commit();

    sendJson([
        'success'         => true,
        'enrollment_id'   => $enrollmentId,
        'school_record_id'=> $schoolRecordId,
        'message'         => 'Enrollment verified and permanent records created',
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}