<?php
/**
 * Hauptheader und Navigation
 * Wird in allen Seiten eingebunden
 */

// Aktive Seite bestimmen
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'FutureLaunch' ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Eigene Styles -->
    <link rel="stylesheet" href="/assets/css/main.css">
    
    <?php if (isset($extraStyles)): ?>
        <?= $extraStyles ?>
    <?php endif; ?>
</head>
<body>
    <!-- Loading Screen einbinden -->
    <?php include_once 'includes/loadingscreen.php'; ?>
    
    <!-- Hauptnavigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.html">
                <img src="/assets/img/logo.svg" alt="FutureLaunch" height="40" class="me-2">
                FutureLaunch
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'index.html' || $currentPage === 'index.php' ? 'active' : '' ?>" href="index.html">
                            <i class="fas fa-home me-1"></i> Startseite
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'services.html' ? 'active' : '' ?>" href="services.html">
                            <i class="fas fa-cogs me-1"></i> Leistungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'about.html' ? 'active' : '' ?>" href="about.html">
                            <i class="fas fa-info-circle me-1"></i> Über uns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'contact.html' ? 'active' : '' ?>" href="contact.html">
                            <i class="fas fa-envelope me-1"></i> Kontakt
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                            <i class="fas fa-user-circle me-1"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hauptinhalt Beginn -->
    <main>
