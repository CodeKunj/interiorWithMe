<?php
/**
 * Amak Interior - Helper Functions
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

// Backward compatibility polyfills for PHP 7.x
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || ($needle === substr($haystack, -strlen($needle)));
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Escapes HTML output securely.
 */
function e(?string $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generates an SEO friendly URL slug from text.
 */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return empty($text) ? 'project-' . time() : $text;
}

/**
 * Returns absolute or base-relative URL for assets with automatic cache-busting.
 */
function asset_url(string $path): string
{
    $clean_path = ltrim($path, '/');
    $url = rtrim(SITE_URL, '/') . '/' . $clean_path;

    // Cache-busting for JS and CSS files so browsers/CDNs always load updated code
    if (str_ends_with($clean_path, '.js') || str_ends_with($clean_path, '.css')) {
        $file_path = DIR_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $clean_path);
        $v = file_exists($file_path) ? filemtime($file_path) : time();
        $url .= '?v=' . $v;
    }

    return $url;
}

/**
 * Returns full URL for an image or uploaded asset with fallback support.
 */
function image_url(?string $path, string $fallback = 'assets/img/hero-fallback.jpg'): string
{
    if (empty($path)) {
        return asset_url($fallback);
    }
    
    // If it's already an absolute URL
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return asset_url($path);
}

/**
 * Formats a date in an elegant editorial style.
 */
function format_editorial_date(string $datetime): string
{
    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return $datetime;
    }
    return date('F j, Y', $timestamp);
}

/**
 * Truncates text safely without breaking words.
 */
function truncate_text(string $text, int $limit = 140, string $end = '...'): string
{
    $clean = strip_tags($text);
    if (mb_strlen($clean) <= $limit) {
        return $clean;
    }
    $truncated = mb_substr($clean, 0, $limit);
    $last_space = mb_strrpos($truncated, ' ');
    if ($last_space !== false) {
        $truncated = mb_substr($truncated, 0, $last_space);
    }
    return $truncated . $end;
}

/**
 * Detects real client IP address for security and rate limiting.
 */
