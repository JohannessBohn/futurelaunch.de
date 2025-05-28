<?php
/**
 * Subscription API Endpoint
 * 
 * A server-side fallback for the client-side subscription handling
 * This provides a way to handle subscriptions when JavaScript localStorage
 * is not available or insufficient
 */

// Set JSON content type
header('Content-Type: application/json');

// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// If form submission, use $_POST instead
if (empty($data) && !empty($_POST)) {
    $data = $_POST;
}

// Validate email
$email = trim($data['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Valid email address is required']);
    exit;
}

// Data file path
$file = __DIR__ . '/../data/subscribers.json';

// Create directory if it doesn't exist
if (!file_exists(dirname($file))) {
    mkdir(dirname($file), 0755, true);
}

// Load existing subscribers
$subscribers = [];
if (file_exists($file)) {
    $fileContent = file_get_contents($file);
    if (!empty($fileContent)) {
        $data = json_decode($fileContent, true);
        $subscribers = $data['subscribers'] ?? [];
    }
}

// Check if email already exists
foreach ($subscribers as $subscriber) {
    if ($subscriber['email'] === $email) {
        echo json_encode(['success' => false, 'message' => 'Email already subscribed']);
        exit;
    }
}

// Add new subscriber
$subscribers[] = [
    'email' => $email,
    'date' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

// Save to file
$result = file_put_contents(
    $file, 
    json_encode(['subscribers' => $subscribers], JSON_PRETTY_PRINT)
);

if ($result === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to save subscription']);
    exit;
}

// Success response
echo json_encode(['success' => true, 'message' => 'Subscription successful']);
