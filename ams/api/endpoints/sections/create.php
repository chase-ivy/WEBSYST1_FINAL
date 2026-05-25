<?php
// ============================================================
// endpoints/sections/create.php
// Creates a new section (and optionally assigns subjects to it).
//
// A section is scoped to a school_year + grade_level. The UNIQUE
// constraint on (school_year, grade_level, name) prevents
// duplicate section names within the same cohort.
//
// POST body:
//   school_year  — e.g. "2025-2026"
//   grade_level  — e.g. "Grade 1", "Grade 7"
//   name         — e.g. "Sampaguita"
//
//   subjects[] (optional array):
//     subject_id, teacher_id
//     Both must reference existing rows in subjects and users.
//
// Accessible by: admin only
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';
require_once __DIR__ . '/sections_helper.php';

require_role(['admin']);
requireMethod('POST');

$data = getJsonInput();

$schoolYear = trim($data['school_year'] ?? '');
$gradeLevel = trim($data['grade_level'] ?? '');
$name       = trim($data['name']        ?? '');

if ($schoolYear === '') sendJson(['success' => false, 'error' => 'school_year is required'], 400);
if ($gradeLevel === '') sendJson(['success' => false, 'error' => 'grade_level is required'], 400);
if ($name === '')       sendJson(['success' => false, 'error' => 'name is required'], 400);

$subjects = is_array($data['subjects'] ?? null) ? $data['subjects'] : [];
$adviserId = array_key_exists('adviser_id', $data) ? ($data['adviser_id'] === '' ? null : intval($data['adviser_id'])) : null;

$result = createSection($pdo, $schoolYear, $gradeLevel, $name, 1, $subjects, $adviserId);
if (!$result['success']) {
    sendJson(['success' => false, 'error' => $result['error']], 400);
}

sendJson(array_merge(['success' => true], $result));
