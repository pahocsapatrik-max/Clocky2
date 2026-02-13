<?php
session_start();
require_once 'config.php';

// PHPMailer osztályok betöltése
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

// --- OKOS ROUTER ---
$request_uri = $_SERVER['REQUEST_URI'];
$current_file = basename($_SERVER['PHP_SELF']);

$routes = [
    'add_emp.php' => 'uj-dolgozo',
    'index.php' => 'bejelentkezes'
];

if (strpos($request_uri, '.php') !== false && !isset($_GET['api'])) {
    $pretty_name = $routes[$current_file] ?? 'dolgozok';
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $pretty_name);
    exit();
}

// --- JOGOSULTSÁG ELLENŐRZÉSE ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 1) {
    header('Location: bejelentkezes');
    exit();
}

$success_msg = "";
$error_msg = "";

// --- FORM FELDOLGOZÁSA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : NULL;
    $tn = intval($_POST['tn'] ?? 0);
    $roleID = $_POST['roleID'] ?? NULL;

    if (!empty($name) && !empty($email) && !empty($roleID)) {
        
        $conn->begin_transaction();

        try {
            // 1. ADATOK GENERÁLÁSA
            $email_parts = explode('@', $email)[0];
            $clean_username = preg_replace("/[^A-Za-z0-9]/", '', $email_parts);
            $default_username = strtolower(substr($clean_username, 0, 8)) . rand(10,99); // Egyediség miatt +2 számjegy
            $default_password = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 10); 
            
            // 2. BESZÚRÁS A USERS TÁBLÁBA (Belépési adatok)
            $user_stmt = $conn->prepare("INSERT INTO users (username, password, name, jogosultsag, FK_roleID) VALUES (?, ?, ?, 0, ?)");
            $user_stmt->bind_param("sssi", $default_username, $default_password, $name, $roleID);
            $user_stmt->execute();
            $new_user_id = $conn->insert_id;
            
            // 3. BESZÚRÁS AZ EMP TÁBLÁBA (Munkavállalói adatok)
            $stmt = $conn->prepare("INSERT INTO emp (name, email, dob, tn, FK_roleID, active, FK_userID) VALUES (?, ?, ?, ?, ?, 1, ?)");
            $stmt->bind_param("sssiis", $name, $email, $dob, $tn, $roleID, $new_user_id);
            $stmt->execute();

            // 4. PHPMAILER KÜLDÉS
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'clockytimer@gmail.com'; 
            $mail->Password   = 'wsyp chxu zxbe umyp'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('clockytimer@gmail.com', 'CLOCKY');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Sikeres regisztráció - CLOCKY';
            
            $mail->Body = "
            <html>
            <body style='background-color: #0f0f0f; color: #ffffff; font-family: sans-serif; padding: 40px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #1a1a1a; border-radius: 20px; padding: 40px; border: 1px solid #333;'>
                    <h1 style='color: #00ffe1; text-align: center;'>CLOCKY</h1>
                    <p>Kedves <strong>$name</strong>!</p>
                    <p>Regisztráltunk a rendszerben. Itt vannak a belépési adataid:</p>
                    <div style='background: #252525; padding: 20px; border-radius: 12px; border-left: 4px solid #00ffe1;'>
                        <p><strong>Felhasználónév:</strong> $default_username</p>
                        <p><strong>Ideiglenes jelszó:</strong> $default_password</p>
                    </div>
                    <p style='text-align: center; margin-top: 30px;'>
                        <a href='http://localhost/clocky/index.php' style='background: #00ffe1; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Belépés most</a>
                    </p>
                </div>
            </body>
            </html>";

            $mail->send();
            
            $conn->commit();
            $success_msg = "Sikeres mentés! Belépési adatok elküldve a(z) <strong>$email</strong> címre.";

        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Hiba történt: " . $mail->ErrorInfo;
        }
    } else {
        $error_msg = "Kérjük, töltsön ki minden kötelező mezőt!";
    }
}

