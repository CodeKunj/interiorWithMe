<?php
/**
 * Amak Interior - Admin Project Create & Edit Form
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

$admin_title = ($is_edit ? 'Edit Monograph' : 'Create New Monograph') . ' | Amak Studio Portal';
$admin_active = $is_edit ? 'projects' : 'project-new';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db_connection();

$project = [
    'id'                => 0,
    'title'             => '',
    'slug'              => '',
    'category'          => 'Living Room',
    'location'          => 'Manhattan, New York',
    'year'              => (string)date('Y'),
    'area'              => '3,500 sq ft',
    'client_type'       => 'Private Residence',
    'short_description' => '',
    'full_description'  => '',
    'thumbnail'         => '',
    'glb_model'         => '',
    'panorama_image'    => '',
    'display_order'     => 0,
    'is_featured'       => 0
];

$gallery_images = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $fetched = $stmt->fetch();

    if (!$fetched) {
        set_flash_message('error', 'Project not found.');
        header('Location: ' . asset_url('admin/projects.php'));
        exit;
    }

    $project = $fetched;

    // Fetch existing gallery images
    $gStmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
    $gStmt->execute([$id]);
    $gallery_images = $gStmt->fetchAll();
}
?>

<div class="space-y-6 max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between border-b border-white/10 pb-6">
        <div>
            <a href="<?= asset_url('admin/projects.php') ?>" class="text-xs text-brass hover:underline uppercase tracking-wider block mb-1">
                &larr; Back to Monographs Index
            </a>
            <h1 class="font-serif text-3xl text-ivory">
                <?= $is_edit ? 'Edit Monograph: ' . e($project['title']) : 'Create New Spatial Monograph' ?>
            </h1>
        </div>

        <?php if ($is_edit): ?>
            <a href="<?= asset_url('project.php?slug=' . urlencode($project['slug'])) ?>" target="_blank" class="btn-outline-brass py-2 px-4 text-xs">
                View Live Page &rarr;
            </a>
        <?php endif; ?>
    </div>

    <form action="<?= asset_url('admin/project-save.php') ?>" method="POST" enctype="multipart/form-data" class="space-y-8">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $project['id'] ?>">

        <!-- Primary Details Card -->
        <div class="luxury-card p-6 md:p-8 space-y-6">
            <h2 class="font-serif text-xl text-ivory border-b border-white/10 pb-3">Primary Monograph Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div>
                    <label for="project_title" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Project Title *
                    </label>
                    <input 
                        type="text" 
                        id="project_title" 
                        name="title" 
                        required 
                        value="<?= e($project['title']) ?>" 
                        placeholder="e.g. The Tribeca Loft" 
                        class="form-input"
                    >
                </div>

                <!-- Slug -->
                <div>
                    <label for="project_slug" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        URL Slug *
                    </label>
                    <input 
                        type="text" 
                        id="project_slug" 
                        name="slug" 
                        required 
                        value="<?= e($project['slug']) ?>" 
                        placeholder="e.g. the-tribeca-loft" 
                        class="form-input font-mono text-xs"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Category -->
                <div>
                    <label for="category" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Typology Category *
                    </label>
                    <select id="category" name="category" class="form-input text-ivory bg-charcoal-card">
                        <option value="Living Room" <?= $project['category'] === 'Living Room' ? 'selected' : '' ?>>Living Room</option>
                        <option value="Bedroom" <?= $project['category'] === 'Bedroom' ? 'selected' : '' ?>>Bedroom</option>
                        <option value="Kitchen" <?= $project['category'] === 'Kitchen' ? 'selected' : '' ?>>Kitchen</option>
                        <option value="Bathroom" <?= $project['category'] === 'Bathroom' ? 'selected' : '' ?>>Bathroom</option>
                        <option value="Office" <?= $project['category'] === 'Office' ? 'selected' : '' ?>>Office</option>
                        <option value="Commercial" <?= $project['category'] === 'Commercial' ? 'selected' : '' ?>>Commercial</option>
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Location
                    </label>
                    <input 
                        type="text" 
                        id="location" 
                        name="location" 
                        value="<?= e($project['location']) ?>" 
                        placeholder="e.g. Tribeca, New York" 
                        class="form-input"
                    >
                </div>

                <!-- Year -->
                <div>
                    <label for="year" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Year
                    </label>
                    <input 
                        type="text" 
                        id="year" 
                        name="year" 
                        value="<?= e($project['year']) ?>" 
                        placeholder="2025" 
                        class="form-input"
                    >
                </div>

                <!-- Area -->
                <div>
                    <label for="area" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Spatial Area
                    </label>
                    <input 
                        type="text" 
                        id="area" 
                        name="area" 
                        value="<?= e($project['area']) ?>" 
                        placeholder="e.g. 4,200 sq ft" 
                        class="form-input"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Type -->
                <div>
                    <label for="client_type" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                        Patron / Client Typology
                    </label>
                    <input 
                        type="text" 
                        id="client_type" 
                        name="client_type" 
                        value="<?= e($project['client_type']) ?>" 
                        placeholder="e.g. Private Collector / Family Office" 
                        class="form-input"
                    >
                </div>

                <!-- Display Order & Featured -->
                <div class="flex items-center space-x-6 pt-6">
                    <div class="w-32">
                        <label for="display_order" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                            Display Order
                        </label>
                        <input 
                            type="number" 
                            id="display_order" 
                            name="display_order" 
                            value="<?= (int)$project['display_order'] ?>" 
                            class="form-input"
                        >
                    </div>

                    <div class="flex items-center space-x-3 pt-4">
                        <input 
                            type="checkbox" 
                            id="is_featured" 
                            name="is_featured" 
                            value="1" 
                            <?= $project['is_featured'] ? 'checked' : '' ?> 
                            class="w-4 h-4 accent-brass"
                        >
                        <label for="is_featured" class="text-xs uppercase tracking-wider text-ivory cursor-pointer">
                            Feature on Home Page
                        </label>
                    </div>
                </div>
            </div>

            <!-- Short Description -->
            <div>
                <label for="short_description" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                    Short Editorial Excerpt (Homepage &amp; Grid) *
                </label>
                <textarea 
                    id="short_description" 
                    name="short_description" 
                    rows="2" 
                    required 
                    class="form-input"
                    placeholder="A concise summary of materials and architectural intent..."
                ><?= e($project['short_description']) ?></textarea>
            </div>

            <!-- Full Description -->
            <div>
                <label for="full_description" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                    Full Architectural Monograph Narrative *
                </label>
                <textarea 
                    id="full_description" 
                    name="full_description" 
                    rows="6" 
                    required 
                    class="form-input leading-relaxed"
                    placeholder="Comprehensive description of the architectural envelope, materiality, spatial reconfiguration, and bespoke details..."
                ><?= e($project['full_description']) ?></textarea>
            </div>
        </div>

        <!-- Media Assets Card -->
        <div class="luxury-card p-6 md:p-8 space-y-6">
            <h2 class="font-serif text-xl text-ivory border-b border-white/10 pb-3">Visual &amp; 3D Media Assets</h2>

            <!-- Primary Thumbnail -->
            <div>
                <label class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                    Primary Thumbnail Image (JPG, PNG, WebP &bull; Max 8MB) <?= $is_edit ? '' : '*' ?>
                </label>
                
                <?php if (!empty($project['thumbnail'])): ?>
                    <div class="mb-3 flex items-center space-x-4">
                        <img src="<?= image_url($project['thumbnail']) ?>" alt="Current thumbnail" class="w-32 h-20 object-cover border border-white/10">
                        <span class="text-xs text-stone-subtle font-mono"><?= e($project['thumbnail']) ?></span>
                    </div>
                <?php endif; ?>

                <input 
                    type="file" 
                    id="project_thumbnail" 
                    name="thumbnail" 
                    accept="image/jpeg,image/png,image/webp" 
                    class="form-input file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:bg-brass file:text-charcoal-dark"
                >
                <img id="thumbnail_preview" class="hidden mt-3 w-40 h-24 object-cover border border-brass">
            </div>

            <!-- Optional 3D GLB Model -->
            <div class="pt-4 border-t border-white/5">
                <label class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                    3D Digital Twin Model (.glb / .gltf &bull; Max 30MB &bull; Optional)
                </label>
                <?php if (!empty($project['glb_model'])): ?>
                    <div class="mb-2 text-xs text-emerald-400 font-mono">Current 3D asset: <?= e($project['glb_model']) ?></div>
                <?php endif; ?>
                <input 
                    type="file" 
                    name="glb_model" 
                    accept=".glb,.gltf" 
                    class="form-input file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:bg-charcoal file:text-ivory file:border file:border-white/10"
                >
            </div>

            <!-- Optional 360 Panorama Image -->
            <div class="pt-4 border-t border-white/5">
                <label class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                    360&deg; Equirectangular Panorama Image (.jpg / .webp &bull; Optional)
                </label>
                <?php if (!empty($project['panorama_image'])): ?>
                    <div class="mb-2 text-xs text-sky-400 font-mono">Current panorama: <?= e($project['panorama_image']) ?></div>
                <?php endif; ?>
                <input 
                    type="file" 
                    name="panorama_image" 
                    accept="image/jpeg,image/png,image/webp" 
                    class="form-input file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:bg-charcoal file:text-ivory file:border file:border-white/10"
                >
            </div>
        </div>

        <!-- Additional Gallery Images Card -->
        <div class="luxury-card p-6 md:p-8 space-y-6">
            <h2 class="font-serif text-xl text-ivory border-b border-white/10 pb-3">Gallery Image Suite</h2>

            <?php if (!empty($gallery_images)): ?>
                <div class="space-y-3">
                    <span class="text-xs uppercase tracking-wider text-stone-subtle block">Existing Gallery Images:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($gallery_images as $g): ?>
                            <div class="p-3 bg-charcoal border border-white/10 space-y-2">
                                <img src="<?= image_url($g['image_path']) ?>" alt="<?= e($g['alt_text']) ?>" class="w-full h-24 object-cover">
                                <div class="text-[11px] text-stone-subtle truncate"><?= e($g['alt_text']) ?></div>
                                <label class="flex items-center space-x-2 text-[11px] text-red-400 cursor-pointer">
                                    <input type="checkbox" name="delete_gallery_ids[]" value="<?= $g['id'] ?>" class="accent-red-500">
                                    <span>Delete this photo</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="pt-4 border-t border-white/5 space-y-3">
                <label class="block text-xs uppercase tracking-widest text-stone-subtle font-medium">
                    Upload Additional Gallery Images (Select up to 6 files)
                </label>
                <input 
                    type="file" 
                    name="gallery_files[]" 
                    multiple 
                    accept="image/jpeg,image/png,image/webp" 
                    class="form-input file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:bg-charcoal file:text-ivory file:border file:border-white/10"
                >
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-between pt-4">
            <a href="<?= asset_url('admin/projects.php') ?>" class="text-xs uppercase tracking-wider text-stone-subtle hover:text-ivory">
                Cancel
            </a>
            <button type="submit" class="btn-brass py-3.5 px-8">
                <?= $is_edit ? 'Save &amp; Update Monograph' : 'Publish Monograph' ?>
            </button>
        </div>

    </form>

</div>

<?php
require_once __DIR__ . '/includes/admin-footer.php';
?>
