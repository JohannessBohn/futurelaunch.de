<?php
// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mail_error.log');

// Set SMTP settings for PHP mail
ini_set('SMTP', 'smtp.gmail.com');
ini_set('smtp_port', '587');
ini_set('sendmail_from', 'johannesbohn03@gmail.com');

// Clear any previous output
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set error logging
error_log("=== Starting contact form submission ===\n", 3, "mail_error.log");

try {
    // Get the raw POST data
    $raw_data = file_get_contents('php://input');
    error_log("Raw data received: " . $raw_data . "\n", 3, "mail_error.log");
    
    // Decode JSON
    $data = json_decode($raw_data, true);
    
    // Log the decoded data
    error_log("Decoded data: " . print_r($data, true) . "\n", 3, "mail_error.log");
    
    if (!$data) {
        throw new Exception('Ungültige Anfrage: JSON konnte nicht dekodiert werden');
    }
    
    // Validate required fields
    $required_fields = ['name', 'email', 'message'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Fehler: {$field} ist erforderlich");
        }
    }
    
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Ungültige E-Mail-Adresse');
    }
    
    // Sanitize inputs
    $name = htmlspecialchars($data['name']);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($data['message']);
    
    // Create CSV directory if it doesn't exist
    $csv_dir = __DIR__ . '/../csv';
    if (!file_exists($csv_dir)) {
        mkdir($csv_dir, 0777, true);
    }
    
    // Save to CSV file as backup
    $csv_file = $csv_dir . '/contact_submissions.csv';
    $is_new_file = !file_exists($csv_file);
    
    $fp = fopen($csv_file, 'a');
    
    // Add headers if new file
    if ($is_new_file) {
        fputcsv($fp, ['Timestamp', 'Name', 'Email', 'Message']);
    }
    
    // Add data
    fputcsv($fp, [date('Y-m-d H:i:s'), $name, $email, $message]);
    fclose($fp);
    
    error_log("Contact form data saved to CSV\n", 3, "mail_error.log");
    
    // Prepare email
    $to = 'johannesbohn03@gmail.com';
    $subject = 'Neue Kontaktanfrage von ' . $name;
    
    // Create HTML message
    $html_message = "
    <html>
    <head>
        <title>Neue Kontaktanfrage</title>
    </head>
    <body>
        <h2>Neue Kontaktanfrage von der Website</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>E-Mail:</strong> {$email}</p>
        <p><strong>Nachricht:</strong></p>
        <p>" . nl2br($message) . "</p>
        <hr>
        <p><small>Diese Nachricht wurde über das Website Analyse Tool gesendet.</small></p>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Website Analyse Tool <johannesbohn03@gmail.com>',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'X-Mailer: PHP/' . phpversion()
    );
    
    // Send email
    $mail_sent = mail($to, $subject, $html_message, implode("\r\n", $headers));
    
    if ($mail_sent) {
        error_log("Email sent successfully\n", 3, "mail_error.log");
        echo json_encode([
            'success' => true,
            'message' => 'Ihre Nachricht wurde erfolgreich gesendet!'
        ]);
    } else {
        error_log("Email sending failed using mail() function\n", 3, "mail_error.log");
        // Still return success since we saved to CSV
        echo json_encode([
            'success' => true,
            'message' => 'Ihre Nachricht wurde erfolgreich gespeichert und wird schnellstmöglich bearbeitet!'
        ]);
    }
    
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    error_log("Contact form error: " . $e->getMessage() . "\n", 3, "mail_error.log");
    error_log("Stack trace: " . $e->getTraceAsString() . "\n", 3, "mail_error.log");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}