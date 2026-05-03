<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Gibraltar AMES</title>
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
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--canvas);
            color: var(--text);
            display: flex;
            flex-direction: column;
        }
        a { text-decoration: none; color: inherit; }

        /* ── HEADER ── */
        header {
            background: var(--brand-dark);
            padding: 20px 20px;
            text-align: center;
            font-size: 12px;
            background-color: #4e0303;
        }
        header a { color: #000000; font-weight: 600; }
        header a:hover { color: #fff; }

        /* ── NAV ── */
        nav {
            position: absolute;
            top: 40px; /* matches header height */
            left: 0; right: 0;
            z-index: 100;
        }
        .nav-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 32px;
        height: 68px;
        
        /* REMOVE colored background */
        background: transparent;

        border-bottom: 1px solid rgba(255,255,255,0.10);
        transition: background var(--transition), box-shadow var(--transition);
}
        .nav-inner:hover {
            background: rgba(255,255,255,0.96);
            box-shadow: var(--shadow-md);
        }

        /* Logo */
        .nav-logo { display: flex; align-items: center; gap: 10px; }
    
        .logo-name {
            font-size: 14px; font-weight: 700;
            color: #fff;
            transition: color var(--transition);
        }

        .logo-box {
        width: 70px;
        height: 70px;
        flex-shrink: 0;
}
        .logo-box img {
        width: 70px;
        height: 70px;
        object-fit: contain;
}
        .nav-inner:hover .logo-name { color: var(--text); }

        /* Nav links — white, turn dark on nav hover */
        .nav-links { display: flex; align-items: center; gap: 2px; list-style: none; }
        .nav-links > li { position: relative; }
        .nav-links a {
            padding: 8px 13px;
            font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,0.88);
            border-radius: var(--radius-sm);
            transition: background var(--transition), color var(--transition);
        }
        .nav-inner:hover .nav-links a { color: var(--muted); }
        .nav-links a:hover,
        .nav-inner:hover .nav-links a:hover { background: #f0f4fc; color: var(--text); }

        /* Dropdown trigger button */
        .nav-drop-btn {
            display: flex; align-items: center; gap: 4px;
            padding: 8px 13px;
            font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,0.88);
            border-radius: var(--radius-sm);
            background: none; border: none; cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background var(--transition), color var(--transition);
        }
        .nav-inner:hover .nav-drop-btn { color: var(--muted); }
        .nav-drop-btn:hover,
        .nav-inner:hover .nav-drop-btn:hover { background: #f0f4fc; color: var(--text); }
        .nav-drop-btn svg {
            width: 13px; height: 13px;
            stroke: currentColor; fill: none; stroke-width: 2.2;
            transition: transform var(--transition);
        }
        .nav-links li:hover .nav-drop-btn svg { transform: rotate(180deg); }

        /* Dropdown panel */
        .dropdown {
            position: absolute; top: 100%; right: 0;
            min-width: 190px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 6px;
            /* Padding-top creates a hover bridge so the gap doesn't break hover */
            padding-top: 14px;
            margin-top: -6px; /* pull it up so visible gap stays small */
            opacity: 0; pointer-events: none;
            transform: translateY(-4px);
            transition: opacity var(--transition), transform var(--transition);
            z-index: 200;
        }
        .nav-links li:hover .dropdown { opacity: 1; pointer-events: auto; transform: translateY(0); }
        .dropdown a {
            display: block; padding: 9px 12px;
            font-size: 13px; color: var(--muted);
            border-radius: var(--radius-md);
            transition: background var(--transition), color var(--transition);
        }
        .dropdown a:hover { background: #f0f4fc; color: var(--text); }
        .dropdown hr { border: none; border-top: 1px solid var(--border); margin: 4px 0; }

        /* Login button */
        .btn-login {
            padding: 8px 18px;
            background: var(--brand); color: #fff;
            font-size: 13px; font-weight: 600;
            border-radius: var(--radius-sm);
            border: none; cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background var(--transition), transform var(--transition);
        }
        .btn-login:hover { background: var(--brand-dark); transform: translateY(-1px); }

        /* ── HERO ── */
        .hero {
            position: relative;
            min-height: 800px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }

        .hero-bg {
        position: absolute;
        inset: 0;
        overflow: hidden;
}

        .hero-bg::before {
        content: "";
        position: absolute;
        inset: -0.1%;
        background-image: url('https://scontent.fcrk2-4.fna.fbcdn.net/v/t39.30808-6/476068719_935032598807887_1545626344390710672_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=b895b5&_nc_eui2=AeGagfxKFSkTG6bRcoGUU53FcaOZtEVwD2txo5m0RXAPa9tbDJsUXJwF934yBe0LWmrqgdK0MYjA-6XvB-jbYj4p&_nc_ohc=su0yKzqup5MQ7kNvwGShvG-&_nc_oc=AdpCY_oXn0aZAFRL4WvXXj9Hwth6BT1T_oXAnHfwCCkS6GMhPFTroDlOix21a9HjPtQ&_nc_zt=23&_nc_ht=scontent.fcrk2-4.fna&_nc_gid=oesTFn8J_wAMisWSaFsi1w&_nc_ss=7b2a8&oh=00_Af2QEEm04znDlJmWljdrGEyNhybgyJVSvfyVkD0zH1DmXA&oe=69F86B77');
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        transform: scale(1);
        transform-origin: center;
}

      
        .hero-content {
            position: relative; z-index: 10;
            text-align: center; padding: 80px 20px 0;
            animation: fadeUp 0.6s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .hero h1 {
            font-size: clamp(28px, 4.5vw, 50px);
            font-weight: 700; color: #ffffff;;
            line-height: 1.2; margin-bottom: 14px;
        }
        .hero h1 span { color: #4e0303; }
        .hero p {
            font-size: 16px; color: rgba(255,255,255,0.78);
            max-width: 500px; margin: 0 auto 28px; line-height: 1.65;
        }
        .hero-btns { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn-primary {
            padding: 11px 24px;
            background: var(--brand); color: #fff;
            font-size: 14px; font-weight: 600;
            border-radius: var(--radius-sm);
            transition: background var(--transition), transform var(--transition);
        }
        .btn-primary:hover { background: var(--brand-dark); transform: translateY(-1px); }
        .btn-outline {
            padding: 10px 22px;
            background: rgba(255,255,255,0.12); color: #fff;
            font-size: 14px; font-weight: 500;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(255,255,255,0.30);
            transition: background var(--transition), transform var(--transition);
        }
        .btn-outline:hover { background: rgba(255,255,255,0.22); transform: translateY(-1px); }

        /* ── FOOTER — flush, no trailing whitespace ── */
        footer {
            background: #4e0303;
            color: rgba(255,255,255,0.55);
            text-align: center;
            padding: 20px 20px;
            font-size: 12px;
            border-top: 3px solid var(--brand);
            margin-top: auto;
        }
        footer strong { color: rgba(255,255,255,0.85); }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    
</header>

<!-- NAV: logo left, links right, white → black on hover, dropdowns stay open when moving into them -->
<nav>
    <div class="nav-inner">

        <a href="index.php" class="nav-logo">
            <div class="logo-box">
             <img src="../style/logo.png" alt="Logo">
            </div>
            <span class="logo-name">Gibraltar Elementary School</span>
        </a>

        <ul class="nav-links">

            <li><a href="index.php">Home</a></li>

            <li>
                <button class="nav-drop-btn">
                    About Us
                    <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="dropdown">
                    <a href="mission.php">Our Mission</a>
                    <a href="history.php">School History</a>
                </div>
            </li>

            <li>
                <button class="nav-drop-btn">
                    More
                    <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="dropdown">
                    <a href="contact.php">Contact Us</a>
                    <a href="calendar.php">School Calendar</a>
                    <a href="announcements.php">Announcements</a>
                </div>
            </li>

            <li><button class="btn-login" onclick="location.href='login.php'">Login</button></li>

        </ul>
    </div>
</nav>

<!-- HERO: background image, nav overlaps it from top -->
<div class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <h1>ANO LALAGAY<br><span>ANO LALAGAY</span></h1>
        <p>Gibraltar Elementary School nurtures young minds with quality education, a safe environment, and a community that cares.</p>
        <div class="hero-btns">
            <a href="enrollment.php" class="btn-primary">Register Now</a>
        </div>
    </div>
</div>

<footer>
    &copy; 2025 <strong>Gibraltar Elementary School — AMES</strong>. All rights reserved. Baguio City, Philippines.
</footer>

</body>
</html>