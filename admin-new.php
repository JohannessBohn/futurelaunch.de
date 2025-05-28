<?php
// Include configuration and database connection
require_once 'config/database.php';
require_once 'admin-config.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin-new.php');
        exit();
    } else {
        $login_error = 'Ungültige Anmeldedaten';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-new.php');
    exit();
}

// Check if logged in
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// If not logged in, show login form
if (!$is_logged_in) {
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - FutureLaunch</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-container {
                max-width: 400px;
                width: 100%;
                padding: 2rem;
                background: white;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2 class="text-center mb-4">FutureLaunch Admin</h2>
            <?php if (isset($login_error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Benutzername</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Passwort</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100">Anmelden</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Database operations only for logged-in users
try {
    // Get total subscribers count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 1");
    $total_subscribers = $stmt->fetch()['total'];

    // Get today's subscribers count
    $stmt = $pdo->query("SELECT COUNT(*) as today FROM newsletter_subscribers WHERE DATE(created_at) = CURDATE()");
    $today_subscribers = $stmt->fetch()['today'];

    // Handle delete action
    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: admin-new.php?deleted=1');
        exit();
    }

    // Handle export
    if (isset($_GET['export'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter_subscribers_'.date('Y-m-d').'.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'E-Mail', 'Registriert am']);
        
        $stmt = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['id'],
                $row['email'],
                $row['created_at']
            ]);
        }
        fclose($output);
        exit();
    }

    // Get all subscribers for display
    $stmt = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
    $subscribers = $stmt->fetchAll();

} catch(PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
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
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --danger: #e74a3b;
            --light: #f8f9fc;
        }
        
        body {
            font-size: 0.9rem;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary) 10%, #224abe 100%);
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            margin: 0.2rem 1rem;
            border-radius: 0.35rem;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 1.5rem;
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        .navbar {
            background: white !important;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 0.5rem 1rem;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .btn-export {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-export:hover {
            background-color: #17a673;
            border-color: #169b6b;
        }
        
        .stats-card {
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .stats-card .card-body {
            padding: 1.25rem;
        }
        
        .stats-card .icon {
            font-size: 2rem;
            opacity: 0.3;
        }
        
        .stats-card .text-xs {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            font-weight: 600;
        }
        
        .stats-card .h5 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0.5rem 0 0;
        }
        
        /* Mobile styles */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
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
                <a class="nav-link active" href="admin-new.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#subscribers">
                    <i class="fas fa-fw fa-envelope"></i> Abonnenten
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-white-50" href="?logout=1" id="logoutLink">
                    <i class="fas fa-fw fa-sign-out-alt"></i> Abmelden
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand navbar-light mb-4">
            <div class="container-fluid">
                <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0">Dashboard</h5>
                <div class="ms-auto">
                    <span class="me-3 d-none d-md-inline">Angemeldet als: <strong>Admin</strong></span>
                    <span class="badge bg-primary">v2.0</span>
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
                    <div class="card border-left-primary h-100 stats-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Abonnenten gesamt</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_subscribers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success h-100 stats-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Heute hinzugefügt</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_subscribers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info h-100 stats-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Letzte Aktualisierung</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date('d.m.Y H:i'); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscribers Table -->
            <div class="card shadow mb-4" id="subscribers">
                <div class="card-header py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Newsletter Abonnenten</h6>
                    <div class="mt-2 mt-md-0">
                        <a href="?export=1" class="btn btn-sm btn-export text-white">
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
                            <table class="table table-hover" id="subscribersTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>E-Mail</th>
                                        <th>Registriert am</th>
                                        <th class="text-end">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td><?php echo $subscriber['id']; ?></td>
                                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($subscriber['created_at'])); ?></td>
                                            <td class="text-end">
                                                <a href="?delete=<?php echo $subscriber['id']; ?>" 
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

        // Confirm before logout
        document.getElementById('logoutLink').addEventListener('click', function(e) {
            if (!confirm('Möchten Sie sich wirklich abmelden?')) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>
