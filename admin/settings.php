<?php
/**
 * Amak Interior - Global Studio Settings Management
 * Supports General, Contact, Social Media, SEO & Analytics, SMTP Mailer, Hero & 3D, and Admin Security
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin_auth();

$admin_user = get_current_admin();
$pdo = get_db_connection();
ensure_settings_table();

$tab = $_GET['tab'] ?? 'general';
$valid_tabs = ['general', 'contact', 'social', 'seo', 'smtp', 'hero', 'security'];
if (!in_array($tab, $valid_tabs, true)) {
    $tab = 'general';
}

// Ensure settings uploads directory exists
$settings_upload_dir = DIR_UPLOADS . DIRECTORY_SEPARATOR . 'settings';
if (!is_dir($settings_upload_dir)) {
    @mkdir($settings_upload_dir, 0755, true);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $action = $_POST['action'] ?? 'save_settings';
    $submitted_tab = $_POST['current_tab'] ?? $tab;

    if ($action === 'test_smtp') {
        // Send a live test email
        $test_recipient = trim($_POST['test_email'] ?? '');
        if (!filter_var($test_recipient, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('error', 'Please provide a valid test recipient email address.');
        } else {
            $test_data = [
                'name'         => 'Amak Studio Diagnostic Agent',
                'email'        => $test_recipient,
                'phone'        => '+1 (212) 555-0198',
                'project_type' => 'System SMTP Diagnostic',
                'budget_range' => 'Internal Test',
                'message'      => "This is a diagnostic test email verifying that your Amak Interior SMTP mail server configuration is functional and authenticated properly at " . date('Y-m-d H:i:s T') . ".",
                'ip_address'   => get_client_ip(),
            ];

            $sent = send_enquiry_email($test_data);
            if ($sent) {
                set_flash_message('success', "Diagnostic test email dispatched successfully to {$test_recipient}.");
            } else {
                set_flash_message('error', 'Failed to dispatch test email. Please review your SMTP credentials and port settings.');
            }
        }
        header('Location: ' . asset_url('admin/settings.php?tab=smtp'));
        exit;
    }

    if ($action === 'update_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $admin_full_name  = trim($_POST['admin_full_name'] ?? '');
        $admin_email      = trim($_POST['admin_email'] ?? '');

        // Fetch current admin
        $adminStmt = $pdo->prepare("SELECT id, password_hash FROM admins WHERE id = ? LIMIT 1");
        $adminStmt->execute([$admin_user['id']]);
        $currAdmin = $adminStmt->fetch();

        $errors = [];

        if (!empty($admin_full_name) || !empty($admin_email)) {
            if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid admin email address.';
            } else {
                $upStmt = $pdo->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
                $upStmt->execute([$admin_full_name, $admin_email, $admin_user['id']]);
                $_SESSION['admin_name'] = $admin_full_name;
            }
        }

        if (!empty($new_password)) {
            if (empty($current_password) || !password_verify($current_password, $currAdmin['password_hash'])) {
                $errors[] = 'The current password provided is incorrect.';
            } elseif (strlen($new_password) < 8) {
                $errors[] = 'The new password must contain at least 8 characters.';
            } elseif ($new_password !== $confirm_password) {
                $errors[] = 'The new password and confirmation password do not match.';
            } else {
                $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $pwdStmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $pwdStmt->execute([$new_hash, $admin_user['id']]);
                set_flash_message('success', 'Admin profile & master security password updated successfully.');
                header('Location: ' . asset_url('admin/settings.php?tab=security'));
                exit;
            }
        }

        if (!empty($errors)) {
            set_flash_message('error', implode(' ', $errors));
        } else {
            set_flash_message('success', 'Admin profile information updated successfully.');
        }

        header('Location: ' . asset_url('admin/settings.php?tab=security'));
        exit;
    }

    // Standard Settings Field Processing
    $fields_map = [
        'general' => [
            'site_name', 'site_tagline', 'site_description', 'currency_symbol', 'copyright_text', 'primary_color'
        ],
        'contact' => [
            'contact_email', 'admin_notification_email', 'contact_phone', 'whatsapp_number',
            'whatsapp_floating_button', 'studio_address', 'working_hours', 'google_maps_iframe'
        ],
        'social' => [
            'social_instagram', 'social_pinterest', 'social_linkedin', 'social_facebook',
            'social_archdaily', 'social_youtube', 'social_twitter'
        ],
        'seo' => [
            'meta_title', 'meta_description', 'meta_keywords',
            'google_search_console_verification', 'bing_webmaster_verification',
            'google_analytics_id', 'google_tag_manager_id', 'facebook_pixel_id',
            'custom_head_code', 'custom_body_code',
            'header_scripts', 'footer_scripts',
            'seo_home_title', 'seo_home_desc', 'seo_home_keywords',
            'seo_portfolio_title', 'seo_portfolio_desc', 'seo_portfolio_keywords',
            'seo_contact_title', 'seo_contact_desc', 'seo_contact_keywords',
        ],
        'smtp' => [
            'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption',
            'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name'
        ],
        'hero' => [
            'hero_badge', 'hero_heading', 'hero_subheading', 'hero_cta_text',
            'hero_cta_link', 'enable_3d_viewer', 'default_glb_model', 'hero_video_url'
        ]
    ];

    if (isset($fields_map[$submitted_tab])) {
        foreach ($fields_map[$submitted_tab] as $key) {
            $val = $_POST[$key] ?? '';
            // Handle checkboxes / toggles that may not be present in POST when unchecked
            if (in_array($key, ['whatsapp_floating_button', 'smtp_enabled', 'enable_3d_viewer'], true)) {
                $val = isset($_POST[$key]) ? '1' : '0';
            }
            update_setting($key, is_string($val) ? trim($val) : (string)$val, $submitted_tab);
        }
    }

    // Handle File Uploads (site_logo, site_favicon, og_image, glb_model_file, hero_video_file)
    $upload_keys = [
        'site_logo'       => ['type' => 'image', 'group' => 'general'],
        'site_favicon'    => ['type' => 'image', 'group' => 'general'],
        'og_image'        => ['type' => 'image', 'group' => 'seo'],
        'glb_model_file'  => ['type' => 'model', 'group' => 'hero', 'setting_key' => 'default_glb_model'],
        'hero_video_file' => ['type' => 'video', 'group' => 'hero', 'setting_key' => 'hero_video_url']
    ];

    foreach ($upload_keys as $input_name => $info) {
        if (!empty($_FILES[$input_name]['name']) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$input_name];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $target_key = $info['setting_key'] ?? $input_name;

            if ($info['type'] === 'image') {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'ico'];
                if (in_array($ext, $allowed, true) && $file['size'] <= MAX_IMAGE_SIZE) {
                    $filename = $input_name . '_' . time() . '.' . $ext;
                    $targetPath = $settings_upload_dir . DIRECTORY_SEPARATOR . $filename;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        update_setting($target_key, 'uploads/settings/' . $filename, $info['group']);
                    }
                }
            } elseif ($info['type'] === 'model') {
                $allowed = ['glb', 'gltf'];
                if (in_array($ext, $allowed, true) && $file['size'] <= MAX_MODEL_SIZE) {
                    $filename = 'model_' . time() . '.' . $ext;
                    $targetPath = DIR_MODEL_UPLOADS . DIRECTORY_SEPARATOR . $filename;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        update_setting($target_key, 'uploads/models/' . $filename, $info['group']);
                    }
                }
            } elseif ($info['type'] === 'video') {
                $allowed = ['mp4', 'webm', 'mov', 'm4v'];
                if (in_array($ext, $allowed, true) && $file['size'] <= MAX_VIDEO_SIZE) {
                    $filename = 'hero_video_' . time() . '.' . $ext;
                    $targetPath = DIR_VIDEO_UPLOADS . DIRECTORY_SEPARATOR . $filename;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        update_setting($target_key, 'uploads/videos/' . $filename, $info['group']);
                    }
                }
            }
        }
    }

    // Handle File Removal Actions
    foreach (['remove_site_logo' => 'site_logo', 'remove_site_favicon' => 'site_favicon', 'remove_og_image' => 'og_image', 'remove_hero_video' => 'hero_video_url'] as $post_btn => $set_key) {
        if (!empty($_POST[$post_btn])) {
            $fallback_val = ($set_key === 'hero_video_url') ? 'https://storage.googleapis.com/webild/default/templates/marbella/hero/hero.mp4' : '';
            update_setting($set_key, $fallback_val, $submitted_tab);
        }
    }

    set_flash_message('success', 'Studio configuration settings saved successfully.');
    header('Location: ' . asset_url('admin/settings.php?tab=' . urlencode($submitted_tab)));
    exit;
}

// Fetch all current settings
$s = get_all_settings();

// Admin profile info for security tab
$adminInfo = $pdo->prepare("SELECT username, email, full_name FROM admins WHERE id = ? LIMIT 1");
$adminInfo->execute([$admin_user['id']]);
$currentAdminData = $adminInfo->fetch() ?: ['username' => 'admin', 'email' => 'admin@amakinterior.com', 'full_name' => 'Amak Studio Administrator'];

$admin_title = 'Studio Settings | Amak Portal';
$admin_active = 'settings';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="space-y-8">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
        <div>
            <span class="text-xs uppercase tracking-widest text-brass font-mono">System & Atelier Preferences</span>
            <h1 class="font-serif text-3xl text-ivory mt-1">Studio Settings</h1>
            <p class="text-xs text-stone-subtle mt-1">Configure global branding, concierge contacts, SEO, SMTP mail dispatch, 3D experience, and security.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= asset_url('') ?>" target="_blank" class="btn-outline-brass py-2.5 px-4 text-xs flex items-center space-x-2">
                <span>View Live Site</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
        </div>
    </div>

    <!-- Layout Grid: Tab Navigation Left / Content Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Tab Navigation Sidebar (3 cols) -->
        <div class="lg:col-span-3 luxury-card p-3 space-y-1 sticky top-24">
            <nav class="space-y-1">
                <a href="?tab=general" class="w-full flex items-center space-x-3 px-4 py-3 text-xs uppercase tracking-wider rounded transition-colors <?= $tab === 'general' ? 'bg-brass text-charcoal-dark font-bold' : 'text-ivory/80 hover:bg-white/5 hover:text-brass' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>General &amp; Branding</span>
                </a>

                <a href="?tab=contact" class="w-full flex items-center space-x-3 px-4 py-3 text-xs uppercase tracking-wider rounded transition-colors <?= $tab === 'contact' ? 'bg-brass text-charcoal-dark font-bold' : 'text-ivory/80 hover:bg-white/5 hover:text-brass' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Contact &amp; Atelier</span>
                </a>

                <a href="?tab=social" class="w-full flex items-center space-x-3 px-4 py-3 text-xs uppercase tracking-wider rounded transition-colors <?= $tab === 'social' ? 'bg-brass text-charcoal-dark font-bold' : 'text-ivory/80 hover:bg-white/5 hover:text-brass' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span>Social Media</span>
                </a>

                <a href="?tab=seo" class="w-full flex items-center space-x-3 px-4 py-3 text-xs uppercase tracking-wider rounded transition-colors <?= $tab === 'seo' ? 'bg-brass text-charcoal-dark font-bold' : 'text-ivory/80 hover:bg-white/5 hover:text-brass' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>SEO &amp; Webmaster</span>
                </a>

                <a href="?tab=smtp" class="w-full flex items-center space-x-3 px-4 py-3 text-xs uppercase tracking-wider rounded transition-colors <?= $tab === 'smtp' ? 'bg-brass text-charcoal-dark font-bold' : 'text-ivory/80 hover:bg-white/5 hover:text-brass' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Email &amp; SMTP</span>
                </a>

                <a href="?tab=hero" class="w-full flex items-center space-x-3 px-4 py-3 text-xs uppercase tracking-wider rounded transition-colors <?= $tab === 'hero' ? 'bg-brass text-charcoal-dark font-bold' : 'text-ivory/80 hover:bg-white/5 hover:text-brass' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Hero Video Banner</span>
                </a>

                <a href="?tab=security" class="w-full flex items-center space-x-3 px-4 py-3 text-xs uppercase tracking-wider rounded transition-colors <?= $tab === 'security' ? 'bg-brass text-charcoal-dark font-bold' : 'text-ivory/80 hover:bg-white/5 hover:text-brass' ?>">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Admin Security</span>
                </a>
            </nav>
        </div>

        <!-- Tab Content Area (9 cols) -->
        <div class="lg:col-span-9 space-y-6">

            <!-- 1. GENERAL & BRANDING TAB -->
            <?php if ($tab === 'general'): ?>
                <form action="<?= asset_url('admin/settings.php') ?>" method="POST" enctype="multipart/form-data" class="luxury-card p-8 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="current_tab" value="general">
                    <input type="hidden" name="action" value="save_settings">

                    <div class="border-b border-white/10 pb-4">
                        <h2 class="font-serif text-2xl text-ivory">General Studio &amp; Identity</h2>
                        <p class="text-xs text-stone-subtle mt-0.5">Control the core branding and identity displayed across all studio portals.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Studio / Brand Name</label>
                            <input type="text" name="site_name" value="<?= e(get_setting('site_name', 'Amak Interior')) ?>" required class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Tagline / Motto</label>
                            <input type="text" name="site_tagline" value="<?= e(get_setting('site_tagline', 'Where Vision Meets Dimension')) ?>" required class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Studio Bio / Mission (Footer &amp; Meta Default)</label>
                        <textarea name="site_description" rows="3" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('site_description')) ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-white/5">
                        <!-- Site Logo -->
                        <div class="space-y-3">
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Header Brand Logo</label>
                            <?php $curr_logo = get_setting('site_logo'); if (!empty($curr_logo)): ?>
                                <div class="p-4 bg-charcoal-dark border border-white/10 rounded flex items-center justify-between">
                                    <img src="<?= image_url($curr_logo) ?>" alt="Logo" class="max-h-12 object-contain">
                                    <button type="submit" name="remove_site_logo" value="1" class="text-xs text-red-400 hover:text-red-300 underline">Remove</button>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="site_logo" accept="image/*" class="w-full text-xs text-stone-subtle file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:bg-brass/20 file:text-brass hover:file:bg-brass hover:file:text-charcoal-dark file:cursor-pointer">
                            <p class="text-[11px] text-stone-subtle">Recommended: Transparent PNG, SVG, or WebP. Leave blank to retain typographic header brand.</p>
                        </div>

                        <!-- Site Favicon -->
                        <div class="space-y-3">
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Browser Favicon</label>
                            <?php $curr_favicon = get_setting('site_favicon'); if (!empty($curr_favicon)): ?>
                                <div class="p-4 bg-charcoal-dark border border-white/10 rounded flex items-center justify-between">
                                    <img src="<?= image_url($curr_favicon) ?>" alt="Favicon" class="w-8 h-8 object-contain">
                                    <button type="submit" name="remove_site_favicon" value="1" class="text-xs text-red-400 hover:text-red-300 underline">Remove</button>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="site_favicon" accept="image/x-icon,image/png,image/svg+xml" class="w-full text-xs text-stone-subtle file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:bg-brass/20 file:text-brass hover:file:bg-brass hover:file:text-charcoal-dark file:cursor-pointer">
                            <p class="text-[11px] text-stone-subtle">Recommended: 32x32 or 64x64 PNG / ICO format.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-white/5">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Currency Symbol</label>
                            <input type="text" name="currency_symbol" value="<?= e(get_setting('currency_symbol', '$')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Accent Color Theme (Hex)</label>
                            <div class="flex items-center space-x-3">
                                <input type="color" id="primary_color_picker" name="primary_color" value="<?= e(get_setting('primary_color', '#B08D57')) ?>" oninput="document.getElementById('primary_color_text').value = this.value" class="h-10 w-12 bg-charcoal border border-white/15 rounded cursor-pointer shrink-0">
                                <input type="text" id="primary_color_text" value="<?= e(get_setting('primary_color', '#B08D57')) ?>" oninput="if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { document.getElementById('primary_color_picker').value = this.value; }" placeholder="#B08D57" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory font-mono focus:outline-none focus:border-brass rounded">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Copyright Text (Footer)</label>
                        <input type="text" name="copyright_text" value="<?= e(get_setting('copyright_text', '© ' . date('Y') . ' Amak Interior Studio LLC. All rights reserved.')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="btn-brass py-3 px-8 text-xs font-semibold tracking-widest uppercase">
                            Save General Settings
                        </button>
                    </div>
                </form>
            <?php endif; ?>


            <!-- 2. CONTACT & ATELIER TAB -->
            <?php if ($tab === 'contact'): ?>
                <form action="<?= asset_url('admin/settings.php') ?>" method="POST" class="luxury-card p-8 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="current_tab" value="contact">
                    <input type="hidden" name="action" value="save_settings">

                    <div class="border-b border-white/10 pb-4">
                        <h2 class="font-serif text-2xl text-ivory">Contact &amp; Studio Atelier</h2>
                        <p class="text-xs text-stone-subtle mt-0.5">Manage concierge communication channels, client reception, and office addresses.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Public Inquiries Email</label>
                            <input type="email" name="contact_email" value="<?= e(get_setting('contact_email', 'concierge@amakinterior.com')) ?>" required class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            <span class="text-[11px] text-stone-subtle mt-1 block">Displayed on website footer and contact page.</span>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Admin Lead Notification Email</label>
                            <input type="email" name="admin_notification_email" value="<?= e(get_setting('admin_notification_email', 'admin@amakinterior.com')) ?>" required class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            <span class="text-[11px] text-stone-subtle mt-1 block">Where new consultation submissions are emailed.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Direct Telephone / Atelier Line</label>
                            <input type="text" name="contact_phone" value="<?= e(get_setting('contact_phone', '+1 (212) 555-0198')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">WhatsApp Direct Number</label>
                            <input type="text" name="whatsapp_number" value="<?= e(get_setting('whatsapp_number', '+1 (212) 555-0198')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded" placeholder="+12125550198">
                        </div>
                    </div>

                    <!-- WhatsApp Floating Widget Toggle -->
                    <div class="p-4 bg-charcoal border border-white/10 rounded flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-ivory block">Floating WhatsApp Chat Widget</span>
                            <span class="text-xs text-stone-subtle block mt-0.5">Enable instant floating WhatsApp quick-contact icon on the public frontend.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="whatsapp_floating_button" value="1" <?= get_setting('whatsapp_floating_button', '1') === '1' ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-charcoal-dark peer-focus:outline-none border border-white/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-ivory after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brass"></div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Physical Studio Atelier Address</label>
                        <textarea name="studio_address" rows="2" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('studio_address', '482 Broome Street, Studio 4A, SoHo, New York, NY 10013')) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Working &amp; Consultation Hours</label>
                        <input type="text" name="working_hours" value="<?= e(get_setting('working_hours', 'By Private Appointment Only | Mon - Sat: 9:00 AM - 7:00 PM')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Google Maps Embed URL / Iframe Code</label>
                        <textarea name="google_maps_iframe" rows="3" placeholder="https://www.google.com/maps/embed?pb=..." class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-xs font-mono text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('google_maps_iframe')) ?></textarea>
                        <span class="text-[11px] text-stone-subtle mt-1 block">Paste embed iframe source URL or embed HTML tag from Google Maps.</span>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="btn-brass py-3 px-8 text-xs font-semibold tracking-widest uppercase">
                            Save Contact Details
                        </button>
                    </div>
                </form>
            <?php endif; ?>


            <!-- 3. SOCIAL MEDIA TAB -->
            <?php if ($tab === 'social'): ?>
                <form action="<?= asset_url('admin/settings.php') ?>" method="POST" class="luxury-card p-8 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="current_tab" value="social">
                    <input type="hidden" name="action" value="save_settings">

                    <div class="border-b border-white/10 pb-4">
                        <h2 class="font-serif text-2xl text-ivory">Social Media &amp; Architecture Profiles</h2>
                        <p class="text-xs text-stone-subtle mt-0.5">Connect your official social media and architectural portfolio channels.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Instagram Profile URL</label>
                            <div class="flex items-center">
                                <span class="bg-charcoal-dark border border-r-0 border-white/15 px-3 py-2.5 text-xs text-stone-subtle rounded-l">instagram.com/</span>
                                <input type="text" name="social_instagram" value="<?= e(get_setting('social_instagram', 'https://instagram.com/amakinterior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded-r">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Pinterest Gallery URL</label>
                            <input type="text" name="social_pinterest" value="<?= e(get_setting('social_pinterest', 'https://pinterest.com/amakinterior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">LinkedIn Company Page</label>
                            <input type="text" name="social_linkedin" value="<?= e(get_setting('social_linkedin', 'https://linkedin.com/company/amakinterior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Facebook Page URL</label>
                            <input type="text" name="social_facebook" value="<?= e(get_setting('social_facebook', 'https://facebook.com/amakinterior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">ArchDaily / Architectural Digest Profile</label>
                            <input type="text" name="social_archdaily" value="<?= e(get_setting('social_archdaily', 'https://archdaily.com/professionals/amakinterior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">YouTube Channel URL</label>
                                <input type="text" name="social_youtube" value="<?= e(get_setting('social_youtube')) ?>" placeholder="https://youtube.com/@amakinterior" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">X / Twitter URL</label>
                                <input type="text" name="social_twitter" value="<?= e(get_setting('social_twitter')) ?>" placeholder="https://x.com/amakinterior" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="btn-brass py-3 px-8 text-xs font-semibold tracking-widest uppercase">
                            Save Social Channels
                        </button>
                    </div>
                </form>
            <?php endif; ?>


            <!-- 4. SEO & WEBMASTER TAB -->
            <?php if ($tab === 'seo'): ?>
                <form action="<?= asset_url('admin/settings.php') ?>" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <?= csrf_field() ?>
                    <input type="hidden" name="current_tab" value="seo">
                    <input type="hidden" name="action" value="save_settings">

                    <!-- Top Banner / Header -->
                    <div class="luxury-card p-6 border-l-4 border-l-brass flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs uppercase tracking-widest text-brass font-mono">Organic Visibility &amp; Tracking</span>
                            <h2 class="font-serif text-2xl text-ivory mt-0.5">SEO &amp; Webmaster Management</h2>
                            <p class="text-xs text-stone-subtle mt-1">Configure site verification, Google Analytics 4, Tag Manager, Meta Pixel, global fallbacks, and dedicated per-page SEO.</p>
                        </div>
                        <button type="submit" class="btn-brass py-2.5 px-6 text-xs font-semibold tracking-widest uppercase self-start md:self-auto shrink-0 shadow-lg">
                            Save All SEO Settings
                        </button>
                    </div>

                    <!-- 1. Search Engine Verification & Webmasters -->
                    <div class="luxury-card p-8 space-y-6">
                        <div class="border-b border-white/10 pb-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-serif text-xl text-ivory">1. Search Console &amp; Webmaster Verification</h3>
                                <p class="text-xs text-stone-subtle mt-0.5">Verify site ownership with Google Search Console and Bing Webmaster Tools.</p>
                            </div>
                            <span class="text-[10px] uppercase font-mono text-brass border border-brass/30 px-2.5 py-1 rounded">Ownership</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Google Search Console Verification (HTML Tag Content)</label>
                                <input type="text" name="google_search_console_verification" value="<?= e(get_setting('google_search_console_verification')) ?>" placeholder="zVvlzydruyeRZpm2DWlMr-IX-azar2kc36JIJ0ldHawI" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-xs font-mono text-ivory focus:outline-none focus:border-brass rounded">
                                <span class="text-[11px] text-stone-subtle mt-1.5 block">From Search Console &rarr; Settings &rarr; Ownership &rarr; HTML tag. Paste just the content value.</span>
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Bing Webmaster Verification (msvalidate.01)</label>
                                <input type="text" name="bing_webmaster_verification" value="<?= e(get_setting('bing_webmaster_verification')) ?>" placeholder="4F6C7174D226DAD8399E43A581735C7C" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-xs font-mono text-ivory focus:outline-none focus:border-brass rounded">
                                <span class="text-[11px] text-stone-subtle mt-1.5 block">Paste your Bing Webmaster verification meta content code.</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Analytics & Conversion Tracking -->
                    <div class="luxury-card p-8 space-y-6">
                        <div class="border-b border-white/10 pb-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-serif text-xl text-ivory">2. Analytics &amp; Conversion Tracking</h3>
                                <p class="text-xs text-stone-subtle mt-0.5">Deploy Google Analytics 4, Google Tag Manager container, and Meta (Facebook) Pixel.</p>
                            </div>
                            <span class="text-[10px] uppercase font-mono text-brass border border-brass/30 px-2.5 py-1 rounded">Telemetry</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Google Analytics 4 — Measurement ID</label>
                                <input type="text" name="google_analytics_id" value="<?= e(get_setting('google_analytics_id')) ?>" placeholder="G-XXXXXXXXXX" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm font-mono text-ivory focus:outline-none focus:border-brass rounded">
                                <span class="text-[11px] text-stone-subtle mt-1.5 block">Measurement ID from GA4 Web Stream (e.g. G-71K8Q94WXX).</span>
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Google Tag Manager (GTM) Container ID</label>
                                <input type="text" name="google_tag_manager_id" value="<?= e(get_setting('google_tag_manager_id')) ?>" placeholder="GTM-XXXXXXX" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm font-mono text-ivory focus:outline-none focus:border-brass rounded">
                                <span class="text-[11px] text-stone-subtle mt-1.5 block">If managing tags via GTM, fill this in and you can leave GA4 blank.</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Meta (Facebook / Instagram) Pixel ID</label>
                            <input type="text" name="facebook_pixel_id" value="<?= e(get_setting('facebook_pixel_id')) ?>" placeholder="123456789012345" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm font-mono text-ivory focus:outline-none focus:border-brass rounded">
                            <span class="text-[11px] text-stone-subtle mt-1.5 block">Numeric Pixel ID from Meta Events Manager for social retargeting &amp; conversion tracking.</span>
                        </div>
                    </div>

                    <!-- 3. Raw Custom Scripts & Code Injection -->
                    <div class="luxury-card p-8 space-y-6">
                        <div class="border-b border-white/10 pb-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-serif text-xl text-ivory">3. Custom Code &amp; Script Injection</h3>
                                <p class="text-xs text-stone-subtle mt-0.5">Inject raw HTML/JS code into the site header and footer without modifying core template files.</p>
                            </div>
                            <span class="text-[10px] uppercase font-mono text-brass border border-brass/30 px-2.5 py-1 rounded">Raw Code</span>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Custom &lt;head&gt; Code Injection (Before &lt;/head&gt;)</label>
                                <textarea name="custom_head_code" rows="4" placeholder="<script>...</script> or <link rel=&quot;...&quot;>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-xs font-mono text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('custom_head_code', get_setting('header_scripts'))) ?></textarea>
                                <span class="text-[11px] text-stone-subtle mt-1.5 block">Raw HTML/JS injected before &lt;/head&gt; on every public page (e.g. font links, verification tags, third-party SDKs).</span>
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Custom Body-End Code Injection (Before &lt;/body&gt;)</label>
                                <textarea name="custom_body_code" rows="4" placeholder="<!-- Chat Widget / Analytics Pixel -->&#10;<script>...</script>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-xs font-mono text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('custom_body_code', get_setting('footer_scripts'))) ?></textarea>
                                <span class="text-[11px] text-stone-subtle mt-1.5 block">Raw HTML/JS injected right before &lt;/body&gt; on every public page (e.g. live chat widgets, remarketing tags).</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Global Search Metadata & OpenGraph -->
                    <div class="luxury-card p-8 space-y-6">
                        <div class="border-b border-white/10 pb-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-serif text-xl text-ivory">4. Global Default Search Metadata</h3>
                                <p class="text-xs text-stone-subtle mt-0.5">Fallback search engine tags and social card preview image for all public routes.</p>
                            </div>
                            <span class="text-[10px] uppercase font-mono text-brass border border-brass/30 px-2.5 py-1 rounded">Global Fallback</span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium">Default Website Meta Title</label>
                                <span id="count_meta_title" class="text-[11px] font-mono text-stone-subtle">0 / 60 chars</span>
                            </div>
                            <input type="text" id="input_meta_title" name="meta_title" value="<?= e(get_setting('meta_title', 'Amak Interior | Where Vision Meets Dimension')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            <span class="text-[11px] text-stone-subtle mt-1.5 block">Recommended length: 50–60 characters. Appears as the main headline in search engine listings.</span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium">Default Meta Description</label>
                                <span id="count_meta_desc" class="text-[11px] font-mono text-stone-subtle">0 / 160 chars</span>
                            </div>
                            <textarea id="input_meta_desc" name="meta_description" rows="3" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('meta_description', 'Bespoke luxury interior architecture and experiential spatial design studio based in Manhattan, New York. Crafting timeless sanctuaries through vision and dimension.')) ?></textarea>
                            <span class="text-[11px] text-stone-subtle mt-1.5 block">Recommended length: 140–160 characters. Summarizes the studio philosophy for search snippets.</span>
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Default Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="<?= e(get_setting('meta_keywords', 'luxury interior design, 3D interior architecture, high-end residential design, Manhattan interior designer, custom furniture design, Amak Interior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            <span class="text-[11px] text-stone-subtle mt-1.5 block">Comma-separated keywords representing core disciplines, locations, and architectural capabilities.</span>
                        </div>

                        <!-- OpenGraph Preview Image -->
                        <div class="space-y-3 pt-4 border-t border-white/5">
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Social Share (OpenGraph) Preview Image</label>
                            <?php $curr_og = get_setting('og_image'); if (!empty($curr_og)): ?>
                                <div class="p-4 bg-charcoal-dark border border-white/10 rounded flex items-center justify-between">
                                    <img src="<?= image_url($curr_og) ?>" alt="OG Share Image" class="max-h-24 rounded object-cover">
                                    <button type="submit" name="remove_og_image" value="1" class="text-xs text-red-400 hover:text-red-300 underline">Remove</button>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="og_image" accept="image/*" class="w-full text-xs text-stone-subtle file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:bg-brass/20 file:text-brass hover:file:bg-brass hover:file:text-charcoal-dark file:cursor-pointer">
                            <p class="text-[11px] text-stone-subtle">Recommended dimensions: 1200 &times; 630 px (JPG or WebP). Displayed when sharing links on WhatsApp, LinkedIn, iMessage, and X.</p>
                        </div>
                    </div>

                    <!-- 5. Dedicated Per-Page SEO & SERP Simulator -->
                    <div class="luxury-card p-8 space-y-6">
                        <div class="border-b border-white/10 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="font-serif text-xl text-ivory">5. Dedicated Per-Page SEO &amp; SERP Simulator</h3>
                                <p class="text-xs text-stone-subtle mt-0.5">Configure distinct titles, meta descriptions, and keywords for individual studio pages with live Google previews.</p>
                            </div>
                            <span class="text-[10px] uppercase font-mono text-brass border border-brass/30 px-2.5 py-1 rounded self-start">Per-Page Control</span>
                        </div>

                        <!-- Page Selection Pills -->
                        <div class="flex flex-wrap gap-2 p-1.5 bg-charcoal-dark border border-white/10 rounded">
                            <button type="button" onclick="switchSeoPageTab('home', this)" class="seo-page-pill px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded transition-all bg-brass text-charcoal-dark">
                                Home Page (/)
                            </button>
                            <button type="button" onclick="switchSeoPageTab('portfolio', this)" class="seo-page-pill px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded transition-all text-stone-subtle hover:text-ivory hover:bg-white/5">
                                Portfolio (/portfolio.php)
                            </button>
                            <button type="button" onclick="switchSeoPageTab('contact', this)" class="seo-page-pill px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded transition-all text-stone-subtle hover:text-ivory hover:bg-white/5">
                                Contact (/contact.php)
                            </button>
                        </div>

                        <!-- Page 1: Home -->
                        <div id="seo_panel_home" class="seo-page-panel space-y-6">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                                <div class="lg:col-span-7 space-y-5">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Home Page Title</label>
                                            <span id="count_home_title" class="text-[11px] font-mono text-stone-subtle">0 / 60</span>
                                        </div>
                                        <input type="text" id="input_home_title" name="seo_home_title" value="<?= e(get_setting('seo_home_title', 'Amak Interior | Bespoke 3D Spatial Architecture & Luxury Living')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Home Meta Description</label>
                                            <span id="count_home_desc" class="text-[11px] font-mono text-stone-subtle">0 / 160</span>
                                        </div>
                                        <textarea id="input_home_desc" name="seo_home_desc" rows="3" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('seo_home_desc', 'Amak Interior is a premier spatial design atelier based in Manhattan. We curate ultra-luxury residential spaces through immersive 3D pre-visualization, rare materials, and architectural precision.')) ?></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Home Meta Keywords</label>
                                        <input type="text" name="seo_home_keywords" value="<?= e(get_setting('seo_home_keywords', 'luxury interior design, Manhattan architect, 3D pre-visualization, SoHo interior atelier, bespoke residences')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                                    </div>
                                </div>

                                <!-- SERP Simulator Right -->
                                <div class="lg:col-span-5 bg-charcoal-dark border border-white/10 rounded-lg p-5 space-y-3">
                                    <div class="flex items-center justify-between border-b border-white/10 pb-2">
                                        <span class="text-[10px] uppercase font-mono tracking-widest text-brass">Google SERP Live Preview</span>
                                        <span class="text-[10px] text-stone-subtle">Desktop Snippet</span>
                                    </div>
                                    <div class="bg-white rounded p-4 text-left shadow-md">
                                        <div class="text-[12px] text-[#202124] flex items-center space-x-1.5 truncate mb-1">
                                            <span class="w-4 h-4 rounded-full bg-[#B08D57] inline-flex items-center justify-center text-[9px] text-white font-bold">A</span>
                                            <span class="text-[#202124] font-medium truncate"><?= parse_url(SITE_URL, PHP_URL_HOST) ?: 'amakinterior.com' ?></span>
                                            <span class="text-[#5f6368]">&rsaquo;</span>
                                        </div>
                                        <h4 id="serp_home_title" class="text-[#1a0dab] hover:underline text-[17px] font-normal leading-snug cursor-pointer line-clamp-1 mb-1">
                                            <?= e(get_setting('seo_home_title', 'Amak Interior | Bespoke 3D Spatial Architecture & Luxury Living')) ?>
                                        </h4>
                                        <p id="serp_home_desc" class="text-[#4d5156] text-[13px] leading-relaxed line-clamp-2">
                                            <?= e(get_setting('seo_home_desc', 'Amak Interior is a premier spatial design atelier based in Manhattan. We curate ultra-luxury residential spaces through immersive 3D pre-visualization, rare materials, and architectural precision.')) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page 2: Portfolio -->
                        <div id="seo_panel_portfolio" class="seo-page-panel space-y-6 hidden">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                                <div class="lg:col-span-7 space-y-5">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Portfolio Page Title</label>
                                            <span id="count_portfolio_title" class="text-[11px] font-mono text-stone-subtle">0 / 60</span>
                                        </div>
                                        <input type="text" id="input_portfolio_title" name="seo_portfolio_title" value="<?= e(get_setting('seo_portfolio_title', 'Portfolio & Monograph Archive | Amak Interior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Portfolio Meta Description</label>
                                            <span id="count_portfolio_desc" class="text-[11px] font-mono text-stone-subtle">0 / 160</span>
                                        </div>
                                        <textarea id="input_portfolio_desc" name="seo_portfolio_desc" rows="3" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('seo_portfolio_desc', 'Explore our monograph of private luxury residential commissions, penthouse gut renovations, and bespoke spatial architecture across Manhattan and the Hamptons.')) ?></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Portfolio Meta Keywords</label>
                                        <input type="text" name="seo_portfolio_keywords" value="<?= e(get_setting('seo_portfolio_keywords', 'interior portfolio, luxury penthouse design, SoHo residences, architectural case studies, high-end millwork')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                                    </div>
                                </div>

                                <!-- SERP Simulator Right -->
                                <div class="lg:col-span-5 bg-charcoal-dark border border-white/10 rounded-lg p-5 space-y-3">
                                    <div class="flex items-center justify-between border-b border-white/10 pb-2">
                                        <span class="text-[10px] uppercase font-mono tracking-widest text-brass">Google SERP Live Preview</span>
                                        <span class="text-[10px] text-stone-subtle">Desktop Snippet</span>
                                    </div>
                                    <div class="bg-white rounded p-4 text-left shadow-md">
                                        <div class="text-[12px] text-[#202124] flex items-center space-x-1.5 truncate mb-1">
                                            <span class="w-4 h-4 rounded-full bg-[#B08D57] inline-flex items-center justify-center text-[9px] text-white font-bold">A</span>
                                            <span class="text-[#202124] font-medium truncate"><?= parse_url(SITE_URL, PHP_URL_HOST) ?: 'amakinterior.com' ?></span>
                                            <span class="text-[#5f6368]">&rsaquo; portfolio</span>
                                        </div>
                                        <h4 id="serp_portfolio_title" class="text-[#1a0dab] hover:underline text-[17px] font-normal leading-snug cursor-pointer line-clamp-1 mb-1">
                                            <?= e(get_setting('seo_portfolio_title', 'Portfolio & Monograph Archive | Amak Interior')) ?>
                                        </h4>
                                        <p id="serp_portfolio_desc" class="text-[#4d5156] text-[13px] leading-relaxed line-clamp-2">
                                            <?= e(get_setting('seo_portfolio_desc', 'Explore our monograph of private luxury residential commissions, penthouse gut renovations, and bespoke spatial architecture across Manhattan and the Hamptons.')) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page 3: Contact -->
                        <div id="seo_panel_contact" class="seo-page-panel space-y-6 hidden">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                                <div class="lg:col-span-7 space-y-5">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Contact Page Title</label>
                                            <span id="count_contact_title" class="text-[11px] font-mono text-stone-subtle">0 / 60</span>
                                        </div>
                                        <input type="text" id="input_contact_title" name="seo_contact_title" value="<?= e(get_setting('seo_contact_title', 'Private Commissions & Inquiries | Amak Interior')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs uppercase tracking-wider text-brass font-medium">Contact Meta Description</label>
                                            <span id="count_contact_desc" class="text-[11px] font-mono text-stone-subtle">0 / 160</span>
                                        </div>
                                        <textarea id="input_contact_desc" name="seo_contact_desc" rows="3" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('seo_contact_desc', 'Initiate a private spatial consultation with Amak Interior in SoHo, New York. We accept a limited number of residential and commercial commissions annually.')) ?></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Contact Meta Keywords</label>
                                        <input type="text" name="seo_contact_keywords" value="<?= e(get_setting('seo_contact_keywords', 'contact interior designer, private consultation, SoHo atelier appointment, hire luxury architect')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                                    </div>
                                </div>

                                <!-- SERP Simulator Right -->
                                <div class="lg:col-span-5 bg-charcoal-dark border border-white/10 rounded-lg p-5 space-y-3">
                                    <div class="flex items-center justify-between border-b border-white/10 pb-2">
                                        <span class="text-[10px] uppercase font-mono tracking-widest text-brass">Google SERP Live Preview</span>
                                        <span class="text-[10px] text-stone-subtle">Desktop Snippet</span>
                                    </div>
                                    <div class="bg-white rounded p-4 text-left shadow-md">
                                        <div class="text-[12px] text-[#202124] flex items-center space-x-1.5 truncate mb-1">
                                            <span class="w-4 h-4 rounded-full bg-[#B08D57] inline-flex items-center justify-center text-[9px] text-white font-bold">A</span>
                                            <span class="text-[#202124] font-medium truncate"><?= parse_url(SITE_URL, PHP_URL_HOST) ?: 'amakinterior.com' ?></span>
                                            <span class="text-[#5f6368]">&rsaquo; contact</span>
                                        </div>
                                        <h4 id="serp_contact_title" class="text-[#1a0dab] hover:underline text-[17px] font-normal leading-snug cursor-pointer line-clamp-1 mb-1">
                                            <?= e(get_setting('seo_contact_title', 'Private Commissions & Inquiries | Amak Interior')) ?>
                                        </h4>
                                        <p id="serp_contact_desc" class="text-[#4d5156] text-[13px] leading-relaxed line-clamp-2">
                                            <?= e(get_setting('seo_contact_desc', 'Initiate a private spatial consultation with Amak Interior in SoHo, New York. We accept a limited number of residential and commercial commissions annually.')) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Save Actions Bottom -->
                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="btn-brass py-3.5 px-10 text-xs font-semibold tracking-widest uppercase shadow-xl">
                            Save All SEO &amp; Analytics Settings
                        </button>
                    </div>
                </form>

                <!-- Interactive Tab & Live Preview Scripts -->
                <script>
                function switchSeoPageTab(pageKey, btn) {
                    document.querySelectorAll('.seo-page-panel').forEach(function(panel) {
                        panel.classList.add('hidden');
                    });
                    var targetPanel = document.getElementById('seo_panel_' + pageKey);
                    if (targetPanel) targetPanel.classList.remove('hidden');

                    document.querySelectorAll('.seo-page-pill').forEach(function(pill) {
                        pill.classList.remove('bg-brass', 'text-charcoal-dark');
                        pill.classList.add('text-stone-subtle');
                    });
                    btn.classList.add('bg-brass', 'text-charcoal-dark');
                    btn.classList.remove('text-stone-subtle');
                }

                function bindLiveCounter(inputId, counterId, serpId, maxLen) {
                    var input = document.getElementById(inputId);
                    var counter = document.getElementById(counterId);
                    var serp = document.getElementById(serpId);
                    if (!input) return;

                    function update() {
                        var len = input.value.length;
                        if (counter) {
                            counter.textContent = len + ' / ' + maxLen + ' chars';
                            if (len > maxLen) {
                                counter.classList.add('text-red-400');
                                counter.classList.remove('text-stone-subtle');
                            } else {
                                counter.classList.remove('text-red-400');
                                counter.classList.add('text-stone-subtle');
                            }
                        }
                        if (serp) {
                            serp.textContent = input.value || '(Empty preview)';
                        }
                    }

                    input.addEventListener('input', update);
                    update();
                }

                document.addEventListener('DOMContentLoaded', function() {
                    bindLiveCounter('input_meta_title', 'count_meta_title', null, 60);
                    bindLiveCounter('input_meta_desc', 'count_meta_desc', null, 160);

                    bindLiveCounter('input_home_title', 'count_home_title', 'serp_home_title', 60);
                    bindLiveCounter('input_home_desc', 'count_home_desc', 'serp_home_desc', 160);

                    bindLiveCounter('input_portfolio_title', 'count_portfolio_title', 'serp_portfolio_title', 60);
                    bindLiveCounter('input_portfolio_desc', 'count_portfolio_desc', 'serp_portfolio_desc', 160);

                    bindLiveCounter('input_contact_title', 'count_contact_title', 'serp_contact_title', 60);
                    bindLiveCounter('input_contact_desc', 'count_contact_desc', 'serp_contact_desc', 160);
                });
                </script>
            <?php endif; ?>


            <!-- 5. EMAIL & SMTP TAB -->
            <?php if ($tab === 'smtp'): ?>
                <div class="space-y-6">
                    <form action="<?= asset_url('admin/settings.php') ?>" method="POST" class="luxury-card p-8 space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="current_tab" value="smtp">
                        <input type="hidden" name="action" value="save_settings">

                        <div class="border-b border-white/10 pb-4">
                            <h2 class="font-serif text-2xl text-ivory">Email &amp; SMTP Configuration</h2>
                            <p class="text-xs text-stone-subtle mt-0.5">Configure PHPMailer SMTP to send consultation lead confirmations &amp; admin notifications.</p>
                        </div>

                        <!-- SMTP Toggle Switch -->
                        <div class="p-4 bg-charcoal border border-white/10 rounded flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-ivory block">Enable Production SMTP Mailer</span>
                                <span class="text-xs text-stone-subtle block mt-0.5">When disabled, enquiries are logged safely in database without attempting live socket SMTP connections.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="smtp_enabled" value="1" <?= get_setting('smtp_enabled', '0') === '1' ? 'checked' : '' ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-charcoal-dark peer-focus:outline-none border border-white/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-ivory after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brass"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">SMTP Host</label>
                                <input type="text" name="smtp_host" value="<?= e(get_setting('smtp_host', 'smtp.gmail.com')) ?>" placeholder="smtp.gmail.com or mail.domain.com" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory font-mono focus:outline-none focus:border-brass rounded">
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">SMTP Port</label>
                                <input type="number" name="smtp_port" value="<?= e(get_setting('smtp_port', '587')) ?>" placeholder="587 / 465 / 25" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory font-mono focus:outline-none focus:border-brass rounded">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Encryption Type</label>
                                <select name="smtp_encryption" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                                    <option value="tls" <?= get_setting('smtp_encryption', 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                                    <option value="ssl" <?= get_setting('smtp_encryption', 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                    <option value="none" <?= get_setting('smtp_encryption', 'none') === 'none' ? 'selected' : '' ?>>None (Port 25)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">SMTP Username</label>
                                <input type="text" name="smtp_username" value="<?= e(get_setting('smtp_username')) ?>" placeholder="user@gmail.com" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">SMTP Password</label>
                                <input type="password" name="smtp_password" value="<?= e(get_setting('smtp_password')) ?>" placeholder="App Password" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-white/5">
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">From / Sender Email Address</label>
                                <input type="email" name="smtp_from_email" value="<?= e(get_setting('smtp_from_email', 'concierge@amakinterior.com')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">From / Sender Display Name</label>
                                <input type="text" name="smtp_from_name" value="<?= e(get_setting('smtp_from_name', 'Amak Interior Concierge')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex justify-end">
                            <button type="submit" class="btn-brass py-3 px-8 text-xs font-semibold tracking-widest uppercase">
                                Save SMTP Settings
                            </button>
                        </div>
                    </form>

                    <!-- Test Email Utility Card -->
                    <form action="<?= asset_url('admin/settings.php') ?>" method="POST" class="luxury-card p-6 border-l-4 border-l-sky-500 space-y-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="current_tab" value="smtp">
                        <input type="hidden" name="action" value="test_smtp">

                        <h3 class="font-serif text-lg text-ivory">Dispatch Live SMTP Diagnostic Test</h3>
                        <p class="text-xs text-stone-subtle">Send a simulated consultation notice to verify that your mail server credentials and TLS handshakes succeed.</p>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="email" name="test_email" required value="<?= e($currentAdminData['email']) ?>" placeholder="Enter test recipient email..." class="flex-1 bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            <button type="submit" class="btn-outline-brass py-2.5 px-6 text-xs uppercase tracking-wider">
                                Send Diagnostic
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>


            <!-- 6. HERO VIDEO BANNER TAB -->
            <?php if ($tab === 'hero'): ?>
                <form action="<?= asset_url('admin/settings.php') ?>" method="POST" enctype="multipart/form-data" class="luxury-card p-8 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="current_tab" value="hero">
                    <input type="hidden" name="action" value="save_settings">

                    <div class="border-b border-white/10 pb-4">
                        <h2 class="font-serif text-2xl text-ivory">Homepage Hero Video Banner &amp; Copy</h2>
                        <p class="text-xs text-stone-subtle mt-0.5">Customize the full-bleed cinematic video background, badge text, main headline, and call-to-action button.</p>
                    </div>

                    <!-- Cinematic Background Video Section -->
                    <div class="space-y-4">
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium">Hero Background Cinematic Video (.MP4 / WebM / URL)</label>
                        <?php 
                        $curr_video = get_setting('hero_video_url', 'https://storage.googleapis.com/webild/default/templates/marbella/hero/hero.mp4'); 
                        $is_custom_video = (!empty($curr_video) && $curr_video !== 'https://storage.googleapis.com/webild/default/templates/marbella/hero/hero.mp4');
                        ?>
                        
                        <?php if (!empty($curr_video)): ?>
                            <div class="p-4 bg-charcoal-dark border border-white/10 rounded space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-stone-subtle font-mono truncate max-w-md"><?= e($curr_video) ?></span>
                                    <?php if ($is_custom_video): ?>
                                        <button type="submit" name="remove_hero_video" value="1" class="text-xs text-red-400 hover:text-red-300 underline">Reset to Default Video</button>
                                    <?php endif; ?>
                                </div>
                                <div class="aspect-video max-w-md bg-black rounded overflow-hidden border border-white/10">
                                    <video controls playsinline preload="metadata" class="w-full h-full object-cover">
                                        <source src="<?= image_url($curr_video) ?>" type="video/mp4">
                                        Your browser does not support HTML5 video preview.
                                    </video>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div>
                            <span class="text-[11px] text-stone-subtle uppercase tracking-wider block mb-1">Direct Video URL or Local Path:</span>
                            <input type="text" name="hero_video_url" value="<?= e($curr_video) ?>" placeholder="https://example.com/video.mp4 or uploads/videos/hero.mp4" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-xs font-mono text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <span class="text-[11px] text-stone-subtle uppercase tracking-wider block mb-1">Or Upload Video File (Max 50MB):</span>
                            <input type="file" name="hero_video_file" accept="video/mp4,video/webm,video/quicktime" class="w-full text-xs text-stone-subtle file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:bg-brass/20 file:text-brass hover:file:bg-brass hover:file:text-charcoal-dark file:cursor-pointer">
                            <p class="text-[11px] text-stone-subtle mt-1">Recommended format: 1080p H.264 MP4 or WebM (optimized for high-performance streaming).</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-white/5">
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Hero Tagline / Badge</label>
                        <input type="text" name="hero_badge" value="<?= e(get_setting('hero_badge', 'HAUTE COUTURE ARCHITECTURE')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Hero Main Headline</label>
                        <input type="text" name="hero_heading" value="<?= e(get_setting('hero_heading', 'Where Vision Meets Dimension')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Hero Subheading Text</label>
                        <textarea name="hero_subheading" rows="3" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded"><?= e(get_setting('hero_subheading', 'Bespoke residential sanctuaries and spatial architecture crafted with three-dimensional precision.')) ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-white/5">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Primary CTA Button Label</label>
                            <input type="text" name="hero_cta_text" value="<?= e(get_setting('hero_cta_text', 'Explore Commissions')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Primary CTA Destination Link</label>
                            <input type="text" name="hero_cta_link" value="<?= e(get_setting('hero_cta_link', 'portfolio.php')) ?>" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="btn-brass py-3 px-8 text-xs font-semibold tracking-widest uppercase">
                            Save Hero Video &amp; Copy
                        </button>
                    </div>
                </form>
            <?php endif; ?>


            <!-- 7. ADMIN SECURITY & PROFILE TAB -->
            <?php if ($tab === 'security'): ?>
                <form action="<?= asset_url('admin/settings.php') ?>" method="POST" class="luxury-card p-8 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="current_tab" value="security">
                    <input type="hidden" name="action" value="update_password">

                    <div class="border-b border-white/10 pb-4">
                        <h2 class="font-serif text-2xl text-ivory">Admin Security &amp; Master Profile</h2>
                        <p class="text-xs text-stone-subtle mt-0.5">Manage administrator credentials, contact notification email, and access password.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Administrator Full Name</label>
                            <input type="text" name="admin_full_name" value="<?= e($currentAdminData['full_name']) ?>" required class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Administrator Email Address</label>
                            <input type="email" name="admin_email" value="<?= e($currentAdminData['email']) ?>" required class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/10 space-y-4">
                        <h3 class="font-serif text-lg text-ivory">Change Master Password</h3>
                        <p class="text-xs text-stone-subtle">Leave blank if you do not wish to modify the administrator password.</p>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Current Master Password</label>
                            <input type="password" name="current_password" placeholder="••••••••••••" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">New Password (Min 8 characters)</label>
                                <input type="password" name="new_password" placeholder="••••••••••••" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider text-brass font-medium mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" placeholder="••••••••••••" class="w-full bg-charcoal border border-white/15 px-4 py-2.5 text-sm text-ivory focus:outline-none focus:border-brass rounded">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="btn-brass py-3 px-8 text-xs font-semibold tracking-widest uppercase">
                            Update Admin Profile &amp; Password
                        </button>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin-footer.php';
?>
