<?php
/**
 * Datenbankkonfiguration für FutureLaunch
 */

// Datenbankverbindungsdaten
define('DB_HOST', 'localhost');
define('DB_NAME', 'futurelaunch');
define('DB_USER', 'root');
define('DB_PASS', ''); // Standard für XAMPP, anpassen falls nötig

/**
 * Stellt eine Datenbankverbindung her
 * 
 * @return PDO Die Datenbankverbindung
 */
function connectDB() {
    try {
        $db = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $db;
    } catch (PDOException $e) {
        die('Datenbankfehler: ' . $e->getMessage());
    }
}
