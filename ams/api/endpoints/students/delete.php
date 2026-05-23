<?php
// ============================================================
// endpoints/students/delete.php
// Deletes a student and related enrollment data if present.
// POST body: student_id
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();
$studentId = intval($data['student_id'] ?? 0);
if ($studentId <= 0) {
    sendJson(['success' => false, 'error' => 'student_id is required'], 400);
}

$studentStmt = $pdo->prepare('SELECT student_id, user_id FROM students WHERE student_id = ? LIMIT 1');
$studentStmt->execute([$studentId]);
$student = $studentStmt->fetch();
if (!$student) {
    sendJson(['success' => false, 'error' => 'Student not found'], 404);
}

try {
    $pdo->beginTransaction();

    $enrollmentStmt = $pdo->prepare('SELECT enrollment_id FROM enrollments WHERE student_id = ?');
    $enrollmentStmt->execute([$studentId]);
    $enrollmentIds = array_map(fn($row) => intval($row['enrollment_id']), $enrollmentStmt->fetchAll());

    if (!empty($enrollmentIds)) {
        $ids = implode(',', array_fill(0, count($enrollmentIds), '?'));
        $pdo->prepare("DELETE FROM enrollment_disabilities WHERE enrollment_id IN ($ids)")->execute($enrollmentIds);
        $pdo->prepare("DELETE FROM enrollment_returning_learners WHERE enrollment_id IN ($ids)")->execute($enrollmentIds);

        $medInfoStmt = $pdo->prepare('SELECT medical_information_id FROM enrollment_medical_information WHERE enrollment_id = ?');
        $medicalIds = [];
        foreach ($enrollmentIds as $enrollmentId) {
            $medInfoStmt->execute([$enrollmentId]);
            foreach ($medInfoStmt->fetchAll() as $row) {
                $medicalIds[] = intval($row['medical_information_id']);
            }
        }

        if (!empty($medicalIds)) {
            $medIds = implode(',', array_fill(0, count($medicalIds), '?'));
            $pdo->prepare("DELETE FROM enrollment_medical_allergies WHERE medical_information_id IN ($medIds)")->execute($medicalIds);
            $pdo->prepare("DELETE FROM enrollment_medical_conditions WHERE medical_information_id IN ($medIds)")->execute($medicalIds);
            $pdo->prepare("DELETE FROM enrollment_medical_surgeries WHERE medical_information_id IN ($medIds)")->execute($medicalIds);
            $pdo->prepare("DELETE FROM enrollment_medical_treatments WHERE medical_information_id IN ($medIds)")->execute($medicalIds);
            $pdo->prepare("DELETE FROM enrollment_family_medical_history WHERE medical_information_id IN ($medIds)")->execute($medicalIds);
            $pdo->prepare("DELETE FROM enrollment_medical_information WHERE medical_information_id IN ($medIds)")->execute($medicalIds);
        }

        $pdo->prepare("DELETE FROM student_school_records WHERE enrollment_id IN ($ids)")->execute($enrollmentIds);
        $pdo->prepare("DELETE FROM enrollments WHERE enrollment_id IN ($ids)")->execute($enrollmentIds);
    }

    $pdo->prepare('DELETE FROM student_parent_guardians WHERE student_id = ?')->execute([$studentId]);
    $pdo->prepare('DELETE FROM student_addresses WHERE student_id = ?')->execute([$studentId]);
    $pdo->prepare('DELETE FROM students WHERE student_id = ?')->execute([$studentId]);
    $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$student['user_id']]);

    $pdo->commit();
    sendJson(['success' => true, 'message' => 'Student deleted successfully']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
