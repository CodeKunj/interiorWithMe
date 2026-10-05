<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * Exception class.
 */

namespace PHPMailer\PHPMailer;

class Exception extends \Exception
{
    public function errorMessage(): string
    {
        return '<strong>' . htmlspecialchars($this->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</strong><br />\n";
    }
}
