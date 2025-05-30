<?php

// Ensure no errors are output to the response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mail_error.log');

// Clear any previous output
if (ob_get_length()) ob_clean();

// Handle CORS headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Function to send JSON response
function sendJsonResponse(\, \, \ = null, \ = 200) {
    http_response_code(\);
    echo json_encode([
        'success' => \,
        'message' => \,
        'data' => \
    ]);
    exit;
}

// Handle preflight requests
if (\['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK', null, 200);
}

// Check if it's a POST request
if (\['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed. Please use POST.', null, 405);
}

try {
    // Get POST data
    \ = file_get_contents('php://input');
    \ = json_decode(\, true);

    // If JSON decode failed, try to get form data
    if (json_last_error() !== JSON_ERROR_NONE) {
        \ = \;
    }

    // If still no data
    if (empty(\)) {
        error_log('No data received in request');
        sendJsonResponse(false, 'No data received in request', null, 400);
    }

    // Log the submission for debugging
    \ = date('Y-m-d H:i:s') . ' - Form data received: ' . print_r(\, true);
    error_log(\);
    
    // Demo mode - always return success
    sendJsonResponse(true, 'Ihre Nachricht wurde erfolgreich gesendet!');

} catch (Exception \) {
    error_log('Contact form error: ' . \->getMessage());
    sendJsonResponse(false, 'Ein Fehler ist aufgetreten: ' . \->getMessage(), null, 500);
}

