<?php
/**
 * FutureLaunch Setup Tool
 * Erstellt die notwendige Verzeichnisstruktur und Basisdateien
 */

// Setup-Status
$status = 'checking';
$messages = [];
$success = true;

// Verzeichnisstruktur prüfen und erstellen
$directories = [
    'assets',
    'assets/img',
    'assets/css',
    'assets/js',
    'config',
    'data',
    'includes',
    'logs',
    'uploads',
    'templates'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            $messages[] = "Verzeichnis '$dir' erfolgreich erstellt.";
        } else {
            $success = false;
            $messages[] = "FEHLER: Konnte Verzeichnis '$dir' nicht erstellen.";
        }
    } else {
        $messages[] = "Verzeichnis '$dir' bereits vorhanden.";
    }
}

// Platzhalter-Datei für Uploads erstellen
if (!file_exists('uploads/.gitkeep')) {
    if (touch('uploads/.gitkeep')) {
        $messages[] = "Datei 'uploads/.gitkeep' erstellt.";
    }
}

// Überprüfen, ob Basisdateien existieren
$baseFiles = [
    'config/db_config.php' => "<?php\n// Datenbank-Konfiguration\ndefine('DB_HOST', 'localhost');\ndefine('DB_NAME', 'futurelaunch');\ndefine('DB_USER', 'root');\ndefine('DB_PASS', '');\n\n/**\n * Stellt eine Datenbankverbindung her\n * @return PDO Die Datenbankverbindung\n */\nfunction connectDB() {\n    try {\n        \$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';\n        return new PDO(\$dsn, DB_USER, DB_PASS, [\n            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC\n        ]);\n    } catch (PDOException \$e) {\n        die('Datenbankverbindung fehlgeschlagen: ' . \$e->getMessage());\n    }\n}",
    
    'data/subscribers.json' => '{"subscribers":[]}',
    
    'logs/error.log' => '',
    'logs/access.log' => ''
];

foreach ($baseFiles as $file => $content) {
    if (!file_exists($file)) {
        if (file_put_contents($file, $content)) {
            $messages[] = "Datei '$file' erstellt.";
        } else {
            $success = false;
            $messages[] = "FEHLER: Konnte Datei '$file' nicht erstellen.";
        }
    } else {
        $messages[] = "Datei '$file' bereits vorhanden.";
    }
}

// Setup abgeschlossen
$status = $success ? 'success' : 'error';

// Setup-Flagge setzen
if ($success) {
    file_put_contents('data/setup_complete.txt', date('Y-m-d H:i:s'));
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutureLaunch - Setup</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 40px;
        }
        
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .setup-logo {
            width: 120px;
            margin-bottom: 20px;
        }
        
        .setup-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .setup-card-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
        }
        
        .setup-card-body {
            padding: 25px;
            background-color: white;
        }
        
        .message-list {
            max-height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .message-item {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .message-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .message-success {
            color: #28a745;
        }
        
        .message-error {
            color: #dc3545;
        }
        
        .message-info {
            color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <img src="assets/img/logo.svg" alt="FutureLaunch Logo" class="setup-logo">
            <h1>FutureLaunch Setup</h1>
            <p class="lead">Willkommen beim Setup-Assistenten für FutureLaunch</p>
        </div>
        
        <div class="setup-card">
            <div class="setup-card-header">
                <?php if ($status === 'success'): ?>
                    <i class="fas fa-check-circle me-2"></i> Setup erfolgreich abgeschlossen
                <?php elseif ($status === 'error'): ?>
                    <i class="fas fa-exclamation-circle me-2"></i> Fehler beim Setup
                <?php else: ?>
                    <i class="fas fa-cog me-2"></i> Setup-Status
                <?php endif; ?>
            </div>
            
            <div class="setup-card-body">
                <?php if ($status === 'success'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> Das Setup wurde erfolgreich abgeschlossen. Ihre Website ist jetzt einsatzbereit.
                    </div>
                <?php elseif ($status === 'error'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> Beim Setup sind Fehler aufgetreten. Bitte überprüfen Sie die Meldungen unten.
                    </div>
                <?php endif; ?>
                
                <h5 class="mb-3">Setup-Protokoll:</h5>
                <div class="message-list">
                    <?php foreach ($messages as $message): ?>
                        <div class="message-item">
                            <?php if (strpos($message, 'FEHLER') !== false): ?>
                                <i class="fas fa-times-circle text-danger me-2"></i>
                                <span class="message-error"><?php echo htmlspecialchars($message); ?></span>
                            <?php elseif (strpos($message, 'bereits vorhanden') !== false): ?>
                                <i class="fas fa-info-circle text-info me-2"></i>
                                <span class="message-info"><?php echo htmlspecialchars($message); ?></span>
                            <?php else: ?>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span class="message-success"><?php echo htmlspecialchars($message); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-primary me-2">
                        <i class="fas fa-tachometer-alt me-2"></i> Zum Dashboard
                    </a>
                    <a href="index.html" class="btn btn-secondary">
                        <i class="fas fa-home me-2"></i> Zur Startseite
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center text-muted">
            <p><small>&copy; <?php echo date('Y'); ?> FutureLaunch.de - Alle Rechte vorbehalten</small></p>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
