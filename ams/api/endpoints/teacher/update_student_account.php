<?php
// ============================================================
// endpoints/teacher/update_student_account.php
// Updates a student's user account credentials.
// POST body:
//   student_id, username, email, password (optional)
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();

$studentId = intval($data['student_id'] ?? 0);
$username  = trim($data['username'] ?? '');
$email     = trim($data['email'] ?? '');
$password  = trim($data['password'] ?? '');

if ($studentId <= 0) {
    sendJson(['success' => false, 'error' => 'student_id is required'], 400);
}
if ($username === '') {
    sendJson(['success' => false, 'error' => 'username is required'], 400);
}

$stmt = $pdo->prepare('SELECT s.student_id, u.user_id FROM students s JOIN users u ON s.user_id = u.user_id WHERE s.student_id = ? LIMIT 1');
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    sendJson(['success' => false, 'error' => 'Student not found'], 404);
}

$userIdStmt = $pdo->prepare('SELECT user_id FROM users WHERE username = ? AND user_id != ? LIMIT 1');
$userIdStmt->execute([$username, $student['user_id']]);
if ($userIdStmt->fetch()) {
    sendJson(['success' => false, 'error' => 'Username already taken'], 409);
}

$email = $email === '' ? null : $email;

$updateFields = ['username' => $username, 'email' => $email];
$params = [$username, $email, $student['user_id']];
$sql = 'UPDATE users SET username = ?, email = ?';

if ($password !== '') {
    $sql .= ', password_hash = ?';
    $params = [$username, $email, password_hash($password, PASSWORD_BCRYPT), $student['user_id']];
}
$sql .= ' WHERE user_id = ?';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    sendJson(['success' => true, 'message' => 'Student account updated successfully']);
} catch (Exception $e) {
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
