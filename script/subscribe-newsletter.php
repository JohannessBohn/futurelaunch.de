<?php
// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/newsletter_error.log');

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

// Log start of process
error_log("=== Starting newsletter subscription processing ===\n", 3, __DIR__ . "/newsletter_error.log");

try {
    // Get email data - check if it's form data or JSON
    $email = '';
    
    if (!empty($_POST)) {
        // Regular form POST data
        $email = isset($_POST['email']) ? $_POST['email'] : '';
    } else {
        // Try to get JSON data
        $raw_data = file_get_contents('php://input');
        
        if (!empty($raw_data)) {
            $data = json_decode($raw_data, true);
            if ($data) {
                $email = isset($data['email']) ? $data['email'] : '';
            }
        }
    }
    
    // Validate required fields
    if (empty($email)) {
        throw new Exception("Fehler: E-Mail-Adresse muss angegeben werden");
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Ungültige E-Mail-Adresse');
    }
    
    // Sanitize input
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    // Save to CSV file
    $csv_dir = __DIR__ . '/../csv';
    if (!file_exists($csv_dir)) {
        if (!mkdir($csv_dir, 0777, true)) {
            error_log("Failed to create directory: $csv_dir\n", 3, __DIR__ . "/newsletter_error.log");
        }
    }
    
    $csv_file = $csv_dir . '/newsletter_subscriptions.csv';
    $is_new_file = !file_exists($csv_file);
    
    try {
        $fp = fopen($csv_file, 'a');
        if (!$fp) {
            throw new Exception("Could not open CSV file for writing");
        }
        
        // Add headers if new file
        if ($is_new_file) {
            fputcsv($fp, ['Timestamp', 'Email']);
        }
        
        // Check if email already exists
        $duplicate = false;
        if (!$is_new_file) {
            $fp_read = fopen($csv_file, 'r');
            if ($fp_read) {
                // Skip header
                fgetcsv($fp_read);
                
                while (($data = fgetcsv($fp_read)) !== FALSE) {
                    if (isset($data[1]) && strtolower($data[1]) === strtolower($email)) {
                        $duplicate = true;
                        break;
                    }
                }
                fclose($fp_read);
            }
        }
        
        if (!$duplicate) {
            // Add data only if not duplicate
            fputcsv($fp, [date('Y-m-d H:i:s'), $email]);
            error_log("Newsletter subscription saved to CSV: $email\n", 3, __DIR__ . "/newsletter_error.log");
        } else {
            error_log("Duplicate newsletter subscription: $email\n", 3, __DIR__ . "/newsletter_error.log");
        }
        
        fclose($fp);
    } catch (Exception $csv_error) {
        error_log("CSV Error: " . $csv_error->getMessage() . "\n", 3, __DIR__ . "/newsletter_error.log");
        // Continue execution - we'll return success anyway since the client already stored it locally
    }
    
    // Send notification email to admin (optional)
    $admin_email = 'johannesbohn03@gmail.com';
    $subject = 'Neue Newsletter-Anmeldung';
    $message = "
    <html>
    <head>
        <title>Neue Newsletter-Anmeldung</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            h2 { color: #4A6DE5; }
            .footer { font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <h2>Neue Newsletter-Anmeldung</h2>
        <p>E-Mail: <strong>$email</strong></p>
        <p>Zeitpunkt: " . date('d.m.Y H:i:s') . "</p>
        <hr>
        <p class='footer'>Diese Nachricht wurde automatisch generiert.</p>
    </body>
    </html>";
    
    $plain_message = "Neue Newsletter-Anmeldung\n\nE-Mail: $email\nZeitpunkt: " . date('d.m.Y H:i:s');

    // Try to use PHPMailer for admin notification (simplified, just the key parts)
    // Note: This notification is optional and will not return an error to the user if it fails
    try {
        // Load PHPMailer if available
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Basic setup (same as in send-contact.php)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'johannesbohn03@gmail.com';
            $mail->Password = 'tsqs axin ztun bggs';  // App password, not regular password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('johannesbohn03@gmail.com', 'FutureLaunch Website');
            $mail->addAddress($admin_email);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = $plain_message;

            $mail->send();
            error_log("Admin notification email sent successfully\n", 3, __DIR__ . "/newsletter_error.log");
        }
    } catch (Exception $e) {
        // Just log the error but don't fail the subscription
        error_log("Admin notification email failed: " . $e->getMessage() . "\n", 3, __DIR__ . "/newsletter_error.log");
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Vielen Dank für Ihre Anmeldung zum Newsletter!'
    ]);
    
} catch (Exception $e) {
    // Clear any output and return error
    if (ob_get_length()) ob_clean();
    error_log("Newsletter subscription error: " . $e->getMessage() . "\n", 3, __DIR__ . "/newsletter_error.log");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
