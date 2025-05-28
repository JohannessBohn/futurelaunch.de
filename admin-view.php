<?php
// Simple newsletter subscriber admin interface
session_start();

// Admin credentials
$admin_username = 'gibmirdeinGeld';
$admin_password = '!Dome_Jojo2025';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin-view.php');
        exit();
    } else {
        $login_error = 'Ungültige Anmeldedaten';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-view.php');
    exit();
}

// Check if logged in
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Datenbankverbindung laden
require_once 'config/db_config.php';

// Abonnenten aus der Datenbank holen
function getSubscribers() {
    try {
        $db = connectDB();
        $stmt = $db->query("SELECT email, date_created as date FROM newsletter_subscribers WHERE active = 1 ORDER BY date_created DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Wenn ein Datenbankfehler auftritt, versuche es mit der JSON-Datei als Backup
        $file = 'data/subscribers.json';
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return $data ?: [];
        }
        return [];
    }
}

// Handle delete action
if ($is_logged_in && isset($_GET['delete'])) {
    $email_to_delete = $_GET['delete'];
    
    try {
        // Aus der Datenbank löschen (oder auf inaktiv setzen)
        $db = connectDB();
        $stmt = $db->prepare("UPDATE newsletter_subscribers SET active = 0 WHERE email = ?");
        $stmt->execute([$email_to_delete]);
        
        // Auch aus der JSON-Datei als Backup entfernen
        $file = 'data/subscribers.json';
        if (file_exists($file)) {
            $subscribers = json_decode(file_get_contents($file), true) ?: [];
            
            // Abonnent finden und entfernen
            $updated_subscribers = [];
            foreach ($subscribers as $subscriber) {
                if ($subscriber['email'] !== $email_to_delete) {
                    $updated_subscribers[] = $subscriber;
                }
            }
            
            // Aktualisierte Liste speichern
            file_put_contents($file, json_encode($updated_subscribers, JSON_PRETTY_PRINT));
        }
    } catch (PDOException $e) {
        // Fehler in Logdatei schreiben
        error_log('Newsletter Löschfehler: ' . $e->getMessage());
    }
    
    // Weiterleitung, um erneutes Absenden zu vermeiden
    header('Location: admin-view.php?deleted=1');
    exit();
}

