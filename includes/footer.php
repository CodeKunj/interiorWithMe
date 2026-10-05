<?php
/**
 * Amak Interior - Public Footer Template
 * PHP 8.2+
 */

declare(strict_types=1);

if (!defined('AMAK_INIT')) {
    define('AMAK_INIT', true);
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/functions.php';
?>
<?php
$f_brand_name  = get_setting('site_name', APP_NAME);
$f_tagline     = get_setting('site_tagline', APP_TAGLINE);
$f_description = get_setting('site_description', 'An internationally recognized spatial design studio orchestrating high-end residences, penthouses, and bespoke private commissions across Manhattan, the Hamptons, and worldwide.');
$f_address     = get_setting('studio_address', APP_ADDRESS);
$f_phone       = get_setting('contact_phone', APP_PHONE);
$f_email       = get_setting('contact_email', APP_EMAIL);
$f_hours       = get_setting('working_hours', 'By Private Appointment Only');
$f_copyright   = get_setting('copyright_text', '© ' . date('Y') . ' Amak Interior Studio LLC. All rights reserved.');
$f_scripts     = get_setting('footer_scripts');
$f_whatsapp    = get_setting('whatsapp_number', '+1 (212) 555-0198');
$f_wa_enable   = get_setting('whatsapp_floating_button', '1') === '1';

// Social URLs
$f_instagram = get_setting('social_instagram', SOCIAL_INSTAGRAM);
$f_pinterest = get_setting('social_pinterest', SOCIAL_PINTEREST);
$f_linkedin  = get_setting('social_linkedin', SOCIAL_LINKEDIN);
$f_facebook  = get_setting('social_facebook', '');
$f_archdaily = get_setting('social_archdaily', SOCIAL_ARCHDAILY);
$f_youtube   = get_setting('social_youtube', '');
$f_twitter   = get_setting('social_twitter', '');
?>
    </main>

    <!-- Global Footer -->
    <footer class="bg-charcoal-dark border-t border-white/10 pt-20 pb-12 px-6 md:px-12 text-ivory/80 relative z-20">
        <div class="max-w-7xl mx-auto">
            <!-- Top Footer Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 pb-16 border-b border-white/10">
                
                <!-- Brand Column (4 cols) -->
                <div class="lg:col-span-4 space-y-6">
                    <a href="<?= asset_url('') ?>" class="inline-block">
                        <span class="font-serif text-3xl tracking-widest text-ivory block"><?= e($f_brand_name) ?></span>
                        <span class="text-[10px] tracking-super uppercase text-brass font-medium"><?= e($f_tagline) ?></span>
                    </a>
                    <p class="text-sm text-stone-subtle leading-relaxed pr-6">
                        <?= e($f_description) ?>
                    </p>
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <?php if (!empty($f_instagram)): ?>
                        <a href="<?= e($f_instagram) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-ivory hover:border-brass hover:text-brass transition-all duration-300" aria-label="Instagram">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($f_pinterest)): ?>
                        <a href="<?= e($f_pinterest) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-ivory hover:border-brass hover:text-brass transition-all duration-300" aria-label="Pinterest">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345-.09.375-.291 1.199-.334 1.373-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($f_linkedin)): ?>
                        <a href="<?= e($f_linkedin) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-ivory hover:border-brass hover:text-brass transition-all duration-300" aria-label="LinkedIn">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($f_facebook)): ?>
                        <a href="<?= e($f_facebook) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-ivory hover:border-brass hover:text-brass transition-all duration-300" aria-label="Facebook">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.667 5H18V0h-3.808C10.597 0 9 1.583 9 4.615V8z"/></svg>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($f_youtube)): ?>
                        <a href="<?= e($f_youtube) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-ivory hover:border-brass hover:text-brass transition-all duration-300" aria-label="YouTube">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($f_twitter)): ?>
                        <a href="<?= e($f_twitter) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-ivory hover:border-brass hover:text-brass transition-all duration-300" aria-label="Twitter">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Navigation Links (2 cols) -->
                <div class="lg:col-span-2 space-y-4">
                    <h2 class="text-xs uppercase tracking-widest text-brass font-semibold">Exploration</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="<?= asset_url('') ?>" class="hover:text-brass transition-colors">Home Studio</a></li>
                        <li><a href="<?= asset_url('portfolio.php') ?>" class="hover:text-brass transition-colors">Portfolio</a></li>
                        <li><a href="<?= asset_url('#about') ?>" class="hover:text-brass transition-colors">Design Philosophy</a></li>
                        <li><a href="<?= asset_url('#services') ?>" class="hover:text-brass transition-colors">Capabilities</a></li>
                        <li><a href="<?= asset_url('#process') ?>" class="hover:text-brass transition-colors">Methodology</a></li>
                        <li><a href="<?= asset_url('contact.php') ?>" class="hover:text-brass transition-colors">Private Commissions</a></li>
                    </ul>
                </div>

                <!-- Typologies (3 cols) -->
                <div class="lg:col-span-3 space-y-4">
                    <h2 class="text-xs uppercase tracking-widest text-brass font-semibold">Typologies</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="<?= asset_url('portfolio.php?cat=Living%20Room') ?>" class="hover:text-brass transition-colors">Living Salons & Lounges</a></li>
                        <li><a href="<?= asset_url('portfolio.php?cat=Bedroom') ?>" class="hover:text-brass transition-colors">Master Sanctuary Suites</a></li>
                        <li><a href="<?= asset_url('portfolio.php?cat=Kitchen') ?>" class="hover:text-brass transition-colors">Architectural Kitchens</a></li>
                        <li><a href="<?= asset_url('portfolio.php?cat=Bathroom') ?>" class="hover:text-brass transition-colors">Spa & Bathing Pavilions</a></li>
                        <li><a href="<?= asset_url('portfolio.php?cat=Office') ?>" class="hover:text-brass transition-colors">Private Executive Offices</a></li>
                    </ul>
                </div>

                <!-- Studio Atelier (3 cols) -->
                <div class="lg:col-span-3 space-y-4">
                    <h2 class="text-xs uppercase tracking-widest text-brass font-semibold">Studio Atelier</h2>
                    <address class="not-italic text-sm text-stone-subtle space-y-2 leading-relaxed">
                        <p class="text-ivory"><?= e($f_address) ?></p>
                        <p>Direct: <a href="tel:<?= preg_replace('/[^\d+]/', '', $f_phone) ?>" class="text-ivory hover:text-brass transition-colors"><?= e($f_phone) ?></a></p>
                        <p>Inquiries: <a href="mailto:<?= e($f_email) ?>" class="text-ivory hover:text-brass transition-colors"><?= e($f_email) ?></a></p>
                        <p class="text-xs text-stone-subtle pt-2"><?= e($f_hours) ?></p>
                    </address>
                </div>

            </div>

            <!-- Bottom Legal Bar -->
            <div class="pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-stone-subtle space-y-4 md:space-y-0">
                <p><?= e($f_copyright) ?></p>
                <div class="flex items-center space-x-6">
                    <a href="<?= asset_url('admin/login.php') ?>" class="hover:text-brass transition-colors">Studio Portal</a>
                    <span>&bull;</span>
                    <a href="<?= asset_url('sitemap.xml') ?>" class="hover:text-brass transition-colors">Sitemap</a>
                    <span>&bull;</span>
                    <span>Crafted with Three.js &amp; Editorial Precision</span>
                </div>
            </div>
        </div>
    </footer>

    <?php if ($f_wa_enable && !empty($f_whatsapp)): 
        $wa_digits = preg_replace('/[^\d]/', '', $f_whatsapp);
    ?>
    <!-- Floating WhatsApp Concierge Button -->
    <aside aria-label="WhatsApp Concierge" class="fixed bottom-6 right-6 z-40 group">
        <a href="https://wa.me/<?= $wa_digits ?>?text=<?= urlencode('Hello Amak Interior, I would like to inquire about a private commission.') ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-14 h-14 bg-emerald-600 hover:bg-emerald-500 text-white rounded-full shadow-2xl transition-all duration-300 transform group-hover:scale-110" aria-label="Chat with Concierge on WhatsApp">
            <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </a>
    </aside>
    <?php endif; ?>

    <!-- Schema.org LocalBusiness & InteriorDesign Studio Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "HomeAndConstructionBusiness",
      "additionalType": "https://schema.org/InteriorDesignStudio",
      "name": "<?= e($f_brand_name) ?>",
      "image": "<?= image_url(get_setting('og_image', 'assets/img/og-image.jpg')) ?>",
      "@id": "<?= SITE_URL ?>/#organization",
      "url": "<?= SITE_URL ?>",
      "telephone": "<?= e($f_phone) ?>",
      "email": "<?= e($f_email) ?>",
      "priceRange": "$$$$",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "<?= e($f_address) ?>"
      },
      "sameAs": [
        "<?= e($f_instagram) ?>",
        "<?= e($f_pinterest) ?>",
        "<?= e($f_linkedin) ?>"
      ]
    }
    </script>

    <!-- Global Core Application Logic -->
    <script src="<?= asset_url('assets/js/app.js') ?>"></script>
    <script src="<?= asset_url('assets/js/scroll-animations.js') ?>"></script>

    <?php if (isset($extra_scripts) && is_array($extra_scripts)): ?>
        <?php foreach ($extra_scripts as $script): ?>
            <script type="module" src="<?= asset_url($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($f_scripts)): ?>
    <!-- Custom Footer Code Injection -->
    <?= $f_scripts ?>
    <?php endif; ?>

</body>
</html>

