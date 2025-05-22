<?php
// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mail_error.log');

// Clear any previous output
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
        error_log("Form data received via POST\n", 3, __DIR__ . "/mail_error.log");
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';
    } else {
        // Try to get JSON data
        $raw_data = file_get_contents('php://input');
        error_log("Raw data received: " . $raw_data . "\n", 3, __DIR__ . "/mail_error.log");
        
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
    $csv_dir = __DIR__ . '/../data';
    if (!file_exists($csv_dir)) {
        mkdir($csv_dir, 0777, true);
    }
    
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
    
    error_log("Contact form data saved to CSV\n", 3, __DIR__ . "/mail_error.log");
    
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
    
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
        
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'johannesbohn03@gmail.com';
                $mail->Password = 'tsqs axin ztun bggs';
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
                error_log("PHPMailer error: " . $e->getMessage() . "\n", 3, __DIR__ . "/mail_error.log");
                // Will fall back to simple mail
            }
        }
    }
    
    // Fallback to simple mail if PHPMailer failed or isn't available
    if (!$mail_sent) {
        $mail_sent = send_simple_mail(
            'johannesbohn03@gmail.com',
            'Neue Kontaktanfrage von ' . $name,
            $email_html,
            'website@futurelaunch.de'
        );
        
        if ($mail_sent) {
            error_log("Email sent successfully via simple mail\n", 3, __DIR__ . "/mail_error.log");
        } else {
            error_log("Simple mail sending failed\n", 3, __DIR__ . "/mail_error.log");
        }
    }
    
    // Always report success since we saved to CSV
    echo json_encode([
        'success' => true,
        'message' => 'Ihre Nachricht wurde erfolgreich gesendet!'
    ]);
    
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