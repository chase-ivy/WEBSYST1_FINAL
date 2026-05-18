<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
// This API was originally protected by session-based auth. For environments
// where the verification UI is used inside the app (same origin) and during
// initial deployment/testing, we allow access without requiring login.
// WARNING: Removing auth makes this endpoint accessible to anyone who can
// reach the server. Reinstate `require_once __DIR__ . '/../login/auth.php'`
// and the `is_logged_in()` guard in production if needed.

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'list') {
        // Use explicit enrollment_status = 'pending' to find current enrollments
        // that need verification. This matches the application's workflow.
        $stmt = $pdo->prepare("SELECT e.enrollment_id, e.student_id, COALESCE(s.lrn, '') AS lrn, CONCAT(COALESCE(s.last_name,''), ', ', COALESCE(s.first_name,'')) AS student_name, e.school_year, e.enrollment_status FROM enrollments e LEFT JOIN students s ON s.student_id = e.student_id WHERE e.enrollment_status = 'pending' ORDER BY e.enrollment_id DESC");
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'details') {
        $enrollment_id = intval($_GET['enrollment_id'] ?? 0);
        if ($enrollment_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'enrollment_id required']);
            exit;
        }

        $res = [];
        $stmt = $pdo->prepare('SELECT e.*, s.lrn, s.first_name, s.last_name, s.middle_name, s.birth_date, s.sex, s.place_of_birth FROM enrollments e LEFT JOIN students s ON s.student_id = e.student_id WHERE e.enrollment_id = ? LIMIT 1');
        $stmt->execute([$enrollment_id]);
        $res['enrollment'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        // Addresses: some schemas store addresses by enrollment_id in `addresses`,
        // others use `student_addresses` keyed by student_id. Try `addresses`
        // first and fall back to `student_addresses`.
        $res['addresses'] = [];
        try {
            $addrStmt = $pdo->prepare('SELECT * FROM addresses WHERE enrollment_id = ?');
            $addrStmt->execute([$enrollment_id]);
            $res['addresses'] = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $studentId = $res['enrollment']['student_id'] ?? 0;
            if ($studentId) {
                $addrStmt = $pdo->prepare('SELECT * FROM student_addresses WHERE student_id = ?');
                $addrStmt->execute([$studentId]);
                $res['addresses'] = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
        $medStmt->execute([$enrollment_id]);
        $medical = $medStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $res['medical'] = $medical;

        if ($medical) {
            $medical_id = $medical['medical_id'] ?? 0;
            $allergiesStmt = $pdo->prepare('SELECT * FROM enrollment_medical_allergies WHERE medical_information_id = ?');
            $allergiesStmt->execute([$medical_id]);
            $res['medical_allergies'] = $allergiesStmt->fetchAll(PDO::FETCH_ASSOC);

            $conditionsStmt = $pdo->prepare('SELECT * FROM enrollment_medical_conditions WHERE medical_information_id = ?');
            $conditionsStmt->execute([$medical_id]);
            $res['medical_conditions'] = $conditionsStmt->fetchAll(PDO::FETCH_ASSOC);

            $surgeriesStmt = $pdo->prepare('SELECT * FROM enrollment_medical_surgeries WHERE medical_information_id = ?');
            $surgeriesStmt->execute([$medical_id]);
            $res['medical_surgeries'] = $surgeriesStmt->fetchAll(PDO::FETCH_ASSOC);

            $treatStmt = $pdo->prepare('SELECT * FROM enrollment_medical_treatments WHERE medical_information_id = ?');
            $treatStmt->execute([$medical_id]);
            $res['medical_treatments'] = $treatStmt->fetchAll(PDO::FETCH_ASSOC);

            $familyStmt = $pdo->prepare('SELECT * FROM enrollment_family_medical_history WHERE medical_information_id = ?');
            $familyStmt->execute([$medical_id]);
            $res['medical_family'] = $familyStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['success' => true, 'data' => $res]);
        exit;
    }

    if ($action === 'verify') {
        $data = json_decode(file_get_contents('php://input'), true);
        $enrollment_id = intval($data['enrollment_id'] ?? 0);
        if ($enrollment_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'enrollment_id required']);
            exit;
        }

        $pdo->beginTransaction();

        // Check if already has school record
        $chk = $pdo->prepare('SELECT COUNT(*) FROM student_school_records WHERE enrollment_id = ?');
        $chk->execute([$enrollment_id]);
        if ((int)$chk->fetchColumn() > 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Enrollment already verified (snapshot exists).']);
            exit;
        }

        // Fetch enrollment + student
        $stmt = $pdo->prepare('SELECT e.*, s.lrn, s.first_name, s.last_name, s.middle_name, s.extension_name, s.birth_date, s.sex, s.place_of_birth, e.school_year, e.grade_level FROM enrollments e LEFT JOIN students s ON s.student_id = e.student_id WHERE e.enrollment_id = ? LIMIT 1');
        $stmt->execute([$enrollment_id]);
        $en = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$en) throw new Exception('Enrollment not found');

        // Insert student_school_records snapshot
        $ssrStmt = $pdo->prepare('INSERT INTO student_school_records (enrollment_id, student_id, school_year, grade_level, lrn, last_name, first_name, middle_name, extension_name, birth_date, sex, place_of_birth, mother_tongue, indigenous_group, four_ps_household_id, is_learner_with_disability, is_returning_learner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        // Some schemas keep grade_level elsewhere; default to empty string to satisfy NOT NULL.
        $gradeLevel = isset($en['grade_level']) ? $en['grade_level'] : '';
        $ssrStmt->execute([
            $enrollment_id,
            $en['student_id'] ?? null,
            $en['school_year'] ?? null,
            $gradeLevel,
            $en['lrn'] ?? null,
            $en['last_name'] ?? null,
            $en['first_name'] ?? null,
            $en['middle_name'] ?? null,
            $en['extension_name'] ?? null,
            $en['birth_date'] ?? null,
            $en['sex'] ?? null,
            $en['place_of_birth'] ?? null,
            $en['mother_tongue'] ?? null,
            $en['indigenous_group'] ?? null,
            $en['four_ps_household_id'] ?? null,
            $en['is_learner_with_disability'] ?? null,
            $en['is_returning_learner'] ?? null,
        ]);
        $schoolRecordId = intval($pdo->lastInsertId());

        // Build medical JSON from enrollment_medical_* tables
        $medStmt = $pdo->prepare('SELECT * FROM enrollment_medical_information WHERE enrollment_id = ? LIMIT 1');
        $medStmt->execute([$enrollment_id]);
        $med = $medStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $allergiesJson = null;
        $conditionsJson = null;
        $surgeriesJson = null;
        $treatmentsJson = null;
        $familyJson = null;

        if ($med) {
            $medical_id = $med['medical_id'] ?? 0;
            $allergies = $pdo->prepare('SELECT * FROM enrollment_medical_allergies WHERE medical_information_id = ?');
            $allergies->execute([$medical_id]);
            $a = $allergies->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($a)) $allergiesJson = json_encode($a);

            $conds = $pdo->prepare('SELECT * FROM enrollment_medical_conditions WHERE medical_information_id = ?');
            $conds->execute([$medical_id]);
            $c = $conds->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($c)) $conditionsJson = json_encode($c);

            $surg = $pdo->prepare('SELECT * FROM enrollment_medical_surgeries WHERE medical_information_id = ?');
            $surg->execute([$medical_id]);
            $su = $surg->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($su)) $surgeriesJson = json_encode($su);

            $treat = $pdo->prepare('SELECT * FROM enrollment_medical_treatments WHERE medical_information_id = ?');
            $treat->execute([$medical_id]);
            $tr = $treat->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tr)) $treatmentsJson = json_encode($tr);

            $fam = $pdo->prepare('SELECT * FROM enrollment_family_medical_history WHERE medical_information_id = ?');
            $fam->execute([$medical_id]);
            $fm = $fam->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($fm)) $familyJson = json_encode($fm);
        }

        $smrStmt = $pdo->prepare('INSERT INTO student_medical_records (school_record_id, exposed_to_cigarette_vape_smoke, other_pertinent_information, allergies, conditions, surgeries, treatments, family_medical_history) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $smrStmt->execute([
            $schoolRecordId,
            $med['exposed_to_cigarette_vape_smoke'] ?? null,
            $med['other_pertinent_information'] ?? null,
            $allergiesJson,
            $conditionsJson,
            $surgeriesJson,
            $treatmentsJson,
            $familyJson,
        ]);

        // Optionally mark enrollment verified if column exists
        try {
            // Prefer the schema's enrollment_status column; fall back to status if present
            $pdo->prepare('UPDATE enrollments SET enrollment_status = ? WHERE enrollment_id = ?')->execute(['verified', $enrollment_id]);
        } catch (Exception $e1) {
            try {
                $pdo->prepare('UPDATE enrollments SET status = ? WHERE enrollment_id = ?')->execute(['verified', $enrollment_id]);
            } catch (Exception $e2) {
                // ignore if neither column exists
            }
        }

        $pdo->commit();

        echo json_encode(['success' => true, 'school_record_id' => $schoolRecordId]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>
