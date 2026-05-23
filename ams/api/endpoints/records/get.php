<?php
// ============================================================
// endpoints/records/get.php
// Fetches permanent student records (school + medical).
// These records are written by enrollment/verify.php and are
// the canonical source of truth for the teacher dashboard.
//
// GET ?school_record_id=<id>
//     Single school record + its linked medical record.
//
// GET ?student_id=<id>
//     All school records for a student (across school years),
//     each with its linked medical record.
//
// GET ?section_id=<id>[&school_year=<year>]
//     All school records assigned to a section,
//     each with its linked medical record.
//     Use this to populate the teacher class list.
//
// GET ?school_year=<year>[&grade_level=<level>][&academic_status=<status>]
//     Filtered list of school records — useful for admin views.
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('GET');

$schoolRecordId = isset($_GET['school_record_id']) ? intval($_GET['school_record_id']) : null;
$studentId      = isset($_GET['student_id'])       ? intval($_GET['student_id'])       : null;
$sectionId      = isset($_GET['section_id'])       ? intval($_GET['section_id'])       : null;
$schoolYear     = trim($_GET['school_year']      ?? '');
$gradeLevel     = trim($_GET['grade_level']      ?? '');
$academicStatus = trim($_GET['academic_status']  ?? '');

// ── Helper: attach medical record to a school record row ──────

function attachMedical(PDO $pdo, array $record): array {
    $stmt = $pdo->prepare('
        SELECT *
        FROM student_medical_records
        WHERE school_record_id = ?
        LIMIT 1
    ');
    $stmt->execute([$record['school_record_id']]);
    $med = $stmt->fetch();

    // Decode stored JSON columns so the client gets arrays, not strings
    if ($med) {
        foreach (['allergies', 'conditions', 'surgeries', 'treatments', 'family_medical_history'] as $col) {
            if (!empty($med[$col])) {
                $decoded = json_decode($med[$col], true);
                $med[$col] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $med[$col];
            }
        }
    }

    // Decode disabilities stored on the school record itself
    if (!empty($record['disabilities'])) {
        $decoded = json_decode($record['disabilities'], true);
        $record['disabilities'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $record['disabilities'];
    }

    $record['medical_record'] = $med ?: null;
    return $record;
}

// ── Mode 1: single school record ─────────────────────────────

if ($schoolRecordId !== null) {
    $stmt = $pdo->prepare('
        SELECT ssr.*,
               sec.name  AS section_name,
               sec.grade_level AS section_grade_level
        FROM student_school_records ssr
        LEFT JOIN student_sections ss  ON ssr.school_record_id = ss.school_record_id
        LEFT JOIN sections sec         ON ss.section_id = sec.section_id
        WHERE ssr.school_record_id = ?
        LIMIT 1
    ');
    $stmt->execute([$schoolRecordId]);
    $record = $stmt->fetch();

    if (!$record) {
        sendJson(['success' => false, 'error' => 'Record not found'], 404);
    }

    sendJson(['success' => true, 'data' => attachMedical($pdo, $record)]);
}

// ── Mode 2: all records for a student ────────────────────────

if ($studentId !== null) {
    $stmt = $pdo->prepare('
        SELECT ssr.*,
               sec.name       AS section_name,
               sec.grade_level AS section_grade_level
        FROM student_school_records ssr
        LEFT JOIN student_sections ss  ON ssr.school_record_id = ss.school_record_id
        LEFT JOIN sections sec         ON ss.section_id = sec.section_id
        WHERE ssr.student_id = ?
        ORDER BY ssr.school_year DESC
    ');
    $stmt->execute([$studentId]);
    $rows = $stmt->fetchAll();

    $data = array_map(fn($r) => attachMedical($pdo, $r), $rows);
    sendJson(['success' => true, 'data' => $data]);
}

// ── Mode 3: all records in a section ─────────────────────────

if ($sectionId !== null) {
    $sql    = '
        SELECT ssr.*,
               sec.name       AS section_name,
               sec.grade_level AS section_grade_level,
               ss.assigned_at
        FROM student_sections ss
        JOIN student_school_records ssr ON ss.school_record_id = ssr.school_record_id
        JOIN sections sec               ON ss.section_id = sec.section_id
        WHERE ss.section_id = ?
    ';
    $params = [$sectionId];

    if ($schoolYear !== '') {
        $sql .= ' AND ssr.school_year = ?';
        $params[] = $schoolYear;
    }

    $sql .= ' ORDER BY ssr.last_name ASC, ssr.first_name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $data = array_map(fn($r) => attachMedical($pdo, $r), $rows);
    sendJson(['success' => true, 'data' => $data]);
}

// ── Mode 4: filtered list (admin / report view) ───────────────

$sql    = '
    SELECT ssr.*,
           sec.name       AS section_name,
           sec.grade_level AS section_grade_level
    FROM student_school_records ssr
    LEFT JOIN student_sections ss  ON ssr.school_record_id = ss.school_record_id
    LEFT JOIN sections sec         ON ss.section_id = sec.section_id
    WHERE 1=1
';
$params = [];

if ($schoolYear !== '')     { $sql .= ' AND ssr.school_year = ?';     $params[] = $schoolYear;     }
if ($gradeLevel !== '')     { $sql .= ' AND ssr.grade_level = ?';     $params[] = $gradeLevel;     }
if ($academicStatus !== '') { $sql .= ' AND ssr.academic_status = ?'; $params[] = $academicStatus; }

$sql .= ' ORDER BY ssr.last_name ASC, ssr.first_name ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// In list mode skip attaching medical records — keeps the payload lean.
// If the caller needs medical data, they should fetch by school_record_id.
sendJson(['success' => true, 'data' => $rows]);