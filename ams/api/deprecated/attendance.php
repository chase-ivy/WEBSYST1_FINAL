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
    if ($action === 'class') {
        $class_id = intval($_GET['class_id'] ?? 0);
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $stmt = $pdo->prepare('SELECT cs.class_student_id, s.first_name, s.last_name, a.attendance_id, a.status
                              FROM class_students cs
                              JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                              JOIN students s ON e.student_id = s.student_id
                              LEFT JOIN attendance a ON cs.class_student_id = a.class_student_id AND a.date = ?
                              WHERE cs.class_id = ?
                              ORDER BY s.last_name, s.first_name');
        $stmt->execute([$date, $class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'record') {
        $data = json_decode(file_get_contents('php://input'), true);
        $class_student_id = intval($data['class_student_id'] ?? 0);
        $date = $data['date'] ?? date('Y-m-d');
        $status = $data['status'] ?? 'present';

        // Validate status
        if (!in_array($status, ['present', 'absent', 'late', 'excused'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }

        // Check if attendance exists
        $check = $pdo->prepare('SELECT attendance_id FROM attendance WHERE class_student_id = ? AND date = ?');
        $check->execute([$class_student_id, $date]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE attendance SET status = ? WHERE attendance_id = ?');
            $stmt->execute([$status, $existing['attendance_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO attendance (class_student_id, date, status) VALUES (?, ?, ?)');
            $stmt->execute([$class_student_id, $date, $status]);
        }
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'summary') {
        $enrollment_id = intval($_GET['enrollment_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT 
                              SUM(CASE WHEN a.status = \'present\' THEN 1 ELSE 0 END) AS present,
                              SUM(CASE WHEN a.status = \'absent\' THEN 1 ELSE 0 END) AS absent,
                              SUM(CASE WHEN a.status = \'late\' THEN 1 ELSE 0 END) AS late_count,
                              SUM(CASE WHEN a.status = \'excused\' THEN 1 ELSE 0 END) AS excused
                              FROM attendance a
                              JOIN class_students cs ON a.class_student_id = cs.class_student_id
                              WHERE cs.enrollment_id = ?');
        $stmt->execute([$enrollment_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
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
