<?php
/**
 * Datenbank-Setup und Test-Abonnent
 */

// Datenbankverbindung laden
require_once 'config/db_config.php';

echo "<h2>Datenbank-Setup</h2>";

try {
    // Verbindung zur MySQL-Instanz herstellen (ohne Datenbank)
    $pdo = new PDO(
        'mysql:host=' . DB_HOST,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Datenbank erstellen, falls sie nicht existiert
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>Datenbank '" . DB_NAME . "' erfolgreich erstellt oder bereits vorhanden.</p>";
    
    // Zur futurelaunch-Datenbank wechseln
    $pdo->exec("USE " . DB_NAME);
    
    // Tabelle für Newsletter-Abonnenten erstellen
    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        active TINYINT(1) NOT NULL DEFAULT 1
    );");
    echo "<p>Tabelle 'newsletter_subscribers' erfolgreich erstellt.</p>";
    
    // Test-Abonnent hinzufügen
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?) 
                        ON DUPLICATE KEY UPDATE date_created = CURRENT_TIMESTAMP");
    $testEmail = "test@futurelaunch.de";
    $stmt->execute([$testEmail]);
    
    echo "<p>Test-Abonnent mit E-Mail '$testEmail' erfolgreich hinzugefügt.</p>";
    
    // Weitere Test-Abonnenten hinzufügen
    $moreEmails = [
        "info@futurelaunch.de",
        "kontakt@futurelaunch.de",
        "support@futurelaunch.de"
    ];
    
    foreach ($moreEmails as $email) {
        $stmt->execute([$email]);
        echo "<p>Abonnent mit E-Mail '$email' erfolgreich hinzugefügt.</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>Setup erfolgreich abgeschlossen! Du kannst jetzt <a href='admin-view.php'>zum Admin-Bereich</a> gehen, um die Abonnenten anzusehen.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Fehler: " . $e->getMessage() . "</p>";
}
