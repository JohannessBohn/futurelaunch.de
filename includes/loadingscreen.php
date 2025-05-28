<?php
/**
 * Zentraler Ladebildschirm für alle Seiten
 * Wird über include eingebunden
 */
?>
<!-- Central Loading Screen -->
<div class="loading-overlay" id="loadingScreen">
    <div class="text-center">
        <img src="/assets/img/logo.svg" alt="FutureLaunch Logo" class="loading-logo">
        <h3 class="mt-3">FutureLaunch</h3>
        <div class="spinner-border text-primary mt-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<style>
    /* Loading Screen Styles */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: all 0.5s ease;
    }
    
    .loading-logo {
        width: 120px;
        height: 120px;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<script>
    // Loading screen functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Show loading screen immediately
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.style.opacity = 1;
        }
        
        // Hide loading screen when page is fully loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (loadingScreen) {
                    loadingScreen.style.opacity = 0;
                    setTimeout(function() {
                        loadingScreen.style.display = 'none';
                    }, 500);
                }
            }, 800); // Minimum display time
        });
    });
</script>
