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

function fetchParents($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT p.*
        FROM parents p
        JOIN student_parents sp ON p.parent_id = sp.parent_id
        WHERE sp.student_id = ?
    ");
    $stmt->execute([$student_id]);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['parent_type']] = $row;
    }
    return $result;
}

function fetchDisabilities($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT disability_type_id FROM student_disabilities WHERE student_id = ?
    ");
    $stmt->execute([$student_id]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'disability_type_id');
}

$action = $_GET['action'] ?? '';

try {

       //GET FULL STUDENT DATA

    if ($action === 'get') {

        $student_id = intval($_GET['student_id'] ?? 0);

        $student   = fetchOne($pdo, 'students', $student_id);
        $current   = fetchOne($pdo, 'current_address', $student_id);
        $permanent = fetchOne($pdo, 'permanent_address', $student_id);
        $parents   = fetchParents($pdo, $student_id);
        $returning = fetchOne($pdo, 'returning_learner_information', $student_id);
        $disability_ids = fetchDisabilities($pdo, $student_id);

        echo json_encode([
            'success' => true,
            'data' => [
                'student' => $student,
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
            SET lrn=?, first_name=?, last_name=?, middle_name=?, birth_date=?, sex=?, place_of_birth=?
            WHERE student_id=?
        ")->execute([
            $data['lrn'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['birth_date'],
            $data['sex'],
            $data['place_of_birth'],
            $student_id
        ]);

        // 2. current address
        $pdo->prepare("
            UPDATE current_address SET
            house_no=?, street_name=?, barangay=?, municipality_city=?, province=?, country=?, zip_code=?
            WHERE student_id=?
        ")->execute([
            $data['current']['house_no'] ?? '',
            $data['current']['street_name'] ?? '',
            $data['current']['barangay'] ?? '',
            $data['current']['municipality_city'] ?? '',
            $data['current']['province'] ?? '',
            $data['current']['country'] ?? '',
            $data['current']['zip_code'] ?? '',
            $student_id
        ]);

        // 3. permanent address
        $pdo->prepare("
            UPDATE permanent_address SET
            house_no=?, street_name=?, barangay=?, municipality_city=?, province=?, country=?, zip_code=?
            WHERE student_id=?
        ")->execute([
            $data['permanent']['house_no'] ?? '',
            $data['permanent']['street_name'] ?? '',
            $data['permanent']['barangay'] ?? '',
            $data['permanent']['municipality_city'] ?? '',
            $data['permanent']['province'] ?? '',
            $data['permanent']['country'] ?? '',
            $data['permanent']['zip_code'] ?? '',
            $student_id
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}