function get_client_ip(): string
{
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip_list = explode(',', $_SERVER[$header]);
            $ip = trim($ip_list[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Checks contact form rate limit (e.g. max 4 submissions per IP per hour).
 */
function check_rate_limit(string $ip, int $max_attempts = 4, int $time_window_seconds = 3600): bool
{
    $pdo = get_db_connection();
    $cutoff = date('Y-m-d H:i:s', time() - $time_window_seconds);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE ip_address = ? AND created_at >= ?");
    $stmt->execute([$ip, $cutoff]);
    $count = (int)$stmt->fetchColumn();

    return $count < $max_attempts;
}

/**
 * Sets a flash message in the session.
 */
function set_flash_message(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'error', 'info'
        'message' => $message
    ];
}

/**
 * Retrieves and clears the flash message.
 */
function get_flash_message(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Handles PHPMailer dispatch or simulated sending.
 */
function send_enquiry_email(array $data): bool
{
    $mail_config = require __DIR__ . '/../config/mail.php';

    // Build Luxury Email Body
    $subject = 'New Private Commission Enquiry: ' . $data['name'] . ' [' . $data['project_type'] . ']';
    
    $html_body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #F5F1EA; color: #1C1C1C; margin: 0; padding: 30px; }
            .container { max-width: 600px; margin: 0 auto; background: #FFFFFF; border-top: 4px solid #B08D57; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
            h1 { font-family: Georgia, serif; font-size: 24px; color: #1C1C1C; margin-top: 0; font-weight: normal; letter-spacing: 1px; border-bottom: 1px solid #EDE8DF; padding-bottom: 15px; }
            .tag { display: inline-block; background: #B08D57; color: #FFF; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; padding: 4px 10px; margin-bottom: 20px; }
            .item { margin-bottom: 16px; font-size: 14px; line-height: 1.6; }
            .label { font-weight: bold; color: #777; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; display: block; margin-bottom: 4px; }
            .value { color: #1C1C1C; font-size: 15px; }
            .message-box { background: #FAF8F5; border-left: 3px solid #B08D57; padding: 15px; margin-top: 20px; font-style: italic; }
            .footer { margin-top: 30px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #EDE8DF; padding-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="tag">Studio Enquiry</div>
            <h1>Amak Interior</h1>
            <p>You have received a new consultation enquiry via the web portal.</p>
            
            <div class="item">
                <span class="label">Client Name</span>
                <span class="value">' . e($data['name']) . '</span>
            </div>
            <div class="item">
                <span class="label">Email Address</span>
                <span class="value"><a href="mailto:' . e($data['email']) . '">' . e($data['email']) . '</a></span>
            </div>
            <div class="item">
                <span class="label">Phone / WhatsApp</span>
                <span class="value">' . e($data['phone'] ?? 'Not provided') . '</span>
            </div>
            <div class="item">
                <span class="label">Project Typology</span>
                <span class="value">' . e($data['project_type']) . '</span>
            </div>
            <div class="item">
                <span class="label">Target Investment / Budget</span>
                <span class="value">' . e($data['budget_range']) . '</span>
            </div>
            
            <div class="label" style="margin-top: 20px;">Client Vision & Notes</div>
            <div class="message-box">
                ' . nl2br(e($data['message'])) . '
            </div>
            
            <div class="footer">
                Amak Interior Concierge System &bull; Received on ' . date('Y-m-d H:i:s T') . ' &bull; IP: ' . e($data['ip_address']) . '
            </div>
        </div>
    </body>
    </html>';

    // Check if dynamic database settings override config file
    $smtp_enabled_setting = get_setting('smtp_enabled', $mail_config['smtp_enabled'] ? '1' : '0');
    $is_smtp_active = ($smtp_enabled_setting === '1' || $smtp_enabled_setting === 'true' || $mail_config['smtp_enabled']);

    $smtp_host = get_setting('smtp_host', $mail_config['host']);
    $smtp_port = (int)get_setting('smtp_port', (string)$mail_config['port']);
    $smtp_enc  = get_setting('smtp_encryption', $mail_config['encryption']);
    $smtp_user = get_setting('smtp_username', $mail_config['username']);
    $smtp_pass = get_setting('smtp_password', $mail_config['password']);
    $from_mail = get_setting('smtp_from_email', $mail_config['from_email']);
    $from_name = get_setting('smtp_from_name', $mail_config['from_name']);
    $to_mail   = get_setting('admin_notification_email', $mail_config['recipient_email']);
    $to_name   = get_setting('site_name', $mail_config['recipient_name']);

    // If SMTP is enabled and PHPMailer is available
    if ($is_smtp_active && !empty($smtp_host) && file_exists(__DIR__ . '/../vendor/phpmailer/PHPMailer.php')) {
        try {
            require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
            require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
            require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = !empty($smtp_user);
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = strtolower($smtp_enc) === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : (strtolower($smtp_enc) === 'tls' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : '');
            $mail->Port       = $smtp_port ?: 587;

            $mail->setFrom($from_mail ?: 'noreply@amakinterior.com', $from_name ?: 'Amak Concierge');
            $mail->addAddress($to_mail ?: 'admin@amakinterior.com', $to_name ?: 'Amak Studio Admin');
            $mail->addReplyTo($data['email'], $data['name']);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html_body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_body));

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log("PHPMailer error: " . $e->getMessage());
            // Fall back to successful DB record
            return true;
        }
    }

    // Fallback: Mail is logged / recorded in DB successfully
    error_log("[Amak Interior Mail Simulated] New enquiry from {$data['name']} ({$data['email']})");
    return true;
}

/**
 * Ensures the settings table exists and has default records.
 */
function ensure_settings_table(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    try {
        $pdo = get_db_connection();
        $tableExists = false;

        $check = $pdo->query("SHOW TABLES LIKE 'settings'");
        if ($check && $check->fetch()) {
            $tableExists = true;
        }

        if (!$tableExists) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `settings` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                    `setting_value` LONGTEXT DEFAULT NULL,
                    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $defaults = [
                // General
                ['site_name', 'Amak Interior', 'general'],
                ['site_tagline', 'Where Vision Meets Dimension', 'general'],
                ['site_description', 'Bespoke luxury interior architecture and experiential spatial design studio based in Manhattan, New York. Crafting timeless sanctuaries through vision and dimension.', 'general'],
                ['site_logo', '', 'general'],
                ['site_favicon', '', 'general'],
                ['currency_symbol', '$', 'general'],
                ['copyright_text', '© ' . date('Y') . ' Amak Interior Studio LLC. All rights reserved.', 'general'],
                ['primary_color', '#B08D57', 'general'],
                
                // Contact
                ['contact_email', 'concierge@amakinterior.com', 'contact'],
                ['admin_notification_email', 'admin@amakinterior.com', 'contact'],
                ['contact_phone', '+1 (212) 555-0198', 'contact'],
                ['whatsapp_number', '+1 (212) 555-0198', 'contact'],
                ['whatsapp_floating_button', '1', 'contact'],
                ['studio_address', '482 Broome Street, Studio 4A, SoHo, New York, NY 10013', 'contact'],
                ['working_hours', 'By Private Appointment Only | Mon - Sat: 9:00 AM - 7:00 PM', 'contact'],
                ['google_maps_iframe', '', 'contact'],

                // Social
                ['social_instagram', 'https://instagram.com/amakinterior', 'social'],
                ['social_pinterest', 'https://pinterest.com/amakinterior', 'social'],
                ['social_linkedin', 'https://linkedin.com/company/amakinterior', 'social'],
                ['social_facebook', 'https://facebook.com/amakinterior', 'social'],
                ['social_archdaily', 'https://archdaily.com/professionals/amakinterior', 'social'],
                ['social_youtube', '', 'social'],
                ['social_twitter', '', 'social'],

                // SEO
                ['meta_title', 'Amak Interior | Where Vision Meets Dimension', 'seo'],
                ['meta_description', 'Bespoke luxury interior architecture and experiential spatial design studio based in Manhattan, New York. Crafting timeless sanctuaries through vision and dimension.', 'seo'],
                ['meta_keywords', 'luxury interior design, 3D interior architecture, high-end residential design, Manhattan interior designer, custom furniture design, Amak Interior', 'seo'],
                ['og_image', 'assets/img/og-image.jpg', 'seo'],
                ['google_analytics_id', '', 'seo'],
                ['header_scripts', '', 'seo'],
                ['footer_scripts', '', 'seo'],

                // SMTP
                ['smtp_enabled', '0', 'smtp'],
                ['smtp_host', 'smtp.gmail.com', 'smtp'],
                ['smtp_port', '587', 'smtp'],
                ['smtp_encryption', 'tls', 'smtp'],
                ['smtp_username', '', 'smtp'],
                ['smtp_password', '', 'smtp'],
                ['smtp_from_email', 'concierge@amakinterior.com', 'smtp'],
                ['smtp_from_name', 'Amak Interior Concierge', 'smtp'],

                // Hero
                ['hero_badge', 'HAUTE COUTURE ARCHITECTURE', 'hero'],
                ['hero_heading', 'Where Vision Meets Dimension', 'hero'],
                ['hero_subheading', 'Bespoke residential sanctuaries and spatial architecture crafted with three-dimensional precision.', 'hero'],
                ['hero_cta_text', 'Explore Commissions', 'hero'],
                ['hero_cta_link', 'portfolio.php', 'hero'],
                ['enable_3d_viewer', '1', 'hero'],
                ['default_glb_model', 'assets/models/room.glb', 'hero'],
                ['hero_video_url', 'https://storage.googleapis.com/webild/default/templates/marbella/hero/hero.mp4', 'hero']
            ];

            $insertStmt = $pdo->prepare("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES (?, ?, ?)");
            foreach ($defaults as $row) {
                $insertStmt->execute($row);
            }
        }
        $ensured = true;
    } catch (\Throwable $e) {
        // Suppress and log if DB is in migration or offline
        error_log("Settings table ensure warning: " . $e->getMessage());
    }
}

/**
 * Loads all settings into a memory cache.
 */
function get_all_settings(): array
{
    static $settings_cache = null;

    if ($settings_cache !== null) {
        return $settings_cache;
    }

    ensure_settings_table();

    $settings_cache = [];
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT setting_key, setting_value, setting_group FROM settings");
        if ($stmt) {
            while ($row = $stmt->fetch()) {
                $settings_cache[$row['setting_key']] = $row['setting_value'];
            }
        }
    } catch (\Throwable $e) {
        // Fallback gracefully
        error_log("Failed to load settings from DB: " . $e->getMessage());
    }

    return $settings_cache;
}

/**
 * Gets a specific setting value with fallback support.
 */
function get_setting(string $key, ?string $default = null): string
{
    $all = get_all_settings();
    if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
        return (string)$all[$key];
    }

    // Check application constants as fallbacks
    $const_map = [
        'site_name'                => 'APP_NAME',
        'site_tagline'             => 'APP_TAGLINE',
        'studio_address'           => 'APP_ADDRESS',
        'contact_phone'            => 'APP_PHONE',
        'contact_email'            => 'APP_EMAIL',
        'admin_notification_email' => 'APP_ADMIN_EMAIL',
        'social_instagram'         => 'SOCIAL_INSTAGRAM',
        'social_pinterest'         => 'SOCIAL_PINTEREST',
        'social_linkedin'          => 'SOCIAL_LINKEDIN',
        'social_archdaily'         => 'SOCIAL_ARCHDAILY',
    ];

    if (isset($const_map[$key]) && defined($const_map[$key])) {
        return (string)constant($const_map[$key]);
    }

    return $default ?? '';
}

/**
 * Updates a single setting value.
 */
function update_setting(string $key, ?string $value, string $group = 'general'): bool
{
    ensure_settings_table();
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_group)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value, $group]);
    } catch (\Throwable $e) {
        error_log("Failed to update setting {$key}: " . $e->getMessage());
        return false;
    }
}

/**
 * Generates dynamic accent CSS variables & overrides based on primary_color setting.
 */
function render_dynamic_accent_styles(): string
{
    $hex = get_setting('primary_color', '#B08D57');
    if (!preg_match('/^#[a-f0-9]{6}$/i', $hex)) {
        $hex = '#B08D57';
    }

    // Convert hex to rgb
    $r = hexdec(substr($hex, 1, 2));
    $g = hexdec(substr($hex, 3, 2));
    $b = hexdec(substr($hex, 5, 2));

    // Light shade (tint 25%)
    $r_light = (int)min(255, $r + (255 - $r) * 0.25);
    $g_light = (int)min(255, $g + (255 - $g) * 0.25);
    $b_light = (int)min(255, $b + (255 - $b) * 0.25);
    $hex_light = sprintf('#%02x%02x%02x', $r_light, $g_light, $b_light);

    // Dark shade (shade 25%)
    $r_dark = (int)max(0, $r * 0.75);
    $g_dark = (int)max(0, $g * 0.75);
    $b_dark = (int)max(0, $b * 0.75);
    $hex_dark = sprintf('#%02x%02x%02x', $r_dark, $g_dark, $b_dark);

    $rgba_muted = "rgba({$r}, {$g}, {$b}, 0.2)";
    $rgba_glow = "rgba({$r}, {$g}, {$b}, 0.35)";

    return "
    <style id=\"dynamic-accent-styles\">
        :root {
            --color-brass: {$hex} !important;
            --color-brass-light: {$hex_light} !important;
            --color-brass-dark: {$hex_dark} !important;
            --color-brass-muted: {$rgba_muted} !important;
        }
        .text-brass { color: {$hex} !important; }
        .hover\:text-brass:hover { color: {$hex} !important; }
        .bg-brass { background-color: {$hex} !important; }
        .hover\:bg-brass:hover { background-color: {$hex_light} !important; }
        .border-brass { border-color: {$hex} !important; }
        .hover\:border-brass:hover { border-color: {$hex_light} !important; }
        .btn-brass {
            background-color: {$hex} !important;
            border-color: {$hex} !important;
            color: #141414 !important;
        }
        .btn-brass:hover {
            background-color: {$hex_light} !important;
            border-color: {$hex_light} !important;
            box-shadow: 0 10px 30px {$rgba_glow} !important;
        }
        .btn-outline-brass {
            border-color: {$hex} !important;
            color: {$hex} !important;
        }
        .btn-outline-brass:hover {
            background-color: {$hex} !important;
            color: #141414 !important;
        }
        .editorial-tag {
            border-color: {$rgba_muted} !important;
            color: {$hex} !important;
        }
        .luxury-card:hover {
            border-color: rgba({$r}, {$g}, {$b}, 0.4) !important;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: {$hex} !important;
        }
        ::selection {
            background: {$hex} !important;
            color: #141414 !important;
        }
    </style>";
}

