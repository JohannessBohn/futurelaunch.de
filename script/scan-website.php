<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Basic error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(30);

// Store progress updates
$progress_updates = [];
function updateProgress($progress, $status) {
    global $progress_updates;
    $progress_updates[] = [
        'progress' => $progress,
        'status' => $status
    ];
}

try {
    // Get and validate URL
    $input = json_decode(file_get_contents('php://input'), true);
    $url = $input['url'] ?? null;

    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Ungültige URL');
    }

    updateProgress(10, 'Prüfe Website-Erreichbarkeit...');

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; FutureLaunchBot/1.0)'
    ]);

    updateProgress(20, 'Verbindung hergestellt...');

    // Get response
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    
    if ($response === false) {
        throw new Exception("Verbindungsfehler: " . $error);
    }

    updateProgress(30, 'Analysiere Performance...');

    // Basic performance metrics
    $performance = [
        'total_time' => $info['total_time'],
        'size' => $info['size_download'],
        'speed' => $info['speed_download']
    ];

    updateProgress(40, 'Prüfe SSL/Sicherheit...');

    // Check SSL
    $ssl = [
        'secure' => $info['scheme'] === 'https',
        'cert_valid' => $info['ssl_verify_result'] === 0
    ];

    updateProgress(50, 'Analysiere SEO-Faktoren...');

    // Parse HTML
    $doc = new DOMDocument();
    @$doc->loadHTML($response, LIBXML_NOERROR);

    // SEO checks
    $title = $doc->getElementsByTagName('title')->item(0)?->nodeValue ?? '';
    $metaDesc = '';
    foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta->getAttribute('name') === 'description') {
            $metaDesc = $meta->getAttribute('content');
            break;
        }
    }

    updateProgress(70, 'Prüfe Mobile-Optimierung...');

    $seo = [
        'title' => [
            'exists' => !empty($title),
            'length' => strlen($title),
            'optimal' => strlen($title) >= 30 && strlen($title) <= 60
        ],
        'meta_description' => [
            'exists' => !empty($metaDesc),
            'length' => strlen($metaDesc),
            'optimal' => strlen($metaDesc) >= 120 && strlen($metaDesc) <= 160
        ]
    ];

    // Mobile checks
    $viewport = false;
    foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta->getAttribute('name') === 'viewport') {
            $viewport = true;
            break;
        }
    }

    $mobile = [
        'viewport' => $viewport,
        'responsive' => strpos($response, '@media') !== false
    ];

    updateProgress(80, 'Berechne Scores...');

    // Calculate scores
    $scores = [
        'performance' => min(100, max(0, 100 - ($performance['total_time'] * 20))),
        'seo' => ($seo['title']['optimal'] ? 50 : ($seo['title']['exists'] ? 25 : 0)) +
                ($seo['meta_description']['optimal'] ? 50 : ($seo['meta_description']['exists'] ? 25 : 0)),
        'mobile' => ($viewport ? 50 : 0) + ($mobile['responsive'] ? 50 : 0),
        'security' => ($ssl['secure'] ? 50 : 0) + ($ssl['cert_valid'] ? 50 : 0)
    ];

    updateProgress(90, 'Erstelle Empfehlungen...');

    // Generate recommendations
    $recommendations = [];
    
    if ($scores['performance'] < 80) {
        $recommendations[] = [
            'category' => 'performance',
            'title' => 'Performance Optimierung',
            'priority' => $scores['performance'] < 50 ? 'hoch' : 'mittel',
            'actions' => ['Optimieren Sie Bilder und Assets', 'Aktivieren Sie Browser-Caching']
        ];
    }

    if ($scores['seo'] < 80) {
        $actions = [];
        if (!$seo['title']['optimal']) $actions[] = 'Optimieren Sie den Title-Tag (30-60 Zeichen)';
        if (!$seo['meta_description']['optimal']) $actions[] = 'Fügen Sie eine optimale Meta-Description hinzu (120-160 Zeichen)';
        
        $recommendations[] = [
            'category' => 'seo',
            'title' => 'SEO Verbesserungen',
            'priority' => $scores['seo'] < 50 ? 'hoch' : 'mittel',
            'actions' => $actions
        ];
    }

    if ($scores['mobile'] < 100) {
        $actions = [];
        if (!$viewport) $actions[] = 'Fügen Sie einen Viewport Meta-Tag hinzu';
        if (!$mobile['responsive']) $actions[] = 'Implementieren Sie ein responsives Design';
        
        $recommendations[] = [
            'category' => 'mobile',
            'title' => 'Mobile Optimierung',
            'priority' => $scores['mobile'] < 50 ? 'hoch' : 'mittel',
            'actions' => $actions
        ];
    }

    if ($scores['security'] < 100) {
        $actions = [];
        if (!$ssl['secure']) $actions[] = 'Aktivieren Sie HTTPS';
        if (!$ssl['cert_valid']) $actions[] = 'Installieren Sie ein gültiges SSL-Zertifikat';
        
        $recommendations[] = [
            'category' => 'security',
            'title' => 'Sicherheitsverbesserungen',
            'priority' => $scores['security'] < 50 ? 'hoch' : 'mittel',
            'actions' => $actions
        ];
    }

    updateProgress(100, 'Analyse abgeschlossen!');

    // Return final results
    echo json_encode([
        'success' => true,
        'progress_updates' => $progress_updates,
        'scores' => $scores,
        'details' => [
            'performance' => $performance,
            'seo' => $seo,
            'mobile' => $mobile,
            'security' => $ssl
        ],
        'recommendations' => $recommendations
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 