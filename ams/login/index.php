<?php
include 'auth.php';

$error = '';
if (is_logged_in()) {
    header('Location: ' . redirect_to_dashboard($_SESSION['role']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $role = login_user($username, $password);

        if ($role === false) {
            $error = 'Invalid username or password.';
        } else {
            header('Location: ' . redirect_to_dashboard($role));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../style/style.css">
    <title>Gibraltar AMS Login</title>
    <style>
        .index-section {
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }
        .index-wrapper {
            width: 100%;
            max-width: 380px;
        }
        .index-wrapper .card {
            text-align: center;
        }
        .index-wrapper h2 {
            font-size: 1.1rem;
            color: #1560a8;
            margin-bottom: 4px;
        }
        .index-wrapper p.subtitle {
            font-size: 0.85rem;
            color: #666;
            margin: 0 0 20px;
        }
        .index-wrapper hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 16px 0;
        }
        .a-button {
            background: #1560a8;
            color: white;
            padding: 11px 20px;
            margin: 6px 0;
            font-size: 0.93rem;
        }
        .a-button:hover {
            background: #0e4a80;
        }
        .apply-1 {
            background: #f5f5f5;
            color: #1560a8;
            border: 1.5px solid #1560a8;
            padding: 11px 20px;
            margin: 6px 0;
            font-size: 0.93rem;
        }
        .apply-1:hover {
            background: #e8f0f7;
        }
        .section-label {
            font-size: 0.85rem;
            font-weight: 1000;
            color: #333;
            background: #f0f0f0;
            margin: 0 0 14px;
            padding: 6px 12px;
            text-align: center;
            border-radius: 7px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .error-message {
            color: #b00020;
            background: #fdecea;
            border: 1px solid #f5c6cb;
            padding: 10px 14px;
            margin-bottom: 16px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<header>
    <h2>Gibraltar - AMS</h2>
    <img src="../style/logo.png" alt="Logo" class="logo">
</header>

<section class="index-section">
    <div class="index-wrapper">
        <div class="container">
            <div class="card">
                <img src="../style/logo.png" class="logo" alt="Logo">
                <h2>Gibraltar AMS</h2>
                <?php if ($error !== ''): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <br>
                    <button type="submit" class="btn">Login</button>
                </form>
            </div>
        </div>
    </div>
</section>

</body>
</html>
