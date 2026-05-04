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
        $stmt = $pdo->query('SELECT c.class_id, c.school_year, c.grade_level, c.section, u.username as adviser
                            FROM classes c
                            LEFT JOIN users u ON c.adviser_id = u.user_id
                            ORDER BY c.school_year DESC, c.grade_level, c.section');
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'teacher_classes') {
        $teacher_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT DISTINCT c.class_id, c.school_year, c.grade_level, c.section, s.name as subject
                              FROM classes c
                              JOIN class_subjects cs ON c.class_id = cs.class_id
                              JOIN subjects s ON cs.subject_id = s.subject_id
                              WHERE cs.teacher_id = ?
                              ORDER BY c.grade_level, c.section, s.name');
        $stmt->execute([$teacher_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'students') {
        $class_id = intval($_GET['class_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT cs.class_student_id, s.student_id, s.lrn, s.first_name, s.last_name
                              FROM class_students cs
                              JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                              JOIN students s ON e.student_id = s.student_id
                              WHERE cs.class_id = ?
                              ORDER BY s.last_name, s.first_name');
        $stmt->execute([$class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO classes (school_year, grade_level, section, adviser_id) 
                              VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['school_year'] ?? '',
            $data['grade_level'] ?? '',
            $data['section'] ?? null,
            $data['adviser_id'] ?? null
        ]);
        echo json_encode(['success' => true, 'class_id' => $pdo->lastInsertId()]);
    }
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $class_id = intval($data['class_id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE classes 
                              SET school_year = ?, grade_level = ?, section = ?, adviser_id = ?
                              WHERE class_id = ?');
        $stmt->execute([
            $data['school_year'] ?? '',
            $data['grade_level'] ?? '',
            $data['section'] ?? null,
            $data['adviser_id'] ?? null,
            $class_id
        ]);
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
