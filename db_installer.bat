@echo off
echo FutureLaunch Datenbankinstaller
echo ============================
echo.

REM Pfad zu MySQL überprüfen
set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
if not exist "%MYSQL_PATH%" (
    echo MySQL nicht gefunden unter %MYSQL_PATH%
    echo Bitte passen Sie den Pfad in dieser Datei an.
    pause
    exit /b 1
)

REM Datenbankverbindungsdaten
set DB_USER=root
set DB_PASS=
set DB_NAME=futurelaunch

echo Erstelle Datenbank %DB_NAME%...
"%MYSQL_PATH%" -u %DB_USER% --password=%DB_PASS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %ERRORLEVEL% neq 0 (
    echo Fehler beim Erstellen der Datenbank.
    pause
    exit /b 1
)

echo Datenbank erfolgreich erstellt.
echo Erstelle Tabellen...

"%MYSQL_PATH%" -u %DB_USER% --password=%DB_PASS% %DB_NAME% -e "
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user',
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$5nv5UXge4VCwm7miPeDp8OaQs9b6WJn/YxPUb.QcGH2WG8.ZIiG3O', 'admin@futurelaunch.de', 'admin')
ON DUPLICATE KEY UPDATE username=username;
"

if %ERRORLEVEL% neq 0 (
    echo Fehler beim Erstellen der Tabellen.
    pause
    exit /b 1
)

echo Installation abgeschlossen!
echo Benutzername: admin
echo Passwort: admin123
echo.
echo Jetzt können Sie die Website über das Dashboard verwalten.
pause
