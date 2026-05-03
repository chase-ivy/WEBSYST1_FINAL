
<?php
session_start();
include '../config/config.php';

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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" href="login_style.css">
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