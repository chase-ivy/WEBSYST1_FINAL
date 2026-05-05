<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in() || $_SESSION['role'] !== 'staff') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$teacher_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'dashboard':
            try {
                $classes = getTeacherClasses($pdo, $teacher_id);
                $students = getTeacherStudentCount($pdo, $teacher_id);
                $subjects = getTeacherSubjects($pdo, $teacher_id);

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'classes' => $classes,
                        'total_students' => $students,
                        'subjects' => $subjects
                    ]
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Dashboard error: ' . $e->getMessage()]);
            }
            break;

        case 'classes':
            try {
                $classes = getTeacherClasses($pdo, $teacher_id);
                echo json_encode(['success' => true, 'data' => $classes]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Classes error: ' . $e->getMessage()]);
            }
            break;

        case 'students':
            try {
                $students = getTeacherStudents($pdo, $teacher_id);
                echo json_encode(['success' => true, 'data' => $students]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Students error: ' . $e->getMessage()]);
            }
            break;

        case 'subjects':
            try {
                $subjects = getTeacherSubjects($pdo, $teacher_id);
                echo json_encode(['success' => true, 'data' => $subjects]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Subjects error: ' . $e->getMessage()]);
            }
            break;

        case 'assign_subject':
            try {
                $class_id = intval($_POST['class_id'] ?? 0);
                $subject_id = intval($_POST['subject_id'] ?? 0);

                if ($class_id <= 0 || $subject_id <= 0) {
                    echo json_encode(['success' => false, 'error' => 'Invalid class or subject ID']);
                    exit;
                }

                $result = assignSubjectToClass($pdo, $class_id, $subject_id, $teacher_id);
                echo json_encode(['success' => $result]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Assign subject error: ' . $e->getMessage()]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getTeacherClasses($pdo, $teacher_id) {
    $stmt = $pdo->prepare("
        SELECT
            c.class_id,
            s.name AS subject_name,
            c.grade_level,
            c.section,
            c.school_year,
            COUNT(cs.class_student_id) as student_count
        FROM class_subjects csj
        JOIN classes c ON csj.class_id = c.class_id
        JOIN subjects s ON csj.subject_id = s.subject_id
        LEFT JOIN class_students cs ON c.class_id = cs.class_id
        WHERE csj.teacher_id = ?
        GROUP BY c.class_id, s.subject_id, s.name, c.grade_level, c.section, c.school_year
        ORDER BY s.name ASC, c.section ASC
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . implode(" ", $pdo->errorInfo()));
    }
    if (!$stmt->execute([$teacher_id])) {
        throw new Exception("Execute failed: " . implode(" ", $stmt->errorInfo()));
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeacherStudentCount($pdo, $teacher_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT s.student_id) as total_students
        FROM class_subjects csj
        JOIN classes c ON csj.class_id = c.class_id
        JOIN class_students cs ON c.class_id = cs.class_id
        JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
        JOIN students s ON e.student_id = s.student_id
        WHERE csj.teacher_id = ?
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . implode(" ", $pdo->errorInfo()));
    }
    if (!$stmt->execute([$teacher_id])) {
        throw new Exception("Execute failed: " . implode(" ", $stmt->errorInfo()));
    }
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_students'] ?? 0;
}

function getTeacherSubjects($pdo, $teacher_id) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.subject_id, s.name, s.description
        FROM class_subjects csj
        JOIN subjects s ON csj.subject_id = s.subject_id
        WHERE csj.teacher_id = ?
        ORDER BY s.name ASC
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . implode(" ", $pdo->errorInfo()));
    }
    if (!$stmt->execute([$teacher_id])) {
        throw new Exception("Execute failed: " . implode(" ", $stmt->errorInfo()));
    }
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
        ORDER BY s.last_name ASC, s.first_name ASC
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . implode(" ", $pdo->errorInfo()));
    }
    if (!$stmt->execute([$teacher_id])) {
        throw new Exception("Execute failed: " . implode(" ", $stmt->errorInfo()));
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function assignSubjectToClass($pdo, $class_id, $subject_id, $teacher_id) {
    // Check if assignment already exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM class_subjects WHERE class_id = ? AND subject_id = ?");
    if (!$check) {
        throw new Exception("Prepare failed: " . implode(" ", $pdo->errorInfo()));
    }
    if (!$check->execute([$class_id, $subject_id])) {
        throw new Exception("Execute failed: " . implode(" ", $check->errorInfo()));
    }
    if ($check->fetchColumn() > 0) {
        return false; // Already assigned
    }

    $stmt = $pdo->prepare("INSERT INTO class_subjects (class_id, subject_id, teacher_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . implode(" ", $pdo->errorInfo()));
    }
    if (!$stmt->execute([$class_id, $subject_id, $teacher_id])) {
        throw new Exception("Execute failed: " . implode(" ", $stmt->errorInfo()));
    }
    return true;
}
?>