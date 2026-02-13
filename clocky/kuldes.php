<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer osztályok betöltése (Ellenőrizd az elérési utat!)
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

/**
 * Megjegyzés: Ha ezt a funkciót egy másik fájlból hívod meg (pl. add_emp.php), 
 * érdemes függvénybe szervezni.
 */

$cimzett_email = 'pelda@email.com'; // Itt add meg a változót, aki kapja
$cimzett_neve = 'Munkatárs';

$mail = new PHPMailer(true);

try {
    // --- SZERVER BEÁLLÍTÁSOK ---
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'clockytimer@gmail.com';
    $mail->Password   = 'wsyp chxu zxbe umyp'; // Alkalmazásjelszó
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // --- CÍMZETTEK ---
    $mail->setFrom('clockytimer@gmail.com', 'Clocky System');
    $mail->addAddress($cimzett_email, $cimzett_neve); 

    // --- TARTALOM (HTML SABLON) ---
    $mail->isHTML(true);
    $mail->Subject = 'CLOCKY | Értesítés';

    // HTML Sablon felépítése
    $html_content = "
    <div style='background-color: #0f0f0f; color: #ffffff; font-family: \"Inter\", Arial, sans-serif; padding: 40px; border-radius: 20px; max-width: 600px; margin: auto; border: 1px solid #1a1a1a;'>
        <div style='text-align: center; margin-bottom: 30px;'>
            <h1 style='color: #ffffff; font-size: 36px; font-weight: 900; letter-spacing: -2px; margin: 0;'>clocky<span style='color: #00ffe1;'>.</span></h1>
        </div>
        
        <div style='background-color: #1a1a1a; padding: 30px; border-radius: 15px; border-left: 4px solid #00ffe1;'>
            <h2 style='color: #00ffe1; margin-top: 0;'>Értesítés érkezett</h2>
            <p style='font-size: 16px; line-height: 1.6; color: #cccccc;'>Kedves <strong>{$cimzett_neve}</strong>!</p>
            <p style='font-size: 16px; line-height: 1.6; color: #cccccc;'>Tájékoztatunk, hogy a rendszerből automatikus értesítésed érkezett. Az időzítőd vagy a munkaidő eseményed állapota megváltozott.</p>
            
            <div style='margin-top: 25px; text-align: center;'>
                <a href='http://localhost/clocky/' style='background-color: #00ffe1; color: #000000; padding: 12px 25px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block;'>Rendszer megnyitása</a>
            </div>
        </div>

        <div style='text-align: center; margin-top: 30px; color: #555555; font-size: 12px;'>
            <p>© " . date("Y") . " Clocky Timer System. Minden jog fenntartva.</p>
            <p>Ezt az üzenetet automatikusan generálta a rendszer, kérjük ne válaszolj rá.</p>
        </div>
    </div>
    ";

    $mail->Body    = $html_content;
    $mail->AltBody = "CLOCKY Értesítés: Az esemény rögzítésre került a rendszerben.";

    $mail->send();
    // Fejlesztés közben jó az echo, de élesben jobb csendben maradni vagy logolni
    // echo 'Sikerült! Az üzenet elküldve.';

} catch (Exception $e) {
    // Hiba esetén naplózás vagy hibaüzenet
    // error_log("PHPMailer Hiba: {$mail->ErrorInfo}");
}
?>