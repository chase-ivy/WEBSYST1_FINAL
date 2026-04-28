<?php ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Gibraltar AMS</title>
    <link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <style>
        .pass-card {
            position: relative;
            width: 100%;
        }
        .pass-card input {
            padding-right: 40px;
        }
        .eye-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: #888;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

<header>
    <h2>Gibraltar - AMS</h2>
    <img src="logo.png" alt="Logo" class="logo">
</header>

<section>
    <div class="grid">
        <div class="card">
            <h2 class="h2">Gibraltar Elementary Management System</h2>

            <form>
                <div class="card">
                    <h4 style="margin: 5px">Log In</h4>
                    <nav class="nav-card">
                        <a href="index.php" class="select">Change Form Access <</a><br><br>
                        <p><strong>Teacher Module</strong></p>
                    </nav>

                    <span>Username:</span>
                    <input type="number" name="lrn"><br><br>

                    <span>Password:</span>
                    <div class="pass-card">
                        <input type="password" name="password" id="pass">
                        <button type="button" class="eye-toggle" id="eyeBtn">
                            <i class="bx bx-hide" id="eyeIcon"></i>
                        </button>
                    </div>

                    <button class="log">Log In</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    const eyeBtn    = document.getElementById('eyeBtn');
    const eyeIcon   = document.getElementById('eyeIcon');
    const passInput = document.getElementById('pass');

    eyeBtn.addEventListener('click', function () {
        const isHidden = passInput.type === 'password';
        passInput.type    = isHidden ? 'text' : 'password';
        eyeIcon.className = isHidden ? 'bx bx-show' : 'bx bx-hide';
    });
</script>

</body>
</html>