<?php
// ============================================================
// endpoints/sections/get.php
// Fetches sections for admin/staff consumption.
// Supports optional filtering by school year, grade level, and active status.
//
// GET ?id=<section_id>
// GET ?school_year=<year>&grade_level=<level>&is_active=<0|1>
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$schoolYear = trim($_GET['school_year'] ?? '');
$gradeLevel = trim($_GET['grade_level'] ?? '');
$isActive = isset($_GET['is_active']) ? trim($_GET['is_active']) : '';

if ($id !== null && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM sections WHERE section_id = ? LIMIT 1');
    $stmt->execute([$id]);
    $section = $stmt->fetch();
    if (!$section) {
        sendJson(['success' => false, 'error' => 'Section not found'], 404);
    }
    sendJson(['success' => true, 'data' => $section]);
}

$sql = 'SELECT * FROM sections WHERE 1=1';
$params = [];
if ($schoolYear !== '') {
    $sql .= ' AND school_year = ?';
    $params[] = $schoolYear;
}
if ($gradeLevel !== '') {
    $sql .= ' AND grade_level = ?';
    $params[] = $gradeLevel;
}
if ($isActive !== '') {
    $sql .= ' AND is_active = ?';
    $params[] = intval($isActive);
}
$sql .= ' ORDER BY school_year DESC, grade_level, name';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sections = $stmt->fetchAll();

sendJson(['success' => true, 'data' => $sections]);
