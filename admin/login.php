<?php
/**
 * Amak Interior - Admin Login
 * Secure bcrypt authentication, rate limiting, and session initialization
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
    header('Location: ' . asset_url('admin/index.php'));
    exit;
}

$error = '';

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Security token invalid. Please refresh the page.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            // Check if admin user exists, or auto-seed default admin if table is empty
            $pdo = get_db_connection();
            try {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
                if ($count === 0) {
                    $def_hash = password_hash('Admin@Amak2026!', PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name) VALUES ('admin', 'admin@amakinterior.com', ?, 'Amak Studio Administrator')");
                    $ins->execute([$def_hash]);
                }
            } catch (\Exception $e) {
                error_log("Admin table check error: " . $e->getMessage());
            }

            if (attempt_admin_login($username, $password)) {
                header('Location: ' . asset_url('admin/index.php'));
                exit;
            } else {
                $error = 'Invalid credentials. Please verify username and password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Portal Login | Amak Interior</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

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
</head>
<body class="bg-charcoal-dark text-ivory font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        
        <!-- Studio Logo -->
        <div class="text-center mb-8">
            <span class="font-serif text-3xl tracking-widest text-ivory block">AMAK</span>
            <span class="text-[10px] tracking-super uppercase text-brass font-medium">STUDIO MANAGEMENT PORTAL</span>
        </div>

        <div class="luxury-card p-8 md:p-10 border border-white/10">
            
            <h1 class="font-serif text-2xl text-ivory mb-2 font-light text-center">
                Authenticate Access
            </h1>
            <p class="text-xs text-stone-subtle text-center mb-6">
                Enter your administrative credentials to manage portfolio monographs and private commissions.
            </p>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 bg-red-950/80 border border-red-500/50 text-red-200 text-xs">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="<?= asset_url('admin/login.php') ?>" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="username" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Username or Email
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                        class="form-input"
                        placeholder="admin"
                    >
                </div>

                <div>
                    <label for="password" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Master Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="form-input"
                        placeholder="••••••••••••"
                    >
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-brass w-full py-3.5">
                        Access Portal &rarr;
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-white/10 text-center text-[11px] text-stone-subtle">
                <p>Default credentials (fresh install):</p>
                <code class="text-brass bg-charcoal px-2 py-0.5 mt-1 inline-block">admin / Admin@Amak2026!</code>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="<?= asset_url('') ?>" class="text-xs text-stone-subtle hover:text-brass transition-colors">
                &larr; Return to Public Website
            </a>
        </div>

    </div>

</body>
</html>
