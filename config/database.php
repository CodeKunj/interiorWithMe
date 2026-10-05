<?php
/**
 * Amak Interior - Database Configuration & PDO Connection
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

// Database Credentials (adjust for local XAMPP/Laragon or production environment)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'amak_interior');
define('DB_USER', getenv('DB_USER') ?: 'kunjr1104_db_user');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'qOtzfSh9GdVLPM1Y');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns the singleton PDO database connection instance.
 *
 * @return PDO
 */
function get_db_connection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log error instead of displaying raw message
            error_log('Database Connection Error: ' . $e->getMessage());
            
            // If the database has not been created yet or MySQL is not running
            if (php_sapi_name() === 'cli') {
                throw $e;
            }
            
            // User-friendly luxury styled offline message
            http_response_code(500);
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Database Offline | Amak Interior</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #1C1C1C; color: #F5F1EA; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                    .card { background: #252525; border: 1px solid #B08D57; padding: 2.5rem; max-width: 520px; border-radius: 4px; text-align: center; }
                    h1 { color: #B08D57; font-size: 1.5rem; margin-top: 0; font-weight: 400; letter-spacing: 0.05em; }
                    p { color: #CCC; font-size: 0.95rem; line-height: 1.6; }
                    code { background: #111; padding: 0.2rem 0.4rem; border-radius: 3px; color: #B08D57; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h1>AMAK INTERIOR</h1>
                    <p>Database connection unavailable. Please ensure your MySQL service is running and import <code>database.sql</code> into database <code>' . htmlspecialchars(DB_NAME) . '</code>.</p>
                </div>
            </body>
            </html>';
            exit;
        }
    }

    return $pdo;
}
