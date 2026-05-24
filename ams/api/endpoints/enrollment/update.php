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
    // For now, we only support reading/display, not updating guardians via this endpoint.
    // Guardian updates would require a separate endpoint or form redesign.

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

