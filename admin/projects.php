<?php
/**
 * Amak Interior - Admin Project Management List
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);
$admin_title = 'Projects Monograph | Amak Studio Portal';
$admin_active = 'projects';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db_connection();

try {
    $stmt = $pdo->query("SELECT id, title, slug, category, location, year, area, thumbnail, is_featured, display_order, glb_model, panorama_image, created_at FROM projects ORDER BY display_order ASC, created_at DESC");
    $projects = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Admin projects fetch error: " . $e->getMessage());
    $projects = [];
}
?>

<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
        <div>
            <span class="text-xs uppercase tracking-widest text-brass font-mono">Portfolio Index</span>
            <h1 class="font-serif text-3xl text-ivory mt-1">Project Monographs (<?= count($projects) ?>)</h1>
        </div>
        <a href="<?= asset_url('admin/project-form.php') ?>" class="btn-brass py-2.5 px-5 text-xs">
            + Create New Project
        </a>
    </div>

    <div class="luxury-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-charcoal border-b border-white/10 text-stone-subtle uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Order</th>
                        <th class="py-3.5 px-4">Visual</th>
                        <th class="py-3.5 px-4">Title &amp; Typology</th>
                        <th class="py-3.5 px-4">Location / Year</th>
                        <th class="py-3.5 px-4">3D / 360</th>
                        <th class="py-3.5 px-4">Hero Featured</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-stone-subtle">
                                No projects found. Click "+ Create New Project" to add your first spatial monograph.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $p): ?>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-4 px-4 font-mono text-stone-subtle">
                                    #<?= e((string)$p['display_order']) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="w-14 h-10 bg-charcoal-dark border border-white/10 overflow-hidden">
                                        <img 
                                            src="<?= image_url($p['thumbnail']) ?>" 
                                            alt="<?= e($p['title']) ?>" 
                                            class="w-full h-full object-cover"
                                            onerror="this.src='https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=200&q=80'"
                                        >
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <a href="<?= asset_url('admin/project-form.php?id=' . $p['id']) ?>" class="font-serif text-base text-ivory hover:text-brass transition-colors block">
                                        <?= e($p['title']) ?>
                                    </a>
                                    <span class="text-[10px] text-stone-subtle font-mono">/project/<?= e($p['slug']) ?> &bull; <?= e($p['category']) ?></span>
                                </td>
                                <td class="py-4 px-4 text-stone-subtle">
                                    <div><?= e($p['location']) ?></div>
                                    <div class="text-[10px]"><?= e($p['year']) ?> &bull; <?= e($p['area']) ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center space-x-1">
                                        <?php if (!empty($p['glb_model'])): ?>
                                            <span class="px-1.5 py-0.5 bg-emerald-950 border border-emerald-500/40 text-emerald-300 text-[9px] font-mono">3D</span>
                                        <?php endif; ?>
                                        <?php if (!empty($p['panorama_image'])): ?>
                                            <span class="px-1.5 py-0.5 bg-sky-950 border border-sky-500/40 text-sky-300 text-[9px] font-mono">360&deg;</span>
                                        <?php endif; ?>
                                        <?php if (empty($p['glb_model']) && empty($p['panorama_image'])): ?>
                                            <span class="text-stone-subtle text-[10px]">&mdash;</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <?php if ($p['is_featured']): ?>
                                        <span class="px-2 py-0.5 bg-brass/20 text-brass border border-brass/40 text-[10px] font-medium uppercase tracking-wider">Yes</span>
                                    <?php else: ?>
                                        <span class="text-stone-subtle text-[10px]">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="<?= asset_url('project.php?slug=' . urlencode($p['slug'])) ?>" target="_blank" class="text-stone-subtle hover:text-ivory" title="Preview Live">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="<?= asset_url('admin/project-form.php?id=' . $p['id']) ?>" class="text-brass hover:text-brass-light font-medium">
                                            Edit
                                        </a>
                                        <form action="<?= asset_url('admin/project-delete.php') ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="text-red-400 hover:text-red-300 confirm-delete" data-item-name="<?= e($p['title']) ?>" title="Delete Project">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/includes/admin-footer.php';
?>
