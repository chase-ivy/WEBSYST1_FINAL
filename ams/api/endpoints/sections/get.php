<?php
// ============================================================
// endpoints/sections/get.php
// Fetches sections for admin/staff consumption.
// Includes adviser username via LEFT JOIN on users.
//
// GET ?id=<section_id>
// GET ?school_year=<year>&grade_level=<level>&is_active=<0|1>
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$id          = isset($_GET['id']) ? intval($_GET['id']) : null;
$schoolYear  = trim($_GET['school_year'] ?? '');
$gradeLevel  = trim($_GET['grade_level'] ?? '');
$isActive    = isset($_GET['is_active']) ? trim($_GET['is_active']) : '';

$baseSelect = 'SELECT s.*, u.username AS adviser_name FROM sections s LEFT JOIN users u ON u.user_id = s.adviser_id';

if ($id !== null && $id > 0) {
    $stmt = $pdo->prepare($baseSelect . ' WHERE s.section_id = ? LIMIT 1');
    $stmt->execute([$id]);
    $section = $stmt->fetch();
    if (!$section) {
        sendJson(['success' => false, 'error' => 'Section not found'], 404);
    }
    sendJson(['success' => true, 'data' => $section]);
}

$sql    = $baseSelect . ' WHERE 1=1';
$params = [];

if ($schoolYear !== '') {
    $sql     .= ' AND s.school_year = ?';
    $params[] = $schoolYear;
}
if ($gradeLevel !== '') {
    $sql     .= ' AND s.grade_level = ?';
    $params[] = $gradeLevel;
}
if ($isActive !== '') {
    $sql     .= ' AND s.is_active = ?';
    $params[] = intval($isActive);
}
$sql .= ' ORDER BY s.school_year DESC, s.grade_level, s.name';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sections = $stmt->fetchAll();

sendJson(['success' => true, 'data' => $sections]);
