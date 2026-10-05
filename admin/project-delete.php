<?php
/**
 * Amak Interior - Admin Project Delete Action Handler
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . asset_url('admin/projects.php'));
    exit;
}

require_csrf_token();

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $pdo = get_db_connection();
    try {
        $stmt = $pdo->prepare("SELECT title FROM projects WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        if ($project) {
            $del = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $del->execute([$id]);
            set_flash_message('success', "Monograph '{$project['title']}' deleted successfully.");
        }
    } catch (\Exception $e) {
        error_log("Project delete error: " . $e->getMessage());
        set_flash_message('error', 'Could not delete project: ' . $e->getMessage());
    }
}

header('Location: ' . asset_url('admin/projects.php'));
exit;
