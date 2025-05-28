<?php
session_start();

// Simple authentication
$valid_username = 'admin';
$valid_password_hash = password_hash('admin123', PASSWORD_DEFAULT); // Change this password!

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === $valid_username && 
        password_verify($_POST['password'], $valid_password_hash)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin-simple.php');
        exit();
    } else {
        $login_error = 'Ungültige Anmeldedaten';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-simple.php');
    exit();
}

// Include storage class
require_once 'config/storage.php';
$storage = new NewsletterStorage();

// Handle actions
if (isset($_SESSION['admin_logged_in'])) {
    // Delete subscriber
    if (isset($_GET['delete'])) {
        $email = urldecode($_GET['delete']);
        $storage->deleteSubscriber($email);
        header('Location: admin-simple.php?deleted=1');
        exit();
    }
    
    // Export to CSV
    if (isset($_GET['export'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter_subscribers_'.date('Y-m-d').'.csv"');
        echo $storage->exportToCsv();
        exit();
    }
}

// Get all subscribers
$subscribers = $storage->getSubscribers();
$total_subscribers = count($subscribers);
$today_subscribers = count(array_filter($subscribers, function($sub) {
    return date('Y-m-d') === date('Y-m-d', strtotime($sub['created_at']));
}));

// Show login form if not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - FutureLaunch</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; }
            .login-container { max-width: 400px; width: 100%; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="login-container mx-auto">
                        <h2 class="text-center mb-4">Admin Login</h2>
                        <?php if (isset($login_error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($login_error) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Benutzername</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Passwort</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">Anmelden</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="index.html" class="text-muted">← Zurück zur Webseite</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FutureLaunch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #4e73df; --secondary: #858796; --success: #1cc88a; --danger: #e74a3b; }
        body { font-size: 0.9rem; background-color: #f8f9fc; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--primary) 10%, #224abe 100%); color: white; width: 250px; position: fixed; }
        .main-content { margin-left: 250px; padding: 1.5rem; min-height: 100vh; }
        .card { border: none; border-radius: 0.5rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); margin-bottom: 1.5rem; }
        .stats-card { border-left: 4px solid var(--primary); }
        .stats-card .icon { font-size: 2rem; opacity: 0.3; }
        @media (max-width: 768px) {
            .sidebar { margin-left: -250px; }
            .sidebar.show { margin-left: 0; }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="text-center py-4">
            <h4 class="text-white mb-0">FutureLaunch</h4>
            <p class="text-white-50 small mb-0">Admin Panel</p>
        </div>
        <hr class="bg-white-50 mx-3 my-2">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin-simple.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-white-50" href="?logout=1">
                    <i class="fas fa-fw fa-sign-out-alt"></i> Abmelden
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand navbar-light mb-4">
            <div class="container-fluid">
                <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0">Dashboard</h5>
                <div class="ms-auto">
                    <span class="me-3 d-none d-md-inline">Angemeldet als: <strong>Admin</strong></span>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Abonnent erfolgreich gelöscht.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 stats-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-uppercase text-primary font-weight-bold mb-1">
                                        Abonnenten gesamt</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_subscribers ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 stats-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-uppercase text-success font-weight-bold mb-1">
                                        Heute hinzugefügt</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_subscribers ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscribers Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Newsletter Abonnenten</h6>
                    <div class="mt-2 mt-md-0">
                        <a href="?export=1" class="btn btn-success text-white">
                            <i class="fas fa-download fa-sm me-1"></i> Export als CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($subscribers)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5>Keine Abonnenten gefunden</h5>
                            <p class="text-muted">Es wurden noch keine E-Mail-Adressen gespeichert.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>E-Mail</th>
                                        <th>Registriert am</th>
                                        <th>Status</th>
                                        <th class="text-end">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($subscriber['email']) ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($subscriber['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-success">Aktiv</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="?delete=<?= urlencode($subscriber['email']) ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Möchten Sie diesen Eintrag wirklich löschen?')">
                                                    <i class="fas fa-trash me-1"></i> Löschen
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
    </script>
</body>
</html>
