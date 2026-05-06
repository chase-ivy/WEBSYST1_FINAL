<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

function fetchOne($pdo, $table, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE student_id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchOneBy($pdo, $table, $column, $id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $column = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchParents($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT e.enrollment_id FROM enrollments e WHERE e.student_id = ? ORDER BY e.enrollment_id DESC LIMIT 1");
    $stmt->execute([$student_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($enrollment['enrollment_id'])) {
        return [];
    }

    $stmt = $pdo->prepare("SELECT p.*, ep.relationship FROM parents p JOIN enrollment_parents ep ON p.parent_id = ep.parent_id WHERE ep.enrollment_id = ?");
    $stmt->execute([$enrollment['enrollment_id']]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['relationship'] ?? 'parent'] = $row;
    }
    return $result;
}

function fetchDisabilities($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT disability_type_id FROM student_disabilities WHERE enrollment_id = (SELECT enrollment_id FROM enrollments WHERE student_id = ? ORDER BY enrollment_id DESC LIMIT 1)");
    $stmt->execute([$student_id]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'disability_type_id');
}

function fetchLatestEnrollment($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? ORDER BY enrollment_id DESC LIMIT 1");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchEnrollmentAddress($pdo, $enrollment_id, $type) {
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE enrollment_id = ? AND address_type = ? LIMIT 1");
    $stmt->execute([$enrollment_id, $type]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function upsertEnrollmentAddress($pdo, $enrollment_id, $type, $data) {
    $stmt = $pdo->prepare("UPDATE addresses SET house_no = ?, street_name = ?, barangay = ?, municipality_city = ?, province = ?, country = ?, zip_code = ? WHERE enrollment_id = ? AND address_type = ?");
    $stmt->execute([
        $data['house_no'] ?? '',
        $data['street_name'] ?? '',
        $data['barangay'] ?? '',
        $data['municipality_city'] ?? '',
        $data['province'] ?? '',
        $data['country'] ?? '',
        $data['zip_code'] ?? '',
        $enrollment_id,
        $type
    ]);

    if ($stmt->rowCount() === 0) {
        $insert = $pdo->prepare("INSERT INTO addresses (enrollment_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([
            $enrollment_id,
            $type,
            $data['house_no'] ?? '',
            $data['street_name'] ?? '',
            $data['barangay'] ?? '',
            $data['municipality_city'] ?? '',
            $data['province'] ?? '',
            $data['country'] ?? '',
            $data['zip_code'] ?? ''
        ]);
    }
}

function updateOrInsertParent($pdo, $enrollment_id, $relationship, $parentData) {
    $stmt = $pdo->prepare("UPDATE parents p JOIN enrollment_parents ep ON p.parent_id = ep.parent_id SET p.last_name = ?, p.first_name = ?, p.middle_name = ?, p.contact_number = ? WHERE ep.enrollment_id = ? AND ep.relationship = ?");
    $stmt->execute([
        $parentData['last_name'] ?? '',
        $parentData['first_name'] ?? '',
        $parentData['middle_name'] ?? '',
        $parentData['contact_number'] ?? '',
        $enrollment_id,
        $relationship
    ]);

    if ($stmt->rowCount() === 0) {
        $insertParent = $pdo->prepare("INSERT INTO parents (last_name, first_name, middle_name, contact_number) VALUES (?, ?, ?, ?)");
        $insertParent->execute([
            $parentData['last_name'] ?? '',
            $parentData['first_name'] ?? '',
            $parentData['middle_name'] ?? '',
            $parentData['contact_number'] ?? ''
        ]);
        $parentId = $pdo->lastInsertId();
        $link = $pdo->prepare("INSERT INTO enrollment_parents (enrollment_id, parent_id, relationship) VALUES (?, ?, ?)");
        $link->execute([$enrollment_id, $parentId, $relationship]);
    }
}

function updateReturningLearner($pdo, $enrollment_id, $isReturning, $returningData) {
    if ($isReturning) {
        $stmt = $pdo->prepare("SELECT enrollment_id FROM returning_learners WHERE enrollment_id = ? LIMIT 1");
        $stmt->execute([$enrollment_id]);
        if ($stmt->fetch()) {
            $update = $pdo->prepare("UPDATE returning_learners SET last_grade_level_completed = ?, last_school_attended = ?, last_school_year_completed = ?, school_id = ? WHERE enrollment_id = ?");
            $update->execute([
                $returningData['last_grade_level_completed'] ?? '',
                $returningData['last_school_attended'] ?? '',
                $returningData['last_school_year_completed'] ?? '',
                $returningData['school_id'] ?? '',
                $enrollment_id
            ]);
        } else {
            $insert = $pdo->prepare("INSERT INTO returning_learners (enrollment_id, last_grade_level_completed, last_school_attended, last_school_year_completed, school_id) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([
                $enrollment_id,
                $returningData['last_grade_level_completed'] ?? '',
                $returningData['last_school_attended'] ?? '',
                $returningData['last_school_year_completed'] ?? '',
                $returningData['school_id'] ?? ''
            ]);
        }
    } else {
        $delete = $pdo->prepare("DELETE FROM returning_learners WHERE enrollment_id = ?");
        $delete->execute([$enrollment_id]);
    }
}

function updateDisabilities($pdo, $enrollment_id, $disabilities) {
    $delete = $pdo->prepare("DELETE FROM student_disabilities WHERE enrollment_id = ?");
    $delete->execute([$enrollment_id]);

    if (is_array($disabilities)) {
        $insert = $pdo->prepare("INSERT INTO student_disabilities (enrollment_id, disability_type_id, disability_subtype_id) VALUES (?, ?, ?)");
        foreach ($disabilities as $typeId) {
            $insert->execute([$enrollment_id, $typeId, null]);
        }
    }
}

$action = $_GET['action'] ?? '';

try {

    if ($action === 'list') {
        $stmt = $pdo->query("SELECT s.student_id, s.lrn, s.first_name, s.last_name, s.middle_name, s.sex, s.place_of_birth,
            (SELECT grade_level FROM enrollments e WHERE e.student_id = s.student_id ORDER BY e.enrollment_id DESC LIMIT 1) AS grade_level,
            (SELECT school_year FROM enrollments e WHERE e.student_id = s.student_id ORDER BY e.enrollment_id DESC LIMIT 1) AS school_year
            FROM students s
            ORDER BY s.last_name ASC, s.first_name ASC");

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

       //GET FULL STUDENT DATA

    if ($action === 'get') {

        $student_id = intval($_GET['student_id'] ?? 0);

        $student   = fetchOne($pdo, 'students', $student_id);
        $latestEnrollment = fetchLatestEnrollment($pdo, $student_id);
        $current   = [];
        $permanent = [];

        if (!empty($latestEnrollment['enrollment_id'])) {
            $current = fetchEnrollmentAddress($pdo, $latestEnrollment['enrollment_id'], 'current');
            $permanent = fetchEnrollmentAddress($pdo, $latestEnrollment['enrollment_id'], 'permanent');
        }

        $parents   = fetchParents($pdo, $student_id);
        $returning = [];
        if (!empty($latestEnrollment['enrollment_id'])) {
            $returning = fetchOneBy($pdo, 'returning_learners', 'enrollment_id', $latestEnrollment['enrollment_id']);
        }
        $disability_ids = fetchDisabilities($pdo, $student_id);

        $schoolYearStart = '';
        $schoolYearEnd = '';
        if (!empty($latestEnrollment['school_year']) && strpos($latestEnrollment['school_year'], '-') !== false) {
            [$schoolYearStart, $schoolYearEnd] = explode('-', $latestEnrollment['school_year'], 2);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'student' => $student,
                'latest_enrollment' => array_merge($latestEnrollment, [
                    'year_start' => $schoolYearStart,
                    'year_end' => $schoolYearEnd,
                ]),
                'current_address' => $current,
                'permanent_address' => $permanent,
                'parents' => $parents,
                'returning' => $returning,
                'disabilities' => $disability_ids
            ]
        ]);
        exit;
    }

       //CREATE FULL PROFILE
    if ($action === 'create') {

        $data = json_decode(file_get_contents("php://input"), true);

        // 1. student
        $stmt = $pdo->prepare("
            INSERT INTO students (lrn, first_name, last_name, middle_name, birth_date, sex, place_of_birth)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['lrn'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['birth_date'],
            $data['sex'],
            $data['place_of_birth']
        ]);

        $student_id = $pdo->lastInsertId();

        // 2. addresses
        $pdo->prepare("INSERT INTO current_address (student_id) VALUES (?)")
            ->execute([$student_id]);

        $pdo->prepare("INSERT INTO permanent_address (student_id) VALUES (?)")
            ->execute([$student_id]);

        echo json_encode(['success' => true, 'student_id' => $student_id]);
        exit;
    }

       //UPDATE FULL PROFILE
    if ($action === 'update') {

        $data = json_decode(file_get_contents("php://input"), true);
        $student_id = intval($data['student_id']);

        // 1. students
        $pdo->prepare("
            UPDATE students
            SET lrn=?, first_name=?, last_name=?, middle_name=?, extension_name=?, birth_date=?, sex=?, place_of_birth=?
            WHERE student_id=?
        ")->execute([
            $data['Learner_Reference_No'] ?? '',
            $data['Learner_First_Name'] ?? '',
            $data['Learner_Last_Name'] ?? '',
            $data['Learner_Middle_Name'] ?? '',
            $data['Learner_Extension_Name'] ?? '',
            $data['Birth_Date'] ?? '',
            $data['sex'] ?? '',
            $data['Place_of_Birth'] ?? '',
            $student_id
        ]);

        $latest = fetchLatestEnrollment($pdo, $student_id);
        if (!empty($latest['enrollment_id'])) {
            $schoolYear = trim(($data['year_start'] ?? '') . '-' . ($data['year_end'] ?? ''));
            $withLrn = !empty($data['with_lrn']) && in_array((string)$data['with_lrn'], ['1', 'Yes'], true) ? 1 : 0;
            $isIndigenous = (!empty($data['ip']) && $data['ip'] === 'Yes') ? 1 : 0;
            $isFourPs = (!empty($data['fourps']) && $data['fourps'] === 'Yes') ? 1 : 0;
            $isLearnerWithDisability = !empty($data['disabilities']) ? 1 : 0;
            $isReturning = !empty($data['returning']) && $data['returning'] === '1' ? 1 : 0;
            $age = isset($data['Birth_Date']) && trim($data['Birth_Date']) !== '' ? (int) floor((time() - strtotime($data['Birth_Date'])) / 31557600) : null;

            $pdo->prepare("UPDATE enrollments SET school_year = ?, grade_level = ?, with_lrn = ?, psa_bcn = ?, age = ?, mother_tongue = ?, is_indigenous = ?, indigenous_group = ?, is_four_ps_beneficiary = ?, four_ps_household_id = ?, is_learner_with_disability = ?, is_returning_learner = ? WHERE enrollment_id = ?")
                ->execute([
                    $schoolYear,
                    $data['Grade_Level'] ?? $latest['grade_level'],
                    $withLrn,
                    $data['psa_bcn'] ?? $latest['psa_bcn'],
                    $age,
                    $data['Mother_Tongue'] ?? $latest['mother_tongue'],
                    $isIndigenous,
                    $data['IP_Specify'] ?? $latest['indigenous_group'],
                    $isFourPs,
                    $data['FourPs_Specify'] ?? $latest['four_ps_household_id'],
                    $isLearnerWithDisability,
                    $isReturning,
                    $latest['enrollment_id']
                ]);

            upsertEnrollmentAddress($pdo, $latest['enrollment_id'], 'current', [
                'house_no' => $data['Current_House_No'] ?? '',
                'street_name' => $data['Current_Street_Name'] ?? '',
                'barangay' => $data['Current_Barangay'] ?? '',
                'municipality_city' => $data['Current_Municipality_City'] ?? '',
                'province' => $data['Current_Province'] ?? '',
                'country' => $data['Current_Country'] ?? '',
                'zip_code' => $data['Current_Zip_Code'] ?? ''
            ]);

            upsertEnrollmentAddress($pdo, $latest['enrollment_id'], 'permanent', [
                'house_no' => $data['Permanent_House_No'] ?? '',
                'street_name' => $data['Permanent_Street_Name'] ?? '',
                'barangay' => $data['Permanent_Barangay'] ?? '',
                'municipality_city' => $data['Permanent_Municipality_City'] ?? '',
                'province' => $data['Permanent_Province'] ?? '',
                'country' => $data['Permanent_Country'] ?? '',
                'zip_code' => $data['Permanent_Zip_Code'] ?? ''
            ]);

            updateOrInsertParent($pdo, $latest['enrollment_id'], 'father', [
                'last_name' => $data['father_last_name'] ?? '',
                'first_name' => $data['father_first_name'] ?? '',
                'middle_name' => $data['father_middle_name'] ?? '',
                'contact_number' => $data['father_contact_number'] ?? ''
            ]);

            updateOrInsertParent($pdo, $latest['enrollment_id'], 'mother', [
                'last_name' => $data['mother_last_name'] ?? '',
                'first_name' => $data['mother_first_name'] ?? '',
                'middle_name' => $data['mother_middle_name'] ?? '',
                'contact_number' => $data['mother_contact_number'] ?? ''
            ]);

            updateOrInsertParent($pdo, $latest['enrollment_id'], 'guardian', [
                'last_name' => $data['guardian_last_name'] ?? '',
                'first_name' => $data['guardian_first_name'] ?? '',
                'middle_name' => $data['guardian_middle_name'] ?? '',
                'contact_number' => $data['guardian_contact_number'] ?? ''
            ]);

            updateReturningLearner($pdo, $latest['enrollment_id'], $isReturning, [
                'last_grade_level_completed' => $data['Returning_Grade_Level'] ?? '',
                'last_school_attended' => $data['Last_School_Attended'] ?? '',
                'last_school_year_completed' => $data['Last_School_Year_Completed'] ?? '',
                'school_id' => $data['school_ID'] ?? ''
            ]);

            updateDisabilities($pdo, $latest['enrollment_id'], $data['disabilities'] ?? []);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = intval($data['student_id'] ?? 0);

        if ($student_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
            exit;
        }

        $pdo->beginTransaction();
        $deleteClassStudents = $pdo->prepare('DELETE FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteClassStudents->execute([$student_id]);
        $deleteEnrollments = $pdo->prepare('DELETE FROM enrollments WHERE student_id = ?');
        $deleteEnrollments->execute([$student_id]);
        $deleteStudent = $pdo->prepare('DELETE FROM students WHERE student_id = ?');
        $deleteStudent->execute([$student_id]);
        $pdo->commit();

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}