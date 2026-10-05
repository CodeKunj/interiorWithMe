<?php
/**
 * PHPMailer - PHP email creation and transport class.
 */

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const CHARSET_UTF8 = 'utf-8';
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    public $CharSet = self::CHARSET_UTF8;
    public $ContentType = 'text/plain';
    public $Encoding = '8bit';
    public $From = '';
    public $FromName = '';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $Host = 'localhost';
    public $Port = 25;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $Timeout = 300;

    protected $smtp = null;
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $ReplyTo = [];
    protected $isHTML = false;
    protected $exceptions = false;

    public function __construct($exceptions = false)
    {
        $this->exceptions = (bool)$exceptions;
    }

    public function isSMTP(): void
    {
        // Sets transport mode
    }

    public function isHTML($isHtml = true): void
    {
        $this->isHTML = (bool)$isHtml;
        $this->ContentType = $isHtml ? 'text/html' : 'text/plain';
    }

    public function setFrom($address, $name = ''): bool
    {
        $this->From = trim($address);
        $this->FromName = trim($name);
        return true;
    }

    public function addAddress($address, $name = ''): bool
    {
        $this->to[] = [trim($address), trim($name)];
        return true;
    }

    public function addReplyTo($address, $name = ''): bool
    {
        $this->ReplyTo[] = [trim($address), trim($name)];
        return true;
    }

    public function send(): bool
    {
        try {
            $this->smtp = new SMTP();
            $secure_port = $this->Port;
            $host = $this->Host;
            if ($this->SMTPSecure === self::ENCRYPTION_SMTPS) {
                $host = 'ssl://' . $host;
            }

            if (!$this->smtp->connect($host, $secure_port, $this->Timeout)) {
                throw new Exception('SMTP connect failed: ' . json_encode($this->smtp->getError()));
            }

            if (!$this->smtp->hello(gethostname() ?: 'localhost')) {
                throw new Exception('SMTP HELO failed');
            }

            if ($this->SMTPSecure === self::ENCRYPTION_STARTTLS) {
                if (!$this->smtp->startTLS()) {
                    throw new Exception('SMTP STARTTLS failed');
                }
                $this->smtp->hello(gethostname() ?: 'localhost');
            }

            if ($this->SMTPAuth) {
                if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                    throw new Exception('SMTP Authentication failed');
                }
            }

            $from_addr = $this->From ?: 'no-reply@amakinterior.com';
            if (!$this->smtp->mail($from_addr)) {
                throw new Exception('SMTP MAIL FROM failed');
            }

            foreach ($this->to as $recipient) {
                if (!$this->smtp->recipient($recipient[0])) {
                    throw new Exception('SMTP RCPT TO failed for ' . $recipient[0]);
                }
            }

            $mime_message = $this->createMimeHeader() . "\r\n\r\n" . $this->Body;
            if (!$this->smtp->data($mime_message)) {
                throw new Exception('SMTP DATA transmission failed');
            }

            $this->smtp->quit();
            return true;
        } catch (\Exception $e) {
            if ($this->smtp) {
                $this->smtp->close();
            }
            if ($this->exceptions) {
                throw new Exception($e->getMessage());
            }
            return false;
        }
    }

    protected function createMimeHeader(): string
    {
        $headers = [];
        $from_name = $this->FromName ? '=?UTF-8?B?' . base64_encode($this->FromName) . '?=' : '';
        $headers[] = 'From: ' . ($from_name ? "$from_name <{$this->From}>" : $this->From);

        $to_formatted = [];
        foreach ($this->to as $rcpt) {
            $to_name = $rcpt[1] ? '=?UTF-8?B?' . base64_encode($rcpt[1]) . '?=' : '';
            $to_formatted[] = $to_name ? "$to_name <{$rcpt[0]}>" : $rcpt[0];
        }
        $headers[] = 'To: ' . implode(', ', $to_formatted);

        if (!empty($this->ReplyTo)) {
            $reply_formatted = [];
            foreach ($this->ReplyTo as $rp) {
                $reply_formatted[] = $rp[1] ? "{$rp[1]} <{$rp[0]}>" : $rp[0];
            }
            $headers[] = 'Reply-To: ' . implode(', ', $reply_formatted);
        }

        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($this->Subject) . '?=';
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet;
        $headers[] = 'Content-Transfer-Encoding: ' . $this->Encoding;
        $headers[] = 'X-Mailer: AmakInteriorMailer/1.0';

        return implode("\r\n", $headers);
    }
}
