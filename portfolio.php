<?php
/**
 * Amak Interior - Portfolio / Monograph Gallery
 * Filterable luxury project grid with lazy loading & category switching
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);
$current_page = 'portfolio';
$page_title = 'Portfolio & Works | Amak Interior';
$page_description = 'Explore the monograph of bespoke private residences, penthouses, and executive architectural projects by Amak Interior across Manhattan and worldwide.';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

// Fetch all categories present in database
try {
    $catStmt = $pdo->query("SELECT DISTINCT category FROM projects ORDER BY category ASC");
    $db_categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    $db_categories = ['Living Room', 'Bedroom', 'Kitchen', 'Bathroom', 'Office'];
}

// Fetch all active projects
try {
    $stmt = $pdo->query("SELECT id, title, slug, category, location, year, area, short_description, thumbnail, glb_model, panorama_image FROM projects ORDER BY display_order ASC, created_at DESC");
    $projects = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Portfolio fetch error: " . $e->getMessage());
    $projects = [];
}

$active_cat = $_GET['cat'] ?? 'all';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ==============================================================================
     PORTFOLIO HERO HEADER
     ============================================================================== -->
<section class="pt-36 pb-16 px-6 md:px-12 bg-charcoal-dark border-b border-white/5 relative">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex items-center space-x-3">
            <span class="w-8 h-[1px] bg-brass"></span>
            <span class="editorial-tag">Curated Monograph</span>
        </div>
        <h1 class="font-serif text-4xl sm:text-5xl md:text-6xl text-ivory font-light leading-tight">
            Works &amp; Spatial Compositions
        </h1>
        <p class="text-sm md:text-base text-stone-subtle max-w-2xl leading-relaxed">
            A curated record of private residential transformations, penthouses, and sanctuaries characterized by rare tactile materiality and architectural calm.
        </p>
    </div>
</section>

<!-- ==============================================================================
     CATEGORY FILTER BAR & GRID
     ============================================================================== -->
<section class="py-16 px-6 md:px-12 bg-charcoal min-h-[70vh]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Filter Controls -->
        <div class="flex flex-wrap items-center gap-3 pb-12 border-b border-white/10" aria-label="Portfolio Categories">
            <button 
                type="button" 
                class="portfolio-filter-btn px-5 py-2 text-xs uppercase tracking-widest transition-all duration-300 border <?= $active_cat === 'all' ? 'bg-brass text-charcoal-dark border-brass font-medium' : 'text-ivory/70 border-white/10 hover:border-brass hover:text-brass' ?>" 
                data-category="all"
            >
                All Works (<?= count($projects) ?>)
            </button>

            <?php foreach ($db_categories as $cat): ?>
                <button 
                    type="button" 
                    class="portfolio-filter-btn px-5 py-2 text-xs uppercase tracking-widest transition-all duration-300 border <?= $active_cat === $cat ? 'bg-brass text-charcoal-dark border-brass font-medium' : 'text-ivory/70 border-white/10 hover:border-brass hover:text-brass' ?>" 
                    data-category="<?= e($cat) ?>"
                >
                    <?= e($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Project Grid -->
        <div id="portfolio-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mt-12">
            <?php foreach ($projects as $p): ?>
                <article 
                    class="portfolio-item project-card luxury-card group overflow-hidden flex flex-col transition-all duration-500" 
                    data-category="<?= e($p['category']) ?>"
                >
                    <a href="<?= asset_url('project.php?slug=' . urlencode($p['slug'])) ?>" class="block relative aspect-[4/3] overflow-hidden bg-charcoal-dark">
                        <!-- Shimmer placeholder -->
                        <div class="image-skeleton absolute inset-0"></div>
                        
                        <img 
                            src="<?= image_url($p['thumbnail']) ?>" 
                            alt="<?= e($p['title']) ?>" 
                            loading="lazy" 
                            class="project-card-image w-full h-full object-cover relative z-10 opacity-0 transition-opacity duration-700"
                            onerror="this.src='https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1200&q=80'"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-charcoal-dark/95 via-transparent to-transparent z-20"></div>
                        
                        <!-- Badges: Category & 3D/360 indicator -->
                        <div class="absolute top-4 left-4 z-30 flex items-center space-x-2">
                            <span class="px-3 py-1 bg-charcoal/90 backdrop-blur-md text-[10px] uppercase tracking-widest text-brass font-medium border border-brass/20">
                                <?= e($p['category']) ?>
                            </span>
                            <?php if (!empty($p['glb_model'])): ?>
                                <span class="px-2 py-1 bg-charcoal/90 backdrop-blur-md text-[9px] uppercase tracking-widest text-ivory font-mono border border-white/10" title="Interactive 3D Available">
                                    3D Model
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($p['panorama_image'])): ?>
                                <span class="px-2 py-1 bg-charcoal/90 backdrop-blur-md text-[9px] uppercase tracking-widest text-brass-light font-mono border border-brass/20" title="360° Panorama View Available">
                                    360&deg; Tour
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-xs text-stone-subtle mb-2">
                                <span><?= e($p['location']) ?></span>
                                <span><?= e($p['year']) ?> &bull; <?= e($p['area']) ?></span>
                            </div>
                            <h2 class="font-serif text-2xl text-ivory group-hover:text-brass transition-colors font-light">
                                <a href="<?= asset_url('project.php?slug=' . urlencode($p['slug'])) ?>">
                                    <?= e($p['title']) ?>
                                </a>
                            </h2>
                            <p class="text-xs text-stone-subtle leading-relaxed mt-2 line-clamp-2">
                                <?= e($p['short_description']) ?>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-brass">
                            <span class="tracking-widest uppercase text-[10px]">Explore Case Study</span>
                            <span class="group-hover:translate-x-1.5 transition-transform">&rarr;</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- No Projects Found Fallback -->
        <div id="no-projects-message" class="hidden text-center py-24 space-y-4">
            <p class="font-serif text-2xl text-ivory/70">No projects found in this typology category.</p>
            <p class="text-xs text-stone-subtle">Please select another category or check back for new monograph additions.</p>
        </div>

    </div>
</section>

<?php
$extra_scripts = ['assets/js/portfolio-filter.js'];
require_once __DIR__ . '/includes/footer.php';
?>
