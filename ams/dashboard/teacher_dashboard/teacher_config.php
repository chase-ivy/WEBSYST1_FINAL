<?php
include '../../config/config.php';

/* =========================
   AUTH
========================= */
function requireTeacher()
{
    if (!function_exists('is_logged_in') || !is_logged_in() || $_SESSION['role'] !== 'staff') {
        header('Location: ../../login/login.php');
        exit();
    }
}

/* =========================
   STUDENTS
========================= */
function getAllStudents($pdo) {
    $stmt = $pdo->query("
        SELECT student_id, first_name, last_name, sex
        FROM students
        ORDER BY last_name ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*
   FIXED:
   - removed invalid e.grade_level
   - improved JOIN clarity
*/
function getStudentsWithEnrollments($pdo) {
    $stmt = $pdo->query("
        SELECT 
            s.student_id,
            s.first_name,
            s.last_name,
        FROM students s
        ORDER BY s.last_name ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================
   TEACHER DATA
========================= */
function getTeacherClasses($pdo, $teacher_id) {
    $stmt = $pdo->prepare("
        SELECT 
            c.class_id,
            s.name AS subject_name,
            c.grade_level,
            c.section,
            c.school_year
        FROM class_subjects cs
        JOIN classes c ON cs.class_id = c.class_id
        JOIN subjects s ON cs.subject_id = s.subject_id
        WHERE cs.teacher_id = ?
        ORDER BY s.name ASC, c.section ASC
    ");
    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeacherStudentEnrollments($pdo, $teacher_id) {
    $stmt = $pdo->prepare("
        SELECT 
            s.student_id,
            s.first_name,
            s.last_name,
            c.section,
            sub.name AS subject_name
        FROM class_students cs
        JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
        JOIN students s ON e.student_id = s.student_id
        JOIN classes c ON cs.class_id = c.class_id
        JOIN class_subjects cs2 ON c.class_id = cs2.class_id
        JOIN subjects sub ON cs2.subject_id = sub.subject_id
        WHERE cs2.teacher_id = ?
        ORDER BY s.last_name ASC
    ");

    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllClasses($pdo) {
    $stmt = $pdo->query("
        SELECT 
            c.class_id,
            s.name AS subject_name,
            c.grade_level,
            c.section,
            c.school_year
        FROM class_subjects cs
        JOIN classes c ON cs.class_id = c.class_id
        JOIN subjects s ON cs.subject_id = s.subject_id
        ORDER BY s.name ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================
   SUBJECTS
========================= */
function addSubject($pdo, $name) {
    $stmt = $pdo->prepare("
        INSERT INTO subjects (name)
        VALUES (?)
    ");
    return $stmt->execute([$name]);
}

function getSubjects($pdo) {
    $stmt = $pdo->query("
        SELECT * FROM subjects ORDER BY name ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateSubject($pdo, $id, $name) {
    $stmt = $pdo->prepare("
        UPDATE subjects
        SET name = ?
        WHERE subject_id = ?
    ");
    return $stmt->execute([$name, $id]);
}

function deleteSubject($pdo, $id) {
    $stmt = $pdo->prepare("
        DELETE FROM subjects WHERE subject_id = ?
    ");
    return $stmt->execute([$id]);
}

/* =========================
   ACTIVITIES
========================= */
function addActivity($pdo, $class_subject_id, $title, $max_score, $due_date) {
    $stmt = $pdo->prepare("
        INSERT INTO activities (class_subject_id, title, max_score, due_date)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$class_subject_id, $title, $max_score, $due_date]);
}

function getActivitiesByClass($pdo, $class_subject_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM activities
        WHERE class_subject_id = ?
        ORDER BY due_date DESC
    ");
    $stmt->execute([$class_subject_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================
   SCORES
========================= */
function addOrUpdateStudentScore($pdo, $activity_id, $class_student_id, $score) {

    $check = $pdo->prepare("
        SELECT 1 FROM activity_scores
        WHERE activity_id = ? AND class_student_id = ?
        LIMIT 1
    ");
    $check->execute([$activity_id, $class_student_id]);

    if ($check->fetch()) {
        $stmt = $pdo->prepare("
            UPDATE activity_scores
            SET score = ?
            WHERE activity_id = ? AND class_student_id = ?
        ");
        return $stmt->execute([$score, $activity_id, $class_student_id]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO activity_scores (activity_id, class_student_id, score)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$activity_id, $class_student_id, $score]);
}

/* =========================
   ATTENDANCE
========================= */
function addOrUpdateAttendance($pdo, $class_student_id, $date, $status) {

    $check = $pdo->prepare("
        SELECT 1 FROM attendance
        WHERE class_student_id = ? AND date = ?
        LIMIT 1
    ");
    $check->execute([$class_student_id, $date]);

    if ($check->fetch()) {
        $stmt = $pdo->prepare("
            UPDATE attendance
            SET status = ?
            WHERE class_student_id = ? AND date = ?
        ");
        return $stmt->execute([$status, $class_student_id, $date]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO attendance (class_student_id, date, status)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$class_student_id, $date, $status]);
}

/* =========================
   CLASS ASSIGNMENT
========================= */
function assignSubjectToClass($pdo, $class_id, $subject_id, $teacher_id) {
    $stmt = $pdo->prepare("
        INSERT INTO class_subjects (class_id, subject_id, teacher_id)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$class_id, $subject_id, $teacher_id]);
}
?>