
<?php
session_start();
include '../config/config.php';
include '../functions/oop.php';

$oop = new oopPHP($pdo);

$error = '';

if (isset($_POST['login_faculty'])) {
    $oop->loginFaculty($_POST['email'], $_POST['password']);
}

if (isset($_POST['login_parent'])) {
    $oop->loginParent($_POST['lrn'], $_POST['pin']);
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · Gibraltar AMES</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
           :root {
            --brand:        #4e0303;
            --brand-dark:   #ec3f3f;
            --brand-light:  #e8f0f7;
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
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body { font-family: 'DM Sans', sans-serif; background: var(--canvas); color: var(--text); display: flex; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }

        /* ── LEFT PANEL ── */
        .left {
            flex: 1;
            position: relative;
            background:
                url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1200&q=80') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 25px 25px;
            min-height: 100vh;
        }

        /* Top: back link */
        .left-back {
            display: inline-flex;
            align-items: center;
            gap: px;
            font-size: 13px;
            font-weight: 500;
            color: white;
            background: #4e0303;
            border: 1px solid var(--brand);
            border-radius: var(--radius-sm);
            padding: 7px 14px;
            width: fit-content;
            transition: background var(--transition), color var(--transion), border-color var(--transition);
        }
        .left-back:hover { background: #ec3f3f; color: white; border-color: black; }
        .left-back svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; }

        /* Bottom: copy */
        .left-copy { color: #fff; }
        .left-logo {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 28px;
        }
        .left-logo-box {
            width: 42px; height: 42px;
            background: var(--brand);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .left-logo-box img { width: 42px; height: 42px; object-fit: contain; }
        .left-logo-name { font-size: 14px; font-weight: 700; color: #fff; }
        .left-copy h2 {
            font-size: clamp(22px, 2.8vw, 32px);
            font-weight: 700; line-height: 1.2;
            margin-bottom: 12px;
        }
        .left-copy h2 span { color: #4e0303; }
        .left-copy p { font-size: 14px; color: rgba(255,255,255,0.68); line-height: 1.65; max-width: 340px; }

        /* Badges row */
        .left-badges {
            display: flex; gap: 8px; flex-wrap: wrap;
            margin-top: 24px;
        }
        .left-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 600;
            color: rgba(255,255,255,0.80);
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 9999px;
            padding: 4px 11px;
            letter-spacing: 0.3px;
        }
        .left-badge svg { width: 11px; height: 11px; stroke: currentColor; fill: none; stroke-width: 2; }

        /* ── RIGHT PANEL ── */
        .right {
            width: 480px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: var(--surface);
        }

        .box {
            width: 100%;
            max-width: 380px;
            animation: fadeUp 0.5s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .box-header { margin-bottom: 28px; }
        .box-header h2 { font-size: 24px; font-weight: 700; margin-bottom: 4px; letter-spacing: -0.3px; }
        .box-header p  { font-size: 13px; color: var(--muted); }

        /* Tabs */
        .tabs {
            display: flex;
            background: var(--canvas);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 4px;
            margin-bottom: 26px;
            gap: 4px;
        }
        .tab {
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px;
            padding: 9px 12px;
            font-size: 13px; font-weight: 600;
            color: var(--muted);
            border-radius: var(--radius-sm);
            cursor: pointer; border: none; background: none;
            font-family: 'DM Sans', sans-serif;
            transition: background var(--transition), color var(--transition), box-shadow var(--transition);
        }
        .tab svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 1.8; }
        .tab.active {
            background: var(--surface);
            color: var(--brand);
            box-shadow: var(--shadow-sm);
        }

        /* Form panels */
        .form-panel { display: none; }
        .form-panel.active { display: block; }

        /* Fields */
        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            font-size: 11px; font-weight: 700;
            color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .inp-wrap { position: relative; }
        .inp-wrap input {
            width: 100%;
            padding: 11px 38px 11px 13px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px; color: var(--text);
            background: var(--canvas);
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
        }
        .inp-wrap input:focus {
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(21,96,168,0.10);
        }
        .inp-wrap input::placeholder { color: #b0b8c4; }
        .inp-icon {
            position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
            color: var(--muted); cursor: pointer;
            display: flex; align-items: center;
        }
        .inp-icon svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.8; }

        .field-hint {
            font-size: 11px; color: var(--muted);
            margin-top: 5px; line-height: 1.5;
        }

        /* Forgot row */
        .forgot {
            text-align: right; margin-top: -8px; margin-bottom: 18px;
        }
        .forgot a { font-size: 12px; color: var(--brand); font-weight: 600; }
        .forgot a:hover { color: var(--brand-dark); }

        /* Submit button */
        .btn-submit {
            width: 100%; padding: 12px;
            background: var(--brand); color: #fff;
            font-size: 14px; font-weight: 700;
            border-radius: var(--radius-sm); border: none; cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
            margin-top: 4px;
        }
        .btn-submit:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(21,96,168,0.28);
        }

        /* Error alert */
        .alert-error {
            display: flex; align-items: center; gap: 8px;
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #dc2626;
            font-size: 13px;
            padding: 10px 13px;
            border-radius: var(--radius-md);
            margin-bottom: 18px;
        }
        .alert-error svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; flex-shrink: 0; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 10px;
            margin: 20px 0 16px;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .divider span { font-size: 11px; font-weight: 600; color: var(--muted); letter-spacing: 0.5px; text-transform: uppercase; }

        
        /* Footer */
        .box-footer {
            margin-top: 22px; text-align: center;
            font-size: 12px; color: var(--muted);
            border-top: 1px solid var(--border);
            padding-top: 16px;
        }
        .box-footer a { color: var(--brand); font-weight: 600; }
        .box-footer a:hover { color: var(--brand-dark); }

        /* Responsive */
        @media (max-width: 820px) {
            .left { display: none; }
            .right { width: 100%; }
            body { background: var(--canvas); }
        }
    </style>
</head>
<body>

<!-- LEFT: image panel matching index style -->
<div class="left">
    <a href="index.php" class="left-back">
        <svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 5-7 7 7 7"/></svg>
        Back to Home
    </a>

    <div class="left-copy">
        
        <h2>Welcome back to<br><span>Gibraltar AMES</span></h2>
        <p>Access your portal to view grades, announcements, school calendar, and more — all in one place.</p>

        
    </div>
</div>

<!-- RIGHT: login form -->
<div class="right">
    <div class="box">

        <div class="box-header">
            <h2>Sign In</h2>
            <p>Choose your role and enter your credentials</p>
        </div>

        <?php if ($error): ?>
        <div class="alert-error">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs" role="tablist">
            <button class="tab active" onclick="switchTab('faculty', this)" role="tab">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                Staff
            </button>
            <button class="tab" onclick="switchTab('parent', this)" role="tab">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Parent / Guardian
            </button>
        </div>

        <!-- Faculty Form -->
        <div id="panel-faculty" class="form-panel active">
            <form method="POST">
                <div class="field">
                    <label>Email Address</label>
                    <div class="inp-wrap">
                        <input type="email" name="email" placeholder="yourname@gibraltar.edu.ph" required autocomplete="username">
                        <span class="inp-icon">
                            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="inp-wrap">
                        <input type="password" id="pw-faculty" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <span class="inp-icon" onclick="togglePw('pw-faculty', this)">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                    </div>
                </div>

                <div class="forgot"><a href="forgot_password.php">Forgot password?</a></div>

                <button type="submit" name="login_faculty" class="btn-submit">Sign In as Faculty</button>
            </form>
        </div>

        <!-- Parent Form -->
        <div id="panel-parent" class="form-panel">
          

            <form method="POST">
                <div class="field">
                    <label>LRN Number</label>
                    <div class="inp-wrap">
                        <input type="text" name="lrn" placeholder="12-digit LRN (e.g. 123456789012)" maxlength="12" pattern="\d{12}" required autocomplete="off">
                        
                    </div>
                    <p class="field-hint">Enter the 12-digit LRN found on your child's report card.</p>
                </div>

                <div class="field">
                    <label>Parental PIN</label>
                    <div class="inp-wrap">
                        <input type="password" id="pw-parent" name="pin" placeholder="••••••" maxlength="6" required autocomplete="off">
                        <span class="inp-icon" onclick="togglePw('pw-parent', this)">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                    </div>
                    <p class="field-hint">Contact the registrar if you have not yet set your PIN.</p>
                </div>

                <button type="submit" name="login_parent" class="btn-submit">Sign In as Parent</button>
            </form>
        </div>

        <div class="box-footer">
            Need help? <a href="contact.php">Contact the school registrar</a>
        </div>

    </div>
</div>

<script>
function switchTab(panel, btn) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('panel-' + panel).classList.add('active');
}

function togglePw(id, el) {
    const input = document.getElementById(id);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    el.innerHTML = isHidden
        ? '<svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.8"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
</script>
</body>
</html>