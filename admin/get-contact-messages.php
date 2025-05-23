<?php
// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../script/contact_error.log');

// Restrict access (optional, implement more secure authentication in production)
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
    $csv_file = __DIR__ . '/../csv/contact_messages.csv';
    
    // Check if file exists
    if (!file_exists($csv_file)) {
        // Return empty array if file doesn't exist
        echo json_encode([]);
        exit;
    }
    
    // Read CSV file
    $contacts = [];
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Skip header if present (check if first line starts with ")
        $firstLine = fgetcsv($handle);
        if (count($firstLine) > 0 && !preg_match('/^\d{4}-\d{2}-\d{2}/', $firstLine[0])) {
            // This is a header, start from next line
            rewind($handle);
            fgetcsv($handle); // Skip header
        } else {
            // No header, rewind to start
            rewind($handle);
        }
        
        // Read data rows
        while (($data = fgetcsv($handle)) !== FALSE) {            if (count($data) >= 3) {
                $contacts[] = [
                    'timestamp' => trim($data[0], '"'),
                    'name' => trim($data[1], '"'),
                    'email' => trim($data[2], '"'),
                    'subject' => isset($data[3]) ? trim($data[3], '"') : 'Keine Betreffzeile',
                    'message' => isset($data[4]) ? trim($data[4], '"') : (isset($data[3]) ? trim($data[3], '"') : ''),
                    'status' => 'Offen' // Default status as we don't store this in CSV
                ];
            }
        }
        fclose($handle);
    }
    
    // Return contacts as JSON
    echo json_encode($contacts);
    
} catch (Exception $e) {
    // Log error
    error_log("Error fetching contact messages: " . $e->getMessage(), 3, __DIR__ . '/../script/contact_error.log');
    
    // Return error
    http_response_code(500);
    echo json_encode([
        'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
    ]);
}
?>
