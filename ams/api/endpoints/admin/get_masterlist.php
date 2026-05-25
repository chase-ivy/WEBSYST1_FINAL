<?php
// ============================================================
// endpoints/admin/get_masterlist.php
// Fetches student masterlist with specific fields:
// LRN, Name (First, Middle, Last), Sex, Birth Date, 
// Indigenous People (IP), Mother Tongue
//
// GET (no params)
//     Full masterlist of all students with the specified fields.
//
// GET ?school_year=<school_year>
//     Filter masterlist by school year.
//
// GET ?section_id=<section_id>
//     Filter masterlist by section.
//
// Accessible by: admin, staff
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$schoolYear = trim($_GET['school_year'] ?? '');
$sectionId = isset($_GET['section_id']) ? intval($_GET['section_id']) : null;

try {
    // Base query to fetch masterlist from student profile and latest enrollment data.
    $query = '
        SELECT
            s.lrn AS lrn,
            s.last_name AS last_name,
            s.first_name AS first_name,
            s.middle_name AS middle_name,
            s.sex AS sex,
            s.birth_date AS birth_date,
            ig.name AS indigenous_people,
            mt.name AS mother_tongue
        FROM students s
        LEFT JOIN (
            SELECT e1.*
            FROM enrollments e1
            JOIN (
                SELECT student_id, MAX(created_at) AS max_created_at
                FROM enrollments
                GROUP BY student_id
            ) latest ON latest.student_id = e1.student_id AND latest.max_created_at = e1.created_at
        ) e ON e.student_id = s.student_id
        LEFT JOIN indigenous_groups ig ON e.indigenous_group_id = ig.indigenous_group_id
        LEFT JOIN mother_tongues mt ON e.mother_tongue_id = mt.mother_tongue_id
        WHERE 1=1
    ';

    $params = [];

    // Filter by school year if provided.
    if ($schoolYear !== '') {
        $query .= ' AND e.school_year = ?';
        $params[] = $schoolYear;
    }

    // Filter by section if provided.
    if ($sectionId !== null) {
        $query .= ' AND s.student_id IN (
            SELECT ssr.student_id
            FROM student_school_records ssr
            JOIN student_sections st ON st.school_record_id = ssr.school_record_id
            WHERE st.section_id = ?
        )';
        $params[] = $sectionId;
    }

    $query .= ' ORDER BY s.last_name ASC, s.first_name ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $masterlist = $stmt->fetchAll();

    sendJson([
        'success' => true,
        'data' => $masterlist,
        'count' => count($masterlist)
    ]);

} catch (Exception $e) {
    sendJson([
        'success' => false,
        'error' => 'Failed to fetch masterlist: ' . $e->getMessage()
    ], 500);
}
?>
