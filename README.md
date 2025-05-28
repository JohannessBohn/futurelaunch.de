# 🚀 FutureLaunch.de

> **Ihr Partner für digitale B2B-Vertriebslösungen!**

![FutureLaunch Banner](assets/banner.png)

## 💫 Das ist FutureLaunch

FutureLaunch ist Ihr Experte für digitale B2B-Vertriebslösungen. Wir optimieren Ihren Vertriebsprozess durch innovative Technologien und maßgeschneiderte Web-Lösungen. Von der ersten Leadgenerierung bis zum Vertragsabschluss - wir digitalisieren Ihren B2B-Vertrieb.

### 🌟 Unsere B2B-Lösungen

- **Digitale Vertriebsplattformen** - Maßgeschneiderte B2B-Portale und E-Commerce-Lösungen
- **Sales Enablement Tools** - Digitale Werkzeuge für Ihren Vertriebserfolg
- **Conversion-optimierte Websites** - B2B-fokussierte Designs mit klarem ROI
- **Vertriebsautomatisierung** - CRM-Integration und Marketing-Automation

## 🔧 Tech-Stack

```
🎨 Design: Figma + UX-Expertise für B2B
🖥️ Frontend: HTML5, CSS3, JavaScript, React
🔄 Backend: Node.js, Python, CRM-Integrationen
🚀 Deployment: Cloudflare Pages, Enterprise-ready
```

## 📂 Projektstruktur

```
FutureLaunch.de/
├── .idea/          # IDE-Konfiguration
├── admin/          # Admin-Interface und Verwaltungstools
├── assets/         # Bilder, Icons, Medien
│   └── img/        # Bilder und Logo-Dateien
├── config/         # Konfigurationsdateien
│   ├── auth_config.php    # Authentifizierungseinstellungen
│   └── db_config.php      # Datenbankverbindung
├── css/            # Stylesheets
├── data/           # Datenablage (JSON, Konfiguration)
├── includes/       # Wiederverwendbare PHP-Komponenten
│   ├── auth_helper.php    # Authentifizierungsfunktionen
│   └── loadingscreen.php  # Zentraler Ladebildschirm
├── logs/           # Log-Dateien
├── script/         # JavaScript-Dateien und PHP-Handler
├── uploads/        # Nutzer-Uploads (mit .gitkeep)
└── README.md       # Du bist hier! 👋
```

## 📧 Newsletter-System

Das Newsletter-System ermöglicht Besuchern, sich für Updates anzumelden und bietet ein Admin-Interface zur Verwaltung der Abonnenten.

### Features

- **Frontend-Integration**: Formular zur Newsletter-Anmeldung auf allen Seiten
- **Dual-Storage**: Speicherung der Anmeldungen sowohl auf dem Server (CSV) als auch im localStorage des Browsers
- **Validierung**: E-Mail-Validierung und Duplikat-Prüfung
- **Admin-Benachrichtigung**: Optionale E-Mail-Benachrichtigung bei neuen Anmeldungen
- **Admin-Dashboard**: Komplette Verwaltungsoberfläche für Newsletter-Abonnenten

### Dateien

- `script/subscribe-newsletter.php`: Hauptskript für die Verarbeitung von Anmeldungen
- `script/form-handler.js`: JavaScript für Frontend-Formularverarbeitung
- `admin/newsletter.html`: Admin-Dashboard für Abonnentenverwaltung
- `admin/get-newsletter-subscribers.php`: API zum Abrufen von Abonnentendaten
- `admin/remove-newsletter-subscriber.php`: API zum Entfernen von Abonnenten
- `csv/newsletter_subscriptions.csv`: Speicherort für Newsletter-Anmeldungen

### Admin-Features

- Suchfunktion nach E-Mail-Adressen
- Paginierung für große Abonnentenlisten
- Statistik-Übersicht (Gesamtzahl, neueste Anmeldungen)
- Export der Abonnentenliste als CSV
- Einfaches Entfernen von Abonnenten

## 🚀 Los geht's!

1. **Klone das Repository**
   ```bash
   git clone https://github.com/username/futurelaunch.de.git
   cd futurelaunch.de
   ```

2. **Server-Konfiguration**
   - PHP 7.4+ wird für die Serverkomponenten benötigt
   - Für E-Mail-Funktionalität: PHPMailer (optional)
   - Schreibrechte für das `/csv` Verzeichnis

3. **Öffne die Seite lokal**
   ```bash
   # Mit XAMPP, WAMP oder einem anderen lokalen Server
   # oder starte einen PHP-Server:
   php -S localhost:8000
   ```

4. **Admin-Bereich aufrufen**
   ```
   http://localhost:8000/admin/
   ```

## 🤝 Mach mit!

Wir lieben Zusammenarbeit! Hier ist, wie du mitmachen kannst:
- 🐛 Finde Bugs? Erstelle einen Issue!
- 💡 Hast du Ideen? Pull Requests sind willkommen!
- 🎨 Designvorschläge? Wir sind ganz Ohr!

## 📞 Kontakt

Erreiche unser Team unter:
- 📧 Email: team@futurelaunch.de
- 🌐 Web: [futurelaunch.de](https://futurelaunch.de)
- 🐦 X: [@FutureLaunchDE](https://twitter.com/FutureLaunchDE)

### Kontaktformular

Besucher können uns über das Kontaktformular auf der Website erreichen. Nachrichten werden in `csv/contact_messages.csv` gespeichert und optional per E-Mail weitergeleitet.

## 👨‍💻 Admin-Bereich

Der Admin-Bereich bietet Tools zur Verwaltung von Website-Daten:

- **Newsletter-Verwaltung**: Anzeige, Suche und Verwaltung von Newsletter-Abonnenten
- **Kontaktformular-Einträge**: (In Planung) Verwaltung der Kontaktanfragen
- **Website-Analyse**: (In Planung) Statistiken zur Website-Nutzung

Zugriff erfolgt über `/admin/index.html`.

## 🔜 Geplante Erweiterungen

- Double Opt-in System für Newsletter (DSGVO-Konform)
- Verbesserte Authentifizierung für den Admin-Bereich
- Admin-Interface für Kontaktformular-Einträge
- Statistik-Dashboard für Website-Performance

⭐ **Mit 💙 erstellt von Johannes, Dominic und Team** ⭐

© 2025 FutureLaunch.de - Die Zukunft beginnt hier!