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
header('Access-Control-Allow-Methods: GET');

try {
    // Path to the CSV file
    $csv_file = __DIR__ . '/../csv/newsletter_subscriptions.csv';
    
    // Check if file exists
    if (!file_exists($csv_file)) {
        // Return empty array if file doesn't exist
        echo json_encode([]);
        exit;
    }
    
    // Read CSV file
    $subscribers = [];
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);
        
        // Read data rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 2) {
                $subscribers[] = [
                    'timestamp' => $data[0],
                    'email' => $data[1]
                ];
            }
        }
        fclose($handle);
    }
    
    // Return subscribers as JSON
    echo json_encode($subscribers);
    
} catch (Exception $e) {
    // Log error
    error_log("Error fetching newsletter subscribers: " . $e->getMessage(), 3, __DIR__ . '/../script/newsletter_error.log');
    
    // Return error
    http_response_code(500);
    echo json_encode([
        'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
    ]);
}
?>
