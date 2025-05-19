<?php
// send-contact.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    $to      = 'info@futurelaunch.de';
    $subject = 'Neue Kontaktanfrage von der Website';
    $body    = "Name: $name\nE-Mail: $email\n\nNachricht:\n$message";
    $headers = "From: $email\r\nReply-To: $email\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
} 