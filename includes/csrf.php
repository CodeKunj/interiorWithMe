<?php
/**
 * Amak Interior - CSRF Protection Module
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

/**
 * Generates or retrieves the current session CSRF token.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns an HTML hidden input containing the CSRF token.
 */
function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validates the CSRF token from POST or request headers.
 */
function csrf_verify(?string $token = null): bool
{
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    if (!is_string($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforces CSRF verification and terminates request on failure.
 */
function require_csrf_token(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        http_response_code(403);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please refresh the page and try again.']);
            exit;
        }

        echo '<!DOCTYPE html><html><body style="font-family: sans-serif; text-align: center; padding: 50px; background: #1C1C1C; color: #F5F1EA;">';
        echo '<h2 style="color: #B08D57;">Security Token Expired (403)</h2>';
        echo '<p>Your session or CSRF token was invalid. Please return to the previous page, refresh, and submit again.</p>';
        echo '<a href="javascript:history.back()" style="color: #B08D57;">Go Back</a>';
        echo '</body></html>';
        exit;
    }
}
