<?php
namespace PHPMailer\PHPMailer;

class PHPMailer
{
    // Constants
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';

    // Properties
    public $Host = 'localhost';
    public $Port = 25;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $CharSet = 'UTF-8';
    public $ErrorInfo = '';
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $isHTML = false;
    public $to = [];
    public $ReplyTo = [];
    public $SMTPDebug = 0;
    public $Debugoutput = null;
    public $SMTPOptions = [];

    public function isSMTP()
    {
        return true;
    }

    public function isHTML($ishtml = true)
    {
        $this->isHTML = $ishtml;
        return $this;
    }

    public function setFrom($address, $name = '')
    {
        $this->From = $address;
        $this->FromName = $name;
    }

    public function addAddress($address, $name = '')
    {
        $this->to[] = [$address, $name];
    }

    public function addReplyTo($address, $name = '')
    {
        $this->ReplyTo[] = [$address, $name];
    }

    public function send()
    {
        try {
            // Create SMTP connection
            $smtp = new SMTP();
            
            // Connect to server
            if (!$smtp->connect($this->Host, $this->Port)) {
                throw new Exception('Could not connect to SMTP server');
            }

            // Authenticate if needed
            if ($this->SMTPAuth) {
                if (!$smtp->authenticate($this->Username, $this->Password)) {
                    throw new Exception('SMTP authentication failed');
                }
            }

            // Prepare email data
            $data = [
                'from' => $this->From,
                'to' => $this->to,
                'subject' => $this->Subject,
                'body' => $this->Body,
                'isHTML' => $this->isHTML
            ];

            // Send email
            if (!$smtp->send($this->From, $this->to, $data)) {
                throw new Exception('Failed to send email');
            }

            return true;
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            return false;
        }
    }
} 