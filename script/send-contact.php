<?php
// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mail_error.log');

// Clear any previous output
if (ob_get_length()) ob_clean();

// Set appropriate headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true'); // Allow credentials for cross-origin
header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK', null, 200);
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed. Please use POST.', null, 405);
}

try {
    // Get POST data
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Invalid JSON received: ' . $raw_data);
        sendJsonResponse(false, 'Invalid JSON data received.', null, 400);
    }

    // Validate required fields
    $required_fields = ['name', 'email', 'message'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendJsonResponse(false, "Missing required field: $field", null, 400);
        }
    }

    // Sanitize input
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($data['phone'] ?? '', FILTER_SANITIZE_STRING);
    $subject = filter_var($data['subject'] ?? '', FILTER_SANITIZE_STRING);
    $message = filter_var($data['message'], FILTER_SANITIZE_STRING);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, 'Invalid email format.', null, 400);
    }

    // Ensure data directory exists
    $dataDir = __DIR__ . '/../data';
    if (!file_exists($dataDir)) {
        if (!mkdir($dataDir, 0775, true)) {
             error_log('Failed to create data directory: ' . $dataDir);
             // Continue execution, database storage will fail gracefully
        }
    }

    // Database connection and storage
    $dbPath = $dataDir . '/submissions.db';
    try {
        $db = new SQLite3($dbPath);

        // Create table if it doesn't exist
        $db->exec('CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            subject TEXT,
            message TEXT NOT NULL,
            date DATETIME DEFAULT CURRENT_TIMESTAMP,
            read_status INTEGER DEFAULT 0
        )');

        // Insert submission
        $stmt = $db->prepare('INSERT INTO submissions (name, email, phone, subject, message) VALUES (:name, :email, :phone, :subject, :message)');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
        $stmt->bindValue(':subject', $subject, SQLITE3_TEXT);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);

        if (!$stmt->execute()) {
             error_log('SQLite insert failed: ' . $db->lastErrorMsg());
             // Continue execution, email sending might still work
        }

        $db->close();
    } catch (Exception $db_e) {
        error_log('Database error: ' . $db_e->getMessage());
        // Continue execution, email sending might still work
    }

    // Prepare email content (optional, may require SMTP setup)
    $to = 'johannesbohn03@gmail.com'; // Replace with your email
    $email_subject = "New Contact Form Submission" . ($subject ? ": $subject" : "");
    $email_body = "Name: $name\n";
    $email_body .= "Email: $email\n";
    if ($phone) $email_body .= "Phone: $phone\n";
    if ($subject) $email_body .= "Subject: $subject\n";
    $email_body .= "\nMessage:\n$message";

    $headers = "From: website@futurelaunch.de\r\n"; // Replace with your website email
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send email (this requires a configured mail server)
    $mail_sent = @mail($to, $email_subject, $email_body, $headers);

    // Respond based on database save success primarily, email as secondary
    if (isset($db) && !$db->lastErrorMsg()) { // Check if database connection was successful and insert had no error
        sendJsonResponse(true, 'Nachricht erfolgreich gesendet und gespeichert.');
    } elseif ($mail_sent) { // Fallback if database failed but email sent
         sendJsonResponse(true, 'Nachricht erfolgreich gesendet (Datenbank konnte nicht gespeichert werden).');
    } else { // Both failed
         sendJsonResponse(false, 'Nachricht konnte weder gesendet noch gespeichert werden. Bitte versuchen Sie es später erneut.', null, 500);
    }

} catch (Exception $e) {
    error_log("General form submission error: " . $e->getMessage());
    sendJsonResponse(false, 'Ein unerwarteter Fehler ist aufgetreten.', null, 500);
}
?>