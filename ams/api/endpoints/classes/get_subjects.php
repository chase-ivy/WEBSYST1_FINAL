<?php
// ============================================================
// endpoints/classes/get_subjects.php
// Returns all subjects assigned to a section.
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
        ss.section_subject_id   AS class_subject_id,
        ss.section_id,
        ss.subject_id,
        sub.name                AS subject_name,
        ss.teacher_id,
        u.username              AS teacher_name
    FROM section_subjects ss
    JOIN subjects sub ON ss.subject_id = sub.subject_id
    LEFT JOIN users u ON u.user_id = ss.teacher_id
    WHERE ss.section_id = ?
    ORDER BY sub.name ASC
");

$stmt->execute([$section_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendJson(['success' => true, 'data' => $rows]);
