<?php
/**
 * FutureLaunch Installationsskript
 * Einrichtung der erforderlichen Verzeichnisse und Konfigurationen
 */

// Fehlerberichte aktivieren während der Installation
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Prüfen, ob bereits installiert
if (file_exists('config/installed.flag')) {
    header('Location: index.html');
    exit();
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';
$config = [
    'use_db' => false,            // Standardmäßig keine DB verwenden
    'db_host' => 'localhost',
    'db_name' => 'futurelaunch',
    'db_user' => 'root',
    'db_pass' => '',
    'admin_user' => 'admin',
    'admin_pass' => ''
];

// Formular-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Systemanforderungen prüfen
        $requirements = [
            'PHP 7.4 oder höher' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'MySQLi Extension' => extension_loaded('mysqli'),
            'PDO Extension' => extension_loaded('pdo_mysql'),
            'config Directory Writable' => is_writable(__DIR__ . '/config'),
            'JSON Extension' => extension_loaded('json'),
            'cURL Extension' => extension_loaded('curl')
        ];
        
        if (in_array(false, $requirements, true)) {
            $error = 'Some server requirements are not met. Please fix them before continuing.';
        } else {
            header('Location: ?step=2');
            exit();
        }
    } 
    elseif ($step === 2) {
        // Database connection test
        $config = array_merge($config, $_POST);
        
        try {
            // Test MySQL connection
            $dsn = "mysql:host={$config['db_host']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            // Try to create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$config['db_name']}`");
            
            // Save config
            $configContent = "<?php\n";
            $configContent .= "// Database configuration\n";
            $configContent .= "define('DB_HOST', '" . addslashes($config['db_host']) . "');\n";
            $configContent .= "define('DB_NAME', '" . addslashes($config['db_name']) . "');\n";
            $configContent .= "define('DB_USER', '" . addslashes($config['db_user']) . "');\n";
            $configContent .= "define('DB_PASS', '" . addslashes($config['db_pass']) . "');\n";
            $configContent .= "define('ADMIN_USER', '" . addslashes($config['admin_user']) . "');\n";
            $configContent .= "define('ADMIN_PASS', '" . password_hash($config['admin_pass'], PASSWORD_DEFAULT) . "');\n";
            
            if (!is_dir('config')) {
                mkdir('config', 0755, true);
            }
            
            file_put_contents('config/database.php', $configContent);
            
            // Create tables
            $sql = file_get_contents('database/schema.sql');
            $pdo->exec($sql);
            
            // Mark as installed
            file_put_contents('config/installed.flag', '1');
            
            // Redirect to success
            header('Location: ?step=3');
            exit();
            
        } catch (PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - FutureLaunch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .install-container {
            max-width: 800px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .install-header {
            background: #4e73df;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .install-body {
            padding: 30px;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .requirement {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .requirement.passed {
            border-left: 4px solid #1cc88a;
        }
        .requirement.failed {
            border-left: 4px solid #e74a3b;
        }
        .requirement i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container mx-auto">
            <div class="install-header">
                <h2>FutureLaunch Installation</h2>
                <div class="progress mt-3" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" style="width: <?= ($step / 3) * 100 ?>%;" 
                         aria-valuenow="<?= $step ?>" aria-valuemin="1" aria-valuemax="3"></div>
                </div>
            </div>
            
            <div class="install-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <!-- Step 1: Requirements Check -->
                <div class="step <?= $step === 1 ? 'active' : '' ?>" id="step1">
                    <h4>System Requirements</h4>
                    <p>Before we start, let's check if your server meets the requirements.</p>
                    
                    <?php
                    $requirements = [
                        'PHP 7.4 or higher' => version_compare(PHP_VERSION, '7.4.0', '>='),
                        'MySQLi Extension' => extension_loaded('mysqli'),
                        'PDO Extension' => extension_loaded('pdo_mysql'),
                        'config Directory Writable' => is_writable(__DIR__ . '/config') || (!file_exists(__DIR__ . '/config') && is_writable(__DIR__)),
                        'JSON Extension' => extension_loaded('json'),
                        'cURL Extension' => extension_loaded('curl')
                    ];
                    
                    foreach ($requirements as $name => $passed):
                        $class = $passed ? 'passed' : 'failed';
                        $icon = $passed ? 'check-circle text-success' : 'times-circle text-danger';
                    ?>
                        <div class="requirement <?= $class ?>">
                            <i class="fas fa-<?= $icon ?>"></i>
                            <?= htmlspecialchars($name) ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-4">
                        <form method="post" action="?step=1">
                            <button type="submit" class="btn btn-primary" <?= in_array(false, $requirements, true) ? 'disabled' : '' ?>>
                                Continue <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Step 2: Database Setup -->
                <div class="step <?= $step === 2 ? 'active' : '' ?>" id="step2">
                    <h4>Database Configuration</h4>
                    <p>Please enter your database connection details.</p>
                    
                    <form method="post" action="?step=2">
                        <div class="mb-3">
                            <label class="form-label">Database Host</label>
                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                            <div class="form-text">Usually 'localhost'</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Database Name</label>
                            <input type="text" name="db_name" class="form-control" required>
                            <div class="form-text">The database name to use</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Database Username</label>
                            <input type="text" name="db_user" class="form-control" value="root" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Database Password</label>
                            <input type="password" name="db_pass" class="form-control">
                            <div class="form-text">Leave empty if no password is set</div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5>Admin Account</h5>
                        <div class="mb-3">
                            <label class="form-label">Admin Username</label>
                            <input type="text" name="admin_user" class="form-control" value="admin" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Password</label>
                            <input type="password" name="admin_pass" class="form-control" required>
                            <div class="form-text">Choose a strong password</div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="?step=1" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Install Now <i class="fas fa-check ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Step 3: Installation Complete -->
                <div class="step <?= $step === 3 ? 'active' : '' ?> text-center" id="step3">
                    <div class="py-4">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h3>Installation Complete!</h3>
                        <p class="lead">FutureLaunch has been successfully installed.</p>
                        
                        <div class="alert alert-warning text-start">
                            <h5>Important Security Note:</h5>
                            <p>For security reasons, please delete or rename the <code>install.php</code> file.</p>
                            <pre class="bg-dark text-white p-2 rounded"><?= htmlspecialchars('rm ' . __DIR__ . '/install.php') ?></pre>
                        </div>
                        
                        <div class="mt-4">
                            <a href="admin-new.php" class="btn btn-primary me-2">
                                Go to Admin Panel <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            <a href="index.html" class="btn btn-outline-secondary">
                                Visit Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
</body>
</html>
