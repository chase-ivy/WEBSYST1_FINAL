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
        $class_subject_id = intval($_GET['class_subject_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT activity_id, title, description, max_score, due_date
                              FROM activities
                              WHERE class_subject_id = ?
                              ORDER BY due_date DESC');
        $stmt->execute([$class_subject_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO activities (class_subject_id, title, description, max_score, due_date) 
                              VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['class_subject_id'] ?? 0,
            $data['title'] ?? '',
            $data['description'] ?? null,
            $data['max_score'] ?? 0,
            $data['due_date'] ?? null
        ]);
        echo json_encode(['success' => true, 'activity_id' => $pdo->lastInsertId()]);
    }
    elseif ($action === 'scores') {
        $activity_id = intval($_GET['activity_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT s.first_name, s.last_name, cs.class_student_id, 
                                     ascore.activity_score_id, ascore.score, a.max_score
                              FROM activities a
                              JOIN class_subjects csub ON a.class_subject_id = csub.class_subject_id
                              JOIN class_students cs ON csub.class_id = cs.class_id
                              JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                              JOIN students s ON e.student_id = s.student_id
                              LEFT JOIN activity_scores ascore ON a.activity_id = ascore.activity_id AND cs.class_student_id = ascore.class_student_id
                              WHERE a.activity_id = ?
                              ORDER BY s.last_name, s.first_name');
        $stmt->execute([$activity_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'save_score') {
        $data = json_decode(file_get_contents('php://input'), true);
        $activity_id = intval($data['activity_id'] ?? 0);
        $class_student_id = intval($data['class_student_id'] ?? 0);
        $score = floatval($data['score'] ?? 0);

        // Check if score exists
        $check = $pdo->prepare('SELECT activity_score_id FROM activity_scores WHERE activity_id = ? AND class_student_id = ?');
        $check->execute([$activity_id, $class_student_id]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE activity_scores SET score = ? WHERE activity_score_id = ?');
            $stmt->execute([$score, $existing['activity_score_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO activity_scores (activity_id, class_student_id, score) VALUES (?, ?, ?)');
            $stmt->execute([$activity_id, $class_student_id, $score]);
        }
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
