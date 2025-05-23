<?php
// Simple test script to confirm POST requests are working
header('Content-Type: application/json');

// Log request info
error_log("=== TEST SCRIPT ===", 3, __DIR__ . "/mail_error.log");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'], 3, __DIR__ . "/mail_error.log");

// Echo received data
$response = [
    'success' => true,
    'message' => 'Test successful',
    'method' => $_SERVER['REQUEST_METHOD'],
    'data' => $_POST
];

echo json_encode($response);
?>