// Get all subscribers
$subscribers = $is_logged_in ? getSubscribers() : [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Admin - FutureLaunch</title>
    <style>
        /* Bootstrap core styles (embedded to ensure they load) */
        :root{--bs-blue:#0d6efd;--bs-indigo:#6610f2;--bs-purple:#6f42c1;--bs-pink:#d63384;--bs-red:#dc3545;--bs-orange:#fd7e14;--bs-yellow:#ffc107;--bs-green:#198754;--bs-teal:#20c997;--bs-cyan:#0dcaf0;--bs-white:#fff;--bs-gray:#6c757d;--bs-gray-dark:#343a40;--bs-gray-100:#f8f9fa;--bs-gray-200:#e9ecef;--bs-gray-300:#dee2e6;--bs-gray-400:#ced4da;--bs-gray-500:#adb5bd;--bs-gray-600:#6c757d;--bs-gray-700:#495057;--bs-gray-800:#343a40;--bs-gray-900:#212529;--bs-primary:#0d6efd;--bs-secondary:#6c757d;--bs-success:#198754;--bs-info:#0dcaf0;--bs-warning:#ffc107;--bs-danger:#dc3545;--bs-light:#f8f9fa;--bs-dark:#212529;--bs-primary-rgb:13,110,253;--bs-secondary-rgb:108,117,125;--bs-success-rgb:25,135,84;--bs-info-rgb:13,202,240;--bs-warning-rgb:255,193,7;--bs-danger-rgb:220,53,69;--bs-light-rgb:248,249,250;--bs-dark-rgb:33,37,41;--bs-white-rgb:255,255,255;--bs-black-rgb:0,0,0;--bs-body-color-rgb:33,37,41;--bs-body-bg-rgb:255,255,255;--bs-font-sans-serif:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--bs-font-monospace:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;--bs-gradient:linear-gradient(180deg,rgba(255,255,255,.15),rgba(255,255,255,0));--bs-body-font-family:var(--bs-font-sans-serif);--bs-body-font-size:1rem;--bs-body-font-weight:400;--bs-body-line-height:1.5;--bs-body-color:#212529;--bs-body-bg:#fff}*,::after,::before{box-sizing:border-box}@media(prefers-reduced-motion:no-preference){:root{scroll-behavior:smooth}}body{margin:0;font-family:var(--bs-body-font-family);font-size:var(--bs-body-font-size);font-weight:var(--bs-body-font-weight);line-height:var(--bs-body-line-height);color:var(--bs-body-color);text-align:var(--bs-body-text-align);background-color:var(--bs-body-bg);-webkit-text-size-adjust:100%;-webkit-tap-highlight-color:transparent}
        
        .h1,.h2,.h3,.h4,.h5,.h6,h1,h2,h3,h4,h5,h6{margin-top:0;margin-bottom:.5rem;font-weight:500;line-height:1.2}.h1,h1{font-size:calc(1.375rem + 1.5vw)}@media(min-width:1200px){.h1,h1{font-size:2.5rem}}.h2,h2{font-size:calc(1.325rem + .9vw)}@media(min-width:1200px){.h2,h2{font-size:2rem}}.h3,h3{font-size:calc(1.3rem + .6vw)}@media(min-width:1200px){.h3,h3{font-size:1.75rem}}.h4,h4{font-size:calc(1.275rem + .3vw)}@media(min-width:1200px){.h4,h4{font-size:1.5rem}}.h5,h5{font-size:1.25rem}.h6,h6{font-size:1rem}p{margin-top:0;margin-bottom:1rem}
        
        .container{width:100%;padding-right:var(--bs-gutter-x,.75rem);padding-left:var(--bs-gutter-x,.75rem);margin-right:auto;margin-left:auto}@media(min-width:576px){.container{max-width:540px}}@media(min-width:768px){.container{max-width:720px}}@media(min-width:992px){.container{max-width:960px}}@media(min-width:1200px){.container{max-width:1140px}}@media(min-width:1400px){.container{max-width:1320px}}
        
        .btn{display:inline-block;font-weight:400;line-height:1.5;color:#212529;text-align:center;text-decoration:none;vertical-align:middle;cursor:pointer;-webkit-user-select:none;-moz-user-select:none;user-select:none;background-color:transparent;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;border-radius:.25rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out}@media(prefers-reduced-motion:reduce){.btn{transition:none}}.btn:hover{color:#212529}.btn:focus{outline:0;box-shadow:0 0 0 .25rem rgba(13,110,253,.25)}.btn-primary{color:#fff;background-color:#0d6efd;border-color:#0d6efd}.btn-primary:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}.btn-danger{color:#fff;background-color:#dc3545;border-color:#dc3545}.btn-danger:hover{color:#fff;background-color:#bb2d3b;border-color:#b02a37}.btn-sm{padding:.25rem .5rem;font-size:.875rem;border-radius:.2rem}

        .btn-outline-primary{color:#0d6efd;border-color:#0d6efd}.btn-outline-primary:hover{color:#fff;background-color:#0d6efd;border-color:#0d6efd}
        
        .form-control{display:block;width:100%;padding:.375rem .75rem;font-size:1rem;font-weight:400;line-height:1.5;color:#212529;background-color:#fff;background-clip:padding-box;border:1px solid #ced4da;-webkit-appearance:none;-moz-appearance:none;appearance:none;border-radius:.25rem;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out}@media(prefers-reduced-motion:reduce){.form-control{transition:none}}.form-control:focus{color:#212529;background-color:#fff;border-color:#86b7fe;outline:0;box-shadow:0 0 0 .25rem rgba(13,110,253,.25)}
        
        .alert{position:relative;padding:1rem 1rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.25rem}.alert-success{color:#0f5132;background-color:#d1e7dd;border-color:#badbcc}.alert-danger{color:#842029;background-color:#f8d7da;border-color:#f5c2c7}.alert-info{color:#055160;background-color:#cff4fc;border-color:#b6effb}
        
        .table{--bs-table-bg:transparent;--bs-table-accent-bg:transparent;--bs-table-striped-color:#212529;--bs-table-striped-bg:rgba(0,0,0,.05);--bs-table-active-color:#212529;--bs-table-active-bg:rgba(0,0,0,.1);--bs-table-hover-color:#212529;--bs-table-hover-bg:rgba(0,0,0,.075);width:100%;margin-bottom:1rem;color:#212529;vertical-align:top;border-color:#dee2e6}.table>:not(caption)>*>*{padding:.5rem .5rem;background-color:var(--bs-table-bg);border-bottom-width:1px;box-shadow:inset 0 0 0 9999px var(--bs-table-accent-bg)}.table>tbody{vertical-align:inherit}.table>thead{vertical-align:bottom}.table>:not(:first-child){border-top:2px solid currentColor}.table-striped>tbody>tr:nth-of-type(odd)>*{--bs-table-accent-bg:var(--bs-table-striped-bg);color:var(--bs-table-striped-color)}
        
        .mb-3{margin-bottom:1rem!important}.mb-4{margin-bottom:1.5rem!important}.mt-3{margin-top:1rem!important}.mt-4{margin-top:1.5rem!important}.me-2{margin-right:.5rem!important}
        
        .text-center{text-align:center!important}.text-decoration-none{text-decoration:none!important}
        
        .d-grid{display:grid!important}
        
        /* Custom styles */
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .admin-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .table {
            margin-top: 20px;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
        <!-- Login Form -->
        <div class="login-container">
            <h2 class="text-center mb-4">FutureLaunch Admin</h2>
            
            <?php if (isset($login_error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Benutzername</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Passwort</label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" name="login" class="btn btn-primary">Anmelden</button>
                </div>
                <p class="mt-3 text-center">
                    <a href="index.html" class="text-decoration-none">Zurück zur Homepage</a>
                </p>
            </form>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="admin-container">
            <div class="actions">
                <h2>Newsletter Abonnenten</h2>
                <div>
                    <a href="index.html" class="btn btn-outline-primary me-2">Homepage</a>
                    <a href="admin-view.php?logout=1" class="btn btn-danger">Abmelden</a>
                </div>
            </div>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">
                    Der Abonnent wurde erfolgreich gelöscht.
                </div>
            <?php endif; ?>
            
            <?php if (empty($subscribers)): ?>
                <div class="alert alert-info">
                    Keine Abonnenten gefunden.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>E-Mail</th>
                                <th>Datum</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $index => $subscriber): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['date'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="admin-view.php?delete=<?php echo urlencode($subscriber['email']); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Sind Sie sicher, dass Sie diesen Abonnenten löschen möchten?')">
                                            Löschen
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <p><strong>Gesamt:</strong> <?php echo count($subscribers); ?> Abonnenten</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
</body>
</html>
