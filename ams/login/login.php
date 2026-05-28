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
    :root {
        --brand:          #4e0303;
        --brand-hover:    #6b0404;
        --brand-light:    #fdf2f2;
        --brand-dark:     #ec3f3f;
        --border:         #e2e8f0;
        --text:           #0f172a;
        --text-sub:       #64748b;
        --surface:        #ffffff;
        --radius-sm:      6px;
        --radius-md:      10px;
        --radius-lg:      16px;
        --radius-xl:      24px;
        --t:              160ms ease;
        --valid:          #16a34a;
        --valid-bg:       #f0fdf4;
        --valid-ring:     rgba(22,163,74,.12);
        --invalid:        #dc2626;
        --invalid-bg:     #fef2f2;
        --invalid-ring:   rgba(220,38,38,.10);
    }

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    html { height: 100%; }

    body {
        font-family: 'DM Sans', sans-serif;
        color: var(--text);
        min-height: 100vh;
        /* Mobile-first: single column, card centered */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        background: #0d0404;
    }

    /* ── FULL BLEED BACKGROUND ── */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        z-index: 0;
        background:
            url('https://images.unsplash.com/photo-1635424239131-32dc44986b56?q=80&w=2070&auto=format&fit=crop')
            center / cover no-repeat;
        filter: blur(2px) brightness(0.65);
    }

    body::after {
        content: '';
        position: fixed;
        inset: 0;
        z-index: 1;
        background: linear-gradient(
            130deg,
            rgba(6,2,2,0.92) 0%,
            rgba(6,2,2,0.80) 45%,
            rgba(6,2,2,0.55) 100%
        );
    }

    /* ── DESKTOP LAYOUT WRAPPER ── */
    .page {
        position: relative;
        z-index: 2;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    /* ── LEFT PANEL — hidden on mobile/tablet, shown on desktop ── */
    .left {
        display: none; /* mobile-first: hidden */
        color: #fff;
    }

    /* ── RIGHT: the card — always visible ── */
    .right {
        width: 100%;
        max-width: 440px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ── CARD ── */
    .box {
        width: 100%;
        background: var(--surface);
        border-radius: var(--radius-xl);
        padding: 40px 36px 36px;
        box-shadow:
            0 0 0 1px rgba(0,0,0,0.06),
            0 20px 60px rgba(0,0,0,0.30),
            0 4px 16px rgba(0,0,0,0.12);
        position: relative;
        overflow: hidden;
        animation: rise .44s cubic-bezier(.22,1,.36,1) both;
    }

    /* brand-red top stripe */
    .box::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--brand) 0%, var(--brand-dark) 100%);
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    @keyframes rise {
        from { opacity:0; transform:translateY(20px) scale(0.97); }
        to   { opacity:1; transform:none; }
    }

    /* card brand row */
    .box-brand {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 26px;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--border);
    }

    .box-brand-icon {
        width: 38px; height: 38px;
        background: var(--brand);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 10px rgba(78,3,3,.30);
    }

    .box-brand-icon svg {
        width: 19px; height: 19px;
        stroke: #fff; fill: none;
        stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
    }

    .box-brand-text { line-height: 1.25; }

    .box-brand-name {
        font-family: 'Syne', sans-serif;
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: 0.2px;
    }

    .box-brand-name span { color: var(--brand); }

    .box-brand-sub {
        font-size: 10px;
        color: var(--text-sub);
        margin-top: 2px;
        letter-spacing: 0.2px;
    }

    /* heading */
    .box h2 {
        font-family: 'Syne', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 5px;
        line-height: 1.15;
    }

    .box .sub {
        font-size: 13px;
        color: var(--text-sub);
        margin-bottom: 26px;
    }

    .divider {
        height: 1px;
        background: var(--border);
        margin-bottom: 26px;
    }

    /* ── ERROR ── */
    .error-message {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 11px 14px;
        background: var(--brand-light);
        border: 1px solid rgba(78,3,3,.18);
        border-radius: var(--radius-md);
        color: var(--brand);
        font-size: 13px;
        margin-bottom: 20px;
        line-height: 1.5;
        animation: shake .38s ease;
    }

    @keyframes shake {
        0%,100% { transform:translateX(0); }
        20%      { transform:translateX(-5px); }
        40%      { transform:translateX(5px); }
        60%      { transform:translateX(-3px); }
        80%      { transform:translateX(3px); }
    }

    .error-message svg { width:15px; height:15px; flex-shrink:0; }

    /* ── FLOATING LABEL FIELD (email/LRN) ── */
    .username-field {
        position: relative;
        margin-bottom: 6px;
    }

    .username-field input {
        width: 100%;
        height: 54px;
        padding: 20px 38px 8px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: var(--text);
        background: #f8fafc;
        outline: none;
        -webkit-appearance: none;
        transition: border-color var(--t), box-shadow var(--t), background var(--t);
    }

    .username-field label {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: var(--text-sub);
        font-weight: 500;
        pointer-events: none;
        transition: top 140ms ease, transform 140ms ease, font-size 140ms ease,
                    color 140ms ease, letter-spacing 140ms ease;
    }

    .username-field input:focus ~ label,
    .username-field input:not(:placeholder-shown) ~ label {
        top: 11px;
        transform: translateY(0);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .username-field input:focus {
        border-color: var(--brand);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(78,3,3,.09);
    }

    .username-field input:focus ~ label { color: var(--brand); }

    .username-field.is-valid input   { border-color:var(--valid);   background:var(--valid-bg);   box-shadow:0 0 0 3px var(--valid-ring); }
    .username-field.is-valid label   { color:var(--valid); }
    .username-field.is-invalid input { border-color:var(--invalid); background:var(--invalid-bg); box-shadow:0 0 0 3px var(--invalid-ring); }
    .username-field.is-invalid label { color:var(--invalid); }

    .username-field .field-icon {
        position: absolute;
        right: 13px; top: 50%;
        transform: translateY(-50%) scale(.6);
        width: 16px; height: 16px;
        pointer-events: none;
        opacity: 0; fill: none;
        stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;
        transition: opacity 180ms ease, transform 180ms ease;
    }

    .username-field.is-valid   .icon-valid   { opacity:1; transform:translateY(-50%) scale(1); stroke:var(--valid); }
    .username-field.is-invalid .icon-invalid { opacity:1; transform:translateY(-50%) scale(1); stroke:var(--invalid); }

    .type-badge {
        position: absolute;
        right: 13px; top: 9px;
        font-size: 9px; font-weight: 700;
        letter-spacing: .5px; text-transform: uppercase;
        padding: 2px 7px; border-radius: 9999px;
        opacity: 0; transform: scale(.8);
        pointer-events: none;
        transition: opacity 160ms ease, transform 160ms ease;
    }

    .type-badge.show       { opacity:1; transform:scale(1); }
    .type-badge.email-type { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .type-badge.lrn-type   { background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; }

    .username-hint {
        font-size: 11px;
        margin-top: 5px;
        padding-left: 2px;
        min-height: 16px;
        opacity: 0;
        transition: color 160ms ease, opacity 160ms ease;
    }

    .username-field.is-valid   .username-hint { color:var(--valid);   opacity:1; }
    .username-field.is-invalid .username-hint { color:var(--invalid); opacity:1; }

    /* ── PASSWORD ── */
    .form-group { margin-bottom: 0; margin-top: 16px; }

    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-sub);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }

    .password-input { position: relative; }

    .password-input input {
        width: 100%;
        padding: 12px 44px 12px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        outline: none;
        background: #f8fafc;
        color: var(--text);
        -webkit-appearance: none;
        transition: border-color var(--t), box-shadow var(--t), background var(--t);
    }

    .password-input input:focus {
        border-color: var(--brand);
        background: var(--surface);
        box-shadow: 0 0 0 3px rgba(78,3,3,.09);
    }

    .toggle-password {
        position: absolute;
        right: 10px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none;
        cursor: pointer; color: #94a3b8;
        display: flex; align-items: center;
        padding: 6px; border-radius: 6px;
        transition: color var(--t), background var(--t);
    }

    .toggle-password:hover { color: var(--brand); background: var(--brand-light); }
    .toggle-password svg { width: 16px; height: 16px; }

    /* ── SUBMIT ── */
    .login-btn {
        width: 100%;
        height: 50px;
        border: none;
        border-radius: var(--radius-md);
        background: var(--brand);
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: .2px;
        cursor: pointer;
        margin-top: 24px;
        position: relative;
        overflow: hidden;
        transition: background var(--t), transform var(--t), box-shadow var(--t);
    }

    .login-btn::after {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
        transition: left .45s ease;
        pointer-events: none;
    }

    .login-btn:hover {
        background: var(--brand-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(78,3,3,.30);
    }

    .login-btn:hover::after { left: 150%; }
    .login-btn:active { transform: none; box-shadow: none; }

    .login-btn.ready {
        box-shadow: 0 2px 10px rgba(78,3,3,.22);
    }

    /* card footnote */
    .box-note {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
        text-align: center;
        font-size: 11.5px;
        color: var(--text-sub);
        line-height: 1.65;
    }

    /* ── LEFT PANEL CONTENT STYLES ── */
    .left-copy {
        max-width: 420px;
    }

    .left-logo {
        font-family: 'Syne', sans-serif;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        line-height: 1;
    }

    .left-logo span { color: var(--brand-dark); }

    .left-tagline {
        font-size: 10px;
        color: rgba(255,255,255,0.52);
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        margin-bottom: 36px;
    }

    .left-copy h2 {
        font-family: 'Syne', sans-serif;
        font-size: clamp(32px, 3.5vw, 48px);
        font-weight: 800;
        line-height: 1.10;
        margin-bottom: 16px;
        text-shadow: 0 2px 24px rgba(0,0,0,0.5);
    }

    .left-lead {
        font-size: 14px;
        color: rgba(255,255,255,0.70);
        line-height: 1.80;
        margin-bottom: 36px;
        max-width: 360px;
    }

    .brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.16);
        color: rgba(255,255,255,0.85);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        padding: 8px 18px;
        border-radius: 9999px;
        backdrop-filter: blur(6px);
    }

    .brand-pill i {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--brand-dark);
        display: inline-block;
        flex-shrink: 0;
        animation: blink 2s ease infinite;
    }

    @keyframes blink {
        0%,100% { opacity:1; }
        50%      { opacity:0.4; }
    }

    /* feature badges row */
    .left-badges {
        display: flex;
        gap: 10px;
        margin-top: 28px;
        flex-wrap: wrap;
    }

    .left-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 8px;
        color: rgba(255,255,255,0.75);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        backdrop-filter: blur(4px);
    }

    .left-badge svg {
        width: 12px; height: 12px;
        stroke: rgba(255,255,255,0.60);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        flex-shrink: 0;
    }

    /* ── BREAKPOINTS ── */

    /* Tablet and up: show left panel, side-by-side layout */
    @media (min-width: 1025px) {
        body {
            padding: 0;
            justify-content: stretch;
            flex-direction: row;
        }

        .page {
            display: grid;
            grid-template-columns: 1fr 520px;
            width: 100%;
            min-height: 100vh;
        }

        /* left: full height, flex column, content at bottom */
        .left {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 56px 60px;
            position: relative;
            z-index: 2;
        }

        /* right: frosted panel */
        .right {
            max-width: none;
            position: relative;
            z-index: 2;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            border-left: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 44px;
            min-height: 100vh;
        }

        .box { max-width: 420px; }
    }

    /* Mobile adjustments */
    @media (max-width: 480px) {
        body { padding: 20px 14px; }
        .box { padding: 32px 22px 28px; border-radius: 20px; }
        .box h2 { font-size: 22px; }
        .box-brand-name { font-size: 14px; }
    }
    </style>
