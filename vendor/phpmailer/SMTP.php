<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * SMTP RFC 821 class.
 */

namespace PHPMailer\PHPMailer;

class SMTP
{
    const VERSION = '6.9.1';
    const DEFAULT_PORT = 25;
    const MAX_LINE_LENGTH = 998;
    const DEBUG_OFF = 0;
    const DEBUG_CLIENT = 1;
    const DEBUG_SERVER = 2;
    const DEBUG_CONNECTION = 3;
    const DEBUG_LOWLEVEL = 4;

    public $do_debug = self::DEBUG_OFF;
    public $Debugoutput = 'echo';
    public $do_verp = false;
    public $Timeout = 300;
    public $Timelimit = 300;
    
    protected $smtp_conn;
    protected $error = [];
    protected $helo_rply = null;
    protected $server_caps = null;
    protected $last_reply = '';

    public function connect($host, $port = null, $timeout = 30, $options = []): bool
    {
        $this->error = [];
        if ($this->connected()) {
            $this->error = ['error' => 'Already connected to a server'];
            return false;
        }
        if (empty($port)) {
            $port = self::DEFAULT_PORT;
        }

        $errno = 0;
        $errstr = '';
        $socket_context = stream_context_create($options);
        $this->smtp_conn = @stream_socket_client(
            $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $socket_context
        );

        if (!is_resource($this->smtp_conn)) {
            $this->error = [
                'error' => 'Failed to connect to server',
                'errno' => $errno,
                'errstr' => $errstr
            ];
            return false;
        }

        stream_set_timeout($this->smtp_conn, $timeout, 0);
        $announce = $this->get_lines();
        return true;
    }

    public function startTLS(): bool
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        return stream_socket_enable_crypto($this->smtp_conn, true, $crypto_method);
    }

    public function authenticate($user, $pass, $authtype = null): bool
    {
        if (!$this->sendCommand('AUTH LOGIN', 'AUTH LOGIN', 334)) {
            return false;
        }
        if (!$this->sendCommand('User', base64_encode($user), 334)) {
            return false;
        }
        if (!$this->sendCommand('Password', base64_encode($pass), 235)) {
            return false;
        }
        return true;
    }

    public function connected(): bool
    {
        if (is_resource($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                $this->close();
                return false;
            }
            return true;
        }
        return false;
    }

    public function close(): void
    {
        $this->error = [];
        $this->server_caps = null;
        $this->helo_rply = null;
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }

    public function hello($host = ''): bool
    {
        return (bool)($this->sendHello('EHLO', $host) || $this->sendHello('HELO', $host));
    }

    protected function sendHello($hello, $host): bool
    {
        $noerror = $this->sendCommand($hello, $hello . ' ' . $host, 250);
        $this->helo_rply = $this->last_reply;
        return $noerror;
    }

    public function mail($from): bool
    {
        return $this->sendCommand('MAIL FROM', 'MAIL FROM:<' . $from . '>', 250);
    }

    public function recipient($toaddress): bool
    {
        return $this->sendCommand('RCPT TO', 'RCPT TO:<' . $toaddress . '>', [250, 251]);
    }

    public function data($msg_data): bool
    {
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }
        $msg_data = str_replace("\r\n", "\n", $msg_data);
        $msg_data = str_replace("\r", "\n", $msg_data);
        $lines = explode("\n", $msg_data);
        $field = substr($lines[0], 0, strpos($lines[0], ':'));
        $in_headers = false;
        if (!empty($field) && !str_contains($field, ' ')) {
            $in_headers = true;
        }

        foreach ($lines as $line) {
            $lines_out = [];
            if ($in_headers && $line === '') {
                $in_headers = false;
            }
            while (isset($line[self::MAX_LINE_LENGTH])) {
                $pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
                if (!$pos) {
                    $pos = self::MAX_LINE_LENGTH - 1;
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos);
                } else {
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos + 1);
                }
            }
            $lines_out[] = $line;
            foreach ($lines_out as $line_out) {
                if (!empty($line_out) && $line_out[0] === '.') {
                    $line_out = '.' . $line_out;
                }
                $this->client_send($line_out . "\r\n");
            }
        }

        return $this->sendCommand('DATA END', '.', 250);
    }

    public function quit($on_close = true): bool
    {
        $noerror = $this->sendCommand('QUIT', 'QUIT', 221);
        $err = $this->error;
        if ($on_close) {
            $this->close();
        }
        $this->error = $err;
        return $noerror;
    }

    public function sendCommand($commandName, $command, $expect): bool
    {
        if (!$this->connected()) {
            $this->error = ['error' => 'Called ' . $commandName . '() without being connected'];
            return false;
        }
        $this->client_send($command . "\r\n");
        $this->last_reply = $this->get_lines();
        $code = (int)substr($this->last_reply, 0, 3);
        if (is_array($expect)) {
            return in_array($code, $expect, true);
        }
        return $code === $expect;
    }

    protected function client_send($data): int
    {
        return (int)fwrite($this->smtp_conn, $data);
    }

    protected function get_lines(): string
    {
        $data = '';
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
            $str = @fgets($this->smtp_conn, 515);
            $data .= $str;
            if (!isset($str[3]) || $str[3] === ' ') {
                break;
            }
        }
        return $data;
    }

    public function getError(): array
    {
        return $this->error;
    }
}
