<?php
/**
 * Amak Interior - Project Detail View
 * Complete case study with gallery, specs, optional 360° Panorama and 3D Model viewer
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);
$current_page = 'portfolio';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . asset_url('portfolio.php'));
    exit;
}

$pdo = get_db_connection();

// Fetch Project by Slug
$stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $page_title = 'Project Not Found | Amak Interior';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-6">
        <span class="editorial-tag mb-3">404 Exception</span>
        <h1 class="font-serif text-4xl text-ivory mb-4">Project Monograph Not Found</h1>
        <p class="text-stone-subtle text-sm max-w-md mb-8">The requested interior project may have been moved or archived.</p>
        <a href="' . asset_url('portfolio.php') . '" class="btn-brass">Return to Portfolio</a>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch Gallery Images for this project
$imgStmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
$imgStmt->execute([$project['id']]);
$gallery_images = $imgStmt->fetchAll();

// Fetch Next and Previous Project Slugs for smooth navigation
$nextStmt = $pdo->prepare("SELECT slug, title FROM projects WHERE display_order > ? ORDER BY display_order ASC LIMIT 1");
$nextStmt->execute([$project['display_order']]);
$next_project = $nextStmt->fetch();

$prevStmt = $pdo->prepare("SELECT slug, title FROM projects WHERE display_order < ? ORDER BY display_order DESC LIMIT 1");
$prevStmt->execute([$project['display_order']]);
$prev_project = $prevStmt->fetch();

// Dynamic SEO tags
$page_title = $project['title'] . ' | Amak Interior Monograph';
$page_description = truncate_text($project['short_description'], 160);
$og_image = image_url($project['thumbnail']);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ==============================================================================
     PROJECT HERO BANNER
     ============================================================================== -->
<section class="relative pt-36 pb-20 px-6 md:px-12 bg-charcoal-dark border-b border-white/5">
    <div class="max-w-7xl mx-auto">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-xs uppercase tracking-widest text-stone-subtle mb-6" aria-label="Breadcrumb">
            <a href="<?= asset_url('') ?>" class="hover:text-brass transition-colors">Home</a>
            <span>/</span>
            <a href="<?= asset_url('portfolio.php') ?>" class="hover:text-brass transition-colors">Portfolio</a>
            <span>/</span>
            <span class="text-brass"><?= e($project['category']) ?></span>
        </nav>

        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
            <div class="max-w-3xl space-y-4">
                <span class="editorial-tag"><?= e($project['category']) ?> &bull; <?= e($project['year']) ?></span>
                <h1 class="font-serif text-4xl sm:text-5xl md:text-7xl text-ivory font-light leading-tight">
                    <?= e($project['title']) ?>
                </h1>
                <p class="text-sm md:text-base text-stone-subtle leading-relaxed font-light">
                    <?= e($project['short_description']) ?>
                </p>
            </div>

            <div class="flex items-center space-x-4 self-start lg:self-auto">
                <a href="<?= asset_url('contact.php?project=' . urlencode($project['title'])) ?>" class="btn-brass">
                    Inquire for Similar Space
                </a>
            </div>
        </div>

    </div>
</section>

<!-- ==============================================================================
     MAIN PROJECT HERO IMAGE OR 3D / PANORAMA VIEWER
     ============================================================================== -->
<section class="bg-charcoal px-6 md:px-12 py-12">
    <div class="max-w-7xl mx-auto">
        
        <!-- Interactive Mode Switcher Tabs if 360 or 3D is available -->
        <?php if (!empty($project['panorama_image']) || !empty($project['glb_model'])): ?>
            <div class="flex items-center space-x-4 mb-6 border-b border-white/10 pb-4">
                <button type="button" id="tab-btn-photo" class="project-view-tab px-4 py-2 text-xs uppercase tracking-widest border-b-2 border-brass text-brass font-medium focus:outline-none">
                    Architectural Photography
                </button>
                <?php if (!empty($project['panorama_image'])): ?>
                    <button type="button" id="tab-btn-pano" class="project-view-tab px-4 py-2 text-xs uppercase tracking-widest border-b-2 border-transparent text-stone-subtle hover:text-ivory font-medium focus:outline-none">
                        360&deg; Spatial Panorama
                    </button>
                <?php endif; ?>
                <?php if (!empty($project['glb_model'])): ?>
                    <button type="button" id="tab-btn-3d" class="project-view-tab px-4 py-2 text-xs uppercase tracking-widest border-b-2 border-transparent text-stone-subtle hover:text-ivory font-medium focus:outline-none">
                        3D Digital Twin Viewer
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- View 1: Main Photo Banner -->
        <div id="view-photo-container" class="aspect-[16/9] lg:aspect-[21/9] w-full overflow-hidden bg-charcoal-dark border border-white/10 relative">
            <div class="image-skeleton absolute inset-0"></div>
            <img 
                src="<?= image_url($project['thumbnail']) ?>" 
                alt="<?= e($project['title']) ?> Primary View"
                loading="eager"
                class="w-full h-full object-cover relative z-10 opacity-0 transition-opacity duration-700"
                onload="this.classList.remove('opacity-0'); this.previousElementSibling.classList.add('hidden');"
                onerror="this.src='https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1600&q=80'"
            >
        </div>

        <!-- View 2: 360° Panorama Viewer Container -->
        <?php if (!empty($project['panorama_image'])): ?>
            <div id="view-pano-container" class="hidden viewer-360-container" data-pano-url="<?= image_url($project['panorama_image']) ?>">
                <div class="absolute top-4 left-4 z-20 bg-charcoal/80 backdrop-blur-md px-3 py-1.5 border border-brass/30 text-[10px] uppercase tracking-widest text-brass font-mono">
                    Drag mouse to rotate 360&deg; view
                </div>
                <div id="pano-webgl-mount" class="w-full h-full"></div>
            </div>
        <?php endif; ?>

        <!-- View 3: 3D Model Viewer Container -->
        <?php if (!empty($project['glb_model'])): ?>
            <div id="view-3d-container" class="hidden model-viewer-container" data-model-url="<?= image_url($project['glb_model']) ?>">
                <div class="absolute top-4 left-4 z-20 bg-charcoal/80 backdrop-blur-md px-3 py-1.5 border border-brass/30 text-[10px] uppercase tracking-widest text-brass font-mono">
                    Orbit 3D spatial geometry &bull; Left click rotate &bull; Scroll zoom
                </div>
                <div id="model-webgl-mount" class="w-full h-full"></div>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- ==============================================================================
     PROJECT SPECIFICATIONS & EDITORIAL NARRATIVE
     ============================================================================== -->
<section class="py-20 px-6 md:px-12 bg-charcoal">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            
            <!-- Left Sidebar Specs (4 cols) -->
            <div class="lg:col-span-4 space-y-8">
                <div class="luxury-card p-8 space-y-6">
                    <h2 class="font-serif text-2xl text-ivory border-b border-white/10 pb-4">
                        Project Monograph
                    </h2>

                    <div class="space-y-4 text-xs">
                        <div>
                            <span class="text-stone-subtle uppercase tracking-widest block text-[10px]">Location</span>
                            <span class="text-ivory font-medium text-sm"><?= e($project['location']) ?></span>
                        </div>
                        <div class="pt-3 border-t border-white/5">
                            <span class="text-stone-subtle uppercase tracking-widest block text-[10px]">Typology</span>
                            <span class="text-ivory font-medium text-sm"><?= e($project['category']) ?></span>
                        </div>
                        <div class="pt-3 border-t border-white/5">
                            <span class="text-stone-subtle uppercase tracking-widest block text-[10px]">Spatial Footprint</span>
                            <span class="text-ivory font-medium text-sm"><?= e($project['area']) ?></span>
                        </div>
                        <div class="pt-3 border-t border-white/5">
                            <span class="text-stone-subtle uppercase tracking-widest block text-[10px]">Year of Completion</span>
                            <span class="text-ivory font-medium text-sm"><?= e($project['year']) ?></span>
                        </div>
                        <div class="pt-3 border-t border-white/5">
                            <span class="text-stone-subtle uppercase tracking-widest block text-[10px]">Patron Client</span>
                            <span class="text-ivory font-medium text-sm"><?= e($project['client_type']) ?></span>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/10">
                        <a href="<?= asset_url('contact.php?project=' . urlencode($project['title'])) ?>" class="btn-brass w-full text-center">
                            Request Consultation
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Full Narrative (8 cols) -->
            <div class="lg:col-span-8 space-y-8">
                <div class="flex items-center space-x-3">
                    <span class="w-8 h-[1px] bg-brass"></span>
                    <span class="editorial-tag">Architectural Concept</span>
                </div>

                <div class="font-serif text-xl sm:text-2xl text-ivory/90 leading-relaxed font-light italic">
                    <?= nl2br(e($project['short_description'])) ?>
                </div>

                <div class="text-sm md:text-base text-stone-subtle leading-relaxed font-light space-y-6">
                    <?= nl2br(e($project['full_description'])) ?>
                </div>

                <!-- Gallery Grid of Project Images -->
                <?php if (!empty($gallery_images)): ?>
                    <div class="pt-12 border-t border-white/10 space-y-8">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-[1px] bg-brass"></span>
                            <span class="editorial-tag">Materiality &amp; Details</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($gallery_images as $gImg): ?>
                                <div class="luxury-card overflow-hidden group">
                                    <div class="aspect-[4/3] bg-charcoal-dark overflow-hidden relative">
                                        <div class="image-skeleton absolute inset-0"></div>
                                        <img 
                                            src="<?= image_url($gImg['image_path']) ?>" 
                                            alt="<?= e($gImg['alt_text'] ?? $project['title']) ?>" 
                                            loading="lazy" 
                                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-0"
                                            onload="this.classList.remove('opacity-0'); this.previousElementSibling.classList.add('hidden');"
                                            onerror="this.src='https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1000&q=80'"
                                        >
                                    </div>
                                    <?php if (!empty($gImg['alt_text'])): ?>
                                        <div class="p-4 text-xs text-stone-subtle bg-charcoal-card border-t border-white/5 font-light">
                                            <?= e($gImg['alt_text']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</section>

<!-- ==============================================================================
     PREVIOUS / NEXT PROJECT NAVIGATION
     ============================================================================== -->
<section class="py-16 px-6 md:px-12 bg-charcoal-dark border-t border-white/10">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        
        <?php if ($prev_project): ?>
            <a href="<?= asset_url('project.php?slug=' . urlencode($prev_project['slug'])) ?>" class="group flex flex-col space-y-1">
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle flex items-center group-hover:text-brass transition-colors">
                    &larr; Previous Work
                </span>
                <span class="font-serif text-lg md:text-2xl text-ivory group-hover:text-brass transition-colors">
                    <?= e($prev_project['title']) ?>
                </span>
            </a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>

        <a href="<?= asset_url('portfolio.php') ?>" class="hidden md:inline-block text-xs uppercase tracking-widest text-brass hover:text-ivory transition-colors">
            All Works Monograph
        </a>

        <?php if ($next_project): ?>
            <a href="<?= asset_url('project.php?slug=' . urlencode($next_project['slug'])) ?>" class="group flex flex-col items-end space-y-1 text-right">
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle flex items-center group-hover:text-brass transition-colors">
                    Next Work &rarr;
                </span>
                <span class="font-serif text-lg md:text-2xl text-ivory group-hover:text-brass transition-colors">
                    <?= e($next_project['title']) ?>
                </span>
            </a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>

    </div>
</section>

<!-- Interactive tab script logic for 360 / 3D switcher -->
<script type="module">
import { initPanoramaViewer, initModelViewer } from '<?= asset_url('assets/js/panorama-viewer.js') ?>';

document.addEventListener('DOMContentLoaded', () => {
    const tabPhoto = document.getElementById('tab-btn-photo');
    const tabPano = document.getElementById('tab-btn-pano');
    const tab3d = document.getElementById('tab-btn-3d');

    const viewPhoto = document.getElementById('view-photo-container');
    const viewPano = document.getElementById('view-pano-container');
    const view3d = document.getElementById('view-3d-container');

    let panoInitialized = false;
    let modelInitialized = false;

    function resetTabs() {
        document.querySelectorAll('.project-view-tab').forEach(b => {
            b.classList.remove('border-brass', 'text-brass');
            b.classList.add('border-transparent', 'text-stone-subtle');
        });
        if (viewPhoto) viewPhoto.classList.add('hidden');
        if (viewPano) viewPano.classList.add('hidden');
        if (view3d) view3d.classList.add('hidden');
    }

    if (tabPhoto) {
        tabPhoto.addEventListener('click', () => {
            resetTabs();
            tabPhoto.classList.add('border-brass', 'text-brass');
            tabPhoto.classList.remove('border-transparent', 'text-stone-subtle');
            if (viewPhoto) viewPhoto.classList.remove('hidden');
        });
    }

    if (tabPano) {
        tabPano.addEventListener('click', () => {
            resetTabs();
            tabPano.classList.add('border-brass', 'text-brass');
            tabPano.classList.remove('border-transparent', 'text-stone-subtle');
            if (viewPano) {
                viewPano.classList.remove('hidden');
                if (!panoInitialized) {
                    const panoUrl = viewPano.getAttribute('data-pano-url');
                    initPanoramaViewer('pano-webgl-mount', panoUrl);
                    panoInitialized = true;
                }
            }
        });
    }

    if (tab3d) {
        tab3d.addEventListener('click', () => {
            resetTabs();
            tab3d.classList.add('border-brass', 'text-brass');
            tab3d.classList.remove('border-transparent', 'text-stone-subtle');
            if (view3d) {
                view3d.classList.remove('hidden');
                if (!modelInitialized) {
                    const modelUrl = view3d.getAttribute('data-model-url');
                    initModelViewer('model-webgl-mount', modelUrl);
                    modelInitialized = true;
                }
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
