<?php
/**
 * Amak Interior - Application Configuration
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

// PHP 7.x backward compatibility polyfills for PHP 8 string functions
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

// Studio Identity & Branding
define('APP_NAME', 'Amak Interior');
define('APP_TAGLINE', 'Where Vision Meets Dimension');
define('APP_LOCATION', 'Manhattan, New York');
define('APP_ADDRESS', '482 Broome Street, Studio 4A, SoHo, New York, NY 10013');
define('APP_PHONE', '+1 (212) 555-0198');
define('APP_EMAIL', 'concierge@amakinterior.com');
define('APP_ADMIN_EMAIL', 'admin@amakinterior.com');

// Environment (development or production)
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', APP_ENV === 'development');

// Base URL detection (supports local subfolder, virtual hosts, and production domains)
function get_base_url(): string
{
    if (defined('APP_URL') && APP_URL !== '') {
        return rtrim(APP_URL, '/');
    }

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
        || (isset($_SERVER['HTTP_CF_VISITOR']) && str_contains($_SERVER['HTTP_CF_VISITOR'], 'https'));

    $protocol = $is_https ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Calculate root relative directory from script filename
    $script_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    // If in /admin, strip /admin
    $clean_dir = preg_replace('#/admin$#', '', $script_dir);
    $clean_dir = rtrim(str_replace('\\', '/', $clean_dir), '/');

    return $protocol . $host . $clean_dir;
}

define('SITE_URL', get_base_url());

// Social Media Links
define('SOCIAL_INSTAGRAM', 'https://instagram.com/amakinterior');
define('SOCIAL_PINTEREST', 'https://pinterest.com/amakinterior');
define('SOCIAL_LINKEDIN', 'https://linkedin.com/company/amakinterior');
define('SOCIAL_ARCHDAILY', 'https://archdaily.com/professionals/amakinterior');

// Upload Paths
define('DIR_ROOT', dirname(__DIR__));
define('DIR_UPLOADS', DIR_ROOT . DIRECTORY_SEPARATOR . 'uploads');
define('DIR_PROJECT_UPLOADS', DIR_UPLOADS . DIRECTORY_SEPARATOR . 'projects');
define('DIR_MODEL_UPLOADS', DIR_UPLOADS . DIRECTORY_SEPARATOR . 'models');
define('DIR_VIDEO_UPLOADS', DIR_UPLOADS . DIRECTORY_SEPARATOR . 'videos');

// Allowed Upload Mime Types & Max Sizes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_IMAGE_SIZE', 8 * 1024 * 1024); // 8MB
define('ALLOWED_MODEL_EXTENSIONS', ['glb', 'gltf']);
define('MAX_MODEL_SIZE', 30 * 1024 * 1024); // 30MB
define('ALLOWED_VIDEO_EXTENSIONS', ['mp4', 'webm', 'mov', 'm4v']);
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024); // 50MB

// Ensure uploads directories exist
if (!is_dir(DIR_PROJECT_UPLOADS)) {
    @mkdir(DIR_PROJECT_UPLOADS, 0755, true);
}
if (!is_dir(DIR_MODEL_UPLOADS)) {
    @mkdir(DIR_MODEL_UPLOADS, 0755, true);
}
if (!is_dir(DIR_VIDEO_UPLOADS)) {
    @mkdir(DIR_VIDEO_UPLOADS, 0755, true);
}
