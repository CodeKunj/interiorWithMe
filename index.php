<?php
/**
 * Amak Interior - Home Page
 * Full-screen 3D Hero, ScrollTrigger Spatial Navigation, Editorial Sections
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);
$current_page = 'home';
$page_title = 'Amak Interior | Bespoke 3D Spatial Architecture & Luxury Living';
$page_description = 'Amak Interior is a premier spatial design atelier based in Manhattan. We curate ultra-luxury residential spaces through immersive 3D pre-visualization, rare materials, and architectural precision.';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

// Fetch Featured Projects from Database
$pdo = get_db_connection();
try {
    $stmt = $pdo->query("SELECT id, title, slug, category, location, year, short_description, thumbnail FROM projects WHERE is_featured = 1 ORDER BY display_order ASC LIMIT 3");
    $featured_projects = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Database query error: " . $e->getMessage());
    $featured_projects = [];
}

// Fetch Active Testimonials
try {
    $stmt = $pdo->query("SELECT client_name, client_title, location, quote, rating FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 3");
    $testimonials = $stmt->fetchAll();
} catch (\PDOException $e) {
    $testimonials = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<?php
$h_badge     = get_setting('hero_badge', 'HAUTE COUTURE ARCHITECTURE');
$h_heading   = get_setting('hero_heading', 'Where Vision Meets Dimension');
$h_subhead   = get_setting('hero_subheading', 'We orchestrate private residences of monolithic stone, natural timber, and tailored illumination—seamlessly unified through cinematic film and bespoke spatial craftsmanship.');
$h_cta_text  = get_setting('hero_cta_text', 'Explore Commissions');
$h_cta_link  = get_setting('hero_cta_link', 'portfolio.php');
$h_video_url = image_url(get_setting('hero_video_url', 'https://storage.googleapis.com/webild/default/templates/marbella/hero/hero.mp4'));
?>

<!-- ==============================================================================
     HERO SECTION: Full-Bleed Cinematic Film
     ============================================================================== -->
<section id="hero-canvas-container" class="hero-canvas-container relative w-full h-screen overflow-hidden bg-charcoal">

    <!-- 1. Full-bleed Cinematic Interior Video (Customizable from Admin Settings) -->
    <div id="hero-video-wrap" class="absolute inset-0 z-0 overflow-hidden">
        <video 
            id="hero-bg-video" 
            muted 
            playsinline 
            preload="auto"
            class="w-full h-full object-cover scale-105"
        >
            <source src="<?= e($h_video_url) ?>" type="video/mp4">
        </video>
        <!-- Luxury Vignette & Dark Gradient Mask for Typography Legibility -->
        <div class="absolute inset-0 bg-gradient-to-t from-charcoal via-charcoal/40 to-charcoal/50 z-1"></div>
        <div class="absolute inset-0 bg-radial-vignette opacity-70 z-1 pointer-events-none"></div>
    </div>

    <!-- 2. Hero Content Overlay (Editorial Typography) -->
    <div class="hero-overlay-content px-6 md:px-16 max-w-7xl mx-auto w-full z-10">
        <div id="hero-headline-wrap" class="max-w-3xl space-y-6">
            <div class="animate-hero inline-flex items-center space-x-3 bg-charcoal-dark/75 backdrop-blur-md px-4 py-1.5 border border-brass/40">
                <span class="w-2 h-2 rounded-full bg-brass animate-pulse"></span>
                <span class="text-[11px] uppercase tracking-widest text-brass font-medium"><?= e($h_badge) ?></span>
            </div>
            
            <h1 class="animate-hero font-serif text-4xl sm:text-5xl md:text-7xl lg:text-8xl text-ivory font-light leading-[1.06] tracking-tight drop-shadow-2xl">
                <?= nl2br(e($h_heading)) ?>
            </h1>

            <p class="animate-hero text-sm md:text-base text-ivory/90 max-w-xl leading-relaxed font-light drop-shadow-md">
                <?= e($h_subhead) ?>
            </p>

            <div class="animate-hero pt-4 flex flex-wrap items-center gap-4 interactive">
                <a href="<?= asset_url($h_cta_link) ?>" class="btn-brass">
                    <?= e($h_cta_text) ?>
                </a>
                <a href="<?= asset_url('portfolio.php') ?>" class="btn-outline-brass">
                    View Portfolio
                </a>
            </div>
        </div>
    </div>

    <!-- 6. Video Controls Pill (Scroll Scrub & Sound) -->
    <div id="video-controls-pill" class="absolute bottom-8 left-6 md:left-12 z-20 flex items-center space-x-3 bg-charcoal-dark/80 backdrop-blur-md px-3.5 py-2 border border-white/10 text-xs text-ivory/80">
        <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-brass animate-pulse"></span>
            <span class="text-[10px] uppercase tracking-widest font-mono text-brass">Scroll to Scrub Film</span>
        </div>
        <span class="text-white/20">|</span>
        <button type="button" id="btn-toggle-video" class="hover:text-brass transition-colors focus:outline-none flex items-center space-x-1.5" title="Switch between Scroll Scrub and Auto-Play">
            <span id="video-play-icon">▶</span>
            <span id="video-play-text" class="text-[10px] uppercase tracking-widest font-mono">Auto Play</span>
        </button>
        <span class="text-white/20">|</span>
        <button type="button" id="btn-toggle-sound" class="hover:text-brass transition-colors focus:outline-none flex items-center space-x-1.5">
            <span id="video-sound-icon">🔇</span>
            <span id="video-sound-text" class="text-[10px] uppercase tracking-widest font-mono">Sound</span>
        </button>
    </div>

    <!-- 7. Floating 3D Material Configurator Dock -->
    <div id="configurator-dock" class="absolute bottom-8 right-6 md:right-12 z-20 max-w-xs sm:max-w-sm w-full opacity-0 pointer-events-none transition-all duration-700">
        <div class="configurator-panel p-5 text-ivory">
            <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-4">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-brass"></span>
                    <span class="text-xs uppercase tracking-widest font-semibold text-brass">Material Palette</span>
                </div>
                <span id="config-status-pill" class="text-[10px] uppercase font-mono text-stone-subtle opacity-0 transition-opacity duration-300">Updated</span>
            </div>

            <!-- Tabs -->
            <div class="flex space-x-4 border-b border-white/5 pb-2 mb-4 text-xs tracking-wider uppercase">
                <button type="button" class="config-tab-btn border-b-2 border-brass text-brass pb-1 font-medium focus:outline-none" data-target="walls">Walls</button>
                <button type="button" class="config-tab-btn border-b-2 border-transparent text-stone-subtle pb-1 font-medium hover:text-ivory focus:outline-none" data-target="floors">Floor</button>
                <button type="button" class="config-tab-btn border-b-2 border-transparent text-stone-subtle pb-1 font-medium hover:text-ivory focus:outline-none" data-target="sofa">Sofa</button>
            </div>

            <!-- Tab 1: Walls -->
            <div id="config-tab-walls" class="config-tab-content">
                <p class="text-[11px] text-stone-subtle mb-3">Select lime-wash plaster tone:</p>
                <div class="flex items-center space-x-3">
                    <button type="button" class="swatch-btn wall-swatch active bg-[#F5F1EA]" data-color="ivory" title="Warm Ivory"></button>
                    <button type="button" class="swatch-btn wall-swatch bg-[#8B9B88]" data-color="sage" title="Minimalist Sage"></button>
                    <button type="button" class="swatch-btn wall-swatch bg-[#C49A82]" data-color="clay" title="Tuscan Terracotta"></button>
                    <button type="button" class="swatch-btn wall-swatch bg-[#3B444B]" data-color="slate" title="Nordic Slate"></button>
                    <button type="button" class="swatch-btn wall-swatch bg-[#1C1C1C]" data-color="charcoal" title="Deep Charcoal"></button>
                </div>
            </div>

            <!-- Tab 2: Floors -->
            <div id="config-tab-floors" class="config-tab-content hidden">
                <p class="text-[11px] text-stone-subtle mb-3">Select architectural surface:</p>
                <div class="flex items-center space-x-3">
                    <button type="button" class="swatch-btn floor-swatch active bg-[#6E5136]" data-floor="oak" title="Smoked French Oak"></button>
                    <button type="button" class="swatch-btn floor-swatch bg-[#E5E0D8]" data-floor="marble" title="Calacatta Travertine"></button>
                    <button type="button" class="swatch-btn floor-swatch bg-[#8C8882]" data-floor="concrete" title="Micro-Cement Screed"></button>
                </div>
            </div>

            <!-- Tab 3: Sofa -->
            <div id="config-tab-sofa" class="config-tab-content hidden">
                <p class="text-[11px] text-stone-subtle mb-3">Select bespoke upholstery:</p>
                <div class="flex items-center space-x-3">
                    <button type="button" class="swatch-btn sofa-swatch active bg-[#222C3A]" data-sofa="midnight" title="Midnight Velvet"></button>
                    <button type="button" class="swatch-btn sofa-swatch bg-[#EDE8DF]" data-sofa="boucle" title="Italian Bouclé"></button>
                    <button type="button" class="swatch-btn sofa-swatch bg-[#6E472A]" data-sofa="leather" title="Cognac Leather"></button>
                    <button type="button" class="swatch-btn sofa-swatch bg-[#1C3B2B]" data-sofa="emerald" title="Forest Mohair"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. Scroll Indicator -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center pointer-events-none opacity-60">
        <span class="text-[9px] uppercase tracking-widest text-brass mb-2 font-mono">Scroll to Explore</span>
        <div class="w-4 h-8 border border-brass/50 rounded-full flex justify-center p-1">
            <div class="w-1 h-2 bg-brass rounded-full animate-bounce"></div>
        </div>
    </div>
</section>

<!-- ==============================================================================
     SECTION 1: ABOUT / STUDIO ETHOS
     ============================================================================== -->
<section id="about" class="py-28 px-6 md:px-12 bg-charcoal relative">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
            
            <!-- Left Narrative (7 cols) -->
            <div class="lg:col-span-7 space-y-8 reveal-header">
                <div class="flex items-center space-x-3">
                    <span class="w-8 h-[1px] bg-brass"></span>
                    <span class="editorial-tag">Studio Philosophy</span>
                </div>

                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-ivory font-light leading-tight">
                    Architecture is not merely inhabited; it is <span class="italic font-normal text-brass">experienced through light, rhythm, and silence.</span>
                </h2>

                <p class="text-sm md:text-base text-stone-subtle leading-relaxed font-light">
                    Founded in SoHo, New York, Amak Interior bridges classical architectural proportion with pioneering real-time 3D spatial simulation. Every commission is treated as a bespoke monograph—orchestrating hand-quarried travertine, patinated bronzes, and acoustic dampening natural textiles to create sanctuaries of sublime calm.
                </p>

                <p class="text-sm md:text-base text-stone-subtle leading-relaxed font-light">
                    We eliminate the uncertainty of physical construction through precise 3D spatial modeling, allowing our patrons to walk through lighting simulations, inspect joinery details, and curate rare finishes before ground is broken.
                </p>

                <!-- Editorial Stats Grid -->
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-white/10">
                    <div>
                        <span class="font-serif text-3xl md:text-4xl text-brass block font-light"><span class="stat-counter" data-target="18">0</span>+</span>
                        <span class="text-[11px] uppercase tracking-wider text-stone-subtle mt-1 block">Years of Mastery</span>
                    </div>
                    <div>
                        <span class="font-serif text-3xl md:text-4xl text-brass block font-light">$<span class="stat-counter" data-target="240">0</span>M+</span>
                        <span class="text-[11px] uppercase tracking-wider text-stone-subtle mt-1 block">Curated Real Estate</span>
                    </div>
                    <div>
                        <span class="font-serif text-3xl md:text-4xl text-brass block font-light"><span class="stat-counter" data-target="85">0</span>+</span>
                        <span class="text-[11px] uppercase tracking-wider text-stone-subtle mt-1 block">Global Accolades</span>
                    </div>
                </div>
            </div>

            <!-- Right Visual Composition (5 cols) -->
            <div class="lg:col-span-5 relative">
                <div class="luxury-card p-4 relative z-10 overflow-hidden">
                    <div class="aspect-[4/5] bg-charcoal-dark relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-t from-charcoal-dark via-transparent to-transparent z-10"></div>
                        <!-- Stylized architectural vignette -->
                        <div class="w-full h-full bg-cover bg-center transition-transform duration-700 hover:scale-105" style="background-image: radial-gradient(circle at 50% 40%, #35302A 0%, #1A1A1A 85%);">
                            <div class="h-full flex flex-col justify-end p-8 relative z-20">
                                <span class="text-xs uppercase tracking-widest text-brass font-mono mb-1">Principle Atelier</span>
                                <h3 class="font-serif text-2xl text-ivory">482 Broome St, SoHo</h3>
                                <p class="text-xs text-stone-subtle mt-2">Private materials library featuring 600+ stone slabs, timber veneers &amp; bronze alloys.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Decorative Brass Outline Accent -->
                <div class="absolute -bottom-6 -right-6 w-full h-full border border-brass/30 z-0 pointer-events-none hidden md:block"></div>
            </div>

        </div>
    </div>
</section>

<!-- ==============================================================================
     SECTION 2: SERVICES / CAPABILITIES
     ============================================================================== -->
<section id="services" class="py-28 px-6 md:px-12 bg-charcoal-dark relative border-t border-white/5">
    <div class="max-w-7xl mx-auto">
        
        <div class="reveal-header max-w-2xl mb-20 space-y-4">
            <div class="flex items-center space-x-3">
                <span class="w-8 h-[1px] bg-brass"></span>
                <span class="editorial-tag">Atelier Capabilities</span>
            </div>
            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-ivory font-light">
                Tailored Disciplines for Exceptional Residences.
            </h2>
            <p class="text-sm text-stone-subtle leading-relaxed">
                From historic brownstone gut-renovations to penthouse architectural planning, we oversee every square millimeter with uncompromising craftsmanship.
            </p>
        </div>

        <div class="services-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            
            <!-- Service 1 -->
            <div class="luxury-card p-8 flex flex-col justify-between h-full group">
                <div>
                    <span class="font-serif text-2xl text-brass/60 group-hover:text-brass transition-colors">01</span>
                    <h3 class="font-serif text-xl text-ivory mt-4 mb-3 group-hover:text-brass-light transition-colors">Residential Spatial Architecture</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        Comprehensive spatial reconfiguration, structural envelope planning, partition acoustics, and holistic interior architectural drawings for luxury residences.
                    </p>
                </div>
                <div class="pt-8 border-t border-white/5 flex items-center justify-between text-xs text-brass">
                    <span class="tracking-widest uppercase text-[10px]">Full Scope</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </div>

            <!-- Service 2 -->
            <div class="luxury-card p-8 flex flex-col justify-between h-full group">
                <div>
                    <span class="font-serif text-2xl text-brass/60 group-hover:text-brass transition-colors">02</span>
                    <h3 class="font-serif text-xl text-ivory mt-4 mb-3 group-hover:text-brass-light transition-colors">Penthouse &amp; Estate Renovation</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        End-to-end turnkey transformation of high-profile properties, coordinating historic landmark approvals, custom mechanicals, and structural glazing.
                    </p>
                </div>
                <div class="pt-8 border-t border-white/5 flex items-center justify-between text-xs text-brass">
                    <span class="tracking-widest uppercase text-[10px]">Turnkey</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </div>

            <!-- Service 3 -->
            <div class="luxury-card p-8 flex flex-col justify-between h-full group">
                <div>
                    <span class="font-serif text-2xl text-brass/60 group-hover:text-brass transition-colors">03</span>
                    <h3 class="font-serif text-xl text-ivory mt-4 mb-3 group-hover:text-brass-light transition-colors">Bespoke Millwork &amp; Furniture</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        Commissioned one-off timber joinery, bookmatched marble fireplace hearths, cast-bronze lighting, and custom hand-loomed Italian textiles.
                    </p>
                </div>
                <div class="pt-8 border-t border-white/5 flex items-center justify-between text-xs text-brass">
                    <span class="tracking-widest uppercase text-[10px]">Artisanal</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </div>

            <!-- Service 4 -->
            <div class="luxury-card p-8 flex flex-col justify-between h-full group">
                <div>
                    <span class="font-serif text-2xl text-brass/60 group-hover:text-brass transition-colors">04</span>
                    <h3 class="font-serif text-xl text-ivory mt-4 mb-3 group-hover:text-brass-light transition-colors">Real-Time 3D Spatial Pre-Viz</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        Interactive WebGL digital twins, accurate solar shadow tracking, VR walk-throughs, and instant material palette curation before construction.
                    </p>
                </div>
                <div class="pt-8 border-t border-white/5 flex items-center justify-between text-xs text-brass">
                    <span class="tracking-widest uppercase text-[10px]">Spatial Tech</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ==============================================================================
     SECTION 3: FEATURED PROJECTS (PULLED FROM DATABASE)
     ============================================================================== -->
<section id="projects" class="py-28 px-6 md:px-12 bg-charcoal relative">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6 reveal-header">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <span class="w-8 h-[1px] bg-brass"></span>
                    <span class="editorial-tag">Selected Monograph</span>
                </div>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-ivory font-light">
                    Featured Works &amp; Sanctuaries
                </h2>
            </div>
            <a href="<?= asset_url('portfolio.php') ?>" class="btn-outline-brass self-start md:self-auto">
                View All Works (<?= count($featured_projects) ?>+) &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($featured_projects as $p): ?>
                <article class="project-card luxury-card group overflow-hidden flex flex-col">
                    <a href="<?= asset_url('project.php?slug=' . urlencode($p['slug'])) ?>" class="block relative aspect-[4/3] overflow-hidden bg-charcoal-dark">
                        <!-- Shimmer Skeleton Placeholder -->
                        <div class="image-skeleton absolute inset-0"></div>
                        
                        <img 
                            src="<?= image_url($p['thumbnail']) ?>" 
                            alt="<?= e($p['title']) ?>" 
                            loading="lazy" 
                            class="project-card-image w-full h-full object-cover relative z-10 opacity-0 transition-opacity duration-700"
                            onerror="this.src='https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1200&q=80'"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-charcoal-dark/90 via-transparent to-transparent z-20"></div>
                        
                        <div class="absolute top-4 left-4 z-30">
                            <span class="px-3 py-1 bg-charcoal/80 backdrop-blur-md text-[10px] uppercase tracking-widest text-brass font-medium border border-brass/20">
                                <?= e($p['category']) ?>
                            </span>
                        </div>
                    </a>

                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-xs text-stone-subtle mb-2">
                                <span><?= e($p['location']) ?></span>
                                <span><?= e($p['year']) ?></span>
                            </div>
                            <h3 class="font-serif text-2xl text-ivory group-hover:text-brass transition-colors">
                                <a href="<?= asset_url('project.php?slug=' . urlencode($p['slug'])) ?>">
                                    <?= e($p['title']) ?>
                                </a>
                            </h3>
                            <p class="text-xs text-stone-subtle leading-relaxed mt-2 line-clamp-2">
                                <?= e($p['short_description']) ?>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-brass">
                            <span class="tracking-widest uppercase text-[10px]">Inspect Details</span>
                            <span class="group-hover:translate-x-1.5 transition-transform">&rarr;</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- ==============================================================================
     SECTION 4: PROCESS / METHODOLOGY TIMELINE
     ============================================================================== -->
<section id="process" class="py-28 px-6 md:px-12 bg-charcoal-dark relative border-t border-white/5 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        
        <div class="reveal-header text-center max-w-2xl mx-auto mb-20 space-y-4">
            <div class="inline-flex items-center space-x-3">
                <span class="w-8 h-[1px] bg-brass"></span>
                <span class="editorial-tag">The Monograph Method</span>
                <span class="w-8 h-[1px] bg-brass"></span>
            </div>
            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-ivory font-light">
                From Pure Concept to Inhabited Reality
            </h2>
            <p class="text-sm text-stone-subtle">
                A rigorous four-phase protocol designed to preserve design integrity and ensure flawless budget execution.
            </p>
        </div>

        <div class="process-timeline relative max-w-4xl mx-auto">
            <!-- Center Vertical Connector Line -->
            <div id="process-progress-line" class="hidden md:block absolute left-1/2 top-0 bottom-0 w-[1px] bg-brass/40 -translate-x-1/2"></div>

            <!-- Step 1 -->
            <div class="process-step relative flex flex-col md:flex-row items-center mb-16">
                <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                    <span class="text-xs font-mono uppercase text-brass tracking-widest block mb-1">Phase 01</span>
                    <h3 class="font-serif text-2xl text-ivory mb-2">Spatial Cartography &amp; Discovery</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        In-depth lifestyle audit, site laser scanning, solar trajectory mapping, and acoustic analysis to define the spatial program.
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-charcoal border border-brass flex items-center justify-center text-xs font-serif text-brass z-10 shadow-xl">
                    I
                </div>
                <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
            </div>

            <!-- Step 2 -->
            <div class="process-step relative flex flex-col md:flex-row items-center mb-16">
                <div class="md:w-1/2 md:pr-12 hidden md:block"></div>
                <div class="w-10 h-10 rounded-full bg-charcoal border border-brass flex items-center justify-center text-xs font-serif text-brass z-10 shadow-xl">
                    II
                </div>
                <div class="md:w-1/2 md:pl-12 md:text-left mb-6 md:mb-0">
                    <span class="text-xs font-mono uppercase text-brass tracking-widest block mb-1">Phase 02</span>
                    <h3 class="font-serif text-2xl text-ivory mb-2">3D Material Synthesis &amp; Lighting</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        Building the full real-time 3D twin, testing rare marble slabs, calibrating circadian downlight temperatures, and finalizing tactile palettes.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="process-step relative flex flex-col md:flex-row items-center mb-16">
                <div class="md:w-1/2 md:pr-12 md:text-right mb-6 md:mb-0">
                    <span class="text-xs font-mono uppercase text-brass tracking-widest block mb-1">Phase 03</span>
                    <h3 class="font-serif text-2xl text-ivory mb-2">Artisanal Millwork &amp; Construction</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        Execution by master stone masons, Italian joinery artisans, and specialist MEP engineers under daily studio supervision.
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-charcoal border border-brass flex items-center justify-center text-xs font-serif text-brass z-10 shadow-xl">
                    III
                </div>
                <div class="md:w-1/2 md:pl-12 hidden md:block"></div>
            </div>

            <!-- Step 4 -->
            <div class="process-step relative flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 md:pr-12 hidden md:block"></div>
                <div class="w-10 h-10 rounded-full bg-charcoal border border-brass flex items-center justify-center text-xs font-serif text-brass z-10 shadow-xl">
                    IV
                </div>
                <div class="md:w-1/2 md:pl-12 md:text-left">
                    <span class="text-xs font-mono uppercase text-brass tracking-widest block mb-1">Phase 04</span>
                    <h3 class="font-serif text-2xl text-ivory mb-2">Curation, Styling &amp; Handover</h3>
                    <p class="text-xs text-stone-subtle leading-relaxed">
                        White-glove placement of curated art, bespoke scent diffusion, custom linen provisioning, and comprehensive digital maintenance archives.
                    </p>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ==============================================================================
     SECTION 5: TESTIMONIALS
     ============================================================================== -->
<section id="testimonials" class="py-28 px-6 md:px-12 bg-charcoal relative">
    <div class="max-w-7xl mx-auto">
        
        <div class="reveal-header max-w-xl mb-16 space-y-4">
            <div class="flex items-center space-x-3">
                <span class="w-8 h-[1px] bg-brass"></span>
                <span class="editorial-tag">Client Monologues</span>
            </div>
            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-ivory font-light">
                Voices of Our Patrons
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card luxury-card p-8 flex flex-col justify-between space-y-6">
                    <div>
                        <!-- 5 Star Rating -->
                        <div class="flex items-center space-x-1 text-brass mb-6">
                            <?php for ($i = 0; $i < (int)$t['rating']; $i++): ?>
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <?php endfor; ?>
                        </div>
                        <blockquote class="font-serif text-lg text-ivory/90 italic leading-relaxed">
                            &ldquo;<?= e($t['quote']) ?>&rdquo;
                        </blockquote>
                    </div>

                    <div class="pt-6 border-t border-white/5">
                        <div class="font-serif text-base text-ivory font-medium"><?= e($t['client_name']) ?></div>
                        <div class="text-[11px] text-brass uppercase tracking-wider mt-0.5"><?= e($t['client_title']) ?></div>
                        <div class="text-[11px] text-stone-subtle mt-0.5"><?= e($t['location']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- ==============================================================================
     SECTION 6: CTA BANNER
     ============================================================================== -->
<section class="py-24 px-6 md:px-12 bg-gradient-to-r from-[#1C1C1C] via-[#2A241B] to-[#1C1C1C] border-y border-brass/30 relative overflow-hidden">
    <div class="max-w-5xl mx-auto text-center relative z-10 space-y-6">
        <span class="editorial-tag">Private Commissions</span>
        <h2 class="font-serif text-3xl sm:text-5xl md:text-6xl text-ivory font-light leading-tight">
            Ready to Translate Your Vision into <br><span class="italic font-normal text-brass-light">Living Architectural Reality?</span>
        </h2>
        <p class="text-sm md:text-base text-stone-subtle max-w-xl mx-auto leading-relaxed">
            Our atelier accepts a strictly limited number of private residential and executive commissions annually to preserve exacting attention to craft.
        </p>
        <div class="pt-4 flex flex-wrap justify-center gap-4">
            <a href="<?= asset_url('contact.php') ?>" class="btn-brass">
                Inquire for Private Consultation
            </a>
            <a href="tel:<?= preg_replace('/[^\d+]/', '', APP_PHONE) ?>" class="btn-outline-brass">
                Call Direct: <?= APP_PHONE ?>
            </a>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
