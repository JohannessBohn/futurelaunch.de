<?php
/**
 * Cleanup-Skript für Produktionsvorbereitung
 * Entfernt Testdaten und temporäre Dateien
 */

// Sicherheitsabfrage
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<h1>FutureLaunch Cleanup</h1>";
    echo "<p>Dieses Skript entfernt alle Testdaten und bereitet das System für den Produktiveinsatz vor.</p>";
    echo "<p style='color: red;'><strong>WARNUNG:</strong> Alle Testdaten werden permanent gelöscht!</p>";
    echo "<p><a href='cleanup.php?confirm=yes' style='color: red;'>Cleanup starten</a></p>";
    exit;
}

// Liste der zu löschenden Testdateien
$filesToDelete = [
    'phptest.php',
    'test.php',
    'cleanup.php' // Selbstlöschung am Ende
];

// Liste der zu leerende Verzeichnisse (Inhalte löschen, aber Verzeichnis behalten)
$dirsToClean = [
    'data',
    'logs'
];

// Testdateien löschen
echo "<h2>Lösche Testdateien:</h2>";
foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<p>✓ Datei gelöscht: {$file}</p>";
        } else {
            echo "<p>✗ Fehler beim Löschen von: {$file}</p>";
        }
    } else {
        echo "<p>⚠ Datei nicht gefunden: {$file}</p>";
    }
}

// Verzeichnisse bereinigen
echo "<h2>Bereinige Verzeichnisse:</h2>";
foreach ($dirsToClean as $dir) {
    if (is_dir($dir)) {
        echo "<p>Verzeichnis: {$dir}</p>";
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                // Bestimmte Dateien behalten
                $keepFiles = [
                    $dir . '/.gitkeep',
                    $dir . '/subscribers.json'
                ];
                
                if (!in_array($file, $keepFiles)) {
                    if (unlink($file)) {
                        echo "<p>✓ Gelöscht: {$file}</p>";
                    } else {
                        echo "<p>✗ Fehler beim Löschen von: {$file}</p>";
                    }
                } else {
                    echo "<p>⚠ Behalte: {$file}</p>";
                }
            }
        }
    } else {
        echo "<p>⚠ Verzeichnis nicht gefunden: {$dir}</p>";
    }
}

// Erstelle eine leere subscribers.json, falls sie nicht existiert
if (!file_exists('data/subscribers.json')) {
    $emptySubscribers = json_encode(['subscribers' => []]);
    if (file_put_contents('data/subscribers.json', $emptySubscribers)) {
        echo "<p>✓ Leere subscribers.json erstellt</p>";
    } else {
        echo "<p>✗ Fehler beim Erstellen von subscribers.json</p>";
    }
}

// Erstelle .htaccess für Sicherheit
$htaccess = <<<EOT
# Security enhanced .htaccess
Options -Indexes
RewriteEngine On

# Ensure PHP files are processed
AddType application/x-httpd-php .php
<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>

# Block access to sensitive files
<FilesMatch "^(\.htaccess|\.gitignore|composer\.json|package\.json|.*\.log)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Protect config directory
<IfModule mod_rewrite.c>
    RewriteRule ^config/ - [F,L]
</IfModule>

# Compress text files
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Set browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# Default index files
DirectoryIndex index.php index.html
EOT;

if (file_put_contents('.htaccess', $htaccess)) {
    echo "<p>✓ Sicherheits-optimierte .htaccess erstellt</p>";
} else {
    echo "<p>✗ Fehler beim Erstellen der .htaccess</p>";
}

// Fertigmeldung
echo "<h2>Bereinigung abgeschlossen!</h2>";
echo "<p>Alle Testdaten wurden entfernt. Das System ist bereit für den Produktiveinsatz.</p>";
echo "<p><a href='index.html'>Zur Startseite</a></p>";
?>
