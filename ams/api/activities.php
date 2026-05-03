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
$user_id = $_SESSION['user_id'] ?? 0;

try {
    if ($action === 'list' && $role === 'teacher') {
        $class_id = intval($_GET['class_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT activity_id, activity_name, max_score, activity_date
                              FROM activities
                              WHERE class_id = ?
                              ORDER BY activity_date DESC');
        $stmt->execute([$class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'create' && $role === 'teacher') {
        $class_id = intval($_POST['class_id'] ?? 0);
        $activity_name = $_POST['activity_name'] ?? '';
        $max_score = intval($_POST['max_score'] ?? 0);
        $activity_date = $_POST['activity_date'] ?? date('Y-m-d');

        $stmt = $pdo->prepare('INSERT INTO activities (class_id, activity_name, max_score, activity_date) VALUES (?, ?, ?, ?)');
        $stmt->execute([$class_id, $activity_name, $max_score, $activity_date]);
        echo json_encode(['success' => true, 'message' => 'Activity created', 'activity_id' => $pdo->lastInsertId()]);
    } elseif ($action === 'score_get' && $role === 'teacher') {
        $activity_id = intval($_GET['activity_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT sas.score_id, e.enrollment_id, s.first_name, s.last_name, sas.score, a.max_score
                              FROM enrollments e
                              JOIN students s ON e.student_id = s.student_id
                              JOIN activities a ON a.activity_id = ?
                              LEFT JOIN student_activity_scores sas ON e.enrollment_id = sas.enrollment_id AND sas.activity_id = a.activity_id
                              WHERE e.class_id = a.class_id
                              ORDER BY s.last_name, s.first_name ASC');
        $stmt->execute([$activity_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } elseif ($action === 'score_save' && $role === 'teacher') {
        $activity_id = intval($_POST['activity_id'] ?? 0);
        $enrollment_id = intval($_POST['enrollment_id'] ?? 0);
        $score = intval($_POST['score'] ?? 0);

        $check = $pdo->prepare('SELECT score_id FROM student_activity_scores WHERE activity_id = ? AND enrollment_id = ?');
        $check->execute([$activity_id, $enrollment_id]);
        $existing = $check->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE student_activity_scores SET score = ? WHERE score_id = ?');
            $stmt->execute([$score, $existing['score_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO student_activity_scores (activity_id, enrollment_id, score) VALUES (?, ?, ?)');
            $stmt->execute([$activity_id, $enrollment_id, $score]);
        }
        echo json_encode(['success' => true, 'message' => 'Score saved']);
    } elseif ($action === 'student_activities' && ($role === 'parent' || $role === 'student')) {
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT s.subject_name, a.activity_name, a.activity_date, a.max_score, sas.score
                              FROM enrollments e
                              JOIN classes c ON e.class_id = c.class_id
                              JOIN subjects s ON c.subject_id = s.subject_id
                              JOIN activities a ON a.class_id = c.class_id
                              LEFT JOIN student_activity_scores sas ON e.enrollment_id = sas.enrollment_id AND sas.activity_id = a.activity_id
                              WHERE e.student_id = ?
                              ORDER BY a.activity_date DESC');
        $stmt->execute([$student_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
