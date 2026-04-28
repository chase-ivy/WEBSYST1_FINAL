<?php ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Gibraltar AMS</title>
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
}


    </style>
</head>
<body>

<header>
    <h2>Gibraltar - AMS</h2>
    <img src="logo.png" alt="Logo" class="logo">
</header>

<section class="index-section">
    <div class="index-wrapper">
        <div class="card">
            <h2>Gibraltar Elementary School</h2>
            <p class="subtitle">Academic Management System</p>

            <hr>
            <p class="section-label">Choose Login Access Form</p>

            <a href="unknown.php" class="a-button">Admin</a>
            <a href="teacher.php" class="a-button">Teacher</a>
            <a href="student.php" class="a-button">Current Student</a>

            <hr>

            <a href="enrollment.php" class="apply-1">Apply as a New Student</a>
        </div>
    </div>
</section>

</body>
</html>