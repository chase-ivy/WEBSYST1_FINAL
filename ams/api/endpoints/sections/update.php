<?php
// ============================================================
// endpoints/sections/update.php
// Updates an existing section's fields (name, grade_level,
// school_year, is_active, adviser_id).
//
// POST body:
//   section_id  — required
//   name        — optional
//   grade_level — optional
//   school_year — optional
//   is_active   — optional (0|1)
//   adviser_id  — optional (user_id of staff; null to unset)
//
// Accessible by: admin only
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['admin']);
requireMethod('POST');

$data = getJsonInput();

$sectionId = intval($data['section_id'] ?? 0);
if ($sectionId <= 0) {
    sendJson(['success' => false, 'error' => 'section_id is required'], 400);
}

// Verify section exists
$check = $pdo->prepare('SELECT section_id FROM sections WHERE section_id = ? LIMIT 1');
$check->execute([$sectionId]);
if (!$check->fetch()) {
    sendJson(['success' => false, 'error' => 'Section not found'], 404);
}

$allowedFields = ['name', 'grade_level', 'school_year', 'is_active', 'adviser_id'];
$set = [];
$params = [];

foreach ($allowedFields as $field) {
    if (!array_key_exists($field, $data)) continue;

    $value = $data[$field];

    if ($field === 'adviser_id') {
        $value = ($value === null || $value === '' || $value === 0) ? null : intval($value);
        if ($value !== null) {
            $staffCheck = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'staff' LIMIT 1");
            $staffCheck->execute([$value]);
            if (!$staffCheck->fetch()) {
                sendJson(['success' => false, 'error' => 'adviser_id must reference a valid staff user'], 400);
            }
        }
    } elseif ($field === 'is_active') {
        $value = intval($value);
    } else {
        $value = trim((string)$value);
        if ($value === '') {
            sendJson(['success' => false, 'error' => "$field cannot be empty"], 400);
        }
    }

    $set[] = "$field = ?";
    $params[] = $value;
}

if (empty($set)) {
    sendJson(['success' => false, 'error' => 'No updatable fields provided'], 400);
}

$params[] = $sectionId;

try {
    $stmt = $pdo->prepare('UPDATE sections SET ' . implode(', ', $set) . ' WHERE section_id = ?');
    $stmt->execute($params);

    // Return updated record
    $row = $pdo->prepare('SELECT s.*, u.username AS adviser_name FROM sections s LEFT JOIN users u ON u.user_id = s.adviser_id WHERE s.section_id = ? LIMIT 1');
    $row->execute([$sectionId]);
    $section = $row->fetch();

    sendJson(['success' => true, 'data' => $section]);
} catch (PDOException $e) {
    $msg = $e->getMessage();
    if (str_contains($msg, 'Duplicate entry')) {
        sendJson(['success' => false, 'error' => 'A section with that name already exists for this grade level and school year'], 400);
    }
    sendJson(['success' => false, 'error' => $msg], 500);
}
