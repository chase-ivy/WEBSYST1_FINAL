<?php
// ============================================================
// endpoints/classes/get_class_students.php
// Returns all students assigned to a section.
// GET ?section_id=<id>
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
if ($section_id <= 0) {
    sendJson(['success' => false, 'error' => 'section_id is required'], 400);
}

$stmt = $pdo->prepare("
    SELECT
        st.student_section_id   AS class_student_id,
        ssr.school_record_id,
        ssr.student_id,
        s.first_name,
        s.last_name,
        s.middle_name,
        ssr.grade_level,
        ssr.school_year,
        ssr.lrn,
        ssr.academic_status
    FROM student_sections st
    JOIN student_school_records ssr ON st.school_record_id = ssr.school_record_id
    JOIN students s ON ssr.student_id = s.student_id
    WHERE st.section_id = ?
    ORDER BY s.last_name ASC, s.first_name ASC
");

$stmt->execute([$section_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendJson(['success' => true, 'data' => $rows]);
