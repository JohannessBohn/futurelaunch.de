<?php
/**
 * Initialisiert die Datenstrukturen für FutureLaunch
 */

// Fehlermeldungen aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>FutureLaunch - Dateninitialisierung</h1>";

// Verzeichnisse erstellen
$directories = ['data', 'logs'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✓ Verzeichnis '$dir' erstellt</p>";
        } else {
            echo "<p>✗ Fehler beim Erstellen von Verzeichnis '$dir'</p>";
        }
    } else {
        echo "<p>ℹ Verzeichnis '$dir' existiert bereits</p>";
    }
}

// subscribers.json mit korrekter Struktur erstellen
$subscribersFile = __DIR__ . '/data/subscribers.json';
$subscribersData = ['subscribers' => []];

// Test-Abonnenten hinzufügen
$testSubscribers = [
    [
        'email' => 'test@example.com',
        'date' => date('Y-m-d H:i:s'),
        'status' => 'active'
    ],
    [
        'email' => 'info@futurelaunch.de',
        'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'status' => 'active'
    ]
];

$subscribersData['subscribers'] = $testSubscribers;

if (file_put_contents($subscribersFile, json_encode($subscribersData, JSON_PRETTY_PRINT))) {
    echo "<p>✓ Subscribers-Datei mit Test-Daten erstellt</p>";
} else {
    echo "<p>✗ Fehler beim Erstellen der Subscribers-Datei</p>";
}

// Leere Log-Dateien erstellen
$logFiles = ['errors.log', 'newsletter.log', 'newsletter_requests.log'];
foreach ($logFiles as $logFile) {
    $file = __DIR__ . '/logs/' . $logFile;
    if (touch($file)) {
        echo "<p>✓ Log-Datei '$logFile' erstellt</p>";
    } else {
        echo "<p>✗ Fehler beim Erstellen der Log-Datei '$logFile'</p>";
    }
}

echo "<p>Initialisierung abgeschlossen.</p>";
echo "<p><a href='dashboard.php'>Zum Dashboard</a></p>";
?>
