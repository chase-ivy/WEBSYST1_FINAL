<?php
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'get') {
        $enrollment_id = intval($_GET['enrollment_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT m.medical_id, m.enrollment_id, m.exposed_to_cigarette_vape_smoke, m.other_pertinent_information
                              FROM medical_information m
                              WHERE m.enrollment_id = ?');
        $stmt->execute([$enrollment_id]);
        $medical = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($medical) {
            // Get medical conditions
            $cond_stmt = $pdo->prepare('SELECT sc.student_condition_id, ct.name as condition_name, sc.description
                                        FROM student_conditions sc
                                        JOIN condition_types ct ON sc.condition_type_id = ct.condition_type_id
                                        JOIN medical_conditions mc ON mc.condition_group_id = ? 
                                        WHERE mc.medical_id = ?');
            $cond_stmt->execute([$medical['medical_id'], $medical['medical_id']]);
            $medical['conditions'] = $cond_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get allergies
            $allergy_stmt = $pdo->prepare('SELECT sa.student_allergy_id, at.name as allergy_type, sa.description
                                           FROM student_allergies sa
                                           JOIN allergy_types at ON sa.allergy_type_id = at.allergy_type_id
                                           JOIN medical_allergies ma ON ma.allergy_group_id = ? 
                                           WHERE ma.medical_id = ?');
            $allergy_stmt->execute([$medical['medical_id'], $medical['medical_id']]);
            $medical['allergies'] = $allergy_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode(['success' => true, 'data' => $medical]);
    }
    elseif ($action === 'save') {
        $data = json_decode(file_get_contents('php://input'), true);
        $enrollment_id = intval($data['enrollment_id'] ?? 0);
        $exposed = intval($data['exposed_to_cigarette_vape_smoke'] ?? 0);
        $other_info = $data['other_pertinent_information'] ?? null;

        // Check if medical record exists
        $check = $pdo->prepare('SELECT medical_id FROM medical_information WHERE enrollment_id = ?');
        $check->execute([$enrollment_id]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE medical_information 
                                  SET exposed_to_cigarette_vape_smoke = ?, other_pertinent_information = ? 
                                  WHERE medical_id = ?');
            $stmt->execute([$exposed, $other_info, $existing['medical_id']]);
            $medical_id = $existing['medical_id'];
        } else {
            $stmt = $pdo->prepare('INSERT INTO medical_information (enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information) 
                                  VALUES (?, ?, ?)');
            $stmt->execute([$enrollment_id, $exposed, $other_info]);
            $medical_id = $pdo->lastInsertId();
        }

        echo json_encode(['success' => true, 'medical_id' => $medical_id]);
    }
    else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

<?php

    $med_state = $pdo->prepare('INSERT INTO medical_allergies (medical_id, has_allergies) VALUES (?, ?)');
    $med_state->execute([
        $medical_id, 
        $_POST['has_allergies'] ?? '0'
    ]);
    
    $allery_group_id = $pdo->lastInsertId();
    
    $med_state1 = $pdo->prepare('INSERT INTO student_allergies (allergy_group_id, allergy_type_id, description) VALUES (?, ?, ?)');
    $med_state1->execute([
        $allery_group_id,
        $_POST['medicine_allergy'] ?? '0',
        $_POST['allergy_description'] ?? ''
    ]);


    $med_state2 = $pdo->prepare('INSERT INTO medical_conditions (medical_id, has_conditions) VALUES (?, ?)');
    $med_state2->execute([$medical_id, $_POST['has_med_conditions'] ?? '0']);

    $condition_group_id = $pdo->lastInsertId();

    $med_state3 = $pdo->prepare('INSERT INTO student_conditions (condition_group_id, condition_type_id, descriptipion) VALUES (?, ?, ?)');
    $med_state3->execute([
        $condition_group_id,
        $_POST['condition_type_id'] ?? '0',
        $_POST['condition_description'] ?? ''
    ]);

    $med_state4 = $pdo->('INSERT INTO medical_surgeries (medical_id, has_surgery, surgery_date, hospital_name, body_part) VALUES (?, ?, ?, ?, ?)');
    $med_state4->execute([
        $medical_id,
        $_POST['has_surgery_hospitalization'] ?? '0',
        $_POST['surgery_date'] ?? '',
        $_POST['hospital_name'] ?? '',
        $_POST['body_part'] ?? ''
    ]);

    $med_state5 = $pdo->prepare('INSERT INTO medical_treatments (medical_id, is_taking_treatment, treatment_medicine, schedule_dosage) VALUES (?, ?, ?, ?)');
    $med_state5->execute([
        $medical_id,
        $_POST['is_taking_treatment'] ?? '0',
        $_POST['treatment_medicine'] ?? '',
        $_POST['schedule_dosage'] ?? ''
    ]);

    $med_state6 = $pdo->prepare('INSERT INTO family_medical_history (medical_id, has_family_history) VALUES (?,?))');
    $med_state6->execute([
        $medical_id,
        $_POST['has_family_history'] ?? '0'
    ]);

    $family_history_id = $pdo->lastInsertId();

    $med_state7 = $pdo0->prepare('INSERT INTO student_family_history (family_history_id, family_condition_type_id, description) VALUES (?, ?, ?)');
    $med_state7->execute([
        $family_history_id,
        $_POST['family_condition_type_id'] ?? '0',
        $_POST['family_condition_description'] ?? ''
    ]);


?>  
