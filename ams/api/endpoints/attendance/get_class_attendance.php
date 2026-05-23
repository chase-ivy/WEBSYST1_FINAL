<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';

try {
    if (!is_logged_in()) throw new Exception('Unauthorized');

    $section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : null;
    $date = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : null;
    if (!$section_id) throw new Exception('Missing section_id');
    if (!$date) throw new Exception('Missing date');

    $stmt = $pdo->prepare("SELECT st.student_section_id AS class_student_id, s.student_id, s.first_name, s.last_name,
        a.status
        FROM student_sections st
        JOIN student_school_records ssr ON st.school_record_id = ssr.school_record_id
        JOIN students s ON ssr.student_id = s.student_id
        LEFT JOIN attendance a ON a.class_student_id = st.student_section_id AND a.date = ?
        WHERE st.section_id = ?
        ORDER BY s.last_name ASC, s.first_name ASC");

    $stmt->execute([$date, $section_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
