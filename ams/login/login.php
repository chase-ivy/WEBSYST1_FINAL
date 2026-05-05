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

        /* validation colors — username only */
        --valid-border: #16a34a;
        --valid-bg:     #f0fdf4;
        --valid-ring:   rgba(22, 163, 74, 0.12);
        --invalid-border: #dc2626;
        --invalid-bg:   #fef2f2;
        --invalid-ring: rgba(220, 38, 38, 0.10);
        --focus-border: #2563eb;
        --focus-ring:   rgba(37, 99, 235, 0.12);
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
        top: 0; left: 0;
        width: 100%; height: 100%;
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
        max-width: 500px;
        background: var(--surface);
        border: 1px solid rgba(0, 0, 0, 1);
        border-radius: var(--radius-xl);
        padding: 40px 38px;
        box-shadow: var(--shadow-md);
        animation: up .45s both;
    }
    @keyframes up {
        from { opacity:0; transform:translateY(14px); }
        to   { opacity:1; transform:none; }
    }
    .box h2 {
        font-family: 'Syne', sans-serif;
        font-size: 25px;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .box .sub { font-size: 13px; color: var(--muted); margin-bottom: 24px; }
    .divider { height: 1px; background: var(--border); opacity: .6; margin-bottom: 24px; }

    /* ── USERNAME FIELD — floating label + validation (NEW) ─── */
    .username-field {
        position: relative;
        margin-bottom: 10px;
    }
    .username-field input {
        width: 100%;
        padding: 18px 35px 10px 15px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        outline: none;
        background: #fafaf8;
        color: var(--text);
        height: 47px;
        transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
    }
    .username-field label {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: var(--muted);
        font-weight: 500;
        pointer-events: none;
        transition: top 160ms ease, transform 160ms ease, font-size 160ms ease,
                    color 160ms ease, letter-spacing 160ms ease;
    }
    /* float up when focused or filled */
    .username-field input:focus ~ label,
    .username-field input:not(:placeholder-shown) ~ label {
        top: 10px;
        transform: translateY(0);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
    }
    /* focus — blue */
   .username-field input:focus {
    border-color: var(--border);   /* keep default border */
    background: #fafaf8;           /* same as normal */
    box-shadow: none;              /* remove glow */
}
    .username-field input:focus ~ label { color: var(--focus-border); }
    /* valid — green */
    .username-field.is-valid input {
        border-color: var(--valid-border);
        background: var(--valid-bg);
        box-shadow: 0 0 0 3px var(--valid-ring);
    }
    .username-field.is-valid label { color: var(--valid-border); }
    /* invalid — red */
    .username-field.is-invalid input {
        border-color: var(--invalid-border);
        background: var(--invalid-bg);
        box-shadow: 0 0 0 3px var(--invalid-ring);
    }
    .username-field.is-invalid label { color: var(--invalid-border); }

    /* status icons */
    .username-field .field-icon {
        position: absolute;
        right: 13px;
        top: 50%;
        transform: translateY(-50%) scale(0.6);
        width: 16px; height: 16px;
        pointer-events: none;
        opacity: 0;
        fill: none;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: opacity 200ms ease, transform 200ms ease;
    }
    .username-field.is-valid   .icon-valid   { opacity:1; transform: translateY(-50%) scale(1); stroke: var(--valid-border);   }
    .username-field.is-invalid .icon-invalid { opacity:1; transform: translateY(-50%) scale(1); stroke: var(--invalid-border); }

    /* Email / LRN type badge */
    .type-badge {
        position: absolute;
        right: 13px;
        top: 8px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        padding: 2px 7px;
        border-radius: 9999px;
        opacity: 0;
        transform: scale(0.85);
        pointer-events: none;
        transition: opacity 180ms ease, transform 180ms ease;
    }
    .type-badge.show       { opacity: 1; transform: scale(1); }
    .type-badge.email-type { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .type-badge.lrn-type   { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

    /* hint below username */
    .username-hint {
        font-size: 11px;
        margin-top: 5px;
        padding-left: 2px;
        min-height: 16px;
        opacity: 0;
        transition: color 180ms ease, opacity 180ms ease;
    }
    .username-field.is-valid   .username-hint { color: var(--valid-border);   opacity: 1; }
    .username-field.is-invalid .username-hint { color: var(--invalid-border); opacity: 1; }

    /* ── PASSWORD FIELD — original, untouched ───────────────── */
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

    /* ── LOGIN BUTTON ───────────────────────────────────────── */
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
    /* lights up bright red when username is valid */
    .login-btn.ready {
        background: var(--brand-dark);
        box-shadow: 0 4px 14px rgba(236,63,63,.35);
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

            <!-- USERNAME — floating label + real-time validation (NEW) -->
            <div class="username-field" id="field-username">
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder=" "
                    autocomplete="username"
                    required
                >
                <label for="username">Email or LRN</label>

                <span class="type-badge" id="type-badge"></span>

                <svg class="field-icon icon-valid" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <svg class="field-icon icon-invalid" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>

                <p class="username-hint" id="username-hint"></p>
            </div>

            <!-- PASSWORD — 100% original -->
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

            <button type="submit" name="login" class="login-btn" id="login-btn">Log In</button>
        </form>
    </div>
</div>

<script>
    /* ── PASSWORD TOGGLE — original, untouched ── */
    function togglePassword() {
        const input = document.getElementById("password");
        const button = document.querySelector(".toggle-password");
        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        button.innerHTML = isHidden
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    }

    /* ── USERNAME VALIDATION — new ── */
    (function () {
        const input    = document.getElementById('username');
        const fieldEl  = document.getElementById('field-username');
        const hintEl   = document.getElementById('username-hint');
        const badgeEl  = document.getElementById('type-badge');
        const loginBtn = document.getElementById('login-btn');

        function setState(state, hint) {
            fieldEl.classList.remove('is-valid', 'is-invalid');
            if (state) fieldEl.classList.add(state);
            hintEl.textContent = hint || '';
            loginBtn.classList.toggle('ready', state === 'is-valid');
        }

        function validate(val) {
            if (!val) {
                setState('', '');
                badgeEl.className = 'type-badge';
                return;
            }

            const hasAt   = val.includes('@');
            const allNums = /^\d+$/.test(val);

            if (hasAt) {
                badgeEl.textContent = 'Email';
                badgeEl.className   = 'type-badge email-type show';
                const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                setState(ok ? 'is-valid' : 'is-invalid', ok ? 'Looks good!' : 'Enter a valid email address');

            } else if (allNums) {
                badgeEl.textContent = 'LRN';
                badgeEl.className   = 'type-badge lrn-type show';
                if (val.length === 12) {
                    setState('is-valid', '12-digit LRN verified');
                } else {
                    const left = 12 - val.length;
                    setState('is-invalid', left > 0
                        ? `${left} more digit${left === 1 ? '' : 's'} needed`
                        : 'LRN must be exactly 12 digits');
                }

            } else {
                badgeEl.className = 'type-badge';
                setState('is-invalid', 'Enter a valid email or 12-digit LRN');
            }
        }

        input.addEventListener('input', () => validate(input.value.trim()));
        input.addEventListener('blur',  () => { if (input.value.trim()) validate(input.value.trim()); });
    })();
</script>
</body>
</html>