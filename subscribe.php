<?php
/**
 * Newsletter-Anmeldeverarbeitung
 * 
 * Verarbeitet Anmeldungen zum Newsletter und speichert sie in einer JSON-Datei
 */

// Debugging aktivieren (nur für Entwicklung)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS-Header setzen
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS-Anfragen abfangen (für CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Nur POST-Anfragen verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Nur POST-Anfragen sind erlaubt.']);
    exit;
}

// E-Mail aus den POST-Daten holen
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Vorbereiten der Antwort
$response = ['success' => false];

// Loggen der eingehenden Anfrage (für Debugging)
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}
file_put_contents('logs/newsletter_requests.log', 
    date('[Y-m-d H:i:s]') . ' Email: ' . $email . ' IP: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL, 
    FILE_APPEND);

// E-Mail validieren
if (empty($email)) {
    $response['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
} else {
    // In JSON-Datei speichern
    if (!is_dir('data')) {
        mkdir('data', 0755, true);
    }
    
    // Pfad zur JSON-Datei
    $file = __DIR__ . '/data/subscribers.json';
    
    // Sicherstellen, dass die Datei existiert und ein gültiges Format hat
    if (!file_exists($file) || filesize($file) === 0) {
        // Wenn die Datei nicht existiert oder leer ist, erstelle sie mit einer leeren Struktur
        file_put_contents($file, json_encode(['subscribers' => []], JSON_PRETTY_PRINT));
    }
    
    // Bestehende Abonnenten laden
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    
    // Bei Problemen mit dem JSON-Format, setze zurück
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['subscribers']) || !is_array($data['subscribers'])) {
        $data = ['subscribers' => []];
    }
    
    // Prüfen, ob E-Mail bereits existiert
    $exists = false;
    foreach ($data['subscribers'] as $subscriber) {
        if (isset($subscriber['email']) && $subscriber['email'] === $email) {
            $exists = true;
            break;
        }
    }
    
    // Wenn die E-Mail nicht existiert, hinzufügen
    if (!$exists) {
        $data['subscribers'][] = [
            'email' => $email,
            'date' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];
        
        // Speichern mit Fehlerbehandlung
        if (file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false) {
            $response['success'] = true;
            $response['message'] = 'Vielen Dank für Ihr Abonnement!';
            
            // Log über erfolgreiche Anmeldung
            file_put_contents('logs/newsletter.log', 
                date('[Y-m-d H:i:s]') . ' Neue Anmeldung: ' . $email . PHP_EOL, 
                FILE_APPEND);
        } else {
            $response['message'] = 'Fehler beim Speichern der Anmeldung. Bitte versuchen Sie es später erneut.';
            // Fehler loggen
            file_put_contents('logs/errors.log', 
                date('[Y-m-d H:i:s]') . ' Fehler beim Speichern: ' . $email . PHP_EOL, 
                FILE_APPEND);
        }
    } else {
        // Wenn bereits vorhanden, trotzdem Erfolg melden
        $response['success'] = true;
        $response['message'] = 'Sie sind bereits für unseren Newsletter angemeldet.';
    }
}

// JSON-Antwort zurückgeben
echo json_encode($response);
exit;
