<?php
include '../../config/config.php';
function requireTeacher()
{
    if (!is_logged_in() || $_SESSION['role'] !== 'staff') {
        header('Location: ../../login/index.php');
        exit();
    }
}

function getAllStudents($pdo) {
    $stmt = $pdo->query("
        SELECT student_id, first_name, last_name, grade_level, sex
        FROM students
        ORDER BY last_name ASC
    ");
    return $stmt->fetchAll();
}

function getStudentsWithEnrollments($pdo) {
    $stmt = $pdo->query("
        SELECT 
            e.enrollment_id,
            e.class_id,
            s.student_id,
            s.first_name,
            s.last_name,
            s.grade_level,
            s.sex
        FROM students s
        LEFT JOIN enrollments e ON s.student_id = e.student_id
        ORDER BY s.last_name ASC
    ");
    return $stmt->fetchAll();
}

function getTeacherStudentEnrollments($pdo, $teacher_id, $grading_period = '1st', $class_id = null) {
    $sql = "
        SELECT 
            e.enrollment_id,
            s.student_id,
            s.first_name,
            s.last_name,
            s.grade_level,
            c.class_id,
            c.section,
            c.school_year,
            sub.subject_name,
            g.final_grade,
            g.remarks
        FROM enrollments e
        JOIN students s ON e.student_id = s.student_id
        JOIN classes c ON e.class_id = c.class_id
        JOIN subjects sub ON c.subject_id = sub.subject_id
        LEFT JOIN grades g ON e.enrollment_id = g.enrollment_id AND g.grading_period = ?
        WHERE c.teacher_id = ?";

    if ($class_id !== null) {
        $sql .= " AND c.class_id = ?";
    }

    $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";

    $stmt = $pdo->prepare($sql);
    $params = [$grading_period, $teacher_id];
    if ($class_id !== null) {
        $params[] = $class_id;
    }

    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTeacherClasses($pdo, $teacher_id) {
    $stmt = $pdo->prepare("
        SELECT c.class_id, s.subject_name, c.grade_level, c.section, c.school_year
        FROM classes c
        JOIN subjects s ON c.subject_id = s.subject_id
        WHERE c.teacher_id = ?
        ORDER BY s.subject_name ASC, c.section ASC
    ");
    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll();
}

function getStudentById($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM students
        WHERE student_id = ?
    ");
    $stmt->execute([$student_id]);
    return $stmt->fetch();
}

function updateStudent($pdo, $student_id, $first_name, $last_name, $grade_level, $sex) {
    $stmt = $pdo->prepare("
        UPDATE students
        SET first_name = ?, last_name = ?, grade_level = ?, sex = ?
        WHERE student_id = ?
    ");
    return $stmt->execute([$first_name, $last_name, $grade_level, $sex, $student_id]);
}

function deleteStudent($pdo, $student_id) {
    $stmt1 = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?");
    $stmt1->execute([$student_id]);

    $stmt2 = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    return $stmt2->execute([$student_id]);
}

function getAllClasses($pdo) {
    $stmt = $pdo->query("
        SELECT 
            c.class_id,
            s.subject_name,
            c.grade_level,
            c.section,
            c.school_year
        FROM classes c
        JOIN subjects s ON c.subject_id = s.subject_id
        ORDER BY s.subject_name ASC
    ");
    return $stmt->fetchAll();
}

function enrollStudent($pdo, $student_id, $class_id) {
    $check = $pdo->prepare("
        SELECT * FROM enrollments
        WHERE student_id = ? AND class_id = ?
    ");
    $check->execute([$student_id, $class_id]);

    if ($check->rowCount() > 0) {
        return "already_enrolled";
    }

    $stmt = $pdo->prepare("
        INSERT INTO enrollments (student_id, class_id)
        VALUES (?, ?)
    ");
    return $stmt->execute([$student_id, $class_id]);
}

function getStudentEnrollments($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT 
            s.subject_name,
            c.grade_level,
            c.section,
            c.school_year
        FROM enrollments e
        JOIN classes c ON e.class_id = c.class_id
        JOIN subjects s ON c.subject_id = s.subject_id
        WHERE e.student_id = ?
    ");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll();
}


function removeEnrollment($pdo, $student_id, $class_id) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT enrollment_id FROM enrollments WHERE student_id = ? AND class_id = ?");
        $stmt->execute([$student_id, $class_id]);
        $enrollment = $stmt->fetch();

        if ($enrollment) {
            $enrollment_id = $enrollment['enrollment_id'];

            $stmt = $pdo->prepare("DELETE FROM attendance WHERE enrollment_id = ?");
            $stmt->execute([$enrollment_id]);

            $stmt = $pdo->prepare("DELETE FROM grades WHERE enrollment_id = ?");
            $stmt->execute([$enrollment_id]);

            $stmt = $pdo->prepare("DELETE FROM student_activity_scores WHERE enrollment_id = ?");
            $stmt->execute([$enrollment_id]);

            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE enrollment_id = ?");
            $stmt->execute([$enrollment_id]);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}


function getStaffInfo($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT user_id, username, email
        FROM users
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function updateStaffInfo($pdo, $user_id, $email) {
    $stmt = $pdo->prepare("
        UPDATE users
        SET first_name = ?, last_name = ?, email = ?
        WHERE user_id = ?
    ");
    return $stmt->execute([ $email, $user_id]);
}

function addSubject($pdo, $subject_name, $description) {
    $stmt = $pdo->prepare("
        INSERT INTO subjects (subject_name, description)
        VALUES (?, ?)
    ");
    return $stmt->execute([$subject_name, $description]);
}

function getSubjects($pdo) {
    $stmt = $pdo->query("
        SELECT * FROM subjects ORDER BY subject_name ASC
    ");
    return $stmt->fetchAll();
}

function addActivity($pdo, $class_id, $activity_name, $max_score, $activity_date) {
    $stmt = $pdo->prepare("
        INSERT INTO activities (class_id, activity_name, max_score, activity_date)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$class_id, $activity_name, $max_score, $activity_date]);
}

function getActivitiesByClass($pdo, $class_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM activities
        WHERE class_id = ?
        ORDER BY activity_date DESC
    ");
    $stmt->execute([$class_id]);
    return $stmt->fetchAll();
}

function addOrUpdateStudentScore($pdo, $activity_id, $enrollment_id, $score) {

    $check = $pdo->prepare("
        SELECT * FROM student_activity_scores
        WHERE activity_id = ? AND enrollment_id = ?
    ");
    $check->execute([$activity_id, $enrollment_id]);

    if ($check->rowCount() > 0) {
        // UPDATE
        $stmt = $pdo->prepare("
            UPDATE student_activity_scores
            SET score = ?
            WHERE activity_id = ? AND enrollment_id = ?
        ");
        return $stmt->execute([$score, $activity_id, $enrollment_id]);
    }

    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO student_activity_scores (activity_id, enrollment_id, score)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$activity_id, $enrollment_id, $score]);
}

function updateGrade($pdo, $enrollment_id, $grading_period, $final_grade, $remarks) {

    $check = $pdo->prepare("
        SELECT * FROM grades
        WHERE enrollment_id = ? AND grading_period = ?
    ");
    $check->execute([$enrollment_id, $grading_period]);

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("
            UPDATE grades
            SET final_grade = ?, remarks = ?
            WHERE enrollment_id = ? AND grading_period = ?
        ");
        return $stmt->execute([$final_grade, $remarks, $enrollment_id, $grading_period]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO grades (enrollment_id, grading_period, final_grade, remarks)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$enrollment_id, $grading_period, $final_grade, $remarks]);
}

function addOrUpdateAttendance($pdo, $enrollment_id, $date, $status) {

    $check = $pdo->prepare("
        SELECT * FROM attendance
        WHERE enrollment_id = ? AND attendance_date = ?
    ");
    $check->execute([$enrollment_id, $date]);

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("
            UPDATE attendance
            SET status = ?
            WHERE enrollment_id = ? AND attendance_date = ?
        ");
        return $stmt->execute([$status, $enrollment_id, $date]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO attendance (enrollment_id, attendance_date, status)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$enrollment_id, $date, $status]);
}

function getEnrollmentsByClass($pdo, $class_id) {
    $stmt = $pdo->prepare("
        SELECT 
            e.enrollment_id,
            s.student_id,
            s.first_name,
            s.last_name
        FROM enrollments e
        JOIN students s ON e.student_id = s.student_id
        WHERE e.class_id = ?
    ");
    $stmt->execute([$class_id]);
    return $stmt->fetchAll();
}

function updateSubject($pdo, $id, $name, $desc) {
    $stmt = $pdo->prepare("
        UPDATE subjects
        SET subject_name = ?, description = ?
        WHERE subject_id = ?
    ");
    return $stmt->execute([$name, $desc, $id]);
}

function deleteSubject($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
    return $stmt->execute([$id]);
}

function assignSubjectToClass($pdo, $subject_id, $grade_level, $section, $teacher_id) {
    $stmt = $pdo->prepare("
        INSERT INTO classes (subject_id, grade_level, section, teacher_id)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$subject_id, $grade_level, $section, $teacher_id]);
}

?>