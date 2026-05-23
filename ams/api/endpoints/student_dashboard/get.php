<?php
// ============================================================
// endpoints/student_dashboard/get.php
// Aggregates student dashboard data for the logged-in student.
// Accessible by: student
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['student']);
requireMethod('GET');

$userId = intval($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    sendJson(['success' => false, 'error' => 'Unauthorized'], 401);
}

$stmt = $pdo->prepare('SELECT s.* FROM students s WHERE s.user_id = ? LIMIT 1');
$stmt->execute([$userId]);
$student = $stmt->fetch();
if (!$student) {
    sendJson(['success' => false, 'error' => 'Student profile not found'], 404);
}

// Latest active school record for the student
$recordStmt = $pdo->prepare('SELECT ssr.*, sec.name AS section_name, sec.grade_level AS section_grade_level
    FROM student_school_records ssr
    LEFT JOIN student_sections st ON st.school_record_id = ssr.school_record_id
    LEFT JOIN sections sec ON st.section_id = sec.section_id
    WHERE ssr.student_id = ?
    ORDER BY ssr.school_year DESC, ssr.created_at DESC
    LIMIT 1');
$recordStmt->execute([$student['student_id']]);
$schoolRecord = $recordStmt->fetch();

$studentProfile = $student;
if ($schoolRecord) {
    $studentProfile['grade_level'] = $schoolRecord['grade_level'] ?? $schoolRecord['section_grade_level'] ?? null;
    $studentProfile['section_name'] = $schoolRecord['section_name'] ?? null;
}

$enrollmentStmt = $pdo->prepare('SELECT enrollment_status, rejection_reason, school_year, grade_level FROM enrollments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1');
$enrollmentStmt->execute([$student['student_id']]);
$latestEnrollment = $enrollmentStmt->fetch();
if ($latestEnrollment) {
    $studentProfile['enrollment_status'] = $latestEnrollment['enrollment_status'];
    $studentProfile['enrollment_rejection_reason'] = $latestEnrollment['rejection_reason'];
    $studentProfile['latest_enrollment_school_year'] = $latestEnrollment['school_year'];
    $studentProfile['latest_enrollment_grade_level'] = $latestEnrollment['grade_level'];
}

$studentSectionIds = [];
if ($schoolRecord) {
    $sectionListStmt = $pdo->prepare('SELECT student_section_id FROM student_sections WHERE school_record_id = ?');
    $sectionListStmt->execute([$schoolRecord['school_record_id']]);
    $studentSectionIds = array_map(fn($row) => intval($row['student_section_id']), $sectionListStmt->fetchAll());
}

$grades = [];
$activities = [];
$attendance = ['present' => 0, 'absent' => 0, 'late_count' => 0, 'excused' => 0];
$reportCard = [];

if (!empty($studentSectionIds)) {
    $idsPlaceholders = implode(',', array_fill(0, count($studentSectionIds), '?'));

    $gradeStmt = $pdo->prepare("SELECT g.grade AS final_grade, CASE WHEN g.grade >= 75 THEN 'Passed' ELSE 'Failed' END AS remarks, g.grading_period, sub.name AS subject_name FROM grades g JOIN section_subjects ss ON g.class_subject_id = ss.section_subject_id JOIN subjects sub ON ss.subject_id = sub.subject_id WHERE g.class_student_id IN ($idsPlaceholders) ORDER BY g.grading_period ASC, sub.name ASC");
    $gradeStmt->execute($studentSectionIds);
    $grades = $gradeStmt->fetchAll();

    $reportStmt = $pdo->prepare("SELECT g.grading_period, AVG(CAST(g.grade AS DECIMAL(5,2))) AS general_average, CASE WHEN AVG(CAST(g.grade AS DECIMAL(5,2))) >= 75 THEN 'Passed' ELSE 'Failed' END AS remarks FROM grades g WHERE g.class_student_id IN ($idsPlaceholders) GROUP BY g.grading_period ORDER BY g.grading_period ASC");
    $reportStmt->execute($studentSectionIds);
    $reportCard = array_map(function ($row) {
        return [
            'grading_period' => $row['grading_period'],
            'general_average' => $row['general_average'] !== null ? number_format((float)$row['general_average'], 2) : '0.00',
            'remarks' => $row['remarks'] ?? 'No remarks',
        ];
    }, $reportStmt->fetchAll());

    $activityStmt = $pdo->prepare("SELECT a.title AS activity_name, a.due_date AS activity_date, a.max_score, COALESCE(ascore.score, 0) AS score, sub.name AS subject_name FROM activities a JOIN section_subjects ss ON a.class_subject_id = ss.section_subject_id JOIN subjects sub ON ss.subject_id = sub.subject_id LEFT JOIN activity_scores ascore ON ascore.activity_id = a.activity_id AND ascore.class_student_id IN ($idsPlaceholders) WHERE ss.section_id IN (SELECT section_id FROM student_sections WHERE school_record_id = ?) ORDER BY a.due_date DESC");
    $activityParams = array_merge($studentSectionIds, [$schoolRecord['school_record_id']]);
    $activityStmt->execute($activityParams);
    $activities = $activityStmt->fetchAll();

    $attendanceStmt = $pdo->prepare("SELECT a.status, COUNT(*) AS total FROM attendance a WHERE a.class_student_id IN ($idsPlaceholders) GROUP BY a.status");
    $attendanceStmt->execute($studentSectionIds);
    foreach ($attendanceStmt->fetchAll() as $row) {
        switch (strtolower($row['status'])) {
            case 'present': $attendance['present'] = intval($row['total']); break;
            case 'absent': $attendance['absent'] = intval($row['total']); break;
            case 'late': $attendance['late_count'] = intval($row['total']); break;
            case 'excused': $attendance['excused'] = intval($row['total']); break;
            default: break;
        }
    }
}

sendJson([
    'success' => true,
    'data' => [
        'student' => $studentProfile,
        'grades' => $grades,
        'activities' => $activities,
        'attendance' => $attendance,
        'report_card' => $reportCard,
    ],
]);
