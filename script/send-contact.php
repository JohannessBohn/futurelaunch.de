<?php
// send-contact.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/src/Exception.php';
require __DIR__ . '/src/PHPMailer.php';
require __DIR__ . '/src/SMTP.php';

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0; // 0 = kein Debug für User, 2 für Fehlersuche
    $mail->Debugoutput = 'html';
    try {
        // IONOS SMTP Einstellungen
        $mail->isSMTP();
        $mail->Host       = 'smtp.ionos.de';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@futurelaunch.de'; // Deine echte IONOS-Adresse
        $mail->Password   = 'FutureLaunch2025!'; // Dein echtes Passwort
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($mail->Username, 'FutureLaunch Kontakt');
        $mail->addAddress('info@futurelaunch.de');
        $mail->addReplyTo($email, $name);

        $mail->Subject = 'Neue Beratung über Website-Check';
        $mail->Body    = "Name: $name\nE-Mail: $email\n\nNachricht:\n$message";

        $mail->send();
        echo 'success';
    } catch (Exception $e) {
        http_response_code(500);
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
} 