</head>
<body>

<div class="page">

    <!-- LEFT PANEL — visible on desktop only (display:none on mobile via CSS) -->
    <div class="left">
        <div class="left-copy">
            <div class="left-logo">Gibraltar <span>AMES</span></div>
            <div class="left-tagline">Attendance Monitoring and Enrollment System</div>
            <h2>Welcome back!</h2>
            <p class="left-lead">Access student records, attendance, and grades in a secure portal built for educators and learners.</p>
            <div class="brand-pill"><i></i>Staff &amp; Student Portal</div>

            <div class="left-badges">
                <div class="left-badge">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Student Records
                </div>
                <div class="left-badge">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    Attendance
                </div>
                <div class="left-badge">
                    <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    Grades
                </div>
                <div class="left-badge">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Secure Access
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL — always visible -->
    <div class="right">
        <div class="box">

            <div class="box-brand">
                <div class="box-brand-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div class="box-brand-text">
                    <div class="box-brand-name">Gibraltar <span>AMES</span></div>
                    <div class="box-brand-sub">Attendance Monitoring &amp; Enrollment System</div>
                </div>
            </div>

            <h2>Sign In</h2>
            <p class="sub">Enter your credentials to continue</p>
            <div class="divider"></div>

            <?php if ($error): ?>
            <div class="error-message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>

                <!-- USERNAME — floating label + real-time validation -->
                <div class="username-field" id="field-username">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder=" "
                        autocomplete="username"
                        autocapitalize="none"
                        required
                    >
                    <label for="username">Email / LRN</label>

                    <span class="type-badge" id="type-badge"></span>

                    <svg class="field-icon icon-valid" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <svg class="field-icon icon-invalid" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>

                    <p class="username-hint" id="username-hint"></p>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="login-btn" id="login-btn">Log In</button>
            </form>

            <p class="box-note">
                Use your staff email address or your 12-digit LRN as a student.
            </p>

        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const input  = document.getElementById("password");
        const button = document.querySelector(".toggle-password");
        const hidden = input.type === "password";
        input.type   = hidden ? "text" : "password";
        button.innerHTML = hidden
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    }

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
            if (!val) { setState('', ''); badgeEl.className = 'type-badge'; return; }

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
                const left = 12 - val.length;
                if (val.length === 12) setState('is-valid', '12-digit LRN verified');
                else setState('is-invalid', left > 0
                    ? `${left} more digit${left === 1 ? '' : 's'} needed`
                    : 'LRN must be exactly 12 digits');
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