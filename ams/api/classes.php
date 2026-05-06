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

        //CLASSES - LIST ALL
    if ($action === 'list') {

        $stmt = $pdo->query('
            SELECT c.class_id, c.school_year, c.grade_level, c.section, u.username as adviser
            FROM classes c
            LEFT JOIN users u ON c.adviser_id = u.user_id
            ORDER BY c.school_year DESC, c.grade_level, c.section
        ');

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

        //TEACHER CLASSES
    elseif ($action === 'teacher_classes') {

        $teacher_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("
            SELECT c.class_id,
                   c.school_year,
                   c.grade_level,
                   c.section,
                   COALESCE(GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', '), '') AS subject,
                   COUNT(DISTINCT cs.class_student_id) AS student_count
            FROM classes c
            LEFT JOIN class_subjects cls ON c.class_id = cls.class_id
            LEFT JOIN subjects s ON cls.subject_id = s.subject_id
            LEFT JOIN class_students cs ON c.class_id = cs.class_id
            WHERE cls.teacher_id = ? OR c.adviser_id = ?
            GROUP BY c.class_id, c.school_year, c.grade_level, c.section
            ORDER BY c.grade_level, c.section, c.school_year DESC
        ");

        $stmt->execute([$teacher_id, $teacher_id]);

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

        //CLASS STUDENTS
    elseif ($action === 'students') {

        $class_id = intval($_GET['class_id'] ?? 0);

        $stmt = $pdo->prepare('
            SELECT cs.class_student_id, s.student_id, s.lrn, s.first_name, s.last_name
            FROM class_students cs
            JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
            JOIN students s ON e.student_id = s.student_id
            WHERE cs.class_id = ?
            ORDER BY s.last_name, s.first_name
        ');

        $stmt->execute([$class_id]);

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

        //CREATE CLASS
    elseif ($action === 'create') {

        $data = json_decode(file_get_contents('php://input'), true);
        $teacher_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare('
            INSERT INTO classes (school_year, grade_level, section, adviser_id)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['school_year'] ?? '',
            $data['grade_level'] ?? '',
            $data['section'] ?? null,
            $teacher_id
        ]);

        echo json_encode(['success' => true, 'class_id' => $pdo->lastInsertId()]);
        exit;
    }

    elseif ($action === 'assign_student') {
        $data = json_decode(file_get_contents('php://input'), true);
        $class_id = intval($data['class_id'] ?? 0);
        $student_id = intval($data['student_id'] ?? 0);
        $teacher_id = $_SESSION['user_id'];

        if ($class_id <= 0 || $student_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid class or student selection']);
            exit;
        }

        $permission = $pdo->prepare('SELECT 1 FROM classes c
            LEFT JOIN class_subjects cls ON c.class_id = cls.class_id
            WHERE c.class_id = ? AND (c.adviser_id = ? OR cls.teacher_id = ?)
            LIMIT 1');
        $permission->execute([$class_id, $teacher_id, $teacher_id]);

        if (!$permission->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            exit;
        }

        $enrollmentStmt = $pdo->prepare('SELECT enrollment_id FROM enrollments WHERE student_id = ? ORDER BY enrollment_id DESC LIMIT 1');
        $enrollmentStmt->execute([$student_id]);
        $enrollment = $enrollmentStmt->fetch(PDO::FETCH_ASSOC);

        if (empty($enrollment['enrollment_id'])) {
            echo json_encode(['success' => false, 'error' => 'Student does not have an enrollment record.']);
            exit;
        }

        $check = $pdo->prepare('SELECT COUNT(*) FROM class_students WHERE class_id = ? AND enrollment_id = ?');
        $check->execute([$class_id, $enrollment['enrollment_id']]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'Student is already assigned to that class.']);
            exit;
        }

        $insert = $pdo->prepare('INSERT INTO class_students (class_id, enrollment_id) VALUES (?, ?)');
        $insert->execute([$class_id, $enrollment['enrollment_id']]);

        echo json_encode(['success' => true, 'message' => 'Student assigned to class successfully.']);
        exit;
    }

        //UPDATE CLASS
    elseif ($action === 'update') {

        $data = json_decode(file_get_contents('php://input'), true);
        $class_id = intval($data['class_id'] ?? 0);
        $teacher_id = $_SESSION['user_id'];

        $check = $pdo->prepare('
            SELECT 1
            FROM classes c
            LEFT JOIN class_subjects cls ON c.class_id = cls.class_id
            WHERE c.class_id = ? AND (c.adviser_id = ? OR cls.teacher_id = ?)
            LIMIT 1
        ');
        $check->execute([$class_id, $teacher_id, $teacher_id]);

        if (!$check->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            exit;
        }

        $stmt = $pdo->prepare('
            UPDATE classes 
            SET school_year = ?, grade_level = ?, section = ?
            WHERE class_id = ?
        ');

        $stmt->execute([
            $data['school_year'] ?? '',
            $data['grade_level'] ?? '',
            $data['section'] ?? null,
            $class_id
        ]);

        echo json_encode(['success' => true]);
        exit;
    }


//ACTIVITIES CRUD SECTION
  //LIST ACTIVITIES BY CLASS
    elseif ($action === 'activities') {

        $class_id = intval($_GET['class_id'] ?? 0);

        $stmt = $pdo->prepare('
            SELECT *
            FROM activities
            WHERE class_id = ?
            ORDER BY due_date ASC, created_at DESC
        ');

        $stmt->execute([$class_id]);

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

        //CREATE ACTIVITY
    elseif ($action === 'delete') {

        $data = json_decode(file_get_contents('php://input'), true);
        $class_id = intval($data['class_id'] ?? 0);
        $teacher_id = $_SESSION['user_id'];

        $check = $pdo->prepare('
            SELECT 1
            FROM classes c
            LEFT JOIN class_subjects cls ON c.class_id = cls.class_id
            WHERE c.class_id = ? AND (c.adviser_id = ? OR cls.teacher_id = ?)
            LIMIT 1
        ');
        $check->execute([$class_id, $teacher_id, $teacher_id]);

        if (!$check->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM classes WHERE class_id = ?');
        $stmt->execute([$class_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    elseif ($action === 'create_activity') {

        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare('
            INSERT INTO activities (class_id, title, description, due_date)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['class_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['due_date'] ?? null
        ]);

        echo json_encode([
            'success' => true,
            'activity_id' => $pdo->lastInsertId()
        ]);
        exit;
    }

        //UPDATE ACTIVITY
    elseif ($action === 'update_activity') {

        $data = json_decode(file_get_contents('php://input'), true);
        $activity_id = intval($data['activity_id'] ?? 0);

        $stmt = $pdo->prepare('
            UPDATE activities
            SET title = ?, description = ?, due_date = ?
            WHERE activity_id = ?
        ');

        $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['due_date'] ?? null,
            $activity_id
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

        //DELETE ACTIVITY
    elseif ($action === 'delete_activity') {

        $data = json_decode(file_get_contents('php://input'), true);
        $activity_id = intval($data['activity_id'] ?? 0);

        $stmt = $pdo->prepare('DELETE FROM activities WHERE activity_id = ?');
        $stmt->execute([$activity_id]);

        echo json_encode(['success' => true]);
        exit;
    }

        //GET CLASS SUBJECTS
    elseif ($action === 'subjects') {

        $class_id = intval($_GET['class_id'] ?? 0);

        $stmt = $pdo->prepare('
            SELECT cs.class_subject_id, s.subject_id, s.name AS subject_name
            FROM class_subjects cs
            JOIN subjects s ON cs.subject_id = s.subject_id
            WHERE cs.class_id = ?
            ORDER BY s.name
        ');

        $stmt->execute([$class_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

        //UNASSIGN SUBJECT FROM CLASS
    elseif ($action === 'unassign_subject') {

        $data = json_decode(file_get_contents('php://input'), true);
        $class_subject_id = intval($data['class_subject_id'] ?? 0);

        $stmt = $pdo->prepare('DELETE FROM class_subjects WHERE class_subject_id = ?');
        $stmt->execute([$class_subject_id]);

        echo json_encode(['success' => true]);
        exit;
    }

        //INVALID ACTION
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}