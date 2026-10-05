<?php
/**
 * Amak Interior - Admin Panel Layout Header
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_auth();

$admin_user = get_current_admin();
$admin_title = $admin_title ?? 'Studio Portal | Amak Interior';
$admin_active = $admin_active ?? 'dashboard';

// Count unread enquiries for notification badge
$pdo = get_db_connection();
try {
    $unread_count = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE is_read = 0")->fetchColumn();
} catch (\Exception $e) {
    $unread_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($admin_title) ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ivory: '#F5F1EA',
                        charcoal: {
                            DEFAULT: '#1C1C1C',
                            light: '#252525',
                            card: '#2A2A2A',
                            dark: '#141414',
                        },
                        brass: {
                            DEFAULT: '#B08D57',
                            light: '#D4B27C',
                            dark: '#8C6C38',
                        }
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <?= render_dynamic_accent_styles() ?>
</head>
<body class="bg-charcoal-dark text-ivory font-sans min-h-screen flex flex-col">

    <!-- Top Admin Bar -->
    <header class="bg-charcoal border-b border-white/10 px-6 py-4 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="<?= asset_url('admin/index.php') ?>" class="flex items-center space-x-2">
                    <span class="font-serif text-2xl text-ivory tracking-widest">AMAK</span>
                    <span class="text-[10px] uppercase font-mono tracking-widest text-brass border-l border-brass/40 pl-2">PORTAL</span>
                </a>

                <nav class="hidden md:flex items-center space-x-6 text-xs uppercase tracking-widest">
                    <a href="<?= asset_url('admin/index.php') ?>" class="hover:text-brass transition-colors <?= $admin_active === 'dashboard' ? 'text-brass font-semibold' : 'text-ivory/70' ?>">Dashboard</a>
                    <a href="<?= asset_url('admin/projects.php') ?>" class="hover:text-brass transition-colors <?= $admin_active === 'projects' ? 'text-brass font-semibold' : 'text-ivory/70' ?>">Projects</a>
                    <a href="<?= asset_url('admin/project-form.php') ?>" class="hover:text-brass transition-colors <?= $admin_active === 'project-new' ? 'text-brass font-semibold' : 'text-ivory/70' ?>">+ New Project</a>
                    <a href="<?= asset_url('admin/enquiries.php') ?>" class="relative hover:text-brass transition-colors <?= $admin_active === 'enquiries' ? 'text-brass font-semibold' : 'text-ivory/70' ?>">
                        Enquiries
                        <?php if ($unread_count > 0): ?>
                            <span class="ml-1 px-1.5 py-0.5 text-[9px] bg-brass text-charcoal font-bold rounded-full"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= asset_url('admin/settings.php') ?>" class="hover:text-brass transition-colors <?= $admin_active === 'settings' ? 'text-brass font-semibold' : 'text-ivory/70' ?>">Settings</a>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <a href="<?= asset_url('') ?>" target="_blank" class="text-xs uppercase tracking-wider text-stone-subtle hover:text-brass transition-colors flex items-center space-x-1">
                    <span>View Live Studio</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
                
                <div class="h-4 w-[1px] bg-white/10"></div>
                
                <span class="text-xs text-ivory/80 hidden sm:inline-block font-mono"><?= e($admin_user['username']) ?></span>
                
                <a href="<?= asset_url('admin/logout.php') ?>" class="text-xs uppercase tracking-wider text-red-400 hover:text-red-300 transition-colors">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Admin Main Body Wrapper -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-8">
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div id="admin-flash-alert" class="mb-6 p-4 border text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-emerald-950/80 border-emerald-500/50 text-emerald-200' : 'bg-red-950/80 border-red-500/50 text-red-200' ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
