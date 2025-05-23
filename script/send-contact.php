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
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple mail function as fallback if PHPMailer isn't available
function send_simple_mail($to, $subject, $message, $from) {
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Set error logging
error_log("=== Starting contact form submission ===\n", 3, __DIR__ . "/mail_error.log");

try {
    // Get input data - check if it's form data or JSON
    $name = $email = $message = '';
    
    if (!empty($_POST)) {
        // Regular form POST data
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';
    } else {
        // Try to get JSON data
        $raw_data = file_get_contents('php://input');
        
        if (!empty($raw_data)) {
            $data = json_decode($raw_data, true);
            if ($data) {
                $name = isset($data['name']) ? $data['name'] : '';
                $email = isset($data['email']) ? $data['email'] : '';
                $message = isset($data['message']) ? $data['message'] : '';
            }
        }
    }
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception("Fehler: Alle Felder müssen ausgefüllt werden");
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Ungültige E-Mail-Adresse');
    }
    
    // Sanitize inputs
    $name = htmlspecialchars($name);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($message);
      // Save to CSV file as backup
    $csv_dir = __DIR__ . '/../csv';
    if (!file_exists($csv_dir)) {
        if (!mkdir($csv_dir, 0777, true)) {
            error_log("Failed to create directory: $csv_dir\n", 3, __DIR__ . "/mail_error.log");
        }
    }
    
    $csv_file = $csv_dir . '/contact_messages.csv';
    $is_new_file = !file_exists($csv_file);
    
    try {
        $fp = fopen($csv_file, 'a');
        if (!$fp) {
            throw new Exception("Could not open CSV file for writing");
        }
        
        // Add headers if new file
        if ($is_new_file) {
            fputcsv($fp, ['Timestamp', 'Name', 'Email', 'Message']);
        }
        
        // Add data
        fputcsv($fp, [date('Y-m-d H:i:s'), $name, $email, $message]);
        fclose($fp);
        
        error_log("Contact form data saved to CSV: $csv_file\n", 3, __DIR__ . "/mail_error.log");
    } catch (Exception $csv_error) {
        error_log("CSV Error: " . $csv_error->getMessage() . "\n", 3, __DIR__ . "/mail_error.log");
        // Continue execution - we'll try to send email anyway
    }
    
    // Prepare email content
    $email_html = "
    <html>
    <head>
        <title>Neue Kontaktanfrage</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            h2 { color: #4A6DE5; }
            .message { background: #f9f9f9; padding: 15px; border-radius: 5px; }
            hr { border: none; border-top: 1px solid #eee; margin: 20px 0; }
            .footer { font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <h2>Neue Kontaktanfrage von der Website</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>E-Mail:</strong> {$email}</p>
        <p><strong>Nachricht:</strong></p>
        <div class='message'>" . nl2br($message) . "</div>
        <hr>
        <p class='footer'>Diese Nachricht wurde über das Kontaktformular gesendet.</p>
    </body>
    </html>";
    
    $email_plain = "Neue Kontaktanfrage\n\nName: {$name}\nE-Mail: {$email}\n\nNachricht:\n{$message}";
      // Try to use PHPMailer if available
    $mail_sent = false;
    $mail_error = '';
    
    // Check for PHPMailer in several possible locations
    $autoload_paths = [
        __DIR__ . '/vendor/autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../../vendor/autoload.php'
    ];
    
    $autoload_found = false;
    foreach ($autoload_paths as $path) {
        if (file_exists($path)) {
            require $path;
            $autoload_found = true;
            error_log("Autoloader found at: $path\n", 3, __DIR__ . "/mail_error.log");
            break;
        }
    }
    
    if (!$autoload_found) {
        error_log("Autoloader not found in any of the checked paths\n", 3, __DIR__ . "/mail_error.log");
        
        // Try to load PHPMailer directly
        $phpmailer_paths = [
            __DIR__ . '/PHPMailer/PHPMailer.php',
            __DIR__ . '/PHPMailer/src/PHPMailer.php',
            __DIR__ . '/src/PHPMailer.php'
        ];
        
        foreach ($phpmailer_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                require_once dirname($path) . '/SMTP.php';
                require_once dirname($path) . '/Exception.php';
                $autoload_found = true;
                error_log("PHPMailer found directly at: $path\n", 3, __DIR__ . "/mail_error.log");
                break;
            }
        }
    }
    
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->SMTPDebug = 0;  // Set to 2 for debugging
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'johannesbohn03@gmail.com';
            $mail->Password = 'tsqs axin ztun bggs';  // App password, not regular password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom('johannesbohn03@gmail.com', 'FutureLaunch Website');
            $mail->addAddress('johannesbohn03@gmail.com', 'Johannes Bohn');
            $mail->addReplyTo($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Neue Kontaktanfrage von ' . $name;
            $mail->Body = $email_html;
            $mail->AltBody = $email_plain;

            $mail->send();
            $mail_sent = true;
            error_log("Email sent successfully via PHPMailer\n", 3, __DIR__ . "/mail_error.log");
        } catch (Exception $e) {
            $mail_error = $e->getMessage();
            error_log("PHPMailer error: " . $mail_error . "\n", 3, __DIR__ . "/mail_error.log");
            // Will fall back to simple mail
        }
    } else {
        error_log("PHPMailer class not found\n", 3, __DIR__ . "/mail_error.log");
    }
      // Fallback to simple mail if PHPMailer failed or isn't available
    if (!$mail_sent) {
        $to = 'johannesbohn03@gmail.com';
        $subject = 'Neue Kontaktanfrage von ' . $name;
        $from = 'website@futurelaunch.de';
        
        $headers = "From: $from\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $mail_result = mail($to, $subject, $email_html, $headers);
        
        if ($mail_result) {
            $mail_sent = true;
            error_log("Email sent successfully via simple mail\n", 3, __DIR__ . "/mail_error.log");
        } else {
            $error_message = error_get_last();
            error_log("Simple mail sending failed: " . ($error_message ? json_encode($error_message) : "Unknown error") . "\n", 3, __DIR__ . "/mail_error.log");
        }
    }
    
    // Report status - consider CSV success as a partial success
    if ($mail_sent) {
        echo json_encode([
            'success' => true,
            'message' => 'Ihre Nachricht wurde erfolgreich gesendet!'
        ]);
    } else {
        // We'll still return success if at least the CSV was saved
        echo json_encode([
            'success' => true,
            'message' => 'Ihre Nachricht wurde gespeichert! Wir werden uns bald mit Ihnen in Verbindung setzen.'
        ]);
        
        // Log the issue for debugging
        error_log("Mail not sent, but CSV saved. Response marked as success.\n", 3, __DIR__ . "/mail_error.log");
    }
    
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    error_log("Contact form error: " . $e->getMessage() . "\n", 3, __DIR__ . "/mail_error.log");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>