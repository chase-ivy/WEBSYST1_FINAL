<?php
include '../config/config.php';
require_once '../login/auth.php';

$error = '';

if (isset($_POST["login"])) {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        $passwordValid = false;
        if ($user) {
            if ($user['role'] === 'admin' && $password === $user['password_hash']) {
                $passwordValid = true;
            } elseif (password_verify($password, $user['password_hash'])) {
                $passwordValid = true;
            }
        }

        if ($user && $passwordValid) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            if ($user['role'] === 'admin' && $password === $user['password_hash']) {
                $_SESSION['special_admin_access'] = true;
            }

            header('Location: ' . redirect_to_dashboard($user['role']));
            exit;
        } else {
            $stmt = $pdo->prepare('SELECT student_id, first_name, last_name FROM students WHERE lrn = ? LIMIT 1');
            $stmt->execute([$username]);
            $student = $stmt->fetch();

            if ($student) {
                if ($password === 'student') {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $student['student_id'];
                    $_SESSION['username'] = $student['first_name'] . ' ' . $student['last_name'];
                    $_SESSION['role'] = 'student';
                    $_SESSION['logged_in'] = true;

                    header('Location: ' . redirect_to_dashboard('student'));
                    exit;
                } else {
                    $error = 'Invalid username or password';
                }
            } else {
                $error = 'Invalid username or password';
            }
        }
    }
}

if (!empty($error)) {
    $_SESSION['login_error'] = $error;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    /* ── DESIGN TOKENS ─────────────────────────────────────── */
    :root {
        --brand:        #4e0303;
        --brand-dark:   #ec3f3f;
        --brand-light:  #fdf2f2;
        --border:       #d1d5db;
        --text:         #000000;
        --muted:        #6b7280;
        --surface:      #ffffff;
        --canvas:       #f5f7fa;
        --shadow-sm:    0 2px 8px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md:    0 4px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04);
        --radius-sm:    6px;
        --radius-md:    10px;
        --radius-lg:    14px;
        --radius-xl:    20px;
        --transition:   180ms ease;
    }

    *,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--canvas);
        color: var(--text);
        min-height: 100vh;
        display: flex;
        
    }

    /* ── LEFT PANEL ─────────────────────────────────────────── */
    .left {
        flex: 1;
        position: relative;
        min-height: 100vh;
        overflow: hidden;
    }

    .left::before {
    content: "";
    position: absolute;
    top: 0; 
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://images.unsplash.com/photo-1635424239131-32dc44986b56?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') center/cover no-repeat;
    filter: blur(3px); 
}
    .left::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(19, 14, 14, 0.88) 0%, rgba(12, 12, 12, 0.68) 100%);
    }
    .left-copy {
        position: absolute;
        bottom: 52px;
        left: 52px;
        z-index: 2;
        color: #fff;
        max-width: 380px;
    }
    .left-logo {
        font-family: 'Syne', sans-serif;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 1px;
        margin-bottom: 4px;
        line-height: 1;
    }
    .left-logo span { color: var(--brand-dark); }
    .left-tagline {
        font-size: 11px;
        color: red;
        font-weight: 600;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        opacity: .6;
        margin-bottom: 24px;
    }
    .brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(249, 248, 248, 0.18);
        border: 1px solid rgba(0, 0, 0, 0.35);
        color: rgb(233, 233, 233);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        padding: 5px 12px;
        border-radius: 9999px;
        margin-bottom: 20px;
    }
    .brand-pill i {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--brand-dark);
        display: inline-block;
        flex-shrink: 0;
    }
    .left-copy h2 {
        font-family: 'Syne', sans-serif;
        font-size: 34px;
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 10px;
    }
    .left-copy p { font-size: 14px; opacity: .72; line-height: 1.65; }

    /* ── RIGHT PANEL ────────────────────────────────────────── */
    .right {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 24px;
        background: var(--canvas);
        border: 1px solid rgba(0, 0, 0, 1);
    
        
    }
    .box {
        width: 100%;
        max-width: 420px;
        background: var(--surface);
        border: 1px solid rgba(0, 0, 0, 1);
        border-radius: var(--radius-xl);
        padding: 30px 35px;
        box-shadow: var(--shadow-md);
        animation: up .45s both;
    }
    @keyframes up {
        from { opacity:0; transform:translateY(14px); }
        to   { opacity:1; transform:none; }
    }

    .box-logo {
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 1px;
        color: var(--text);
        margin-bottom: 4px;
    }
    .box-logo span { color: var(--brand-dark); }
    .box-sub-logo {
        font-size: 11px;
        color: var(--muted);
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-weight: 500;
        margin-bottom: 28px;
    }
    .box h2 {
        font-family: 'Syne', sans-serif;
        font-size: 25px;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .box .sub { font-size: 13px; color: var(--muted); margin-bottom: 24px; }

    .divider { height: 1px; background: var(--border); opacity: .6; margin-bottom: 24px; }

    /* ── FIELDS ─────────────────────────────────────────────── */
    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 5px;
    }
    .form-group input {
        width: 100%;
        padding: 11px 13px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        outline: none;
        background: #fafaf8;
        color: var(--text);
        transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
    }
    .form-group input:focus {
        border-color: var(--brand);
        background: var(--surface);
        box-shadow: 0 0 0 3px rgba(78,3,3,.10);
    }

    /* password wrapper */
    .password-input { position: relative; }
    .password-input input { padding-right: 42px; }
    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        display: flex;
        align-items: center;
        padding: 0;
        transition: color var(--transition);
    }
    .toggle-password:hover { color: var(--brand); }
    .toggle-password svg { width: 16px; height: 16px; }

    /* ── BUTTON ─────────────────────────────────────────────── */
    .login-btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: var(--radius-md);
        background: var(--brand);
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 6px;
        transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
    }
    .login-btn:hover {
        background: var(--brand-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(78,3,3,.28);
    }
    .login-btn:active { transform: none; }

    /* ── ERROR MSG ──────────────────────────────────────────── */
    .error-message {
        background: var(--brand-light);
        border: 1px solid rgba(78,3,3,.2);
        color: var(--brand);
        font-size: 13px;
        padding: 10px 13px;
        border-radius: var(--radius-md);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .error-message svg { width: 15px; height: 15px; flex-shrink: 0; }

    @media(max-width:820px) {
        .left { display: none; }
        .right { background: var(--canvas); }
    }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left">
    <div class="left-copy">
        <div class="left-logo">Gibraltar <span>AMES</span></div>
        <div class="left-tagline">Academic Management System</div>
        <div class="brand-pill"><i></i>Staff &amp; Student Portal</div>
        <h2>Welcome back</h2>
        <p>Access your dashboard, manage records, and track academic progress in one place.</p>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="right">
    <div class="box">


        <h2>Sign In</h2>
        <p class="sub">Enter your credentials to continue</p>
        <div class="divider"></div>

        <?php if ($error): ?>
        <div class="error-message">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your email or LRN" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="login-btn">Log In</button>
        </form>

    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById("password");
        const button = document.querySelector(".toggle-password");
        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        button.innerHTML = isHidden
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    }
</script>
</body>
</html>