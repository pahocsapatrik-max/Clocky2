<?php
/**
 * Clocky - Biztonságos Kijelentkezés
 */
session_start();

// 1. Összes session változó törlése a memóriából
$_SESSION = array();

// 2. A session süti (cookie) érvénytelenítése a böngészőben
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. A szerver oldali session fájl megsemmisítése
session_destroy();

// 4. Gyorsítótár ürítése (hogy a 'Vissza' gombbal ne láthasson érzékeny adatot)
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// 5. Átirányítás a bejelentkező oldalra (a .htaccess szerinti szép néven)
header('Location: bejelentkezes');
exit();
?>