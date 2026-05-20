<?php
// DEPRECATED: This endpoint implementation is out of sync with the current gems_db schema.
// It has been moved to ams/api/deprecated/medical.php to avoid accidental use.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

function normalizeCheckboxValue($value): int {
    return in_array((string)$value, ['1', 'true', 'yes', 'on', 'Yes'], true) ? 1 : 0;
}

function parseIdsValue($value): array {
    if (is_array($value)) {
        return array_values(array_filter(array_map('intval', $value), fn($item) => $item > 0));
    }

    if ($value === null || $value === '') {
        return [];
    }

    return [intval($value)];
}

function getStringValue($value): ?string {
    if (is_array($value)) {
        $parts = array_filter(array_map('trim', $value), fn($item) => $item !== '');
        $value = implode(', ', $parts);
    }

    $value = trim((string)($value ?? ''));
    return $value === '' ? null : $value;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'get') {
        $enrollmentId = intval($_GET['enrollment_id'] ?? 0);
        if ($enrollmentId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'enrollment_id is required']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT medical_id, enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information FROM medical_information WHERE enrollment_id = ?');
        $stmt->execute([$enrollmentId]);
        $medical = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($medical) {
            $medical['allergies'] = [];
            $medical['conditions'] = [];
            $medical['surgeries'] = [];
            $medical['treatments'] = [];
            $medical['family_history'] = [];

            $allergiesStmt = $pdo->prepare('SELECT sa.student_allergy_id, sa.allergy_type_id, at.name AS allergy_type, sa.description FROM medical_allergies ma JOIN student_allergies sa ON ma.allergy_group_id = sa.allergy_group_id JOIN allergy_types at ON sa.allergy_type_id = at.allergy_type_id WHERE ma.medical_id = ?');
            $allergiesStmt->execute([$medical['medical_id']]);
            $medical['allergies'] = $allergiesStmt->fetchAll(PDO::FETCH_ASSOC);

            $conditionsStmt = $pdo->prepare('SELECT sc.student_condition_id, sc.condition_type_id, ct.name AS condition_name, sc.description FROM medical_conditions mc JOIN student_conditions sc ON mc.condition_group_id = sc.condition_group_id JOIN condition_types ct ON sc.condition_type_id = ct.condition_type_id WHERE mc.medical_id = ?');
            $conditionsStmt->execute([$medical['medical_id']]);
            $medical['conditions'] = $conditionsStmt->fetchAll(PDO::FETCH_ASSOC);

            $surgeriesStmt = $pdo->prepare('SELECT surgery_id, has_surgery, surgery_date, hospital_name, body_part FROM medical_surgeries WHERE medical_id = ?');
            $surgeriesStmt->execute([$medical['medical_id']]);
            $medical['surgeries'] = $surgeriesStmt->fetchAll(PDO::FETCH_ASSOC);

            $treatmentsStmt = $pdo->prepare('SELECT treatment_id, is_taking_treatment, treatment_medicine, schedule_dosage FROM medical_treatments WHERE medical_id = ?');
            $treatmentsStmt->execute([$medical['medical_id']]);
            $medical['treatments'] = $treatmentsStmt->fetchAll(PDO::FETCH_ASSOC);

            $familyStmt = $pdo->prepare('SELECT fmh.family_history_id, fmh.has_family_history, sfc.student_family_condition_id, sfc.family_condition_type_id, fct.name AS condition_name, sfc.description FROM family_medical_history fmh LEFT JOIN student_family_conditions sfc ON fmh.family_history_id = sfc.family_history_id LEFT JOIN family_condition_types fct ON sfc.family_condition_type_id = fct.family_condition_type_id WHERE fmh.medical_id = ?');
            $familyStmt->execute([$medical['medical_id']]);
            $familyRows = $familyStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($familyRows)) {
                $family = [
                    'family_history_id' => $familyRows[0]['family_history_id'],
                    'has_family_history' => $familyRows[0]['has_family_history'],
                    'conditions' => []
                ];

                foreach ($familyRows as $row) {
                    if (!empty($row['student_family_condition_id'])) {
                        $family['conditions'][] = [
                            'student_family_condition_id' => $row['student_family_condition_id'],
                            'family_condition_type_id' => $row['family_condition_type_id'],
                            'condition_name' => $row['condition_name'],
                            'description' => $row['description']
                        ];
                    }
                }

                $medical['family_history'] = $family;
            }
        }

        echo json_encode(['success' => true, 'data' => $medical]);
        exit;
    }

    if ($action === 'save') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            exit;
        }

        $enrollmentId = intval($data['enrollment_id'] ?? 0);
        if ($enrollmentId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'enrollment_id is required']);
            exit;
        }

        $exposedToSmoke = normalizeCheckboxValue($data['exposed_to_cigarette_vape_smoke'] ?? 0);
        $otherPertinentInformation = getStringValue($data['other_pertinent_information'] ?? null);
        $hasAllergies = normalizeCheckboxValue($data['has_allergies'] ?? 0);
        $allergyTypeIds = parseIdsValue($data['medicine_allergy'] ?? []);
        $allergyDescriptions = $data['allergy_description'] ?? [];
        if (!is_array($allergyDescriptions)) {
            $allergyDescriptions = ['default' => trim((string)$allergyDescriptions)];
        }
        $hasConditions = normalizeCheckboxValue($data['has_med_condition'] ?? 0);
        $conditionTypeIds = parseIdsValue($data['condition_type_id'] ?? []);
        $conditionDescriptions = $data['condition_description'] ?? [];
        if (!is_array($conditionDescriptions)) {
            $conditionDescriptions = ['default' => trim((string)$conditionDescriptions)];
        }
        $hasSurgery = normalizeCheckboxValue($data['has_surgery_hospitalization'] ?? 0);
        $surgeryDate = getStringValue($data['surgery_date'] ?? null);
        $hospitalName = getStringValue($data['hospital_name'] ?? null);
        $bodyPart = getStringValue($data['body_part'] ?? null);
        $isTakingTreatment = normalizeCheckboxValue($data['is_taking_treatment'] ?? 0);
        $treatmentMedicine = getStringValue($data['treatment_medicine'] ?? null);
        $scheduleDosage = getStringValue($data['schedule_dosage'] ?? null);
        $hasFamilyHistory = normalizeCheckboxValue($data['family_medical_history'] ?? 0);
        $familyConditionTypeIds = parseIdsValue($data['family_condition_type_id'] ?? []);
        $familyConditionDescriptions = $data['family_condition_description'] ?? [];
        if (!is_array($familyConditionDescriptions)) {
            $familyConditionDescriptions = ['default' => trim((string)$familyConditionDescriptions)];
        }

        $pdo->beginTransaction();

        $check = $pdo->prepare('SELECT medical_id FROM medical_information WHERE enrollment_id = ?');
        $check->execute([$enrollmentId]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $medicalId = intval($existing['medical_id']);
            $update = $pdo->prepare('UPDATE medical_information SET exposed_to_cigarette_vape_smoke = ?, other_pertinent_information = ? WHERE medical_id = ?');
            $update->execute([$exposedToSmoke, $otherPertinentInformation, $medicalId]);

            $pdo->prepare('DELETE FROM student_allergies WHERE allergy_group_id IN (SELECT allergy_group_id FROM medical_allergies WHERE medical_id = ?)')->execute([$medicalId]);
            $pdo->prepare('DELETE FROM medical_allergies WHERE medical_id = ?')->execute([$medicalId]);
            $pdo->prepare('DELETE FROM student_conditions WHERE condition_group_id IN (SELECT condition_group_id FROM medical_conditions WHERE medical_id = ?)')->execute([$medicalId]);
            $pdo->prepare('DELETE FROM medical_conditions WHERE medical_id = ?')->execute([$medicalId]);
            $pdo->prepare('DELETE FROM medical_surgeries WHERE medical_id = ?')->execute([$medicalId]);
            $pdo->prepare('DELETE FROM medical_treatments WHERE medical_id = ?')->execute([$medicalId]);
            $pdo->prepare('DELETE FROM student_family_conditions WHERE family_history_id IN (SELECT family_history_id FROM family_medical_history WHERE medical_id = ?)')->execute([$medicalId]);
            $pdo->prepare('DELETE FROM family_medical_history WHERE medical_id = ?')->execute([$medicalId]);
        } else {
            $insert = $pdo->prepare('INSERT INTO medical_information (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) VALUES (?, ?, ?)');
            $insert->execute([$enrollmentId, $exposedToSmoke, $otherPertinentInformation]);
            $medicalId = intval($pdo->lastInsertId());
        }

        $insertAllergies = $pdo->prepare('INSERT INTO medical_allergies (medical_id, has_allergies) VALUES (?, ?)');
        $insertAllergies->execute([$medicalId, $hasAllergies]);
        $allergyGroupId = intval($pdo->lastInsertId());

        if (!empty($allergyTypeIds)) {
            $insertStudentAllergy = $pdo->prepare('INSERT INTO student_allergies (allergy_group_id, allergy_type_id, description) VALUES (?, ?, ?)');
            foreach ($allergyTypeIds as $typeId) {
                $description = trim((string)($allergyDescriptions[$typeId] ?? $allergyDescriptions['default'] ?? ''));
                $description = $description === '' ? null : $description;
                $insertStudentAllergy->execute([$allergyGroupId, $typeId, $description]);
            }
        }

        $insertConditions = $pdo->prepare('INSERT INTO medical_conditions (medical_id, has_conditions) VALUES (?, ?)');
        $insertConditions->execute([$medicalId, $hasConditions]);
        $conditionGroupId = intval($pdo->lastInsertId());

        if (!empty($conditionTypeIds)) {
            $insertStudentCondition = $pdo->prepare('INSERT INTO student_conditions (condition_group_id, condition_type_id, description) VALUES (?, ?, ?)');
            foreach ($conditionTypeIds as $typeId) {
                $description = trim((string)($conditionDescriptions[$typeId] ?? $conditionDescriptions['default'] ?? ''));
                $description = $description === '' ? null : $description;
                $insertStudentCondition->execute([$conditionGroupId, $typeId, $description]);
            }
        }

        $insertSurgery = $pdo->prepare('INSERT INTO medical_surgeries (medical_id, has_surgery, surgery_date, hospital_name, body_part) VALUES (?, ?, ?, ?, ?)');
        $insertSurgery->execute([$medicalId, $hasSurgery, $surgeryDate, $hospitalName, $bodyPart]);

        $insertTreatment = $pdo->prepare('INSERT INTO medical_treatments (medical_id, is_taking_treatment, treatment_medicine, schedule_dosage) VALUES (?, ?, ?, ?)');
        $insertTreatment->execute([$medicalId, $isTakingTreatment, $treatmentMedicine, $scheduleDosage]);

        $insertFamilyHistory = $pdo->prepare('INSERT INTO family_medical_history (medical_id, has_family_history) VALUES (?, ?)');
        $insertFamilyHistory->execute([$medicalId, $hasFamilyHistory]);
        $familyHistoryId = intval($pdo->lastInsertId());

        if (!empty($familyConditionTypeIds)) {
            $insertFamilyCondition = $pdo->prepare('INSERT INTO student_family_conditions (family_history_id, family_condition_type_id, description) VALUES (?, ?, ?)');
            foreach ($familyConditionTypeIds as $typeId) {
                $description = trim((string)($familyConditionDescriptions[$typeId] ?? $familyConditionDescriptions['default'] ?? ''));
                $description = $description === '' ? null : $description;
                $insertFamilyCondition->execute([$familyHistoryId, $typeId, $description]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'medical_id' => $medicalId]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
  
