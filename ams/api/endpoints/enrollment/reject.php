<?php
// ============================================================
// endpoints/enrollment/reject.php
// Rejects a pending enrollment.
// Sets enrollment_status = 'rejected', records who rejected
// it, when, and why. No school record is created.
//
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();

$enrollmentId    = intval($data['enrollment_id'] ?? 0);
$rejectionReason = trim((string)($data['rejection_reason'] ?? '')) ?: null;

if ($enrollmentId <= 0) {
    sendJson(['success' => false, 'error' => 'enrollment_id is required'], 400);
}

$stmt = $pdo->prepare('SELECT enrollment_status FROM enrollments WHERE enrollment_id = ? LIMIT 1');
$stmt->execute([$enrollmentId]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    sendJson(['success' => false, 'error' => 'Enrollment not found'], 404);
}

if ($enrollment['enrollment_status'] !== 'pending') {
    sendJson(['success' => false, 'error' => 'Only pending enrollments can be rejected'], 400);
}

$rejectedBy = intval($_SESSION['user_id']);

try {
    $pdo->prepare('
        UPDATE enrollments
        SET enrollment_status = ?, rejected_by = ?, rejected_at = NOW(), rejection_reason = ?
        WHERE enrollment_id = ?
    ')->execute(['rejected', $rejectedBy, $rejectionReason, $enrollmentId]);

    sendJson([
        'success'       => true,
        'enrollment_id' => $enrollmentId,
        'message'       => 'Enrollment rejected',
    ]);

} catch (Exception $e) {
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}