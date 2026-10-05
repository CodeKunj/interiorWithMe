<?php
/**
 * Amak Interior - Mail & SMTP Configuration
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

return [
    // Enable/Disable live SMTP sending (if false, submissions are saved to DB and logged)
    'smtp_enabled'    => filter_var(getenv('SMTP_ENABLED') ?: false, FILTER_VALIDATE_BOOLEAN),
    
    // SMTP Server Credentials
    'host'            => getenv('SMTP_HOST') ?: 'smtp.mailtrap.io',
    'port'            => (int)(getenv('SMTP_PORT') ?: 587),
    'auth'            => true,
    'username'        => getenv('SMTP_USER') ?: 'your_smtp_username',
    'password'        => getenv('SMTP_PASS') ?: 'your_smtp_password',
    'encryption'      => getenv('SMTP_ENCRYPTION') ?: 'tls', // 'tls' or 'ssl'
    
    // Sender Information
    'from_email'      => getenv('SMTP_FROM_EMAIL') ?: 'concierge@amakinterior.com',
    'from_name'       => getenv('SMTP_FROM_NAME') ?: 'Amak Interior Concierge',
    
    // Studio Notification Recipient
    'recipient_email' => getenv('SMTP_RECIPIENT_EMAIL') ?: 'admin@amakinterior.com',
    'recipient_name'  => getenv('SMTP_RECIPIENT_NAME') ?: 'Amak Studio Management',
    
    // Auto-reply configuration
    'auto_reply'      => true,
];
