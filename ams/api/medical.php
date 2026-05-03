<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$role = $_SESSION['role'] ?? '';

try {
    if ($action === 'get' && ($role === 'parent' || $role === 'student')) {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM medical_information WHERE student_id = ?');
        $stmt->execute([$student_id]);
        $medical = $stmt->fetch();
        
        if ($medical) {
            $allergies_stmt = $pdo->prepare('SELECT allergy_id, allergy_name, severity FROM allergies WHERE medical_id = ?');
            $allergies_stmt->execute([$medical['medical_id']]);
            $medical['allergies'] = $allergies_stmt->fetchAll();
            
            $conditions_stmt = $pdo->prepare('SELECT condition_id, condition_name, status FROM chronic_conditions WHERE medical_id = ?');
            $conditions_stmt->execute([$medical['medical_id']]);
            $medical['conditions'] = $conditions_stmt->fetchAll();
        }
        
        echo json_encode(['success' => true, 'data' => $medical]);
    } elseif ($action === 'save' && ($role === 'admin' || $role === 'teacher')) {
        $student_id = intval($_POST['student_id'] ?? 0);
        $blood_type = $_POST['blood_type'] ?? '';
        $height = $_POST['height'] ?? '';
        $weight = $_POST['weight'] ?? '';
        $last_checkup = $_POST['last_checkup'] ?? '';
        $medication = $_POST['medication'] ?? '';
        $restrictions = $_POST['restrictions'] ?? '';

        $check = $pdo->prepare('SELECT medical_id FROM medical_information WHERE student_id = ?');
        $check->execute([$student_id]);
        $existing = $check->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE medical_information SET blood_type = ?, height = ?, weight = ?, last_checkup = ?, medication = ?, restrictions = ? WHERE medical_id = ?');
            $stmt->execute([$blood_type, $height, $weight, $last_checkup, $medication, $restrictions, $existing['medical_id']]);
            $medical_id = $existing['medical_id'];
        } else {
            $stmt = $pdo->prepare('INSERT INTO medical_information (student_id, blood_type, height, weight, last_checkup, medication, restrictions) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$student_id, $blood_type, $height, $weight, $last_checkup, $medication, $restrictions]);
            $medical_id = $pdo->lastInsertId();
        }

        // Handle allergies
        if (!empty($_POST['allergies'])) {
            $allergies = json_decode($_POST['allergies'], true);
            foreach ($allergies as $allergy) {
                if (!empty($allergy['allergy_name'])) {
                    $stmt = $pdo->prepare('INSERT INTO allergies (medical_id, allergy_name, severity) VALUES (?, ?, ?)');
                    $stmt->execute([$medical_id, $allergy['allergy_name'], $allergy['severity'] ?? 'Mild']);
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Medical information saved']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
