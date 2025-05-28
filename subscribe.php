<?php
/**
 * Newsletter-Anmeldeverarbeitung
 * 
 * Verarbeitet Anmeldungen zum Newsletter und speichert sie in der Datenbank
 * sowie in einer JSON-Datei als Backup
 */

// Datenbankverbindung laden
require_once 'config/db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// E-Mail aus den POST-Daten holen
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Vorbereiten der Antwort
$response = ['success' => false];

// E-Mail validieren
if (empty($email)) {
    $response['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
} else {
    // Doppelte Speichermethode verwenden (Datenbank + JSON als Backup)
    try {
        // 1. In der Datenbank speichern
        $db = connectDB();
        
        // Prüfen, ob die Tabelle existiert, wenn nicht, erstellen
        $db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            active TINYINT(1) NOT NULL DEFAULT 1
        );");
        
        // E-Mail in die Datenbank einfügen
        $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?) 
                            ON DUPLICATE KEY UPDATE date_created = CURRENT_TIMESTAMP");
        $stmt->execute([$email]);
        
        // 2. Auch in JSON-Datei als Backup speichern
        if (!is_dir('data')) {
            mkdir('data', 0755, true);
        }
        
        // Bestehende Abonnenten laden
        $subscribers = [];
        $file = 'data/subscribers.json';
        
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (!empty($content)) {
                $subscribers = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $subscribers = []; // Zurücksetzen bei ungültigem JSON
                }
            }
        }
        
        // Prüfen, ob E-Mail bereits existiert
        $exists = false;
        foreach ($subscribers as $subscriber) {
            if (isset($subscriber['email']) && $subscriber['email'] === $email) {
                $exists = true;
                break;
            }
        }
        
        // Wenn die E-Mail nicht existiert, hinzufügen
        if (!$exists) {
            $subscribers[] = [
                'email' => $email,
                'date' => date('Y-m-d H:i:s')
            ];
            file_put_contents($file, json_encode($subscribers, JSON_PRETTY_PRINT));
        }
        
        // Erfolgreiche Antwort senden
        $response['success'] = true;
        $response['message'] = 'Vielen Dank für Ihr Abonnement!';
        
    } catch (PDOException $e) {
        // Bei Datenbankfehler Fehlermeldung ausgeben
        $response['message'] = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
        // Fehler in Logdatei schreiben
        error_log('Newsletter Fehler: ' . $e->getMessage());
    }
}

// JSON-Antwort zurückgeben
echo json_encode($response);
