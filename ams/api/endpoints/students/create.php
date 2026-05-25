<?php
// ============================================================
// endpoints/students/create.php
// Admin/staff shortcut to create a student directly —
// bypassing the public enrollment form flow.
//
// Use this when a student is known to the school already and
// staff need to manually add them without going through the
// full enrollment submission. The account starts active (1)
// regardless of role.
//
// This differs from students/register.php in two ways:
//   1. Requires an authenticated admin or staff session.
//   2. Optionally accepts addresses and parent/guardian data
//      in the same request so they can be created atomically.
//
// POST body:
//   username, password
//   email (optional)
//   lrn (optional), psa_bcn (optional)
//   last_name, first_name, middle_name (optional),
//   extension_name (optional), birth_date, sex,
//   place_of_birth (optional)
//
//   addresses[] (optional array):
//     type (e.g. "Current", "Permanent"), street, barangay,
//     city_municipality, province, zip_code, ownership_type
//
//   guardians[] (optional array):
//     parent_guardian_type_id, last_name, first_name,
//     middle_name (optional), contact_number (optional),
//     occupation (optional), relationship_status (optional),
//     facebook_messenger (optional),
//     is_emergency_contact (0|1), contact_priority (int),
//     is_contact_visible (0|1, default 1)
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();

$username  = trim($data['username']  ?? '');
$password  = trim($data['password']  ?? '');
$email     = trim($data['email']     ?? '') ?: null;
$lrn       = trim($data['lrn']       ?? '');

if ($username === '') sendJson(['success' => false, 'error' => 'username is required'], 400);

// Prefer LRN as password if available, otherwise require explicit password
if ($password === '') {
    if ($lrn !== '') {
        $password = $lrn;
    } else {
        sendJson(['success' => false, 'error' => 'password or lrn is required'], 400);
    }
}

// Auto-generate email if not provided
if ($email === null || $email === '') {
    $email = $username . '@ges.edu';
}

$lastName  = trim($data['last_name']  ?? '');
$firstName = trim($data['first_name'] ?? '');
$birthDate = trim($data['birth_date'] ?? '');
$sex       = trim($data['sex']        ?? '');

if ($lastName  === '') sendJson(['success' => false, 'error' => 'last_name is required'], 400);
if ($firstName === '') sendJson(['success' => false, 'error' => 'first_name is required'], 400);
if ($birthDate === '') sendJson(['success' => false, 'error' => 'birth_date is required'], 400);
if (!in_array($sex, ['Male', 'Female'], true)) sendJson(['success' => false, 'error' => 'sex must be Male or Female'], 400);

// Check username uniqueness
$check = $pdo->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
$check->execute([$username]);
if ($check->fetch()) {
    sendJson(['success' => false, 'error' => 'Username already taken'], 409);
}

$addresses = is_array($data['addresses'] ?? null) ? $data['addresses'] : [];
$guardians = is_array($data['guardians'] ?? null) ? $data['guardians'] : [];

try {
    $pdo->beginTransaction();

    // 1. Create user account (always active when created by staff/admin)
    $pdo->prepare('
        INSERT INTO users (username, email, password_hash, role, is_active)
        VALUES (?, ?, ?, ?, 1)
    ')->execute([
        $username,
        $email,
        password_hash($password, PASSWORD_BCRYPT),
        'student',
    ]);
    $userId = intval($pdo->lastInsertId());

    // 2. Create student profile
    $pdo->prepare('
        INSERT INTO students
            (user_id, lrn, psa_bcn, last_name, first_name, middle_name,
             extension_name, birth_date, sex, place_of_birth)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $userId,
        trim($data['lrn']            ?? '') ?: null,
        trim($data['psa_bcn']        ?? '') ?: null,
        $lastName,
        $firstName,
        trim($data['middle_name']    ?? '') ?: null,
        trim($data['extension_name'] ?? '') ?: null,
        $birthDate,
        $sex,
        trim($data['place_of_birth'] ?? '') ?: null,
    ]);
    $studentId = intval($pdo->lastInsertId());

    // 3. Addresses (optional)
    $addressStmt = $pdo->prepare('
        INSERT INTO student_addresses
            (student_id, address_type, street_name, barangay, municipality_city,
             province, zip_code, ownership_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    foreach ($addresses as $addr) {
        $addressStmt->execute([
            $studentId,
            trim($addr['type']              ?? '') ?: null,
            trim($addr['street']            ?? '') ?: null,
            trim($addr['barangay']          ?? '') ?: null,
            trim($addr['city_municipality'] ?? '') ?: null,
            trim($addr['province']          ?? '') ?: null,
            trim($addr['zip_code']          ?? '') ?: null,
            trim($addr['ownership_type']    ?? '') ?: null,
        ]);
    }

    // 4. Parent/guardians (optional)
    $guardianStmt = $pdo->prepare('
        INSERT INTO student_parent_guardians
            (student_id, parent_guardian_type_id, last_name, first_name,
             middle_name, contact_number, occupation, relationship_status,
             facebook_messenger, is_emergency_contact, contact_priority,
             is_contact_visible)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    foreach ($guardians as $g) {
        $typeId = intval($g['parent_guardian_type_id'] ?? 0);
        $gLast  = trim($g['last_name']  ?? '');
        $gFirst = trim($g['first_name'] ?? '');

        if ($typeId <= 0 || $gLast === '' || $gFirst === '') {
            // Skip malformed guardian rows rather than aborting the whole request
            continue;
        }

        $guardianStmt->execute([
            $studentId,
            $typeId,
            $gLast,
            $gFirst,
            trim($g['middle_name']         ?? '') ?: null,
            trim($g['contact_number']      ?? '') ?: null,
            trim($g['occupation']          ?? '') ?: null,
            trim($g['relationship_status'] ?? '') ?: null,
            trim($g['facebook_messenger']  ?? '') ?: null,
            intval($g['is_emergency_contact'] ?? 0),
            intval($g['contact_priority']     ?? 0),
            intval($g['is_contact_visible']   ?? 1),
        ]);
    }

    $pdo->commit();

    sendJson([
        'success'    => true,
        'user_id'    => $userId,
        'student_id' => $studentId,
        'is_active'  => 1,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}