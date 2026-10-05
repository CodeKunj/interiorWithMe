<?php
/**
 * Amak Interior - Public Header Template
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

// Default metadata with dynamic database settings
$site_brand_name  = get_setting('site_name', APP_NAME);
$site_motto       = get_setting('site_tagline', APP_TAGLINE);
$site_favicon_url = get_setting('site_favicon');
$site_logo_url    = get_setting('site_logo');
$header_custom_code = get_setting('custom_head_code', get_setting('header_scripts'));
$ga_id            = get_setting('google_analytics_id');
$gtm_id           = get_setting('google_tag_manager_id');
$fb_pixel_id      = get_setting('facebook_pixel_id');
$gsc_verification = get_setting('google_search_console_verification');
$bing_verification = get_setting('bing_webmaster_verification');

$current_page     = $current_page ?? 'home';

// Resolve per-page SEO overrides
$page_override_title = get_setting('seo_' . $current_page . '_title');
$page_override_desc  = get_setting('seo_' . $current_page . '_desc');
$page_override_kw    = get_setting('seo_' . $current_page . '_keywords');

if (!empty($page_override_title)) {
    $page_title = $page_override_title;
} else {
    $page_title = $page_title ?? get_setting('meta_title', $site_brand_name . ' | ' . $site_motto);
}

if (!empty($page_override_desc)) {
    $page_description = $page_override_desc;
} else {
    $page_description = $page_description ?? get_setting('meta_description', 'Bespoke luxury interior architecture and experiential spatial design studio based in Manhattan, New York. Crafting timeless sanctuaries through vision and dimension.');
}

if (!empty($page_override_kw)) {
    $page_keywords = $page_override_kw;
} else {
    $page_keywords = $page_keywords ?? get_setting('meta_keywords', 'luxury interior design, 3D interior architecture, high-end residential design, Manhattan interior designer, custom furniture design, Amak Interior');
}

$og_image         = $og_image ?? image_url(get_setting('og_image', 'assets/img/og-image.jpg'));
$canonical_url    = $canonical_url ?? (rtrim(SITE_URL, '/') . '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Primary Tags -->
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($page_description) ?>">
    <meta name="keywords" content="<?= e($page_keywords) ?>">
    <meta name="author" content="<?= e($site_brand_name) ?>">
    <link rel="canonical" href="<?= e($canonical_url) ?>">

    <?php if (!empty($gsc_verification)): ?>
    <!-- Google Search Console Verification -->
    <meta name="google-site-verification" content="<?= e($gsc_verification) ?>">
    <?php endif; ?>

    <?php if (!empty($bing_verification)): ?>
    <!-- Bing Webmaster Verification -->
    <meta name="msvalidate.01" content="<?= e($bing_verification) ?>">
    <?php endif; ?>

    <?php if (!empty($site_favicon_url)): ?>
    <!-- Custom Favicon -->
    <link rel="icon" href="<?= image_url($site_favicon_url) ?>">
    <?php endif; ?>
    
    <!-- Open Graph / Facebook / LinkedIn -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonical_url) ?>">
    <meta property="og:title" content="<?= e($page_title) ?>">
    <meta property="og:description" content="<?= e($page_description) ?>">
    <meta property="og:image" content="<?= e($og_image) ?>">
    <meta property="og:site_name" content="<?= e($site_brand_name) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($page_title) ?>">
    <meta name="twitter:description" content="<?= e($page_description) ?>">
    <meta name="twitter:image" content="<?= e($og_image) ?>">

    <!-- Theme Color & Mobile Chrome -->
    <meta name="theme-color" content="#1C1C1C">
    <meta name="msapplication-navbutton-color" content="#1C1C1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <?php if (!empty($gtm_id)): ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?= e($gtm_id) ?>');</script>
    <!-- End Google Tag Manager -->
    <?php endif; ?>

    <?php if (!empty($ga_id)): ?>
    <!-- Google Analytics (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga_id) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= e($ga_id) ?>');
    </script>
    <?php endif; ?>

    <?php if (!empty($fb_pixel_id)): ?>
    <!-- Meta (Facebook) Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?= e($fb_pixel_id) ?>');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=<?= e($fb_pixel_id) ?>&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    <?php endif; ?>

    <?php if (!empty($header_custom_code)): ?>
    <!-- Custom Header Code Injection -->
    <?= $header_custom_code ?>
    <?php endif; ?>

    <!-- Google Fonts: Cormorant Garamond & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Inter:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN with Custom Studio Palette) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ivory: {
                            DEFAULT: '#F5F1EA',
                            light: '#FAF8F5',
                            dark: '#EDE8DF',
                        },
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
                            muted: 'rgba(176, 141, 87, 0.2)',
                        },
                        stone: {
                            subtle: '#8E8D8A',
                        }
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                    },
                    letterSpacing: {
                        widest: '0.25em',
                        super: '0.35em',
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">

    <!-- Dynamic Theme Accent Color -->
    <?= render_dynamic_accent_styles() ?>

    <!-- Three.js ES Module Shims / Import Map -->
    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.164.1/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.164.1/examples/jsm/"
        }
    }
    </script>

    <!-- Core Libraries (Lenis Smooth Scroll + GSAP) -->
    <script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
</head>
<body class="bg-charcoal text-ivory font-sans antialiased overflow-x-hidden selection:bg-brass selection:text-charcoal-dark">

    <?php if (!empty($gtm_id)): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= e($gtm_id) ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php endif; ?>

    <!-- Accessibility Skip Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-brass focus:text-charcoal focus:font-semibold focus:outline-none focus:ring-2 focus:ring-ivory">
        Skip to main content
    </a>

    <!-- Global Progress / Preloader Indicator -->
    <div id="site-loader" class="fixed inset-0 z-50 bg-charcoal-dark flex flex-col items-center justify-center transition-opacity duration-700 pointer-events-auto">
        <div class="text-center px-6">
            <span class="text-xs uppercase tracking-widest text-brass font-medium block mb-3">Amak Interior &bull; Spatial Studio</span>
            <h1 class="font-serif text-3xl md:text-4xl text-ivory tracking-wider font-light mb-6">WHERE VISION MEETS DIMENSION</h1>
            <div class="w-48 md:w-64 h-[2px] bg-charcoal-light mx-auto overflow-hidden relative">
                <div id="loader-progress-bar" class="h-full bg-brass w-0 transition-all duration-300 ease-out"></div>
            </div>
            <p id="loader-status-text" class="text-xs tracking-wider text-stone-subtle mt-3 uppercase font-mono">Initializing 3D Environment...</p>
        </div>
    </div>
    <script>
        // Automatic fail-safe dismiss after max 800ms under any network / adblocker / CDN latency
        (function() {
            function dismissLoader() {
                var l = document.getElementById('site-loader');
                if (l) {
                    l.style.opacity = '0';
                    l.style.transition = 'opacity 0.6s ease';
                    setTimeout(function() { l.style.display = 'none'; }, 600);
                }
            }
            if (document.readyState === 'complete') {
                setTimeout(dismissLoader, 400);
            } else {
                window.addEventListener('load', function() { setTimeout(dismissLoader, 400); });
                setTimeout(dismissLoader, 900);
            }
        })();
    </script>

    <!-- Header Navigation -->
    <header id="site-header" class="fixed top-0 left-0 w-full z-40 transition-all duration-500 py-6 px-6 md:px-12 backdrop-blur-md bg-charcoal/40 border-b border-white/5">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="<?= asset_url('') ?>" class="group flex flex-col items-start focus:outline-none" aria-label="<?= e($site_brand_name) ?> Home">
                <?php if (!empty($site_logo_url)): ?>
                    <img src="<?= image_url($site_logo_url) ?>" alt="<?= e($site_brand_name) ?>" class="max-h-10 w-auto object-contain">
                <?php else: ?>
                    <span class="font-serif text-2xl md:text-3xl tracking-widest text-ivory group-hover:text-brass transition-colors duration-300"><?= e($site_brand_name) ?></span>
                    <span class="text-[9px] tracking-super uppercase text-brass group-hover:text-ivory transition-colors duration-300 font-medium -mt-1"><?= e($site_motto) ?></span>
                <?php endif; ?>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden lg:flex items-center space-x-10" aria-label="Main Navigation">
                <a href="<?= asset_url('') ?>" class="nav-link text-xs uppercase tracking-widest transition-colors duration-300 <?= $current_page === 'home' ? 'text-brass font-medium' : 'text-ivory/80 hover:text-brass' ?>">Home</a>
                <a href="<?= asset_url('portfolio.php') ?>" class="nav-link text-xs uppercase tracking-widest transition-colors duration-300 <?= $current_page === 'portfolio' ? 'text-brass font-medium' : 'text-ivory/80 hover:text-brass' ?>">Portfolio</a>
                <a href="<?= asset_url('#about') ?>" class="nav-link text-xs uppercase tracking-widest text-ivory/80 hover:text-brass transition-colors duration-300">About</a>
                <a href="<?= asset_url('#services') ?>" class="nav-link text-xs uppercase tracking-widest text-ivory/80 hover:text-brass transition-colors duration-300">Services</a>
                <a href="<?= asset_url('#process') ?>" class="nav-link text-xs uppercase tracking-widest text-ivory/80 hover:text-brass transition-colors duration-300">Process</a>
                <a href="<?= asset_url('contact.php') ?>" class="nav-link text-xs uppercase tracking-widest transition-colors duration-300 <?= $current_page === 'contact' ? 'text-brass font-medium' : 'text-ivory/80 hover:text-brass' ?>">Contact</a>
            </nav>

            <!-- Action Button & Mobile Trigger -->
            <div class="flex items-center space-x-4">
                <a href="<?= asset_url('contact.php') ?>" class="hidden sm:inline-flex items-center justify-center px-5 py-2.5 text-xs uppercase tracking-widest text-brass border border-brass/60 hover:bg-brass hover:text-charcoal-dark transition-all duration-300 font-medium">
                    Commission Studio
                </a>

                <button id="mobile-menu-btn" class="lg:hidden p-2 text-ivory hover:text-brass focus:outline-none" aria-label="Toggle Navigation Menu" aria-expanded="false">
                    <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Fullscreen Mobile Navigation Menu -->
    <div id="mobile-menu" class="fixed inset-0 z-45 bg-charcoal-dark/98 backdrop-blur-xl flex flex-col justify-between p-8 md:p-16 opacity-0 pointer-events-none transition-all duration-500">
        <div class="flex justify-between items-center border-b border-white/10 pb-6">
            <span class="font-serif text-2xl text-ivory">AMAK <span class="text-brass text-sm block font-sans tracking-widest">INTERIOR</span></span>
            <button id="mobile-menu-close" class="p-2 text-ivory hover:text-brass focus:outline-none" aria-label="Close menu">
                <svg class="w-8 h-8 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex flex-col space-y-6 my-auto text-center" aria-label="Mobile Navigation">
            <a href="<?= asset_url('') ?>" class="mobile-nav-link font-serif text-3xl md:text-4xl text-ivory hover:text-brass transition-colors duration-300">Home</a>
            <a href="<?= asset_url('portfolio.php') ?>" class="mobile-nav-link font-serif text-3xl md:text-4xl text-ivory hover:text-brass transition-colors duration-300">Portfolio & Gallery</a>
            <a href="<?= asset_url('#about') ?>" class="mobile-nav-link font-serif text-3xl md:text-4xl text-ivory hover:text-brass transition-colors duration-300">Studio Ethos</a>
            <a href="<?= asset_url('#services') ?>" class="mobile-nav-link font-serif text-3xl md:text-4xl text-ivory hover:text-brass transition-colors duration-300">Design Services</a>
            <a href="<?= asset_url('#process') ?>" class="mobile-nav-link font-serif text-3xl md:text-4xl text-ivory hover:text-brass transition-colors duration-300">Our Process</a>
            <a href="<?= asset_url('contact.php') ?>" class="mobile-nav-link font-serif text-3xl md:text-4xl text-brass hover:text-ivory transition-colors duration-300">Inquire / Contact</a>
        </nav>

        <div class="text-center border-t border-white/10 pt-6 text-xs text-stone-subtle tracking-widest uppercase">
            <p>482 Broome St, SoHo, NY &bull; <?= APP_PHONE ?></p>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main id="main-content" class="min-h-screen">
