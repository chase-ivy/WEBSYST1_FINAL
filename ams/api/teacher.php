<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../login/auth.php';

if (!is_logged_in() || !in_array($_SESSION['role'], ['staff', 'admin'])) {
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
                'class_count' => count($classes),
                'total_students' => (int)$students,
                'subjects' => $subjects,
                'subject_count' => count($subjects)
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

    if ($action === 'student_account') {
        $student_id = intval($_GET['student_id'] ?? 0);

        if ($student_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT u.user_id, u.username, u.email
                              FROM students s
                              LEFT JOIN users u ON s.user_id = u.user_id
                              WHERE s.student_id = ?");
        $stmt->execute([$student_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?? ['user_id' => null, 'username' => '', 'email' => ''];

        echo json_encode(['success' => true, 'data' => $user]);
        exit;
    }

    if ($action === 'update_student_account') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = intval($data['student_id'] ?? 0);
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($student_id <= 0 || $username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid student account details']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT user_id FROM students WHERE student_id = ? LIMIT 1');
        $stmt->execute([$student_id]);
        $studentRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentUserId = intval($studentRow['user_id'] ?? 0);

        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND user_id <> ?');
        $check->execute([$username, $email, $currentUserId]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'Username or email already exists.']);
            exit;
        }

        if ($currentUserId > 0) {
            $sql = 'UPDATE users SET username = ?, email = ?';
            $params = [$username, $email];
            if ($password !== '') {
                $sql .= ', password_hash = ?';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= ' WHERE user_id = ?';
            $params[] = $currentUserId;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            if ($password === '') {
                echo json_encode(['success' => false, 'error' => 'Password is required when creating a new account.']);
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $insert->execute([$username, $email, $hash, 'student']);
            $currentUserId = intval($pdo->lastInsertId());

            $updateStudent = $pdo->prepare('UPDATE students SET user_id = ? WHERE student_id = ?');
            $updateStudent->execute([$currentUserId, $student_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Student account updated successfully.']);
        exit;
    }

    if ($action === 'delete_student') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = intval($data['student_id'] ?? 0);

        if ($student_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
            exit;
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT user_id FROM students WHERE student_id = ? LIMIT 1');
        $stmt->execute([$student_id]);
        $studentRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = intval($studentRow['user_id'] ?? 0);

        $enrollmentIds = $pdo->prepare('SELECT enrollment_id FROM enrollments WHERE student_id = ?');
        $enrollmentIds->execute([$student_id]);
        $enrollments = $enrollmentIds->fetchAll(PDO::FETCH_COLUMN, 0);

        foreach ($enrollments as $enrollmentId) {
            $medicalStmt = $pdo->prepare('SELECT medical_id FROM medical_information WHERE enrollment_id = ? LIMIT 1');
            $medicalStmt->execute([$enrollmentId]);
            $medicalRow = $medicalStmt->fetch(PDO::FETCH_ASSOC);
            if ($medicalRow) {
                $medicalId = intval($medicalRow['medical_id']);
                $allergyStmt = $pdo->prepare('SELECT allergy_group_id FROM medical_allergies WHERE medical_id = ? LIMIT 1');
                $allergyStmt->execute([$medicalId]);
                $allergyRow = $allergyStmt->fetch(PDO::FETCH_ASSOC);
                if ($allergyRow) {
                    $pdo->prepare('DELETE FROM student_allergies WHERE allergy_group_id = ?')->execute([intval($allergyRow['allergy_group_id'])]);
                }
                $conditionStmt = $pdo->prepare('SELECT condition_group_id FROM medical_conditions WHERE medical_id = ? LIMIT 1');
                $conditionStmt->execute([$medicalId]);
                $conditionRow = $conditionStmt->fetch(PDO::FETCH_ASSOC);
                if ($conditionRow) {
                    $pdo->prepare('DELETE FROM student_conditions WHERE condition_group_id = ?')->execute([intval($conditionRow['condition_group_id'])]);
                }
                $familyStmt = $pdo->prepare('SELECT family_history_id FROM family_medical_history WHERE medical_id = ? LIMIT 1');
                $familyStmt->execute([$medicalId]);
                $familyRow = $familyStmt->fetch(PDO::FETCH_ASSOC);
                if ($familyRow) {
                    $pdo->prepare('DELETE FROM student_family_conditions WHERE family_history_id = ?')->execute([intval($familyRow['family_history_id'])]);
                }
                $pdo->prepare('DELETE FROM medical_allergies WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_conditions WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_surgeries WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_treatments WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM family_medical_history WHERE medical_id = ?')->execute([$medicalId]);
                $pdo->prepare('DELETE FROM medical_information WHERE medical_id = ?')->execute([$medicalId]);
            }
        }

        $deleteActivityScores = $pdo->prepare('DELETE FROM activity_scores WHERE class_student_id IN (SELECT class_student_id FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?))');
        $deleteActivityScores->execute([$student_id]);

        $deleteAttendance = $pdo->prepare('DELETE FROM attendance WHERE class_student_id IN (SELECT class_student_id FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?))');
        $deleteAttendance->execute([$student_id]);

        $deleteGrades = $pdo->prepare('DELETE FROM grades WHERE class_student_id IN (SELECT class_student_id FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?))');
        $deleteGrades->execute([$student_id]);

        $deleteClassStudents = $pdo->prepare('DELETE FROM class_students WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteClassStudents->execute([$student_id]);

        $deleteAddresses = $pdo->prepare('DELETE FROM addresses WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteAddresses->execute([$student_id]);

        $deleteEnrollmentParents = $pdo->prepare('DELETE FROM enrollment_parents WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteEnrollmentParents->execute([$student_id]);

        $deleteReturningLearners = $pdo->prepare('DELETE FROM returning_learners WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteReturningLearners->execute([$student_id]);

        $deleteDisabilities = $pdo->prepare('DELETE FROM student_disabilities WHERE enrollment_id IN (SELECT enrollment_id FROM enrollments WHERE student_id = ?)');
        $deleteDisabilities->execute([$student_id]);

        $deleteEnrollments = $pdo->prepare('DELETE FROM enrollments WHERE student_id = ?');
        $deleteEnrollments->execute([$student_id]);

        $deleteStudent = $pdo->prepare('DELETE FROM students WHERE student_id = ?');
        $deleteStudent->execute([$student_id]);

        if ($userId > 0) {
            $deleteUser = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
            $deleteUser->execute([$userId]);
        }

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Student deleted successfully.']);
        exit;
    }

        //ASSIGN SUBJECT
    if ($action === 'assign_subject') {

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $class_id = intval($data['class_id'] ?? 0);
        $subject_id = intval($data['subject_id'] ?? 0);

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
            COALESCE(GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', '), 'Unassigned') AS subject_name,
            c.grade_level,
            c.section,
            c.school_year,
            COUNT(DISTINCT cs.class_student_id) AS student_count
        FROM classes c
        LEFT JOIN class_subjects csj ON c.class_id = csj.class_id
        LEFT JOIN subjects s ON csj.subject_id = s.subject_id
        LEFT JOIN class_students cs ON c.class_id = cs.class_id
        WHERE c.adviser_id = ?
           OR csj.teacher_id = ?
        GROUP BY c.class_id, c.grade_level, c.section, c.school_year
        ORDER BY c.grade_level, c.section, subject_name
    ");

    $stmt->execute([$teacher_id, $teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeacherStudentCount($pdo, $teacher_id) {

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT s.student_id) AS total_students
        FROM classes c
        JOIN class_students cs ON c.class_id = cs.class_id
        JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
        JOIN students s ON e.student_id = s.student_id
        WHERE c.adviser_id = ?
           OR c.class_id IN (
               SELECT class_id FROM class_subjects WHERE teacher_id = ?
           )
    ");

    $stmt->execute([$teacher_id, $teacher_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row['total_students'] ?? 0;
}

function getTeacherSubjects($pdo, $teacher_id) {

    $stmt = $pdo->prepare("
        SELECT DISTINCT s.subject_id, s.name
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
            s.user_id,
            s.first_name,
            s.last_name,
            s.lrn,
            e.enrollment_id,
            e.grade_level,
            e.school_year,
            c.section,
            csj.class_subject_id,
            GROUP_CONCAT(DISTINCT sub.name ORDER BY sub.name SEPARATOR ', ') AS subject_name
        FROM classes c
        JOIN class_students cs ON c.class_id = cs.class_id
        JOIN enrollments e ON cs.enrollment_id = e.enrollment_id
        JOIN students s ON e.student_id = s.student_id
        LEFT JOIN class_subjects csj ON c.class_id = csj.class_id
        LEFT JOIN subjects sub ON csj.subject_id = sub.subject_id
        WHERE c.adviser_id = ? OR csj.teacher_id = ?
        GROUP BY s.student_id, s.user_id, s.first_name, s.last_name, s.lrn, e.enrollment_id, e.grade_level, e.school_year, c.section, csj.class_subject_id
        ORDER BY s.last_name, s.first_name
    ");

    $stmt->execute([$teacher_id, $teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function canTeacherManageStudent($pdo, $teacher_id, $student_id) {
    $stmt = $pdo->prepare("SELECT 1
        FROM students s
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN class_students cs ON e.enrollment_id = cs.enrollment_id
        JOIN classes c ON cs.class_id = c.class_id
        LEFT JOIN class_subjects sub ON c.class_id = sub.class_id
        WHERE s.student_id = ? AND (c.adviser_id = ? OR sub.teacher_id = ?)
        LIMIT 1");
    $stmt->execute([$student_id, $teacher_id, $teacher_id]);
    return (bool) $stmt->fetchColumn();
}

function assignSubjectToClass($pdo, $class_id, $subject_id, $teacher_id) {

    $check = $pdo->prepare("SELECT COUNT(*) 
        FROM class_subjects 
        WHERE class_id = ? AND subject_id = ?");

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