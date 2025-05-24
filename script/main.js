// Inhalt von js/main.js

document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    const body = document.body;

    // Mobile menu toggle
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            body.classList.toggle('menu-open');
            
            // Change hamburger icon
            if (navLinks.classList.contains('active')) {
                menuToggle.innerHTML = '&#10005;'; // X symbol
            } else {
                menuToggle.innerHTML = '&#9776;'; // Hamburger symbol
            }
        });

        // Close menu when clicking on nav links
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                navLinks.classList.remove('active');
                body.classList.remove('menu-open');
                menuToggle.innerHTML = '&#9776;';
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navLinks.contains(event.target) && !menuToggle.contains(event.target)) {
                navLinks.classList.remove('active');
                body.classList.remove('menu-open');
                menuToggle.innerHTML = '&#9776;';
            }
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });

                if (navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                }
            }
        });
    });

    const dots = document.querySelectorAll('.testimonial-dot');
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            dots.forEach(d => d.classList.remove('active'));
            this.classList.add('active');
        });
    });

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Vielen Dank für Ihre Nachricht! Wir werden uns in Kürze bei Ihnen melden.');
            contactForm.reset();
        });
    }

    function revealOnScroll() {
        const elements = document.querySelectorAll('.service-card, .case-study-card, .pricing-card, .step');
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;

            if (elementTop < windowHeight - 100) {
                element.style.opacity = 1;
                element.style.transform = 'translateY(0)';
            }
        });
    }

    document.querySelectorAll('.service-card, .case-study-card, .pricing-card, .step').forEach(element => {
        element.style.opacity = 0;
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });

    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll();

    // Mobile debugging and optimization utilities
    function initMobileOptimizations() {
        // Detect mobile environment
        const isMobile = window.innerWidth <= 768;
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        
        if (isMobile) {
            // Add mobile-specific body class
            document.body.classList.add('mobile-device');
            
            if (isIOS) {
                document.body.classList.add('ios-device');
                // Fix iOS viewport zoom issues
                document.addEventListener('touchstart', function() {}, {passive: true});
            }
            
            if (isAndroid) {
                document.body.classList.add('android-device');
            }
            
            // Optimize touch events
            document.addEventListener('touchstart', function() {}, {passive: true});
            document.addEventListener('touchmove', function() {}, {passive: true});
            
            // Prevent double-tap zoom on specific elements
            const preventZoomElements = document.querySelectorAll('.button, .nav-links a, .service-card');
            preventZoomElements.forEach(el => {
                el.addEventListener('touchstart', function() {}, {passive: true});
            });
            
            // Mobile-specific viewport height adjustment
            function setVH() {
                const vh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--vh', `${vh}px`);
            }
            
            setVH();
            window.addEventListener('resize', setVH);
            window.addEventListener('orientationchange', () => {
                setTimeout(setVH, 100);
            });
        }
        
        // Enhanced mobile menu functionality
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        if (menuToggle && navLinks) {
            // Add ARIA labels for accessibility
            menuToggle.setAttribute('aria-label', 'Hauptmenü öffnen/schließen');
            menuToggle.setAttribute('aria-expanded', 'false');
            navLinks.setAttribute('aria-hidden', 'true');
            
            // Enhanced mobile menu toggle
            function toggleMobileMenu() {
                const isOpen = navLinks.classList.contains('active');
                
                navLinks.classList.toggle('active');
                body.classList.toggle('menu-open');
                
                // Update ARIA attributes
                menuToggle.setAttribute('aria-expanded', !isOpen);
                navLinks.setAttribute('aria-hidden', isOpen);
                
                // Update icon and focus management
                if (!isOpen) {
                    menuToggle.innerHTML = '&#10005;'; // X symbol
                    // Focus first menu item when opened
                    const firstLink = navLinks.querySelector('a');
                    if (firstLink) firstLink.focus();
                } else {
                    menuToggle.innerHTML = '&#9776;'; // Hamburger symbol
                    menuToggle.focus();
                }
            }
            
            menuToggle.addEventListener('click', toggleMobileMenu);
            
            // Keyboard navigation support
            menuToggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleMobileMenu();
                }
            });
            
            // Close menu with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && navLinks.classList.contains('active')) {
                    toggleMobileMenu();
                }
            });
        }
    }

    // Initialize mobile optimizations
    initMobileOptimizations();
});
