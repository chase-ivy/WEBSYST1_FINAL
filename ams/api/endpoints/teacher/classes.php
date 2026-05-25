<?php
// ============================================================
// endpoints/teacher/classes.php
// Returns all section_subjects assigned to the logged-in teacher.
// Accessible by: staff
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff']);
requireMethod('GET');

$teacher_id = intval($_SESSION['user_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT
        ss.section_subject_id,
        s.section_id,
        sub.subject_id,
        sub.name        AS subject_name,
        s.grade_level,
        s.name          AS section,
        s.school_year,
        COALESCE(
            (SELECT COUNT(*) FROM student_sections st WHERE st.section_id = s.section_id),
            0
        ) AS student_count
    FROM section_subjects ss
    JOIN sections  s   ON ss.section_id  = s.section_id
    JOIN subjects  sub ON ss.subject_id  = sub.subject_id
    WHERE ss.teacher_id = ?
    ORDER BY s.school_year DESC, sub.name ASC, s.name ASC
");

$stmt->execute([$teacher_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array_map(function ($r) {
    return [
        'class_id'     => (int) $r['section_id'],
        'subject_id'   => (int) $r['subject_id'],
        'subject_name' => $r['subject_name'],
        'grade_level'  => $r['grade_level'],
        'section'      => $r['section'],
        'school_year'  => $r['school_year'],
        'student_count'=> (int) $r['student_count'],
    ];
}, $rows);

sendJson(['success' => true, 'data' => $data]);
