<?php
// ============================================================
// endpoints/medical/get_by_enrollment.php
// Loads the medical records for a given enrollment.
// GET ?enrollment_id=<id>
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$enrollmentId = isset($_GET['enrollment_id']) ? intval($_GET['enrollment_id']) : 0;
if ($enrollmentId <= 0) {
    sendJson(['success' => false, 'error' => 'enrollment_id is required'], 400);
}

$medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
$medStmt->execute([$enrollmentId]);
$medicalInfo = $medStmt->fetch();

$medicalInfoId = $medicalInfo ? intval($medicalInfo['medical_information_id']) : null;
$allergies = $conditions = $surgeries = $treatments = $familyHistory = [];

if ($medicalInfoId) {
    $stmt = $pdo->prepare('SELECT * FROM enrollment_medical_allergies WHERE medical_information_id = ?');
    $stmt->execute([$medicalInfoId]);
    $allergies = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM enrollment_medical_conditions WHERE medical_information_id = ?');
    $stmt->execute([$medicalInfoId]);
    $conditions = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM enrollment_medical_surgeries WHERE medical_information_id = ?');
    $stmt->execute([$medicalInfoId]);
    $surgeries = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM enrollment_medical_treatments WHERE medical_information_id = ?');
    $stmt->execute([$medicalInfoId]);
    $treatments = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM enrollment_family_medical_history WHERE medical_information_id = ?');
    $stmt->execute([$medicalInfoId]);
    $familyHistory = $stmt->fetchAll();
}

sendJson([
    'success' => true,
    'data' => [
        'medical_info' => $medicalInfo ?: null,
        'allergies' => $allergies,
        'conditions' => $conditions,
        'surgeries' => $surgeries,
        'treatments' => $treatments,
        'family_history' => [
            'conditions' => $familyHistory,
        ],
    ],
]);
