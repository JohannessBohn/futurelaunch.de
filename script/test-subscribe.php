<?php
// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple response
echo json_encode([
    'success' => true,
    'message' => 'Test successful!',
    'data' => [
        'email' => $_POST['email'] ?? 'No email provided',
        'method' => $_SERVER['REQUEST_METHOD']
    ]
]);

// Log the request
$log = date('Y-m-d H:i:s') . ' - ' . print_r([
    'post' => $_POST,
    'input' => file_get_contents('php://input')
], true) . "\n\n";
file_put_contents(__DIR__ . '/test-subscribe.log', $log, FILE_APPEND);
