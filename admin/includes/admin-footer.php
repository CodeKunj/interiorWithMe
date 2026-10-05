<?php
/**
 * Amak Interior - Admin Panel Layout Footer
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}
?>
    </main>

    <!-- Admin Footer -->
    <footer class="bg-charcoal border-t border-white/10 px-6 py-6 text-center text-xs text-stone-subtle mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; <?= date('Y') ?> Amak Interior Studio LLC &bull; Internal Content Management System</p>
            <p class="font-mono text-[11px]">PHP <?= phpversion() ?> &bull; MySQL &bull; Three.js r164</p>
        </div>
    </footer>

    <!-- Admin Client Script -->
    <script src="<?= asset_url('assets/js/admin.js') ?>"></script>
</body>
</html>
