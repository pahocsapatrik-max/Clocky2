<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$request_uri = $_SERVER['REQUEST_URI'];
$current_file = basename($_SERVER['PHP_SELF']);

$routes = [
    'dashboard.php'   => 'kezdolap',
    'index.php'       => 'bejelentkezes',
    'emp.php'         => 'dolgozok',
    'roles.php'       => 'munkakorok',
    'list.php'        => 'lista',
    'archivum.php'    => 'archivum',
    'logout.php'      => 'bejelentkezes',
    'add_role.php'    => 'uj-munkakor',
    'add_emp.php'     => 'uj-dolgozo',
    'empdashboard.php'=> 'pult'
];

if (strpos($request_uri, '.php') !== false && !isset($_GET['api'])) {
    if (isset($routes[$current_file])) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . $routes[$current_file]);
        exit();
    }
}

function get_clocky_styles() {
    return "
    <style>
        :root {
            --bg-color: #0f0f0f;
            --card-bg: #1a1a1a;
            --accent-color: #00ffe1;
            --text-secondary: #888;
            --danger-color: #ff4757;
        }
    </style>";
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clocky - Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        /* Reset & Alapok */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f0f;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* --- ANIMÁCIÓ --- */
        .page-transition {
            animation: smoothAppear 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        @keyframes smoothAppear {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Fejléc és Logó */
        .header-top {
            position: fixed;
            top: 20px;
            left: 20px;
            right: 20px; /* Mobilnál fontos a szélességhez */
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
        }

        .menu-toggle {
            background: #1a1a1a;
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }

        .menu-toggle:hover {
            background: #00ffe1;
            color: #000;
            transform: translateY(-2px);
        }

        .brand-logo {
            font-weight: 800;
            font-size: 26px;
            letter-spacing: -1.5px;
            color: #fff;
        }

        .brand-logo span {
            color: #00ffe1;
        }

        /* Középre igazított tartalom */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 80px 20px 20px 20px; /* Felső padding a fix fejléc miatt */
        }

        .welcome-box {
            width: 100%;
            max-width: 600px;
        }

        .welcome-text {
            font-weight: 800;
            font-size: clamp(32px, 10vw, 64px); 
            letter-spacing: -2px;       
            color: #fff;
            margin-bottom: 10px;
            line-height: 1.1;
        }

        .welcome-text span {
            color: #00ffe1;
        }

        .admin-badge {
            font-weight: 800;
            font-size: clamp(12px, 3vw, 14px);
            letter-spacing: 0.5px;
            color: #00ffe1;
            background: rgba(0, 255, 225, 0.05);
            padding: 8px 20px;
            border-radius: 100px; 
            display: inline-block;
            border: 1px solid rgba(0, 255, 225, 0.2);
            margin-top: 15px;
        }

        /* Navigáció */
        nav {
            position: fixed;
            top: 0;
            bottom: 0;
            left: -100%; /* Teljes elrejtés mobilra */
            width: 290px; 
            background: linear-gradient(145deg, #161616 0%, #0a0a0a 100%);
            z-index: 1200;
            overflow-y: auto;
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 20px 0 40px rgba(0, 0, 0, 0.7);
            padding-top: 80px;
            border-right: 1px solid rgba(0, 255, 225, 0.1);
        }

        nav.open {
            left: 0;
        }

        nav ul {
            list-style: none;
            padding: 0 15px;
        }

        nav ul li a {
            text-decoration: none;
            color: #888;
            font-weight: 800;
            font-size: 15px;
            letter-spacing: -0.5px;
            padding: 14px 18px;
            border-radius: 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 5px;
        }

        nav ul li a i {
            font-size: 18px;
            width: 25px;
            text-align: center;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: rgba(0, 255, 225, 0.1);
            color: #00ffe1;
        }

        nav ul li.logout {
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 15px;
        }
        
        nav ul li.logout a {
            color: #ff4757;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(4px);
            z-index: 1150;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }

        .hamburger-lines {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .bar {
            width: 18px;
            height: 2px;
            background-color: currentColor;
            border-radius: 2px;
        }

        /* --- TABLET ÉS ASZTALI FINOMÍTÁSOK --- */
        @media (min-width: 768px) {
            nav {
                left: -320px;
                border-radius: 0 30px 30px 0;
                top: 10px;
                bottom: 10px;
            }
            nav ul li a:hover {
                transform: translateX(10px);
            }
            .header-top {
                left: 30px;
                top: 30px;
            }
        }

        /* Mobil specifikus gomb felirat elrejtés kis kijelzőn */
        @media (max-width: 400px) {
            .menu-toggle span {
                display: none;
            }
            .menu-toggle {
                padding: 12px;
            }
        }
    </style>
</head>
<body class="page-transition">

<div id="overlay" class="overlay" onclick="toggleMenu()"></div>

<header class="header-top">
    <button class="menu-toggle" onclick="toggleMenu()" aria-label="Menü megnyitása">
        <div class="hamburger-lines">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        <span>Menü</span>
    </button>
    <div class="brand-logo">Clocky<span>.</span></div>
</header>

<main class="main-content">
    <div class="welcome-box">
        <h1 class="welcome-text">Üdvözöljük<span>!</span></h1>
        <p class="admin-badge">
            <i class="fas fa-shield-alt"></i> Önnek admin jogosultságai vannak
        </p>
    </div>
</main>

<nav id="mainNav">
    <ul>
        <li><a href="dashboard.php" class="<?php echo (isset($page_title) && $page_title=='Dashboard')?'active':''; ?>"><i class="fas fa-home"></i> Kezdőlap</a></li>
        <li><a href="emp.php" class="<?php echo (isset($page_title) && $page_title=='Employees')?'active':''; ?>"><i class="fas fa-users"></i> Dolgozók</a></li>
        <li><a href="roles.php" class="<?php echo (isset($page_title) && $page_title=='Roles')?'active':''; ?>"><i class="fas fa-briefcase"></i> Munkakörök</a></li>
        <li><a href="add_emp.php" class="<?php echo (isset($page_title) && $page_title=='addemp')?'active':''; ?>"><i class="fas fa-user-plus"></i> Hozzáadás</a></li>
        <li><a href="add_role.php" class="<?php echo (isset($page_title) && $page_title=='addrole')?'active':''; ?>"><i class="fas fa-plus-square"></i> Új munkakör</a></li>
        <li><a href="archivum.php" class="<?php echo (isset($page_title) && $page_title=='archivum')?'active':''; ?>"><i class="fas fa-archive"></i> Archívum</a></li>
        <li><a href="list.php" class="<?php echo (isset($page_title) && $page_title=='lista')?'active':''; ?>"><i class="fas fa-list"></i> Lista</a></li>
        
        <li class="logout"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Kijelentkezés</a></li>
    </ul>
</nav>

<script>
    function toggleMenu() {
        const nav = document.getElementById('mainNav');
        const overlay = document.getElementById('overlay');
        nav.classList.toggle('open');
        overlay.classList.toggle('active');
        
        // Tiltjuk a görgetést a háttérben, ha nyitva a menü
        if (nav.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    }

    // Oldalváltás animáció
    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.href.includes('#') && !this.getAttribute('target')) {
                document.body.style.opacity = '0';
                document.body.style.transition = 'opacity 0.4s ease';
            }
        });
    });
</script>
</body>
</html>