<?php
// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../script/newsletter_error.log');

// Restrict access (optional, implement more secure authentication in production)
// For a simple solution, we'll just check the referrer
$validReferrers = [
    'localhost',
    '127.0.0.1',
    'futurelaunch.de'
];

$isValidReferrer = false;
if (isset($_SERVER['HTTP_REFERER'])) {
    $referrer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $isValidReferrer = in_array($referrer, $validReferrers);
}

// Set appropriate headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Methode nicht erlaubt');
    }
    
    // Get email from request
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || empty($input['email'])) {
        throw new Exception('E-Mail-Adresse nicht angegeben');
    }
    
    $emailToRemove = trim(strtolower($input['email']));
    
    // Path to the CSV file
    $csv_file = __DIR__ . '/../csv/newsletter_subscriptions.csv';
    
    // Check if file exists
    if (!file_exists($csv_file)) {
        echo json_encode(['success' => true, 'message' => 'Keine Abonnenten gefunden']);
        exit;
    }
    
    // Read current subscribers
    $subscribers = [];
    $removed = false;
    
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Read header row
        $header = fgetcsv($handle);
        
        // Read data rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 2) {
                // Add subscriber if email doesn't match
                if (strtolower($data[1]) !== $emailToRemove) {
                    $subscribers[] = $data;
                } else {
                    $removed = true;
                }
            }
        }
        fclose($handle);
    }
    
    // Write updated subscribers back to CSV
    if ($removed) {
        if (($handle = fopen($csv_file, "w")) !== FALSE) {
            // Write header row
            fputcsv($handle, $header);
            
            // Write data rows
            foreach ($subscribers as $subscriber) {
                fputcsv($handle, $subscriber);
            }
            fclose($handle);
        } else {
            throw new Exception('Fehler beim Schreiben der CSV-Datei');
        }
    }
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => $removed ? 'Abonnent erfolgreich entfernt' : 'Abonnent nicht gefunden'
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Error removing newsletter subscriber: " . $e->getMessage(), 3, __DIR__ . '/../script/newsletter_error.log');
    
    // Return error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
    ]);
}
?>
