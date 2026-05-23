<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../login/auth.php';
require_once __DIR__ . '/../config/config.php';

try {
    if (!is_logged_in()) throw new Exception('Unauthorized');

    $activity_id = isset($_GET['activity_id']) ? (int)$_GET['activity_id'] : null;
    if (!$activity_id) throw new Exception('Missing activity_id');

    $stmt = $pdo->prepare("SELECT a.activity_id, a.title, a.max_score, st.student_section_id AS class_student_id, ssr.school_record_id, ssr.student_id, s.first_name, s.last_name, COALESCE(ascore.score, NULL) AS score
        FROM activities a
        JOIN section_subjects ss ON ss.section_subject_id = a.class_subject_id
        JOIN student_sections st ON st.section_id = ss.section_id
        JOIN student_school_records ssr ON st.school_record_id = ssr.school_record_id
        JOIN students s ON ssr.student_id = s.student_id
        LEFT JOIN activity_scores ascore ON ascore.activity_id = a.activity_id AND ascore.class_student_id = st.student_section_id
        WHERE a.activity_id = ?
        ORDER BY s.last_name ASC, s.first_name ASC");

    $stmt->execute([$activity_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

