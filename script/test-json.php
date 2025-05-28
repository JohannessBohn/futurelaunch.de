<?php
// Set headers first
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create test response
$response = [
    'success' => true,
    'message' => 'Test successful!',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'get_data' => $_GET,
    'raw_input' => file_get_contents('php://input')
];

// Output JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Log the request
$log = "[" . date('Y-m-d H:i:s') . "] Test request received\n";
$log .= "Headers: " . print_r(getallheaders(), true) . "\n";
$log .= "POST: " . print_r($_POST, true) . "\n";
$log .= "GET: " . print_r($_GET, true) . "\n";
$log .= "Raw input: " . file_get_contents('php://input') . "\n\n";

file_put_contents(__DIR__ . '/test-json.log', $log, FILE_APPEND);