$page_title = 'Új munkatárs';
include 'header.php';
?>

<style>
    :root { --accent-color: #00ffe1; --card-bg: #1a1a1a; --text-color: #ffffff; }
    
    .content-wrapper { 
        width: 100%; 
        max-width: 850px; 
        margin: 40px auto; 
        padding: 0 20px; 
        box-sizing: border-box; 
    }

    .glass-card { 
        background: var(--card-bg); 
        padding: clamp(25px, 5vw, 45px); 
        border-radius: 28px; 
        border: 1px solid rgba(255, 255, 255, 0.05); 
        box-shadow: 0 25px 50px rgba(0,0,0,0.5); 
    }

    h2 { font-weight: 800; margin-bottom: 35px; display: flex; align-items: center; gap: 15px; }
    h2 i { color: var(--accent-color); }

    .input-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
    .field { display: flex; flex-direction: column; }
    .full-width { grid-column: span 2; }

    label { font-size: 0.75rem; text-transform: uppercase; color: #888; margin-bottom: 10px; letter-spacing: 1px; font-weight: 600; }

    input, select { 
        background: rgba(255, 255, 255, 0.03); 
        border: 1px solid rgba(255,255,255,0.1); 
        padding: 16px; 
        border-radius: 14px; 
        color: white; 
        font-size: 1rem; 
        transition: 0.3s; 
    }

    input:focus, select:focus { 
        border-color: var(--accent-color); 
        background: rgba(255, 255, 255, 0.07);
        outline: none;
    }

    .btn-submit { 
        background: var(--accent-color); 
        color: #000; 
        border: none; 
        padding: 18px; 
        font-weight: 800; 
        border-radius: 15px; 
        cursor: pointer; 
        transition: 0.3s; 
        width: 100%; 
        margin-top: 30px;
    }

    .btn-submit:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 10px 20px rgba(0, 255, 225, 0.3); 
    }

    .status-msg { 
        padding: 18px; 
        border-radius: 15px; 
        margin-bottom: 30px; 
        border-left: 5px solid;
    }
    .success-lite { background: rgba(0, 255, 225, 0.05); color: var(--accent-color); border-color: var(--accent-color); }
    .error-lite { background: rgba(255, 71, 87, 0.05); color: #ff4757; border-color: #ff4757; }

    @media (max-width: 650px) { .input-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
</style>

<div class="content-wrapper">
    <div class="glass-card">
        <h2><i class="fas fa-user-plus"></i> Új munkatárs rögzítése</h2>

        <?php if ($success_msg): ?>
            <div class="status-msg success-lite"><i class="fas fa-check-circle"></i> <?= $success_msg ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="status-msg error-lite"><i class="fas fa-exclamation-circle"></i> <?= $error_msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-grid">
                <div class="field">
                    <label>Teljes név *</label>
                    <input type="text" name="name" required placeholder="Példa János">
                </div>

                <div class="field">
                    <label>Email cím *</label>
                    <input type="email" name="email" required placeholder="janos@ceg.hu">
                </div>

                <div class="field">
                    <label>Születési dátum</label>
                    <input type="date" name="dob">
                </div>

                <div class="field">
                    <label>Heti órakeret (óra)</label>
                    <input type="number" name="tn" value="40">
                </div>

                <div class="field full-width">
                    <label>Munkakör / Pozíció *</label>
                    <select name="roleID" required>
                        <option value="" disabled selected>Válassz pozíciót...</option>
                        <?php
                        $roles = $conn->query("SELECT roleID, role_name FROM role ORDER BY role_name ASC");
                        while($r = $roles->fetch_assoc()) {
                            echo "<option value='{$r['roleID']}'>{$r['role_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit">Munkatárs regisztrálása és e-mail küldése</button>
        </form>
    </div>
</div>

<?php $conn->close(); ?>