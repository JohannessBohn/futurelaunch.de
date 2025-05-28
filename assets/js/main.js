/**
 * FutureLaunch - Main JavaScript
 * Zentrale JavaScript-Funktionen für die gesamte Website
 */

// Globales Namespace-Objekt
const FutureLaunch = {
    // Konfiguration
    config: {
        apiEndpoint: '/api',
        dateFormat: 'DD.MM.YYYY HH:mm',
        debug: false
    },
    
    /**
     * Initialisierungsfunktion
     */
    init: function() {
        // Loading Screen
        this.ui.initLoadingScreen();
        
        // Allgemeine UI-Elemente initialisieren
        this.ui.initUI();
        
        // Debug-Meldung
        if (this.config.debug) {
            console.log('FutureLaunch.js initialisiert.');
        }
    },
    
    /**
     * UI-bezogene Funktionen
     */
    ui: {
        /**
         * Loading Screen initialisieren
         */
        initLoadingScreen: function() {
            // Loading Screen anzeigen
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.style.display = 'flex';
                loadingScreen.style.opacity = '1';
                
                // Loading Screen ausblenden, wenn Seite geladen ist
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        loadingScreen.style.opacity = '0';
                        setTimeout(function() {
                            loadingScreen.style.display = 'none';
                        }, 500);
                    }, 800);
                });
            }
        },
        
        /**
         * Allgemeine UI-Elemente initialisieren
         */
        initUI: function() {
            // Tooltips initialisieren (Bootstrap)
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            if (typeof bootstrap !== 'undefined' && tooltipTriggerList.length > 0) {
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            // Mobile Navigation Toggle
            const navToggler = document.querySelector('.navbar-toggler');
            if (navToggler) {
                navToggler.addEventListener('click', function() {
                    document.body.classList.toggle('nav-open');
                });
            }
        },
        
        /**
         * Benachrichtigung anzeigen
         * @param {string} message - Nachrichtentext
         * @param {string} type - Typ (success, error, warning, info)
         * @param {number} duration - Anzeigedauer in ms
         */
        showNotification: function(message, type = 'info', duration = 3000) {
            // Container für Benachrichtigungen erstellen, falls noch nicht vorhanden
            let notifContainer = document.getElementById('notification-container');
            if (!notifContainer) {
                notifContainer = document.createElement('div');
                notifContainer.id = 'notification-container';
                notifContainer.style.position = 'fixed';
                notifContainer.style.top = '20px';
                notifContainer.style.right = '20px';
                notifContainer.style.zIndex = '9999';
                document.body.appendChild(notifContainer);
            }
            
            // Benachrichtigung erstellen
            const notification = document.createElement('div');
            notification.className = 'notification notification-' + type;
            notification.innerHTML = `
                <div class="notification-content">
                    <div class="notification-message">${message}</div>
                    <button class="notification-close">&times;</button>
                </div>
            `;
            
            // Styling
            notification.style.backgroundColor = type === 'success' ? '#28a745' : 
                                               type === 'error' ? '#dc3545' : 
                                               type === 'warning' ? '#ffc107' : '#17a2b8';
            notification.style.color = type === 'warning' ? '#212529' : '#fff';
            notification.style.padding = '15px';
            notification.style.borderRadius = '5px';
            notification.style.marginBottom = '10px';
            notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            notification.style.transition = 'all 0.3s ease';
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(50px)';
            
            // Schließen-Button
            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.style.background = 'transparent';
            closeBtn.style.border = 'none';
            closeBtn.style.color = 'inherit';
            closeBtn.style.fontSize = '20px';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.float = 'right';
            closeBtn.style.marginLeft = '10px';
            closeBtn.addEventListener('click', function() {
                closeNotification(notification);
            });
            
            // Benachrichtigung zum Container hinzufügen
            notifContainer.appendChild(notification);
            
            // Animation starten
            setTimeout(function() {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Nach festgelegter Zeit ausblenden
            if (duration > 0) {
                setTimeout(function() {
                    closeNotification(notification);
                }, duration);
            }
            
            // Funktion zum Schließen der Benachrichtigung
            function closeNotification(notif) {
                notif.style.opacity = '0';
                notif.style.transform = 'translateX(50px)';
                setTimeout(function() {
                    notifContainer.removeChild(notif);
                }, 300);
            }
        }
    },
    
    /**
     * API-Funktionen
     */
    api: {
        /**
         * Daten vom Server abrufen
         * @param {string} endpoint - API-Endpunkt
         * @param {Object} params - Parameter
         * @returns {Promise} - Promise mit Antwort
         */
        get: async function(endpoint, params = {}) {
            try {
                // Parameter in URL-Parameter umwandeln
                const queryString = Object.keys(params)
                    .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
                    .join('&');
                
                // URL erstellen
                const url = FutureLaunch.config.apiEndpoint + endpoint + 
                            (queryString ? '?' + queryString : '');
                
                // Anfrage senden
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                // Antwort prüfen
                if (!response.ok) {
                    throw new Error('Netzwerkantwort war nicht ok');
                }
                
                return await response.json();
            } catch (error) {
                console.error('API-Fehler:', error);
                throw error;
            }
        },
        
        /**
         * Daten an den Server senden
         * @param {string} endpoint - API-Endpunkt
         * @param {Object} data - Zu sendende Daten
         * @returns {Promise} - Promise mit Antwort
         */
        post: async function(endpoint, data = {}) {
            try {
                // URL erstellen
                const url = FutureLaunch.config.apiEndpoint + endpoint;
                
                // Anfrage senden
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });
                
                // Antwort prüfen
                if (!response.ok) {
                    throw new Error('Netzwerkantwort war nicht ok');
                }
                
                return await response.json();
            } catch (error) {
                console.error('API-Fehler:', error);
                throw error;
            }
        }
    },
    
    /**
     * Hilfsfunktionen
     */
    util: {
        /**
         * Cookie setzen
         * @param {string} name - Cookie-Name
         * @param {string} value - Cookie-Wert
         * @param {number} days - Gültigkeitsdauer in Tagen
         */
        setCookie: function(name, value, days = 30) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        },
        
        /**
         * Cookie auslesen
         * @param {string} name - Cookie-Name
         * @returns {string} - Cookie-Wert oder leerer String
         */
        getCookie: function(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return "";
        },
        
        /**
         * Cookie löschen
         * @param {string} name - Cookie-Name
         */
        deleteCookie: function(name) {
            document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
        },
        
        /**
         * String formatieren (einfaches Template-System)
         * @param {string} template - Template-String mit {placeholder}
         * @param {Object} data - Daten für die Platzhalter
         * @returns {string} - Formatierter String
         */
        formatString: function(template, data) {
            return template.replace(/{([^{}]*)}/g, function(match, key) {
                const value = data[key];
                return typeof value !== 'undefined' ? value : match;
            });
        }
    }
};

// Automatisch initialisieren, wenn das DOM geladen ist
document.addEventListener('DOMContentLoaded', function() {
    FutureLaunch.init();
});
