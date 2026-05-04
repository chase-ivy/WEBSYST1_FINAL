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
        $stmt = $pdo->prepare('SELECT g.grade_id, cs.class_student_id, s.first_name, s.last_name, 
                                     subj.name as subject, g.grading_period, g.grade
                              FROM grades g
                              JOIN class_students cs ON g.class_student_id = cs.class_student_id
                              JOIN class_subjects csub ON g.class_subject_id = csub.class_subject_id
                              JOIN subjects subj ON csub.subject_id = subj.subject_id
                              JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                              JOIN students s ON e.student_id = s.student_id
                              WHERE cs.class_id = ?
                              ORDER BY s.last_name, s.first_name, g.grading_period');
        $stmt->execute([$class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'student') {
        $enrollment_id = intval($_GET['enrollment_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT g.grade_id, subj.name as subject, g.grading_period, g.grade
                              FROM grades g
                              JOIN class_students cs ON g.class_student_id = cs.class_student_id
                              JOIN class_subjects csub ON g.class_subject_id = csub.class_subject_id
                              JOIN subjects subj ON csub.subject_id = subj.subject_id
                              WHERE cs.enrollment_id = ?
                              ORDER BY subj.name, g.grading_period');
        $stmt->execute([$enrollment_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'save') {
        $data = json_decode(file_get_contents('php://input'), true);
        $class_student_id = intval($data['class_student_id'] ?? 0);
        $class_subject_id = intval($data['class_subject_id'] ?? 0);
        $grading_period = $data['grading_period'] ?? '';
        $grade = floatval($data['grade'] ?? 0);

        // Check if grade exists
        $check = $pdo->prepare('SELECT grade_id FROM grades WHERE class_student_id = ? AND class_subject_id = ? AND grading_period = ?');
        $check->execute([$class_student_id, $class_subject_id, $grading_period]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE grades SET grade = ? WHERE grade_id = ?');
            $stmt->execute([$grade, $existing['grade_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO grades (class_student_id, class_subject_id, grading_period, grade) 
                                  VALUES (?, ?, ?, ?)');
            $stmt->execute([$class_student_id, $class_subject_id, $grading_period, $grade]);
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
