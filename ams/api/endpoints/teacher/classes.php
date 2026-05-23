<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';

try {
    require_role(['staff']);

    $teacher_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT
        s.section_id,
        sub.name AS subject_name,
        s.grade_level,
        s.name AS section,
        s.school_year,
        COALESCE((SELECT COUNT(*) FROM student_sections st WHERE st.section_id = s.section_id), 0) AS student_count
        FROM section_subjects ss
        JOIN sections s ON ss.section_id = s.section_id
        JOIN subjects sub ON ss.subject_id = sub.subject_id
        WHERE ss.teacher_id = ?
        ORDER BY sub.name ASC, s.name ASC");

    $stmt->execute([$teacher_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map to expected client shape (class_id kept for compatibility)
    $data = array_map(function($r) {
        return [
            'class_id' => (int)$r['section_id'],
            'subject_name' => $r['subject_name'],
            'grade_level' => $r['grade_level'],
            'section' => $r['section'],
            'school_year' => $r['school_year'],
            'student_count' => (int)$r['student_count']
        ];
    }, $rows);

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
