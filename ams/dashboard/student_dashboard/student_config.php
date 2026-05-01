<?php
include '../../config/config.php';

   //STUDENT INFO
function getStudentInfo($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT 
            s.student_id,
            s.first_name,
            s.middle_name,
            s.last_name,
            s.extension_name,
            s.grade_level,
            s.sex,
            s.birth_date,
            s.age
        FROM students s
        WHERE s.student_id = ?
    ");
    $stmt->execute([$student_id]);
    return $stmt->fetch();
}

   //GRADES
function getGrades($pdo, $student_id, $school_year = null) {
    $sql = "
        SELECT 
            s.subject_name,
            g.grading_period,
            g.final_grade,
            g.remarks
        FROM grades g
        JOIN enrollments e ON g.enrollment_id = e.enrollment_id
        JOIN classes c ON e.class_id = c.class_id
        JOIN subjects s ON c.subject_id = s.subject_id
        WHERE e.student_id = ?
    ";

    if ($school_year) {
        $sql .= " AND e.school_year = ?";
    }

    $sql .= " ORDER BY s.subject_name, g.grading_period";

    $stmt = $pdo->prepare($sql);

    if ($school_year) {
        $stmt->execute([$student_id, $school_year]);
    } else {
        $stmt->execute([$student_id]);
    }

    return $stmt->fetchAll();
}

   //ACTIVITIES
function getActivities($pdo, $student_id, $school_year = null) {
    $sql = "
        SELECT 
            s.subject_name,
            a.activity_name,
            a.activity_date,
            a.max_score,
            COALESCE(sas.score, 0) AS score
        FROM enrollments e
        JOIN classes c ON e.class_id = c.class_id
        JOIN subjects s ON c.subject_id = s.subject_id
        JOIN activities a ON a.class_id = c.class_id
        LEFT JOIN student_activity_scores sas 
            ON sas.activity_id = a.activity_id 
            AND sas.enrollment_id = e.enrollment_id
        WHERE e.student_id = ?
    ";

    if ($school_year) {
        $sql .= " AND e.school_year = ?";
    }

    $sql .= " ORDER BY a.activity_date DESC";

    $stmt = $pdo->prepare($sql);

    if ($school_year) {
        $stmt->execute([$student_id, $school_year]);
    } else {
        $stmt->execute([$student_id]);
    }

    return $stmt->fetchAll();
}

   //ATTENDANCE SUMMARY
function getAttendance($pdo, $student_id, $school_year = null) {
    $sql = "
        SELECT 
            SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present,
            SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent,
            SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS late_count,
            SUM(CASE WHEN a.status = 'Excused' THEN 1 ELSE 0 END) AS excused
        FROM attendance a
        JOIN enrollments e ON a.enrollment_id = e.enrollment_id
        WHERE e.student_id = ?
    ";

    if ($school_year) {
        $sql .= " AND e.school_year = ?";
    }

    $stmt = $pdo->prepare($sql);

    if ($school_year) {
        $stmt->execute([$student_id, $school_year]);
    } else {
        $stmt->execute([$student_id]);
    }

    return $stmt->fetch();
}

   //ATTENDANCE PER DATE 
function getAttendanceRecords($pdo, $student_id, $school_year = null) {
    $sql = "
        SELECT 
            s.subject_name,
            a.attendance_date,
            a.status
        FROM attendance a
        JOIN enrollments e ON a.enrollment_id = e.enrollment_id
        JOIN classes c ON e.class_id = c.class_id
        JOIN subjects s ON c.subject_id = s.subject_id
        WHERE e.student_id = ?
    ";

    if ($school_year) {
        $sql .= " AND e.school_year = ?";
    }

    $sql .= " ORDER BY a.attendance_date DESC";

    $stmt = $pdo->prepare($sql);

    if ($school_year) {
        $stmt->execute([$student_id, $school_year]);
    } else {
        $stmt->execute([$student_id]);
    }

    return $stmt->fetchAll();
}

   //REPORT CARD
function getReportCard($pdo, $student_id, $school_year = null) {
    $sql = "
        SELECT 
            g.grading_period,
            ROUND(AVG(g.final_grade), 2) AS general_average,
            CASE 
                WHEN AVG(g.final_grade) >= 75 THEN 'Passed'
                ELSE 'Failed'
            END AS remarks
        FROM grades g
        JOIN enrollments e ON g.enrollment_id = e.enrollment_id
        WHERE e.student_id = ?
    ";

    if ($school_year) {
        $sql .= " AND e.school_year = ?";
    }

    $sql .= "
        GROUP BY g.grading_period
        ORDER BY g.grading_period
    ";

    $stmt = $pdo->prepare($sql);

    if ($school_year) {
        $stmt->execute([$student_id, $school_year]);
    } else {
        $stmt->execute([$student_id]);
    }

    return $stmt->fetchAll();
}

   //FULL DASHBOARD
function getStudentDashboard($pdo, $student_id, $school_year = null) {
    return [
        "student"           => getStudentInfo($pdo, $student_id),
        "grades"            => getGrades($pdo, $student_id, $school_year),
        "activities"        => getActivities($pdo, $student_id, $school_year),
        "attendance"        => getAttendance($pdo, $student_id, $school_year),
        "attendance_logs"   => getAttendanceRecords($pdo, $student_id, $school_year), // NEW
        "report_card"       => getReportCard($pdo, $student_id, $school_year)
    ];
}
?>