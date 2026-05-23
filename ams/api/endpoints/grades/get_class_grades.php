<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';

try {
    if (!is_logged_in()) throw new Exception('Unauthorized');

    $class_subject_id = isset($_GET['class_subject_id']) ? (int)$_GET['class_subject_id'] : null;
    if (!$class_subject_id) throw new Exception('Missing class_subject_id');

    // Return rows: one per student per grading_period (if any)
    $stmt = $pdo->prepare("SELECT st.student_section_id AS class_student_id, ssr.school_record_id, ssr.student_id, s.first_name, s.last_name,
        g.grade_id, g.grading_period, g.grade, ? AS class_subject_id
        FROM student_sections st
        JOIN student_school_records ssr ON st.school_record_id = ssr.school_record_id
        JOIN students s ON ssr.student_id = s.student_id
        LEFT JOIN grades g ON g.class_student_id = st.student_section_id AND g.class_subject_id = ?
        WHERE st.section_id = (SELECT section_id FROM section_subjects WHERE section_subject_id = ? LIMIT 1)
        ORDER BY s.last_name ASC, s.first_name ASC");

    $stmt->execute([$class_subject_id, $class_subject_id, $class_subject_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
