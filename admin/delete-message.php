<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true'); // Allow credentials for cross-origin
header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../script/mail_error.log'); // Log to the same file as contact form

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK', null, 200);
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed. Please use POST.', null, 405);
}

// Get POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('Invalid JSON received for delete: ' . $raw_data);
    sendJsonResponse(false, 'Invalid JSON data received.', null, 400);
}

// Validate message ID
if (empty($data['id'])) {
    sendJsonResponse(false, 'Missing message ID.', null, 400);
}

$messageId = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);

if (!filter_var($messageId, FILTER_VALIDATE_INT)) {
    sendJsonResponse(false, 'Invalid message ID.', null, 400);
}

// Database connection and delete message
$dataDir = __DIR__ . '/../data';
$dbPath = $dataDir . '/submissions.db';

try {
    // Check if database file exists
    if (!file_exists($dbPath)) {
         sendJsonResponse(false, 'Database file not found.', null, 404);
    }

    $db = new SQLite3($dbPath);

    // Prepare and execute delete statement
    $stmt = $db->prepare('DELETE FROM submissions WHERE id = :id');
    $stmt->bindValue(':id', $messageId, SQLITE3_INTEGER);

    if ($stmt->execute()) {
         // Check if any row was actually deleted
         if ($db->changes() > 0) {
            sendJsonResponse(true, 'Message deleted successfully.');
         } else {
            sendJsonResponse(false, 'Message with specified ID not found.', null, 404);
         }
    } else {
         error_log('SQLite delete failed for ID ' . $messageId . ': ' . $db->lastErrorMsg());
         sendJsonResponse(false, 'Error deleting message from database.', null, 500);
    }

    $db->close();

} catch (Exception $e) {
    error_log("Database error deleting message: " . $e->getMessage());
    sendJsonResponse(false, 'An unexpected error occurred during deletion.', null, 500);
}
?> 