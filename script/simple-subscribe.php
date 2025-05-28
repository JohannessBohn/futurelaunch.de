<?php
// Set headers first to prevent any output before headers
if (ob_get_level()) ob_end_clean();

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple error handler
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Get email from POST data
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    sendError('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
}

// Save to a simple text file for now
$file = __DIR__ . '/newsletter_subscribers.txt';
file_put_contents($file, $email . PHP_EOL, FILE_APPEND);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Vielen Dank für Ihr Abonnement!',
    'email' => $email
]);
