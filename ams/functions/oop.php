<?php

class oopPHP {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ================================================================
    // ENROLLMENT — inserts one student record and returns the new ID
    // ================================================================
    public function insertStudent($school_year, $grade_level, $with_lrn,
                                  $returning, $psa_bcn, $lrn,
                                  $last_name, $first_name, $middle_name,
                                  $extension_name, $birth_date, $sex,
                                  $place_of_birth, $age, $mother_tongue,
                                  $indigenous_group, $four_ps, $disability) {

        $sql = "INSERT INTO students (
                    school_year, grade_level, with_lrn, `returning`,
                    psa_bcn, lrn, last_name, first_name, middle_name,
                    extension_name, birth_date, sex, place_of_birth,
                    age, mother_tongue, indigenous_group,
                    `4p_beneficiary`, is_learner_with_disability
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?
                )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $school_year, $grade_level, $with_lrn, $returning,
            $psa_bcn, $lrn, $last_name, $first_name, $middle_name,
            $extension_name, $birth_date, $sex, $place_of_birth,
            $age, $mother_tongue, $indigenous_group,
            $four_ps, $disability
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // ================================================================
    // CURRENT ADDRESS — inserts current address for a student
    // ================================================================
    public function insertCurrentAddress($student_id, $house_no, $street_name,
                                         $barangay, $municipality_city,
                                         $province, $country, $zip_code) {

        $sql = "INSERT INTO current_address (
                    student_id, house_no, street_name, barangay,
                    municipality_city, province, country, zip_code
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $student_id, $house_no, $street_name, $barangay,
            $municipality_city, $province, $country, $zip_code
        ]);
    }

    // ================================================================
    // PERMANENT ADDRESS — inserts permanent address for a student
    // ================================================================
    public function insertPermanentAddress($student_id, $house_no, $street_name,
                                           $barangay, $municipality_city,
                                           $province, $country, $zip_code) {

        $sql = "INSERT INTO permanent_address (
                    student_id, house_no, street_name, barangay,
                    municipality_city, province, country, zip_code
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $student_id, $house_no, $street_name, $barangay,
            $municipality_city, $province, $country, $zip_code
        ]);
    }

    // ================================================================
    // PARENT / GUARDIAN — inserts parent info linked to a student
    // ================================================================
    public function insertParent($student_id,
                                 $father_last, $father_first, $father_middle, $father_contact,
                                 $mother_last, $mother_first, $mother_middle, $mother_contact) {

        $sql = "INSERT INTO parent_guardian_information (
                    student_id,
                    father_last_name, father_first_name, father_middle_name, father_contact_number,
                    mother_last_name, mother_first_name, mother_middle_name, mother_contact_number
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $student_id,
            $father_last, $father_first, $father_middle, $father_contact,
            $mother_last, $mother_first, $mother_middle, $mother_contact
        ]);
    }

    // ================================================================
    // RETURNING LEARNER — inserts transfer/balik-aral info
    // ================================================================
    public function insertReturningLearner($student_id, $last_grade,
                                           $last_school, $last_school_year) {

        $sql = "INSERT INTO returning_learner_information (
                    student_id, last_grade_level_completed,
                    last_school_attended, last_school_year_completed
                ) VALUES (?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $student_id, $last_grade, $last_school, $last_school_year
        ]);
    }

    // ================================================================
    // AUTH HELPERS
    // ================================================================

    private function setLoginSession($user) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['special_admin_access'] = (
            $user['role'] === 'admin' &&
            isset($user['_plain_match']) &&
            $user['_plain_match'] === true
        );
    }

    public function isLoggedIn() {
        return !empty($_SESSION['logged_in'])
            && !empty($_SESSION['user_id'])
            && !empty($_SESSION['role']);
    }

    public function isSpecialAdmin() {
        return $this->isLoggedIn()
            && $_SESSION['role'] === 'admin'
            && !empty($_SESSION['special_admin_access']);
    }

    public function requireRole(array $allowed_roles) {
        if (!$this->isLoggedIn() || !in_array($_SESSION['role'], $allowed_roles, true)) {
            header('Location: ' . $this->getLoginUrl());
            exit;
        }
    }

    public function requireSpecialAdmin() {
        if (!$this->isSpecialAdmin()) {
            header('Location: ' . $this->getLoginUrl());
            exit;
        }
    }

    public function getLoginUrl() {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($script, '/dashboard/') !== false) {
            return dirname($script, 3) . '/login/index.php';
        }
        return dirname($script) . '/login/index.php';
    }

    public function redirectToDashboard($role) {
        switch ($role) {
            case 'admin':
                return '../dashboard/admin_dashboard/admin_dashboard.php';
            case 'teacher':
                return '../dashboard/teacher_dashboard/teacher_dashboard.php';
            case 'parent':
                return '../dashboard/parent_dashboard/parent_dashboard.php';
            default:
                return '../login/index.php';
        }
    }

    public function logoutUser() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    // ================================================================
    // LOGIN — Faculty / Staff (email + password)
    // ================================================================
    public function loginFaculty($email, $password) {
        $email    = trim($email);
        $password = trim($password);

        if ($email === '' || $password === '') {
            $_SESSION['login_error'] = 'Email and password are required.';
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT user_id, username, email, password_hash, role
             FROM users
             WHERE email = ? AND role IN (\'admin\', \'teacher\')
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['login_error'] = 'Invalid email or password.';
            return;
        }

        $valid          = false;
        $plainMatch     = false;

        // Admin accounts may still use a plain-text password (special access)
        if ($user['email'] === 'admin@gibraltar.edu.ph' && $password === 'Admin1234@') {
            $valid      = true;
            $plainMatch = true;
        }

        // Standard bcrypt check for all roles
        if (!$valid && password_verify($password, $user['password_hash'])) {
            $valid = true;
        }

        if (!$valid) {
            $_SESSION['login_error'] = 'Invalid email or password.';
            return;
        }

        $user['_plain_match'] = $plainMatch;
        $this->setLoginSession($user);

        header('Location: ' . $this->redirectToDashboard($user['role']));
        exit;
    }

    // ================================================================
    // LOGIN — Parent / Guardian (LRN + PIN)
    // Parents are looked up via the students table (lrn) then
    // authenticated against the users table linked by student_id.
    // ================================================================
    public function loginParent($lrn, $pin) {
        $lrn = trim($lrn);
        $pin = trim($pin);

        if ($lrn === '' || $pin === '') {
            $_SESSION['login_error'] = 'LRN and PIN are required.';
            return;
        }

        // Join students → users so we get the user account for this parent
        $stmt = $this->pdo->prepare(
            'SELECT u.user_id, u.username, u.password_hash, u.role
             FROM users u
             INNER JOIN students s ON s.student_id = u.student_id
             WHERE s.lrn = ? AND u.role = \'parent\'
             LIMIT 1'
        );
        $stmt->execute([$lrn]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['login_error'] = 'LRN not found or no parent account linked.';
            return;
        }

        if (!password_verify($pin, $user['password_hash'])) {
            $_SESSION['login_error'] = 'Incorrect PIN.';
            return;
        }

        $this->setLoginSession($user);

        header('Location: ' . $this->redirectToDashboard('parent'));
        exit;
    }

}