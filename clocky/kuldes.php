<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';


$cimzett_email = 'pelda@email.com'; 

$mail = new PHPMailer(true);

try {
    // SZERVER BEÁLLÍTÁSOK
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'clockytimer@gmail.com';
    $mail->Password   = 'wsyp chxu zxbe umyp'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // CÍMZETTEK
    $mail->setFrom('clockytimer@gmail.com', 'CLOCKY');
    $mail->addAddress($cimzett_email); 

    // TARTALOM 
    $mail->isHTML(true);
    $mail->Subject = 'CLOCKY | Időzítő Értesítés';
    
    

    $mail->AltBody = "CLOCKY Értesítés: Az időzítőd lejárt!";

    $mail->send();
    echo 'Sikerült! Az üzenet elküldve.';

} catch (Exception $e) {
    echo "Hiba történt: {$mail->ErrorInfo}";
}
?>