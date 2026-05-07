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
        $sessionId = intval($_SESSION['user_id']);
        $student_id = 0;

        $checkStudent = $pdo->prepare('SELECT student_id FROM students WHERE student_id = ? LIMIT 1');
        $checkStudent->execute([$sessionId]);
        if ($checkStudent->fetch()) {
            $student_id = $sessionId;
        } else {
            $checkStudent = $pdo->prepare('SELECT student_id FROM students WHERE user_id = ? LIMIT 1');
            $checkStudent->execute([$sessionId]);
            $student = $checkStudent->fetch(PDO::FETCH_ASSOC);
            $student_id = $student['student_id'] ?? 0;
        }

        if ($student_id <= 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found for current session']);
            exit;
        }
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

    $gradesStmt = $pdo->prepare('SELECT subj.name AS subject_name, g.grading_period, g.grade AS final_grade,
                                         CASE WHEN g.grade >= 75 THEN "Passed" ELSE "Failed" END AS remarks
                                  FROM grades g
                                  JOIN class_students cs ON g.class_student_id = cs.class_student_id
                                  JOIN class_subjects csub ON g.class_subject_id = csub.class_subject_id
                                  JOIN subjects subj ON csub.subject_id = subj.subject_id
                                  JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                                  WHERE e.student_id = ?
                                  ORDER BY subj.name, g.grading_period');
    $gradesStmt->execute([$student_id]);
    $grades = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);

    $activitiesStmt = $pdo->prepare('SELECT subj.name AS subject_name, a.title AS activity_name, a.due_date AS activity_date,
                                             a.max_score, COALESCE(ascore.score, 0) AS score
                                      FROM enrollments e
                                      JOIN class_students cs ON cs.enrollment_id = e.enrollment_id
                                      JOIN class_subjects csub ON cs.class_id = csub.class_id
                                      JOIN subjects subj ON csub.subject_id = subj.subject_id
                                      JOIN activities a ON a.class_subject_id = csub.class_subject_id
                                      LEFT JOIN activity_scores ascore ON ascore.activity_id = a.activity_id
                                           AND ascore.class_student_id = cs.class_student_id
                                      WHERE e.student_id = ?
                                      ORDER BY a.due_date DESC');
    $activitiesStmt->execute([$student_id]);
    $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

    $attendanceStmt = $pdo->prepare('SELECT
                                      SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) AS present,
                                      SUM(CASE WHEN a.status = "absent" THEN 1 ELSE 0 END) AS absent,
                                      SUM(CASE WHEN a.status = "late" THEN 1 ELSE 0 END) AS late_count,
                                      SUM(CASE WHEN a.status = "excused" THEN 1 ELSE 0 END) AS excused
                                      FROM attendance a
                                      JOIN class_students cs ON a.class_student_id = cs.class_student_id
                                      JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                                      WHERE e.student_id = ?');
    $attendanceStmt->execute([$student_id]);
    $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);
    $attendance = array_map(function ($value) {
        return $value === null ? 0 : (int)$value;
    }, $attendance ?: []);

    $attendanceRecordsStmt = $pdo->prepare('SELECT subj.name AS subject_name, a.date AS attendance_date, a.status
                                            FROM attendance a
                                            JOIN class_students cs ON a.class_student_id = cs.class_student_id
                                            JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
                                            JOIN class_subjects csub ON cs.class_id = csub.class_id
                                            JOIN subjects subj ON csub.subject_id = subj.subject_id
                                            WHERE e.student_id = ?
                                            ORDER BY a.date DESC');
    $attendanceRecordsStmt->execute([$student_id]);
    $attendance_records = $attendanceRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

    $reportStmt = $pdo->prepare('SELECT g.grading_period, ROUND(AVG(g.grade), 2) AS general_average,
                                         CASE WHEN AVG(g.grade) >= 75 THEN "Passed" ELSE "Failed" END AS remarks
                                  FROM grades g
                                  JOIN class_students cs ON g.class_student_id = cs.class_student_id
                                  JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
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
