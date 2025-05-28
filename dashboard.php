<?php
/**
 * Admin Dashboard
 * Secure dashboard for FutureLaunch administration
 */

// Load authentication helper
require_once __DIR__ . '/includes/auth_helper.php';

// Initialize session
initSession();

// Process login
$loginError = '';
$lockedOut = false;

if (isset($_POST['login'])) {
    if (isLockedOut()) {
        $lockedOut = true;
        $loginError = 'Zu viele Anmeldeversuche. Bitte warten Sie ' . getRemainingLockoutTime() . ' Minuten.';
    } else {
        if (!verifyLogin($_POST['username'], $_POST['password'])) {
            $loginError = 'Ungültige Anmeldedaten. Bitte versuchen Sie es erneut.';
        }
    }
}

// Process logout
if (isset($_GET['logout'])) {
    logout();
    header('Location: dashboard.php');
    exit;
}

// Get subscribers from JSON file
function getSubscribersFromJSON() {
    $file = __DIR__ . '/data/subscribers.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        return $data['subscribers'] ?? [];
    }
    return [];
}

// Get subscribers (main function)
function getSubscribers() {
    return getSubscribersFromJSON();
}

// Get total stats
function getDashboardStats() {
    $subscribers = getSubscribers();
    $subscriberCount = count($subscribers);
    
    $stats = [
        'total_subscribers' => $subscriberCount,
        'subscribers_today' => 0,
        'subscribers_week' => 0,
        'subscribers_month' => 0
    ];
    
    // Calculate time-based stats
    $today = date('Y-m-d');
    $weekAgo = date('Y-m-d', strtotime('-7 days'));
    $monthAgo = date('Y-m-d', strtotime('-30 days'));
    
    foreach ($subscribers as $subscriber) {
        $date = isset($subscriber['date']) ? substr($subscriber['date'], 0, 10) : '';
        
        if ($date === $today) {
            $stats['subscribers_today']++;
        }
        
        if ($date >= $weekAgo) {
            $stats['subscribers_week']++;
        }
        
        if ($date >= $monthAgo) {
            $stats['subscribers_month']++;
        }
    }
    
    return $stats;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutureLaunch - Admin Dashboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--dark-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .sidebar {
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
        }
        
        .sidebar-brand {
            padding: 20px 15px;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-menu .fas {
            margin-right: 10px;
            width: 20px;
        }
        
        .content-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: all 0.5s;
        }
        
        .loading-logo {
            width: 100px;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-overlay" id="loadingScreen">
        <div class="text-center">
            <img src="assets/img/logo.png" alt="FutureLaunch Logo" class="loading-logo">
            <h3 class="mt-3">FutureLaunch</h3>
            <div class="spinner-border text-primary mt-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <?php if (!isAuthenticated()): ?>
        <!-- Login Page -->
        <div class="login-container">
            <div class="login-logo">
                <img src="assets/img/logo.png" alt="FutureLaunch Logo" height="80">
                <h2 class="mt-3">FutureLaunch</h2>
            </div>
            
            <div class="login-card">
                <div class="login-header">
                    Admin Login
                </div>
                <div class="card-body p-4">
                    <?php if ($loginError): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($lockedOut): ?>
                        <div class="alert alert-warning">
                            Zu viele fehlgeschlagene Anmeldeversuche. Bitte versuchen Sie es in 
                            <?php echo getRemainingLockoutTime(); ?> Minuten erneut.
                        </div>
                    <?php else: ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Benutzername</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="username" id="username" class="form-control" required autofocus>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Passwort</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i> Anmelden
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="sidebar-brand">
                        <i class="fas fa-rocket me-2"></i> FutureLaunch
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="#"><i class="fas fa-users"></i> Abonnenten</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> Newsletter</a></li>
                        <li><a href="#"><i class="fas fa-cog"></i> Einstellungen</a></li>
                        <li><a href="dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> Abmelden</a></li>
                    </ul>
                </div>
                
                <!-- Content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <h1><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h1>
                        <div>
                            <a href="index.html" class="btn btn-outline-primary">
                                <i class="fas fa-home me-1"></i> Zur Website
                            </a>
                            <a href="dashboard.php?logout=1" class="btn btn-outline-danger ms-2">
                                <i class="fas fa-sign-out-alt me-1"></i> Abmelden
                            </a>
                        </div>
                    </div>
                    
                    <?php $stats = getDashboardStats(); ?>
                    
                    <!-- Stats Cards -->
                    <div class="row mt-4">
                        <div class="col-md-3 mb-4">
                            <div class="card stat-card h-100 border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Gesamte Abonnenten</h6>
                                    <h2 class="card-title"><?php echo $stats['total_subscribers']; ?></h2>
                                    <p class="card-text text-success"><i class="fas fa-chart-line me-1"></i> Aktiv</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card stat-card h-100 border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Neue Abonnenten (Heute)</h6>
                                    <h2 class="card-title"><?php echo $stats['subscribers_today']; ?></h2>
                                    <p class="card-text text-primary"><i class="fas fa-calendar-day me-1"></i> Heute</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card stat-card h-100 border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Neue Abonnenten (Woche)</h6>
                                    <h2 class="card-title"><?php echo $stats['subscribers_week']; ?></h2>
                                    <p class="card-text text-info"><i class="fas fa-calendar-week me-1"></i> Letzte 7 Tage</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card stat-card h-100 border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Neue Abonnenten (Monat)</h6>
                                    <h2 class="card-title"><?php echo $stats['subscribers_month']; ?></h2>
                                    <p class="card-text text-warning"><i class="fas fa-calendar-alt me-1"></i> Letzte 30 Tage</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Subscribers Table -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="table-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Abonnentenliste</h4>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-file-export me-1"></i> Export CSV
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-sync-alt me-1"></i> Aktualisieren
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Email</th>
                                                <th>Datum</th>
                                                <th>Status</th>
                                                <th>Aktionen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $subscribers = getSubscribers(); ?>
                                            <?php if (empty($subscribers)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">
                                                        <i class="fas fa-info-circle me-2"></i> Keine Abonnenten gefunden
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($subscribers as $subscriber): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($subscriber['date'] ?? 'N/A'); ?></td>
                                                        <td>
                                                            <span class="badge bg-success">Aktiv</span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-info">
                                                                <i class="fas fa-envelope"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger ms-1">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Loading screen functionality
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const loadingScreen = document.getElementById('loadingScreen');
                if (loadingScreen) {
                    loadingScreen.style.opacity = 0;
                    setTimeout(function() {
                        loadingScreen.style.display = 'none';
                    }, 500);
                }
            }, 1000); // Hide after 1 second
        });
    </script>
</body>
</html>