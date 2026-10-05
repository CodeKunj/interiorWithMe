<?php
/**
 * Amak Interior - Admin Logout Handler
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

admin_logout();

header('Location: ' . asset_url('admin/login.php'));
exit;
