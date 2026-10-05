<?php
/**
 * Amak Interior - Admin Dashboard
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);
$admin_title = 'Dashboard | Amak Studio Portal';
$admin_active = 'dashboard';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db_connection();

// Aggregate Stats
$total_projects = (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$featured_projects = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE is_featured = 1")->fetchColumn();
$total_enquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$unread_enquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE is_read = 0")->fetchColumn();

// Fetch Recent Enquiries
$recentEnquiriesStmt = $pdo->query("SELECT id, name, email, project_type, budget_range, is_read, created_at FROM enquiries ORDER BY created_at DESC LIMIT 5");
$recent_enquiries = $recentEnquiriesStmt->fetchAll();

// Fetch Recent Projects
$recentProjectsStmt = $pdo->query("SELECT id, title, slug, category, location, is_featured, created_at FROM projects ORDER BY created_at DESC LIMIT 5");
$recent_projects = $recentProjectsStmt->fetchAll();
?>

<div class="space-y-8">
    
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
        <div>
            <span class="text-xs uppercase tracking-widest text-brass font-mono">Executive Overview</span>
            <h1 class="font-serif text-3xl text-ivory mt-1">Welcome back, <?= e($admin_user['name']) ?></h1>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= asset_url('admin/project-form.php') ?>" class="btn-brass py-2.5 px-4 text-xs">
                + New Project
            </a>
            <a href="<?= asset_url('admin/enquiries.php') ?>" class="btn-outline-brass py-2.5 px-4 text-xs">
                View Enquiries (<?= $unread_enquiries ?>)
            </a>
            <a href="<?= asset_url('admin/settings.php') ?>" class="btn-outline-brass py-2.5 px-4 text-xs">
                Studio Settings
            </a>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="luxury-card p-6 border-l-4 border-l-brass">
            <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Total Monographs</span>
            <div class="font-serif text-3xl text-ivory"><?= $total_projects ?></div>
            <span class="text-xs text-brass mt-2 block"><?= $featured_projects ?> featured in hero/home</span>
        </div>

        <div class="luxury-card p-6 border-l-4 border-l-emerald-500">
            <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Total Inquiries</span>
            <div class="font-serif text-3xl text-ivory"><?= $total_enquiries ?></div>
            <span class="text-xs text-emerald-400 mt-2 block"><?= $unread_enquiries ?> unread / pending review</span>
        </div>

        <div class="luxury-card p-6 border-l-4 border-l-sky-500">
            <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">3D WebGL Status</span>
            <div class="font-serif text-xl text-ivory flex items-center space-x-2 mt-1">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span>Active</span>
            </div>
            <span class="text-xs text-stone-subtle mt-2 block">Three.js r164 &bull; Fallback Enabled</span>
        </div>

        <div class="luxury-card p-6 border-l-4 border-l-purple-500">
            <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Security &amp; CSRF</span>
            <div class="font-serif text-xl text-ivory flex items-center space-x-2 mt-1">
                <span class="w-3 h-3 rounded-full bg-purple-400"></span>
                <span>Enforced</span>
            </div>
            <span class="text-xs text-stone-subtle mt-2 block">Rate Limiting &bull; Honeypot Protected</span>
        </div>

    </div>

    <!-- Split Grid: Recent Inquiries & Recent Projects -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Left: Recent Enquiries -->
        <div class="luxury-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                    <h2 class="font-serif text-xl text-ivory">Recent Private Inquiries</h2>
                    <a href="<?= asset_url('admin/enquiries.php') ?>" class="text-xs text-brass hover:underline">View All &rarr;</a>
                </div>

                <?php if (empty($recent_enquiries)): ?>
                    <p class="text-xs text-stone-subtle py-8 text-center">No inquiries received yet.</p>
                <?php else: ?>
                    <div class="divide-y divide-white/5 text-xs">
                        <?php foreach ($recent_enquiries as $enq): ?>
                            <div class="py-3.5 flex items-center justify-between hover:bg-white/[0.02] px-2 transition-colors">
                                <div class="space-y-0.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-ivory text-sm"><?= e($enq['name']) ?></span>
                                        <?php if (!$enq['is_read']): ?>
                                            <span class="px-1.5 py-0.5 text-[9px] bg-brass text-charcoal-dark font-bold uppercase">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-stone-subtle text-[11px]"><?= e($enq['project_type']) ?> &bull; <?= e($enq['budget_range']) ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-stone-subtle text-[10px]"><?= date('M j, Y', strtotime($enq['created_at'])) ?></div>
                                    <a href="<?= asset_url('admin/enquiry-view.php?id=' . $enq['id']) ?>" class="text-brass hover:underline text-[11px]">Inspect &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Recent Projects -->
        <div class="luxury-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                    <h2 class="font-serif text-xl text-ivory">Active Monographs</h2>
                    <a href="<?= asset_url('admin/projects.php') ?>" class="text-xs text-brass hover:underline">View All &rarr;</a>
                </div>

                <?php if (empty($recent_projects)): ?>
                    <p class="text-xs text-stone-subtle py-8 text-center">No projects in database.</p>
                <?php else: ?>
                    <div class="divide-y divide-white/5 text-xs">
                        <?php foreach ($recent_projects as $proj): ?>
                            <div class="py-3.5 flex items-center justify-between hover:bg-white/[0.02] px-2 transition-colors">
                                <div class="space-y-0.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-ivory text-sm"><?= e($proj['title']) ?></span>
                                        <?php if ($proj['is_featured']): ?>
                                            <span class="px-1.5 py-0.5 text-[9px] bg-brass/20 text-brass border border-brass/30 uppercase">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-stone-subtle text-[11px]"><?= e($proj['category']) ?> &bull; <?= e($proj['location']) ?></div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <a href="<?= asset_url('project.php?slug=' . urlencode($proj['slug'])) ?>" target="_blank" class="text-stone-subtle hover:text-ivory text-[11px]">Live</a>
                                    <a href="<?= asset_url('admin/project-form.php?id=' . $proj['id']) ?>" class="text-brass hover:underline text-[11px]">Edit</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php
require_once __DIR__ . '/includes/admin-footer.php';
?>
