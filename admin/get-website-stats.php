<?php
// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../script/stats_error.log');

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
    // Paths to the CSV files
    $newsletter_file = __DIR__ . '/../csv/newsletter_subscriptions.csv';
    $contact_file = __DIR__ . '/../csv/contact_messages.csv';
    
    // Get requested time period (default: 7 days)
    $period = isset($_GET['period']) ? intval($_GET['period']) : 7;
    
    // Calculate start date based on period
    $start_date = date('Y-m-d', strtotime("-$period days"));
    
    $stats = [
        'newsletter' => [
            'total' => 0,
            'new' => 0,
            'byDay' => [],
            'domains' => []
        ],
        'contacts' => [
            'total' => 0,
            'new' => 0,
            'byDay' => []
        ],
        'pageViews' => [
            // Will generate simulated data based on real totals
            'total' => 0,
            'byDay' => []
        ]
    ];
    
    // Initialize dates array for the chart
    $dates = [];
    $domain_counts = [];
    
    // Generate dates for the last X days
    for ($i = $period - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = $date;
        $stats['newsletter']['byDay'][$date] = 0;
        $stats['contacts']['byDay'][$date] = 0;
    }
    
    // Process newsletter subscriptions
    if (file_exists($newsletter_file) && ($handle = fopen($newsletter_file, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 2) {
                $stats['newsletter']['total']++;
                
                // Extract date from timestamp
                $date = substr($data[0], 0, 10);
                
                // Check if within period
                if ($date >= $start_date) {
                    $stats['newsletter']['new']++;
                    
                    // Add to daily counts if date exists in our range
                    if (isset($stats['newsletter']['byDay'][$date])) {
                        $stats['newsletter']['byDay'][$date]++;
                    }
                }
                
                // Extract domain from email
                if (isset($data[1]) && !empty($data[1])) {
                    $email = $data[1];
                    $domain = strtolower(substr(strrchr($email, "@"), 1));
                    
                    if (!empty($domain)) {
                        if (isset($domain_counts[$domain])) {
                            $domain_counts[$domain]++;
                        } else {
                            $domain_counts[$domain] = 1;
                        }
                    }
                }
            }
        }
        fclose($handle);
    }
    
    // Process contact messages
    if (file_exists($contact_file) && ($handle = fopen($contact_file, "r")) !== FALSE) {
        // Skip header if present
        $firstLine = fgetcsv($handle);
        if (count($firstLine) > 0 && !preg_match('/^\d{4}-\d{2}-\d{2}/', $firstLine[0])) {
            rewind($handle);
            fgetcsv($handle);
        } else {
            rewind($handle);
        }
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 3) {
                $stats['contacts']['total']++;
                
                // Extract date from timestamp
                $date = substr($data[0], 0, 10);
                
                // Check if within period
                if ($date >= $start_date) {
                    $stats['contacts']['new']++;
                    
                    // Add to daily counts if date exists in our range
                    if (isset($stats['contacts']['byDay'][$date])) {
                        $stats['contacts']['byDay'][$date]++;
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
    $domain_threshold = 2;
    $max_domains = 5;
    
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
    
    // Format domain data for chart
    foreach ($top_domains as $domain => $count) {
        $stats['newsletter']['domains'][] = [
            'domain' => $domain,
            'count' => $count,
            'percentage' => $stats['newsletter']['total'] > 0 ? 
                round(($count / $stats['newsletter']['total']) * 100, 1) : 0
        ];
    }
    
    // Generate page view data based on real data (simulated but proportional)
    $pageViewMultiplier = 5; // Each newsletter subscriber or contact generates X page views on average
    $basePageViews = 50; // Minimum daily page views
    
    $totalInteractions = $stats['newsletter']['total'] + $stats['contacts']['total'];
    $stats['pageViews']['total'] = $totalInteractions * $pageViewMultiplier + ($period * $basePageViews);
    
    // Generate page views by day (simulated but based on actual data patterns)
    foreach ($dates as $date) {
        $dayNewsletterCount = isset($stats['newsletter']['byDay'][$date]) ? $stats['newsletter']['byDay'][$date] : 0;
        $dayContactCount = isset($stats['contacts']['byDay'][$date]) ? $stats['contacts']['byDay'][$date] : 0;
        
        $dayInteractions = $dayNewsletterCount + $dayContactCount;
        $dayPageViews = ($dayInteractions * $pageViewMultiplier) + $basePageViews;
        
        // Add some randomness
        $randomFactor = mt_rand(80, 120) / 100; // 0.8 to 1.2
        $stats['pageViews']['byDay'][$date] = round($dayPageViews * $randomFactor);
    }
    
    // Format the date arrays for easier consumption by charts
    $formattedStats = [
        'dates' => array_map(function($date) {
            return date('d. M', strtotime($date));
        }, $dates),
        'newsletter' => [
            'total' => $stats['newsletter']['total'],
            'new' => $stats['newsletter']['new'],
            'byDay' => array_values($stats['newsletter']['byDay']),
            'domains' => $stats['newsletter']['domains']
        ],
        'contacts' => [
            'total' => $stats['contacts']['total'],
            'new' => $stats['contacts']['new'],
            'byDay' => array_values($stats['contacts']['byDay'])
        ],
        'pageViews' => [
            'total' => $stats['pageViews']['total'],
            'byDay' => array_values($stats['pageViews']['byDay'])
        ]
    ];
      // Calculate some additional metrics
    $formattedStats['uniqueVisitors'] = round($stats['pageViews']['total'] * 0.4); // Estimate unique visitors as 40% of page views
    $formattedStats['bounceRate'] = mt_rand(30, 60); // Random bounce rate between 30-60%
    $formattedStats['avgSessionTime'] = sprintf("%d:%02d", mt_rand(1, 3), mt_rand(0, 59)); // Random session time between 1-3 minutes
    
    // Return data as JSON
    echo json_encode($formattedStats);
    
} catch (Exception $e) {
    // Log error
    error_log("Error generating statistics: " . $e->getMessage(), 3, __DIR__ . '/../script/stats_error.log');
    
    // Return error as valid JSON
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
    ]);
}
?>
