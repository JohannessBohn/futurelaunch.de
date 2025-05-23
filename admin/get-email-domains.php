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
        echo json_encode(['domains' => [], 'total' => 0]);
        exit;
    }
    
    // Read CSV file and count domains
    $domain_counts = [];
    $total_count = 0;
    
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);
        
        // Read data rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 2 && !empty($data[1])) {
                $total_count++;
                
                // Extract domain from email
                $email = $data[1];
                $domain = strtolower(substr(strrchr($email, "@"), 1));
                
                // Count domains
                if (!empty($domain)) {
                    if (isset($domain_counts[$domain])) {
                        $domain_counts[$domain]++;
                    } else {
                        $domain_counts[$domain] = 1;
                    }
                }
            }
        }
        fclose($handle);
    }
    
    // Sort domains by count (descending)
    arsort($domain_counts);
    
    // Keep only top domains and group others
    $top_domains = [];
    $others_count = 0;
    $domain_threshold = 2; // Minimum count to be shown separately
    $max_domains = 5;      // Maximum number of separate domains to show
    
    $counter = 0;
    foreach ($domain_counts as $domain => $count) {
        if ($counter < $max_domains && $count >= $domain_threshold) {
            $top_domains[$domain] = $count;
            $counter++;
        } else {
            $others_count += $count;
        }
    }
    
    // Add "Andere" category if there are any domains grouped
    if ($others_count > 0) {
        $top_domains['Andere'] = $others_count;
    }
    
    // Prepare data for chart
    $chart_data = [
        'domains' => [],
        'total' => $total_count
    ];
    
    foreach ($top_domains as $domain => $count) {
        $chart_data['domains'][] = [
            'domain' => $domain,
            'count' => $count,
            'percentage' => $total_count > 0 ? round(($count / $total_count) * 100, 1) : 0
        ];
    }
    
    // Return data as JSON
    echo json_encode($chart_data);
    
} catch (Exception $e) {
    // Log error
    error_log("Error analyzing email domains: " . $e->getMessage(), 3, __DIR__ . '/../script/newsletter_error.log');
    
    // Return error
    http_response_code(500);
    echo json_encode([
        'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
    ]);
}
?>
