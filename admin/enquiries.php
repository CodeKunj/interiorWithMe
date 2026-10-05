<?php
/**
 * Amak Interior - Admin Enquiries List
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);
$admin_title = 'Private Inquiries | Amak Studio Portal';
$admin_active = 'enquiries';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db_connection();

$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM enquiries ";

if ($filter === 'unread') {
    $query .= "WHERE is_read = 0 ";
} elseif ($filter === 'read') {
    $query .= "WHERE is_read = 1 ";
}

$query .= "ORDER BY created_at DESC";

try {
    $stmt = $pdo->query($query);
    $enquiries = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Enquiries fetch error: " . $e->getMessage());
    $enquiries = [];
}
?>

<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
        <div>
            <span class="text-xs uppercase tracking-widest text-brass font-mono">Commission Inquiries</span>
            <h1 class="font-serif text-3xl text-ivory mt-1">Client Communications (<?= count($enquiries) ?>)</h1>
        </div>

        <!-- Filter tabs -->
        <div class="flex items-center space-x-2 text-xs">
            <a href="<?= asset_url('admin/enquiries.php?filter=all') ?>" class="px-3 py-1.5 border <?= $filter === 'all' ? 'bg-brass text-charcoal font-semibold border-brass' : 'text-stone-subtle border-white/10 hover:text-ivory' ?>">All</a>
            <a href="<?= asset_url('admin/enquiries.php?filter=unread') ?>" class="px-3 py-1.5 border <?= $filter === 'unread' ? 'bg-brass text-charcoal font-semibold border-brass' : 'text-stone-subtle border-white/10 hover:text-ivory' ?>">Unread</a>
            <a href="<?= asset_url('admin/enquiries.php?filter=read') ?>" class="px-3 py-1.5 border <?= $filter === 'read' ? 'bg-brass text-charcoal font-semibold border-brass' : 'text-stone-subtle border-white/10 hover:text-ivory' ?>">Read</a>
        </div>
    </div>

    <div class="luxury-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-charcoal border-b border-white/10 text-stone-subtle uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Client</th>
                        <th class="py-3.5 px-4">Typology &amp; Budget</th>
                        <th class="py-3.5 px-4">Received Date</th>
                        <th class="py-3.5 px-4">Network IP</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($enquiries)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-stone-subtle">
                                No inquiries match the selected filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($enquiries as $enq): ?>
                            <tr class="hover:bg-white/[0.02] transition-colors <?= !$enq['is_read'] ? 'bg-brass/[0.03]' : '' ?>">
                                <td class="py-4 px-4">
                                    <?php if (!$enq['is_read']): ?>
                                        <span class="px-2 py-0.5 bg-brass text-charcoal-dark font-bold text-[9px] uppercase tracking-wider">Unread</span>
                                    <?php else: ?>
                                        <span class="text-stone-subtle text-[10px]">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4">
                                    <a href="<?= asset_url('admin/enquiry-view.php?id=' . $enq['id']) ?>" class="font-serif text-base text-ivory hover:text-brass transition-colors block">
                                        <?= e($enq['name']) ?>
                                    </a>
                                    <span class="text-stone-subtle text-[11px]"><?= e($enq['email']) ?> &bull; <?= e($enq['phone'] ?: 'No phone') ?></span>
                                </td>
                                <td class="py-4 px-4 text-stone-subtle">
                                    <div class="text-ivory font-medium"><?= e($enq['project_type']) ?></div>
                                    <div class="text-[10px] text-brass"><?= e($enq['budget_range']) ?></div>
                                </td>
                                <td class="py-4 px-4 text-stone-subtle font-mono text-[11px]">
                                    <?= date('M j, Y H:i', strtotime($enq['created_at'])) ?>
                                </td>
                                <td class="py-4 px-4 text-stone-subtle font-mono text-[10px]">
                                    <?= e($enq['ip_address']) ?>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <a href="<?= asset_url('admin/enquiry-view.php?id=' . $enq['id']) ?>" class="btn-outline-brass py-1 px-3 text-[10px]">
                                        View Details &rarr;
                                    </a>
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
