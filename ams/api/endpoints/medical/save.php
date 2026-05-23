<?php
// ============================================================
// endpoints/medical/save.php
// Saves or updates medical record data for an enrollment.
// POST body: enrollment_id and medical fields
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();
$enrollmentId = intval($data['enrollment_id'] ?? 0);
if ($enrollmentId <= 0) {
    sendJson(['success' => false, 'error' => 'enrollment_id is required'], 400);
}

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

$medStmt = $pdo->prepare('SELECT medical_information_id FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
$medStmt->execute([$enrollmentId]);
$medInfo = $medStmt->fetch();

try {
    $pdo->beginTransaction();

    if ($medInfo) {
        $medicalInfoId = intval($medInfo['medical_information_id']);
        $update = $pdo->prepare('UPDATE enrollment_medical_information SET exposed_to_cigarette_vape_smoke = ?, other_pertinent_information = ? WHERE medical_information_id = ?');
        $update->execute([
            normalizeCheckbox($data['exposed_to_cigarette_vape_smoke'] ?? 0),
            trim($data['other_pertinent_information'] ?? '') ?: null,
            $medicalInfoId,
        ]);
    } else {
        $insert = $pdo->prepare('INSERT INTO enrollment_medical_information (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?, ?, ?)');
        $insert->execute([
            $enrollmentId,
            normalizeCheckbox($data['exposed_to_cigarette_vape_smoke'] ?? 0),
            trim($data['other_pertinent_information'] ?? '') ?: null,
        ]);
        $medicalInfoId = intval($pdo->lastInsertId());
    }

    $pdo->prepare('DELETE FROM enrollment_medical_allergies WHERE medical_information_id = ?')->execute([$medicalInfoId]);
    $pdo->prepare('DELETE FROM enrollment_medical_conditions WHERE medical_information_id = ?')->execute([$medicalInfoId]);
    $pdo->prepare('DELETE FROM enrollment_medical_surgeries WHERE medical_information_id = ?')->execute([$medicalInfoId]);
    $pdo->prepare('DELETE FROM enrollment_medical_treatments WHERE medical_information_id = ?')->execute([$medicalInfoId]);
    $pdo->prepare('DELETE FROM enrollment_family_medical_history WHERE medical_information_id = ?')->execute([$medicalInfoId]);

    $allergyTypeIds = parseIds($data['medicine_allergy'] ?? []);
    $allergyDescs = is_array($data['allergy_description'] ?? null) ? $data['allergy_description'] : [];
    $allergyStmt = $pdo->prepare('INSERT INTO enrollment_medical_allergies (medical_information_id, allergy_type_id, description) VALUES (?, ?, ?)');
    foreach ($allergyTypeIds as $typeId) {
        $allergyStmt->execute([
            $medicalInfoId,
            $typeId,
            trim($allergyDescs[$typeId] ?? $allergyDescs['default'] ?? '') ?: null,
        ]);
    }

    $conditionTypeIds = parseIds($data['condition_type_id'] ?? []);
    $conditionDescription = trim($data['condition_description'] ?? '') ?: null;
    $conditionStmt = $pdo->prepare('INSERT INTO enrollment_medical_conditions (medical_information_id, condition_type_id, description) VALUES (?, ?, ?)');
    foreach ($conditionTypeIds as $typeId) {
        $conditionStmt->execute([$medicalInfoId, $typeId, $conditionDescription]);
    }

    $hasSurgery = normalizeCheckbox($data['has_surgery_hospitalization'] ?? 0);
    if ($hasSurgery) {
        $surgeryStmt = $pdo->prepare('INSERT INTO enrollment_medical_surgeries (medical_information_id, surgery_date, hospital_name, body_part) VALUES (?, ?, ?, ?)');
        $surgeryStmt->execute([
            $medicalInfoId,
            trim($data['surgery_date'] ?? '') ?: null,
            trim($data['hospital_name'] ?? '') ?: null,
            trim($data['body_part'] ?? '') ?: null,
        ]);
    }

    $isTakingTreatment = normalizeCheckbox($data['is_taking_treatment'] ?? 0);
    if ($isTakingTreatment) {
        $treatmentStmt = $pdo->prepare('INSERT INTO enrollment_medical_treatments (medical_information_id, treatment_medicine, schedule_dosage) VALUES (?, ?, ?)');
        $treatmentStmt->execute([
            $medicalInfoId,
            trim($data['treatment_medicine'] ?? '') ?: null,
            trim($data['schedule_dosage'] ?? '') ?: null,
        ]);
    }

    $familyHistoryTypeIds = parseIds($data['family_condition_type_id'] ?? []);
    $familyDescription = trim($data['family_condition_description'] ?? '') ?: null;
    $familyStmt = $pdo->prepare('INSERT INTO enrollment_family_medical_history (medical_information_id, family_history_type_id, description) VALUES (?, ?, ?)');
    foreach ($familyHistoryTypeIds as $typeId) {
        $familyStmt->execute([$medicalInfoId, $typeId, $familyDescription]);
    }

    $pdo->commit();
    sendJson(['success' => true, 'message' => 'Medical record saved successfully']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
