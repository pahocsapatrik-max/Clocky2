<?php
/**
 * Clocky - Univerzális Konfigurációs Fájl
 * Kompatibilis: XAMPP, Docker, Éles szerver
 */

// 1. ADATBÁZIS BEÁLLÍTÁSOK
// Docker környezeti változók preferálása, XAMPP fallback értékekkel
$servername = getenv('DB_HOST')     ?: "localhost";
$dbname     = getenv('DB_NAME')     ?: "clocky";
$username   = getenv('DB_USER')     ?: "patriiik";
$password   = getenv('DB_PASS')     ?: "patriiik30";

// Kapcsolódás létrehozása
$conn = new mysqli($servername, $username, $password, $dbname);

// Hibaellenőrzés
if ($conn->connect_error) {
    // Csak fejlesztés alatt írassuk ki a pontos hibát, élesben elég a "Hiba" üzenet
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Karakterkódolás kényszerítése az ékezetek miatt
$conn->set_charset("utf8mb4");


// 2. DINAMIKUS ÚTVONALKEZELÉS (Base URL)
// Ez segít, hogy a header('Location: ...') és a linkek ne törjenek el
// felismeri, hogy a projekt a /clocky/ mappában van (XAMPP) vagy a gyökérben (Docker)
$base_url = (strpos($_SERVER['REQUEST_URI'], '/clocky/') !== false) ? '/clocky/' : '/';


// 3. GLOBÁLIS SEGÉDFÜGGVÉNY AZ ÁTIRÁNYÍTÁSHOZ
// Használat a többi fájlban: redirect('kezdolap');
if (!function_exists('redirect')) {
    function redirect($path) {
        global $base_url;
        // Ha a path elején van perjel, levágjuk, hogy ne legyen duplázás
        $clean_path = ltrim($path, '/');
        header("Location: " . $base_url . $clean_path);
        exit();
    }
}

// 4. STÍLUS ÉS TÉMA DEFINÍCIÓK (Opcionális segédlet a headernek)
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