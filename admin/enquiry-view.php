<?php
/**
 * Amak Interior - Admin Single Enquiry Detail
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . asset_url('admin/enquiries.php'));
    exit;
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin_auth();

$pdo = get_db_connection();

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    require_csrf_token();
    $delStmt = $pdo->prepare("DELETE FROM enquiries WHERE id = ?");
    $delStmt->execute([$id]);
    set_flash_message('success', 'Inquiry deleted successfully.');
    header('Location: ' . asset_url('admin/enquiries.php'));
    exit;
}

// Fetch and mark as read
$stmt = $pdo->prepare("SELECT * FROM enquiries WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$enquiry = $stmt->fetch();

if (!$enquiry) {
    set_flash_message('error', 'Inquiry record not found.');
    header('Location: ' . asset_url('admin/enquiries.php'));
    exit;
}

$admin_title = 'Inquiry Inspection | Amak Studio Portal';
$admin_active = 'enquiries';

require_once __DIR__ . '/includes/admin-header.php';

// Automatically update read status
if (!$enquiry['is_read']) {
    $pdo->prepare("UPDATE enquiries SET is_read = 1 WHERE id = ?")->execute([$id]);
}
?>

<div class="space-y-6 max-w-3xl mx-auto">
    
    <div class="flex items-center justify-between border-b border-white/10 pb-6">
        <div>
            <a href="<?= asset_url('admin/enquiries.php') ?>" class="text-xs text-brass hover:underline uppercase tracking-wider block mb-1">
                &larr; Back to Inquiries List
            </a>
            <h1 class="font-serif text-3xl text-ivory">
                Inquiry from <?= e($enquiry['name']) ?>
            </h1>
        </div>

        <div class="flex items-center space-x-3">
            <a href="mailto:<?= e($enquiry['email']) ?>?subject=<?= urlencode('Regarding your Amak Interior Inquiry - ' . $enquiry['project_type']) ?>" class="btn-brass py-2 px-4 text-xs">
                Draft Direct Reply &rarr;
            </a>

            <form action="<?= asset_url('admin/enquiry-view.php?id=' . $id) ?>" method="POST" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn-outline-brass py-2 px-3 text-xs text-red-400 border-red-500/40 hover:bg-red-500 hover:text-white confirm-delete" data-item-name="this inquiry">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Enquiry Detail Card -->
    <div class="luxury-card p-8 space-y-6">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-white/10 pb-6">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Patron Name</span>
                <span class="font-serif text-2xl text-ivory"><?= e($enquiry['name']) ?></span>
            </div>

            <div>
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Electronic Mail</span>
                <a href="mailto:<?= e($enquiry['email']) ?>" class="text-brass hover:underline text-base"><?= e($enquiry['email']) ?></a>
            </div>

            <div>
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Telephone / WhatsApp</span>
                <span class="text-ivory text-sm"><?= e($enquiry['phone'] ?: 'Not specified') ?></span>
            </div>

            <div>
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Timestamp &amp; Network</span>
                <span class="text-stone-subtle text-xs font-mono"><?= date('F j, Y \a\t H:i:s T', strtotime($enquiry['created_at'])) ?><br>IP: <?= e($enquiry['ip_address']) ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-white/10 pb-6">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Project Typology</span>
                <span class="text-ivory text-base font-medium"><?= e($enquiry['project_type']) ?></span>
            </div>

            <div>
                <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-1">Target Capital Investment</span>
                <span class="text-brass text-base font-semibold"><?= e($enquiry['budget_range']) ?></span>
            </div>
        </div>

        <div>
            <span class="text-[10px] uppercase tracking-widest text-stone-subtle block mb-3">Client Spatial Vision &amp; Requirements</span>
            <div class="p-6 bg-charcoal border-l-2 border-brass text-ivory/90 text-sm leading-relaxed whitespace-pre-wrap font-serif italic text-base">
                <?= nl2br(e($enquiry['message'])) ?>
            </div>
        </div>

    </div>

</div>

<?php
require_once __DIR__ . '/includes/admin-footer.php';
?>
