<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action !== 'me') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
    }

    if ($_SESSION['role'] === 'student') {
        $student_id = intval($_SESSION['user_id']);
    } else {
        $student_id = intval($_GET['student_id'] ?? 0);
        if ($student_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'student_id is required for non-student users']);
            exit;
        }
    }

    $stmt = $pdo->prepare('SELECT s.student_id, s.lrn, s.first_name, s.middle_name, s.last_name, s.extension_name, s.sex, s.birth_date, s.place_of_birth,
                                 e.school_year, e.grade_level, e.with_lrn, e.psa_bcn, e.age, e.mother_tongue,
                                 e.is_indigenous, e.indigenous_group, e.is_four_ps_beneficiary, e.four_ps_household_id,
                                 e.is_learner_with_disability, e.is_returning_learner
                          FROM students s
                          LEFT JOIN enrollments e ON e.enrollment_id = (
                              SELECT MAX(enrollment_id) FROM enrollments WHERE student_id = s.student_id
                          )
                          WHERE s.student_id = ?');
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
        exit;
    }

    $gradesStmt = $pdo->prepare('SELECT s.subject_name, g.grading_period, g.final_grade, g.remarks
                                 FROM grades g
                                 JOIN enrollments e ON g.enrollment_id = e.enrollment_id
                                 JOIN classes c ON e.class_id = c.class_id
                                 JOIN subjects s ON c.subject_id = s.subject_id
                                 WHERE e.student_id = ?
                                 ORDER BY s.subject_name, g.grading_period');
    $gradesStmt->execute([$student_id]);
    $grades = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);

    $activitiesStmt = $pdo->prepare('SELECT s.subject_name, a.activity_name, a.activity_date, a.max_score, COALESCE(sas.score, 0) AS score
                                     FROM enrollments e
                                     JOIN classes c ON e.class_id = c.class_id
                                     JOIN subjects s ON c.subject_id = s.subject_id
                                     JOIN activities a ON a.class_id = c.class_id
                                     LEFT JOIN student_activity_scores sas ON sas.activity_id = a.activity_id AND sas.enrollment_id = e.enrollment_id
                                     WHERE e.student_id = ?
                                     ORDER BY a.activity_date DESC');
    $activitiesStmt->execute([$student_id]);
    $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

    $attendanceStmt = $pdo->prepare('SELECT
                                      SUM(CASE WHEN a.status = "Present" THEN 1 ELSE 0 END) AS present,
                                      SUM(CASE WHEN a.status = "Absent" THEN 1 ELSE 0 END) AS absent,
                                      SUM(CASE WHEN a.status = "Late" THEN 1 ELSE 0 END) AS late_count,
                                      SUM(CASE WHEN a.status = "Excused" THEN 1 ELSE 0 END) AS excused
                                      FROM attendance a
                                      JOIN class_students cs ON a.class_student_id = cs.class_student_id
                                      JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                                      WHERE e.student_id = ?');
    $attendanceStmt->execute([$student_id]);
    $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);

    $attendanceRecordsStmt = $pdo->prepare('SELECT subj.subject_name, a.attendance_date, a.status
                                            FROM attendance a
                                            JOIN class_students cs ON a.class_student_id = cs.class_student_id
                                            JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                                            JOIN classes c ON e.class_id = c.class_id
                                            JOIN subjects subj ON c.subject_id = subj.subject_id
                                            WHERE e.student_id = ?
                                            ORDER BY a.attendance_date DESC');
    $attendanceRecordsStmt->execute([$student_id]);
    $attendance_records = $attendanceRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

    $reportStmt = $pdo->prepare('SELECT g.grading_period, ROUND(AVG(g.final_grade), 2) AS general_average,
                                         CASE WHEN AVG(g.final_grade) >= 75 THEN "Passed" ELSE "Failed" END AS remarks
                                  FROM grades g
                                  JOIN enrollments e ON g.enrollment_id = e.enrollment_id
                                  WHERE e.student_id = ?
                                  GROUP BY g.grading_period
                                  ORDER BY g.grading_period');
    $reportStmt->execute([$student_id]);
    $report_card = $reportStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => [
        'student' => $student,
        'grades' => $grades,
        'activities' => $activities,
        'attendance' => $attendance,
        'attendance_records' => $attendance_records,
        'report_card' => $report_card
    ]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
