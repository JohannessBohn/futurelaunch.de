<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get form data
$name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : '';
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
$phone = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_STRING) : '';
$subject = isset($_POST['subject']) ? filter_var($_POST['subject'], FILTER_SANITIZE_STRING) : 'Neue Kontaktanfrage';
$message = isset($_POST['message']) ? filter_var($_POST['message'], FILTER_SANITIZE_STRING) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Bitte füllen Sie alle erforderlichen Felder aus.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.'
    ]);
    exit;
}

// Save to file for backup
$data = [
    'date' => date('Y-m-d H:i:s'),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'subject' => $subject,
    'message' => $message,
    'ip' => $_SERVER['REMOTE_ADDR']
];

// Create messages directory if it doesn't exist
$messagesDir = __DIR__ . '/messages';
if (!file_exists($messagesDir)) {
    mkdir($messagesDir, 0755, true);
}

// Save message to file
$filename = $messagesDir . '/message_' . time() . '_' . uniqid() . '.json';
file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

// Email configuration
$to = 'johannesbohn03@gmail.com';
$email_subject = "[FutureLaunch] Neue Kontaktanfrage: $subject";

$email_body = "Sie haben eine neue Kontaktanfrage erhalten.\n\n";
$email_body .= "Name: $name\n";
$email_body .= "E-Mail: $email\n";
$email_body .= "Telefon: " . ($phone ?: 'Nicht angegeben') . "\n\n";
$email_body .= "Nachricht:\n$message\n\n";
$email_body .= "Diese Nachricht wurde über das Kontaktformular auf futurelaunch.de gesendet.";

$headers = "From: $name <$email>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$mailSent = mail($to, $email_subject, $email_body, $headers);

if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Vielen Dank für Ihre Nachricht! Wir werden uns in Kürze bei Ihnen melden.'
    ]);
} else {
    // Even if email fails, we still consider it a success since we saved the message
    echo json_encode([
        'success' => true,
        'message' => 'Vielen Dank für Ihre Nachricht! Wir haben Ihre Anfrage erhalten und werden uns bald bei Ihnen melden.'
    ]);
}
?>
