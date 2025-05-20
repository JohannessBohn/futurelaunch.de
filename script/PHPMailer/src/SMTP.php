<?php
namespace PHPMailer\PHPMailer;

class SMTP
{
    private $connection;
    private $connected = false;
    private $debug = true;
    private $timeout = 30;

    private function debug($message)
    {
        if ($this->debug) {
            error_log("SMTP Debug: " . $message);
        }
    }

    private function readResponse()
    {
        $response = '';
        while ($line = fgets($this->connection)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    private function sendCommand($command, $expectedCode)
    {
        $this->debug("Sende: " . trim($command));
        fputs($this->connection, $command . "\r\n");
        $response = $this->readResponse();
        $this->debug("Antwort: " . trim($response));

        $code = substr($response, 0, 3);
        if ($code != $expectedCode) {
            throw new Exception("Unerwarteter SMTP-Code: $code (erwartet: $expectedCode) - " . trim($response));
        }

        return $response;
    }

    public function connect($host, $port = 25, $timeout = 30)
    {
        try {
            $this->timeout = $timeout;
            $this->debug("Verbinde mit {$host}:{$port}");
            
            // Prüfe, ob der Host erreichbar ist
            if (!gethostbyname($host)) {
                throw new Exception("Host nicht erreichbar: $host");
            }

            // Öffne die Verbindung
            $this->connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$this->connection) {
                throw new Exception("Verbindung fehlgeschlagen: $errstr ($errno)");
            }

            // Setze Timeout
            stream_set_timeout($this->connection, $timeout);
            
            $this->connected = true;
            $this->debug("Verbindung erfolgreich hergestellt");

            // Warte auf Server-Begrüßung
            $response = $this->readResponse();
            if (substr($response, 0, 3) !== '220') {
                throw new Exception("Unerwartete Server-Antwort: " . trim($response));
            }

            return true;
        } catch (Exception $e) {
            $this->debug("Verbindungsfehler: " . $e->getMessage());
            if ($this->connection) {
                fclose($this->connection);
                $this->connection = null;
            }
            $this->connected = false;
            return false;
        }
    }

    public function authenticate($username, $password)
    {
        if (!$this->connected) {
            $this->debug("Nicht verbunden - Authentifizierung nicht möglich");
            return false;
        }

        try {
            // EHLO
            $this->sendCommand("EHLO " . $_SERVER['SERVER_NAME'], '250');

            // STARTTLS
            $this->sendCommand("STARTTLS", '220');

            // Aktiviere TLS
            if (!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("TLS-Aktivierung fehlgeschlagen");
            }

            // Erneutes EHLO nach TLS
            $this->sendCommand("EHLO " . $_SERVER['SERVER_NAME'], '250');

            // AUTH LOGIN
            $this->sendCommand("AUTH LOGIN", '334');

            // Username
            $this->sendCommand(base64_encode($username), '334');

            // Password
            $this->sendCommand(base64_encode($password), '235');

            $this->debug("Authentifizierung erfolgreich");
            return true;
        } catch (Exception $e) {
            $this->debug("Authentifizierungsfehler: " . $e->getMessage());
            return false;
        }
    }

    public function send($from, $to, $data)
    {
        if (!$this->connected) {
            $this->debug("Nicht verbunden - Senden nicht möglich");
            return false;
        }

        try {
            // MAIL FROM
            $this->sendCommand("MAIL FROM:<$from>", '250');

            // RCPT TO
            foreach ($to as $recipient) {
                $this->sendCommand("RCPT TO:<{$recipient[0]}>", '250');
            }

            // DATA
            $this->sendCommand("DATA", '354');

            // Headers und Body
            $headers = "From: {$data['from']}\r\n";
            $headers .= "Subject: {$data['subject']}\r\n";
            $headers .= "Content-Type: " . ($data['isHTML'] ? 'text/html' : 'text/plain') . "; charset=UTF-8\r\n";
            $headers .= "\r\n";

            $this->debug("Sende E-Mail-Inhalt");
            fputs($this->connection, $headers . $data['body'] . "\r\n.\r\n");
            
            $response = $this->readResponse();
            if (substr($response, 0, 3) !== '250') {
                throw new Exception("E-Mail-Versand fehlgeschlagen: " . trim($response));
            }

            $this->debug("E-Mail erfolgreich gesendet");
            return true;
        } catch (Exception $e) {
            $this->debug("Sendefehler: " . $e->getMessage());
            return false;
        }
    }

    public function __destruct()
    {
        if ($this->connected && $this->connection) {
            try {
                $this->debug("Sende QUIT");
                fputs($this->connection, "QUIT\r\n");
            } catch (Exception $e) {
                $this->debug("Fehler beim Beenden der Verbindung: " . $e->getMessage());
            } finally {
                fclose($this->connection);
                $this->connection = null;
                $this->connected = false;
                $this->debug("Verbindung geschlossen");
            }
        }
    }
} 