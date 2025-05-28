// js/cookie-consent.js

document.addEventListener('DOMContentLoaded', function() {
    // Check if cookieconsent is loaded
    if (window.cookieconsent) {
        window.cookieconsent.initialise({
            // Use a more compatible theme
            theme: 'edgeless',
            position: 'bottom',
            static: true,
            // Better color contrast for Chrome
            palette: {
                popup: { 
                    background: '#1c1c1e',
                    text: '#ffffff'
                },
                button: { 
                    background: '#4A6DE5',
                    text: '#ffffff',
                    border: 'none'
                }
            },
            // Remove animations that might cause issues
            animateRevokable: false,
            // Better button styling
            elements: {
                messagelink: 'cc-message',
                dismiss: 'cc-btn cc-dismiss',
                allow: 'cc-btn cc-allow',
                deny: 'cc-btn cc-deny',
                link: 'cc-link',
                close: 'cc-close',
                checkbox: 'cc-check'
            },
            content: {
                message: 'Diese Website verwendet Cookies, um Ihnen das beste Erlebnis zu bieten.',
                dismiss: 'Verstanden',
                link: 'Mehr erfahren',
                href: '/html/datenschutz',
                target: '_self'
            },
            // Ensure proper z-index
            container: document.body,
            // Disable automatic initialization
            autoOpen: true,
            // Add some basic styles
            onInitialise: function(status) {
                // Add custom class to style the banner
                const banner = document.querySelector('.cc-window');
                if (banner) {
                    banner.style.fontFamily = '"Inter", sans-serif';
                    banner.style.padding = '15px';
                    banner.style.boxShadow = '0 -2px 10px rgba(0,0,0,0.1)';
                    
                    // Style the buttons
                    const buttons = banner.querySelectorAll('.cc-btn');
                    buttons.forEach(btn => {
                        btn.style.padding = '8px 16px';
                        btn.style.borderRadius = '4px';
                        btn.style.fontWeight = '500';
                        btn.style.textTransform = 'none';
                        btn.style.letterSpacing = 'normal';
                    });
                }
            }
        });
    }
});
