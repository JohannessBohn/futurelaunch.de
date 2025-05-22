<?php

namespace PHPMailer\PHPMailer;

class PHPMailer {
    public $SMTPDebug = 0;
    public $Host = 'localhost';
    public $Port = 25;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $CharSet = 'utf-8';
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $ErrorInfo = '';
    private $to = [];
    private $replyTo = [];

    public function __construct($exceptions = null) {
        $this->ErrorInfo = '';
    }

    public function isSMTP() {
        return true;
    }

    public function setFrom($address, $name = '') {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    public function addAddress($address, $name = '') {
        $this->to[] = ['address' => $address, 'name' => $name];
        return true;
    }

    public function addReplyTo($address, $name = '') {
        $this->replyTo[] = ['address' => $address, 'name' => $name];
        return true;
    }

    public function isHTML($isHtml = true) {
        return true;
    }

    public function send() {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=' . $this->CharSet,
            'From: ' . $this->FromName . ' <' . $this->From . '>',
            'X-Mailer: PHP/' . phpversion()
        ];

        if (!empty($this->replyTo)) {
            $replyTo = $this->replyTo[0];
            $headers[] = 'Reply-To: ' . $replyTo['name'] . ' <' . $replyTo['address'] . '>';
        }

        foreach ($this->to as $recipient) {
            $sent = mail(
                $recipient['address'],
                $this->Subject,
                $this->Body,
                implode("\r\n", $headers)
            );

            if (!$sent) {
                $this->ErrorInfo = error_get_last()['message'] ?? 'Unknown error occurred';
                return false;
            }
        }

        return true;
    }
} 