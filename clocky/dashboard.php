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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Clocky - Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #090909;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .page-transition {
            animation: smoothAppear 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes smoothAppear {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- FEJLÉC --- */
        .header-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            background: rgba(9, 9, 9, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle {
            background: #1a1a1a;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
        }

        .menu-toggle:hover {
            background: #00ffe1;
            color: #000;
        }

        .brand-logo {
            font-weight: 800;
            font-size: 26px;
            letter-spacing: -1.5px;
        }

        .brand-logo span { color: #00ffe1; }

        .admin-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #00ffe1;
            background: rgba(0, 255, 225, 0.1);
            padding: 8px 16px;
            border-radius: 50px;
            border: 1px solid rgba(0, 255, 225, 0.2);
        }

        /* --- KÖZPONTI TARTALOM --- */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 120px 20px 60px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 60px;
            width: 100%;
        }

        .welcome-text {
            font-size: clamp(40px, 8vw, 72px);
            font-weight: 800;
            letter-spacing: -3px;
            line-height: 1.1;
            margin-bottom: 10px;
        }

        .welcome-text span { color: #00ffe1; }

        .welcome-subtext {
            color: #888;
            font-size: clamp(16px, 2vw, 18px);
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
        }

        /* --- RESPONZÍV KÁRTYA RÁCS --- */
        .dashboard-grid {
            display: grid;
            width: 100%;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 580px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 992px) {
            .dashboard-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .menu-card {
            background: #121212;
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 40px 20px;
            border-radius: 32px;
            text-decoration: none;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
        }

        .menu-card i {
            font-size: 38px;
            color: #00ffe1;
            background: rgba(0, 255, 225, 0.05);
            width: 75px;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            transition: 0.3s;
        }

        .menu-card h3 {
            font-weight: 700;
            font-size: 17px;
            color: #fff;
            line-height: 1.3;
        }

        .menu-card:hover {
            transform: translateY(-12px);
            background: #161616;
            border-color: rgba(0, 255, 225, 0.3);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        .menu-card:hover i {
            background: #00ffe1;
            color: #000;
            transform: scale(1.1);
        }

        .logout-card:hover { border-color: rgba(255, 71, 87, 0.4); }
        .logout-card i { color: #ff4757; background: rgba(255, 71, 87, 0.05); }
        .logout-card:hover i { background: #ff4757; color: #fff; }

        /* --- OLDALMENÜ --- */
        nav {
            position: fixed;
            top: 0; bottom: 0; left: -100%; width: 300px; 
            background: #0f0f0f;
            z-index: 1200;
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 100px 20px 20px;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        nav.open { left: 0; }
        nav ul { list-style: none; }
        nav ul li a {
            text-decoration: none;
            color: #888;
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 8px;
            transition: 0.3s;
        }

        nav ul li a:hover, nav ul li a.active {
            background: rgba(0, 255, 225, 0.1);
            color: #00ffe1;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
            z-index: 1150;
        }
        .overlay.active { display: block; }

        .hamburger-lines { display: flex; flex-direction: column; gap: 4px; }
        .bar { width: 18px; height: 2px; background-color: currentColor; border-radius: 2px; }

        /* --- MOBIL FIX --- */
        @media (max-width: 600px) {
            .header-admin-info { display: none; }
            /* Biztosítjuk, hogy a Menü felirat ne tűnjön el, ha a gombon belül van */
            .menu-toggle span { display: block !important; } 
            .header-left { gap: 12px; }
            .header-top { padding: 15px 20px; }
            .welcome-text { font-size: 42px; letter-spacing: -1.5px; }
            .main-content { padding-top: 140px; justify-content: flex-start; }
            .menu-card { padding: 30px 15px; }
        }
    </style>
</head>
<body class="page-transition">

<div id="overlay" class="overlay" onclick="toggleMenu()"></div>

<header class="header-top">
    <div class="header-left">
        <button class="menu-toggle" onclick="toggleMenu()" aria-label="Menü">
            <div class="hamburger-lines">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            <span>Menü</span>
        </button>
        <div class="brand-logo">Clocky<span>.</span></div>
    </div>
    
    <div class="header-admin-info">
        <div class="admin-status">
            <i class="fas fa-shield-halved"></i> Admin Jogosultság
        </div>
    </div>
</header>

<main class="main-content">
    <div class="welcome-section">
        <h1 class="welcome-text">Üdvözöljük<span>!</span></h1>
        <p class="welcome-subtext">Válasszon az alábbi menüpontok közül a rendszer kezeléséhez.</p>
    </div>

    <div class="dashboard-grid">
        <a href="dashboard.php" class="menu-card">
            <i class="fas fa-gauge-high"></i>
            <h3>Kezdőlap</h3>
        </a>
        <a href="emp.php" class="menu-card">
            <i class="fas fa-user-group"></i>
            <h3>Dolgozók</h3>
        </a>
        <a href="roles.php" class="menu-card">
            <i class="fas fa-briefcase"></i>
            <h3>Munkakörök</h3>
        </a>
        <a href="add_emp.php" class="menu-card">
            <i class="fas fa-user-plus"></i>
            <h3>Munkatárs Hozzáadás</h3>
        </a>
        <a href="add_role.php" class="menu-card">
            <i class="fas fa-plus-square"></i>
            <h3>Új Munkakör</h3>
        </a>
        <a href="list.php" class="menu-card">
            <i class="fas fa-list-check"></i>
            <h3>Munkaidő Napló</h3>
        </a>
        <a href="archivum.php" class="menu-card">
            <i class="fas fa-box-archive"></i>
            <h3>Archívum</h3>
        </a>
        <a href="logout.php" class="menu-card logout-card">
            <i class="fas fa-right-from-bracket"></i>
            <h3>Kijelentkezés</h3>
        </a>
    </div>
</main>

<nav id="mainNav">
    <ul>
        <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Kezdőlap</a></li>
        <li><a href="emp.php"><i class="fas fa-users"></i> Dolgozók</a></li>
        <li><a href="roles.php"><i class="fas fa-briefcase"></i> Munkakörök</a></li>
        <li><a href="add_emp.php"><i class="fas fa-user-plus"></i> Munkatárs Hozzáadás</a></li>
        <li><a href="add_role.php"><i class="fas fa-plus-square"></i> Munkakör Hozzáadása</a></li>
        <li><a href="archivum.php"><i class="fas fa-archive"></i> Archívum</a></li>
        <li><a href="list.php"><i class="fas fa-list"></i> Munkaidő Napló</a></li>
        <li style="margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
            <a href="logout.php" style="color: #ff4757;"><i class="fas fa-sign-out-alt"></i> Kijelentkezés</a>
        </li>
    </ul>
</nav>

<script>
    function toggleMenu() {
        const nav = document.getElementById('mainNav');
        const overlay = document.getElementById('overlay');
        nav.classList.toggle('open');
        overlay.classList.toggle('active');
        document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : 'auto';
    }
</script>
</body>
</html>