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
    // Base query to fetch masterlist
    $query = '
        SELECT 
            s.learner_id AS lrn,
            s.learner_first_name AS first_name,
            s.learner_middle_name AS middle_name,
            s.learner_last_name AS last_name,
            s.learner_sex AS sex,
            s.learner_birth_date AS birth_date,
            ig.name AS indigenous_people,
            mt.name AS mother_tongue,
            s.student_id,
            s.user_id
        FROM students s
        LEFT JOIN indigenous_groups ig ON s.indigenous_group_id = ig.indigenous_group_id
        LEFT JOIN mother_tongues mt ON s.mother_tongue_id = mt.mother_tongue_id
        WHERE 1=1
    ';

    $params = [];

    // Filter by school year if provided
    if ($schoolYear !== '') {
        $query .= ' AND s.student_id IN (
            SELECT student_id FROM enrollments WHERE school_year = ?
        )';
        $params[] = $schoolYear;
    }

    // Filter by section if provided
    if ($sectionId !== null) {
        $query .= ' AND s.student_id IN (
            SELECT student_id FROM student_sections WHERE section_id = ?
        )';
        $params[] = $sectionId;
    }

    $query .= ' ORDER BY s.learner_last_name ASC, s.learner_first_name ASC';

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
