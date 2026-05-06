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
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT e.enrollment_id, s.student_id, s.lrn, s.first_name, s.last_name, e.school_year, e.grade_level
                            FROM enrollments e
                            JOIN students s ON e.student_id = s.student_id
                            ORDER BY e.school_year DESC, e.grade_level, s.last_name, s.first_name');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'student') {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT e.enrollment_id, e.school_year, e.grade_level
                              FROM enrollments e
                              WHERE e.student_id = ?
                              ORDER BY e.school_year DESC');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, school_year, grade_level, with_lrn, age, mother_tongue) 
                              VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['student_id'] ?? 0,
            $data['school_year'] ?? '',
            $data['grade_level'] ?? '',
            $data['with_lrn'] ?? 0,
            $data['age'] ?? null,
            $data['mother_tongue'] ?? null
        ]);
        echo json_encode(['success' => true, 'enrollment_id' => $pdo->lastInsertId()]);
    }
    elseif ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $enrollment_id = intval($data['enrollment_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE enrollment_id = ?');
        $stmt->execute([$enrollment_id]);
        echo json_encode(['success' => true]);
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
