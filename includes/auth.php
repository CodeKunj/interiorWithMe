<?php
/**
 * Amak Interior - Admin Authentication Module
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/csrf.php';

/**
 * Checks if the current request is authenticated as an admin.
 */
function is_admin_logged_in(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }

    return !empty($_SESSION['admin_user_id']) && !empty($_SESSION['admin_username']);
}

/**
 * Redirects to the login page if the user is not authenticated.
 */
function require_admin_auth(): void
{
    if (!is_admin_logged_in()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Attempts to log in an admin user by verifying username and password.
 */
function attempt_admin_login(string $username, string $password): bool
{
    $pdo = get_db_connection();
    
    // Ensure table exists and has default admin if first run
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION['admin_user_id']  = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name']     = $user['full_name'];
            $_SESSION['admin_login_at'] = time();

            return true;
        }
    } catch (\PDOException $e) {
        error_log("Login query error: " . $e->getMessage());
        return false;
    }

    return false;
}

/**
 * Logs out the current admin session.
 */
function admin_logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Returns current admin information array.
 */
function get_current_admin(): ?array
{
    if (!is_admin_logged_in()) {
        return null;
    }

    return [
        'id'       => $_SESSION['admin_user_id'],
        'username' => $_SESSION['admin_username'],
        'name'     => $_SESSION['admin_name'] ?? 'Studio Admin',
    ];
}
