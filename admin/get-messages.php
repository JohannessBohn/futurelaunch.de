<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$messagesDir = __DIR__ . '/../messages';
$messages = [];

// Check if messages directory exists
if (file_exists($messagesDir) && is_dir($messagesDir)) {
    // Get all JSON files in the messages directory
    $files = glob($messagesDir . '/message_*.json');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        
        if ($data) {
            // Add file info and status
            $data['id'] = basename($file, '.json');
            $data['status'] = 'new'; // You can implement read/unread status if needed
            $data['subject'] = $data['subject'] ?? 'Kein Betreff';
            
            // Add to messages array
            $messages[] = $data;
        }
    }
    
    // Sort by date (newest first)
    usort($messages, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

echo json_encode($messages);
?>