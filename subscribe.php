<?php
// Set headers for CORS and JSON response
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Get email from POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.']);
    exit;
}

// Create subscribers directory if it doesn't exist
$subscribersDir = __DIR__ . '/subscribers';
if (!file_exists($subscribersDir)) {
    mkdir($subscribersDir, 0777, true);
}

// Save to file
$file = $subscribersDir . '/subscribers.txt';
$data = $email . ' | ' . date('Y-m-d H:i:s') . PHP_EOL;

if (file_put_contents($file, $data, FILE_APPEND | LOCK_EX) !== false) {
    echo json_encode(['success' => true, 'message' => 'Vielen Dank für Ihr Abonnement!']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.']);
}
?>
