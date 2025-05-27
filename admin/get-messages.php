<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, 'Method not allowed. Please use GET.', null, 405);
}

// Database connection and fetch messages
$dataDir = __DIR__ . '/../data';
$dbPath = $dataDir . '/submissions.db';
$messages = [];

try {
    // Check if database file exists
    if (!file_exists($dbPath)) {
        sendJsonResponse(true, 'No messages found (database file does not exist).', []);
    }

    $db = new SQLite3($dbPath);

    // Fetch messages, ordered by date descending
    $results = $db->query('SELECT id, name, email, phone, subject, message, date, read_status FROM submissions ORDER BY date DESC');

    if ($results) {
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $messages[] = $row;
        }
    }

    $db->close();
    sendJsonResponse(true, 'Messages fetched successfully.', $messages);

} catch (Exception $e) {
    error_log("Database error fetching messages: " . $e->getMessage());
    sendJsonResponse(false, 'Error fetching messages from database.', null, 500);
}
?> 