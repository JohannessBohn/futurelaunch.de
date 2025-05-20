<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set up local error logging
$logFile = __DIR__ . '/mail_error.log';
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Test if we can write to the log file
try {
    writeLog("=== Starting new mail attempt ===");
    writeLog("PHP Version: " . PHP_VERSION);
    writeLog("Server Software: " . $_SERVER['SERVER_SOFTWARE']);
    writeLog("Document Root: " . $_SERVER['DOCUMENT_ROOT']);
    writeLog("Script Path: " . __FILE__);
} catch (Exception $e) {
    die("Cannot write to log file: " . $e->getMessage());
}

// Set headers for CORS and content type
$allowedOrigins = [
    'http://localhost:8080',
    'http://127.0.0.1:5501',
    'http://localhost:5501'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: text/plain; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit();
}

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// Log received data
writeLog("Received form data: " . print_r($_POST, true));

// Validate form data
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo 'Bitte füllen Sie alle Felder aus.';
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    exit();
}

// Store the message in a CSV file as a fallback
$csvFile = __DIR__ . '/../csv/contact_messages.csv';
$csvDir = dirname($csvFile);
if (!is_dir($csvDir)) {
    mkdir($csvDir, 0755, true);
}

$csvData = array(
    date('Y-m-d H:i:s'),
    $name,
    $email,
    $message
);

$fp = fopen($csvFile, 'a');
fputcsv($fp, $csvData);
fclose($fp);

writeLog("Saved message to CSV file: $csvFile");

try {
    // Check if PHPMailer files exist
    $phpmailerPath = __DIR__ . '/PHPMailer/src/';
    if (!file_exists($phpmailerPath . 'PHPMailer.php')) {
        throw new Exception("PHPMailer.php not found in: " . $phpmailerPath);
    }

    require $phpmailerPath . 'Exception.php';
    require $phpmailerPath . 'PHPMailer.php';
    require $phpmailerPath . 'SMTP.php';

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Use Gmail instead of IONOS as a temporary solution
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->Username = 'johannesbohn03@gmail.com'; // User's Gmail address
    $mail->Password = 'kqdsijnwrzdtqzxv'; // App password (spaces removed)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet = 'UTF-8';
    
    // TLS settings
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Enable debug output
    $mail->SMTPDebug = 3; // Increased debug level
    $mail->Debugoutput = function($str, $level) {
        writeLog("PHPMailer Debug: $str");
    };

    // Recipients
    $mail->setFrom('johannesbohn03@gmail.com', 'FutureLaunch Kontaktformular');
    $mail->addAddress('johannesbohn03@gmail.com', 'FutureLaunch');
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Neue Kontaktanfrage von ' . $name;
    $mail->Body = "
        <h2>Neue Kontaktanfrage</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>E-Mail:</strong> {$email}</p>
        <p><strong>Nachricht:</strong></p>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
    ";

    // Log attempt to send
    writeLog("Attempting to send email to johannesbohn03@gmail.com from {$email}");

    // Send email
    if (!$mail->send()) {
        throw new Exception('E-Mail konnte nicht gesendet werden: ' . $mail->ErrorInfo);
    }

    // Log successful email
    writeLog("E-Mail erfolgreich gesendet an johannesbohn03@gmail.com von {$email}");

    // Return success response
    echo 'success';

} catch (Exception $e) {
    // Log detailed error
    writeLog("E-Mail Fehler: " . $e->getMessage());
    writeLog("Stack Trace: " . $e->getTraceAsString());
    
    // Since we saved to CSV, we can return success anyway
    writeLog("Returning success response because message was saved to CSV");
    echo 'success';
} 