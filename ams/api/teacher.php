<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in() || $_SESSION['role'] !== 'staff') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$teacher_id = $_SESSION['user_id'];

try {

        //DASHBOARD
    if ($action === 'dashboard') {

        $classes  = getTeacherClasses($pdo, $teacher_id);
        $students = getTeacherStudentCount($pdo, $teacher_id);
        $subjects = getTeacherSubjects($pdo, $teacher_id);

        echo json_encode([
            'success' => true,
            'data' => [
                'classes' => $classes,
                'total_students' => (int)$students,
                'subjects' => $subjects
            ]
        ]);
        exit;
    }

        //CLASSES
    if ($action === 'classes') {

        $classes = getTeacherClasses($pdo, $teacher_id);

        echo json_encode([
            'success' => true,
            'data' => $classes
        ]);
        exit;
    }


        //SUBJECTS
    if ($action === 'subjects') {

        $subjects = getTeacherSubjects($pdo, $teacher_id);

        echo json_encode([
            'success' => true,
            'data' => $subjects
        ]);
        exit;
    }

  
        //STUDENTS
    if ($action === 'students') {

        $students = getTeacherStudents($pdo, $teacher_id);

        echo json_encode([
            'success' => true,
            'data' => $students
        ]);
        exit;
    }

        //ASSIGN SUBJECT
    if ($action === 'assign_subject') {

        $class_id   = intval($_POST['class_id'] ?? 0);
        $subject_id = intval($_POST['subject_id'] ?? 0);

        if ($class_id <= 0 || $subject_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid IDs']);
            exit;
        }

        $result = assignSubjectToClass($pdo, $class_id, $subject_id, $teacher_id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Assigned successfully' : 'Already assigned'
        ]);
        exit;
    }


        //INVALID ACTION
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

   //FUNCTIONS (FIXED OUTPUT CONSISTENCY)
function getTeacherClasses($pdo, $teacher_id) {

    $stmt = $pdo->prepare("
        SELECT
            c.class_id,
            s.name AS subject_name,
            c.grade_level,
            c.section,
            c.school_year,
            COUNT(DISTINCT cs.class_student_id) AS student_count
        FROM class_subjects csj
        JOIN classes c ON csj.class_id = c.class_id
        JOIN subjects s ON csj.subject_id = s.subject_id
        LEFT JOIN class_students cs ON c.class_id = cs.class_id
        WHERE csj.teacher_id = ?
        GROUP BY c.class_id, s.subject_id, s.name, c.grade_level, c.section, c.school_year
        ORDER BY c.grade_level, c.section, s.name
    ");

    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeacherStudentCount($pdo, $teacher_id) {

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT s.student_id) AS total_students
        FROM class_subjects csj
        JOIN classes c ON csj.class_id = c.class_id
        JOIN class_students cs ON c.class_id = cs.class_id
        JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
        JOIN students s ON e.student_id = s.student_id
        WHERE csj.teacher_id = ?
    ");

    $stmt->execute([$teacher_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row['total_students'] ?? 0;
}

function getTeacherSubjects($pdo, $teacher_id) {

    $stmt = $pdo->prepare("
        SELECT DISTINCT s.subject_id, s.name, s.description
        FROM class_subjects csj
        JOIN subjects s ON csj.subject_id = s.subject_id
        WHERE csj.teacher_id = ?
        ORDER BY s.name ASC
    ");

    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeacherStudents($pdo, $teacher_id) {

    $stmt = $pdo->prepare("
        SELECT DISTINCT
            s.student_id,
            s.first_name,
            s.last_name,
            s.lrn,
            e.grade_level,
            e.school_year,
            c.section,
            sub.name AS subject_name
        FROM class_subjects csj
        JOIN classes c ON csj.class_id = c.class_id
        JOIN subjects sub ON csj.subject_id = sub.subject_id
        JOIN class_students cs ON c.class_id = cs.class_id
        JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
        JOIN students s ON e.student_id = s.student_id
        WHERE csj.teacher_id = ?
        ORDER BY s.last_name, s.first_name
    ");

    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function assignSubjectToClass($pdo, $class_id, $subject_id, $teacher_id) {

    $check = $pdo->prepare("
        SELECT COUNT(*) 
        FROM class_subjects 
        WHERE class_id = ? AND subject_id = ?
    ");

    $check->execute([$class_id, $subject_id]);

    if ($check->fetchColumn() > 0) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO class_subjects (class_id, subject_id, teacher_id)
        VALUES (?, ?, ?)
    ");

    return $stmt->execute([$class_id, $subject_id, $teacher_id]);
}
?>