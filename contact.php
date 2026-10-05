<?php
/**
 * Amak Interior - Private Consultation & Commissions
 * Secure CSRF, honeypot spam protection, rate-limiting, and PHPMailer integration
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);
$current_page = 'contact';
$page_title = 'Private Consultation & Commissions | Amak Interior';
$page_description = 'Initiate a private spatial consultation with Amak Interior in SoHo, New York. We accept a limited number of residential and commercial commissions annually.';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$prefilled_project = trim($_GET['project'] ?? '');

// Handle Form POST Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Verification
    if (!csrf_verify()) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Security token expired. Please reload the page.']);
            exit;
        }
        set_flash_message('error', 'Security token expired. Please try again.');
        header('Location: ' . asset_url('contact.php'));
        exit;
    }

    // 2. Honeypot check
    $honeypot = trim($_POST['website_url'] ?? '');
    if (!empty($honeypot)) {
        // Silently treat as success to fool automated bots
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Your request has been received.']);
            exit;
        }
        set_flash_message('success', 'Your request has been received.');
        header('Location: ' . asset_url('contact.php'));
        exit;
    }

    // 3. Rate Limit Check (Max 4 requests per IP per hour)
    $client_ip = get_client_ip();
    if (!check_rate_limit($client_ip, 4, 3600)) {
        $rate_msg = 'Submission limit reached from this network. Please phone our studio directly at ' . APP_PHONE;
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $rate_msg]);
            exit;
        }
        set_flash_message('error', $rate_msg);
        header('Location: ' . asset_url('contact.php'));
        exit;
    }

    // 4. Sanitize and Validate Inputs
    $name         = trim(filter_input(INPUT_POST, 'name', FILTER_DEFAULT) ?? '');
    $email        = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: '');
    $phone        = trim(filter_input(INPUT_POST, 'phone', FILTER_DEFAULT) ?? '');
    $project_type = trim(filter_input(INPUT_POST, 'project_type', FILTER_DEFAULT) ?? 'Private Residence');
    $budget_range = trim(filter_input(INPUT_POST, 'budget_range', FILTER_DEFAULT) ?? '$250,000 - $500,000');
    $message      = trim(filter_input(INPUT_POST, 'message', FILTER_DEFAULT) ?? '');

    $errors = [];
    if (empty($name) || mb_strlen($name) < 2) {
        $errors[] = 'Please provide your full name.';
    }
    if (empty($email)) {
        $errors[] = 'Please provide a valid email address.';
    }
    if (empty($message) || mb_strlen($message) < 10) {
        $errors[] = 'Please share a brief summary of your spatial requirements (minimum 10 characters).';
    }

    if (!empty($errors)) {
        $err_msg = implode(' ', $errors);
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $err_msg]);
            exit;
        }
        set_flash_message('error', $err_msg);
        header('Location: ' . asset_url('contact.php'));
        exit;
    }

    // 5. Save Enquiry into Database
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("INSERT INTO enquiries (name, email, phone, project_type, budget_range, message, ip_address, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$name, $email, $phone, $project_type, $budget_range, $message, $client_ip]);

        // 6. Send Email Notification via PHPMailer / SMTP
        send_enquiry_email([
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'project_type' => $project_type,
            'budget_range' => $budget_range,
            'message'      => $message,
            'ip_address'   => $client_ip
        ]);

        $success_msg = 'Thank you for your inquiry. Our studio principal will review your commission and contact you within 24 business hours.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success_msg]);
            exit;
        }
        set_flash_message('success', $success_msg);
        header('Location: ' . asset_url('contact.php'));
        exit;
    } catch (\Exception $e) {
        error_log("Enquiry submission database error: " . $e->getMessage());
        $err_db = 'An error occurred while saving your inquiry. Please call us directly at ' . APP_PHONE;
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $err_db]);
            exit;
        }
        set_flash_message('error', $err_db);
        header('Location: ' . asset_url('contact.php'));
        exit;
    }
}

$flash = get_flash_message();
require_once __DIR__ . '/includes/header.php';
?>

<!-- ==============================================================================
     CONTACT HERO HEADER
     ============================================================================== -->
<section class="pt-36 pb-16 px-6 md:px-12 bg-charcoal-dark border-b border-white/5 relative">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex items-center space-x-3">
            <span class="w-8 h-[1px] bg-brass"></span>
            <span class="editorial-tag">Studio Engagement</span>
        </div>
        <h1 class="font-serif text-4xl sm:text-5xl md:text-6xl text-ivory font-light leading-tight">
            Initiate a Private Commission
        </h1>
        <p class="text-sm md:text-base text-stone-subtle max-w-2xl leading-relaxed">
            We invite prospective patrons to share their architectural vision. Every engagement begins with an intimate spatial dialogue.
        </p>
    </div>
</section>

<!-- ==============================================================================
     CONTACT FORM & ATELIER DETAILS
     ============================================================================== -->
<section class="py-20 px-6 md:px-12 bg-charcoal">
    <div class="max-w-7xl mx-auto">
        
        <!-- Flash Alert Message (if non-AJAX) -->
        <?php if ($flash): ?>
            <div class="mb-12 p-5 border text-sm max-w-3xl <?= $flash['type'] === 'success' ? 'bg-emerald-950/80 border-emerald-500/50 text-emerald-200' : 'bg-red-950/80 border-red-500/50 text-red-200' ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            
            <!-- Left Form (7 cols) -->
            <div class="lg:col-span-7">
                <div class="luxury-card p-8 md:p-12">
                    <h2 class="font-serif text-2xl md:text-3xl text-ivory font-light mb-2">
                        Spatial Consultation Inquiry
                    </h2>
                    <p class="text-xs text-stone-subtle mb-8">
                        Required fields are marked with an asterisk (*). All information is held in strict fiduciary confidence.
                    </p>

                    <!-- Feedback banner for AJAX -->
                    <div id="form-feedback" class="hidden mb-8 p-4 text-xs font-medium"></div>

                    <form id="studio-contact-form" action="<?= asset_url('contact.php') ?>" method="POST" class="space-y-6" novalidate>
                        <?= csrf_field() ?>
                        
                        <!-- Honeypot field (hidden from humans via CSS) -->
                        <div class="hidden" aria-hidden="true">
                            <label for="website_url">Leave empty</label>
                            <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="client_name" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                                    Full Name *
                                </label>
                                <input 
                                    type="text" 
                                    id="client_name" 
                                    name="name" 
                                    required 
                                    placeholder="e.g. Julian Beaumont" 
                                    class="form-input"
                                >
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="client_email" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                                    Email Address *
                                </label>
                                <input 
                                    type="email" 
                                    id="client_email" 
                                    name="email" 
                                    required 
                                    placeholder="e.g. j.beaumont@domain.com" 
                                    class="form-input"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Phone -->
                            <div>
                                <label for="client_phone" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                                    Phone / Mobile (Optional)
                                </label>
                                <input 
                                    type="tel" 
                                    id="client_phone" 
                                    name="phone" 
                                    placeholder="+1 (212) 000-0000" 
                                    class="form-input"
                                >
                            </div>

                            <!-- Project Type -->
                            <div>
                                <label for="project_type" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                                    Project Typology
                                </label>
                                <select id="project_type" name="project_type" class="form-input text-ivory bg-charcoal-card">
                                    <option value="Residential Penthouse">Residential Penthouse</option>
                                    <option value="Historic Brownstone / Townhouse">Historic Townhouse Renovation</option>
                                    <option value="Private Country Estate">Private Country Estate</option>
                                    <option value="Bespoke Executive Office">Bespoke Executive Office</option>
                                    <option value="Commercial Hospitality">Commercial &amp; Hospitality</option>
                                    <option value="Spatial 3D Pre-Visualization">Spatial 3D Pre-Visualization Only</option>
                                </select>
                            </div>
                        </div>

                        <!-- Target Budget -->
                        <div>
                            <label for="budget_range" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                                Target Capital Investment
                            </label>
                            <select id="budget_range" name="budget_range" class="form-input text-ivory bg-charcoal-card">
                                <option value="$100,000 - $250,000">$100,000 &ndash; $250,000</option>
                                <option value="$250,000 - $500,000" selected>$250,000 &ndash; $500,000</option>
                                <option value="$500,000 - $1,000,000">$500,000 &ndash; $1,000,000</option>
                                <option value="$1,000,000 - $2,500,000">$1,000,000 &ndash; $2,500,000</option>
                                <option value="$2,500,000+">$2,500,000+</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="client_message" class="block text-xs uppercase tracking-widest text-stone-subtle mb-2 font-medium">
                                Spatial Vision &amp; Site Details *
                            </label>
                            <textarea 
                                id="client_message" 
                                name="message" 
                                rows="5" 
                                required 
                                placeholder="Please describe the property location, current layout challenges, architectural inspirations, or timeline expectations..." 
                                class="form-input resize-y"
                            ><?= $prefilled_project ? 'Inquiry regarding ' . e($prefilled_project) . ': ' : '' ?></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" id="form-submit-btn" class="btn-brass w-full py-4 space-x-2">
                                <span id="submit-btn-text">Submit Private Commission</span>
                                <span id="submit-btn-spinner" class="hidden animate-spin">&#9696;</span>
                            </button>
                        </div>

                        <p class="text-[11px] text-stone-subtle text-center pt-2">
                            By submitting this inquiry, you agree to our confidential consultation protocol.
                        </p>
                    </form>
                </div>
            </div>

            <!-- Right Atelier Info (5 cols) -->
            <div class="lg:col-span-5 space-y-8">
                
                <div class="luxury-card p-8 space-y-6">
                    <span class="editorial-tag">Studio Atelier</span>
                    <h3 class="font-serif text-2xl text-ivory"><?= e(get_setting('site_name', APP_NAME)) ?></h3>
                    
                    <div class="space-y-4 text-xs text-stone-subtle leading-relaxed">
                        <div>
                            <strong class="text-ivory block uppercase tracking-widest text-[10px] mb-1">Physical Address</strong>
                            <p class="text-ivory text-sm"><?= e(get_setting('studio_address', APP_ADDRESS)) ?></p>
                        </div>

                        <div class="pt-3 border-t border-white/5">
                            <strong class="text-ivory block uppercase tracking-widest text-[10px] mb-1">Direct Telephone</strong>
                            <?php $c_phone = get_setting('contact_phone', APP_PHONE); ?>
                            <p class="text-ivory text-sm"><a href="tel:<?= preg_replace('/[^\d+]/', '', $c_phone) ?>" class="hover:text-brass transition-colors"><?= e($c_phone) ?></a></p>
                        </div>

                        <div class="pt-3 border-t border-white/5">
                            <strong class="text-ivory block uppercase tracking-widest text-[10px] mb-1">Electronic Inquiries</strong>
                            <?php $c_email = get_setting('contact_email', APP_EMAIL); ?>
                            <p class="text-ivory text-sm"><a href="mailto:<?= e($c_email) ?>" class="hover:text-brass transition-colors"><?= e($c_email) ?></a></p>
                        </div>

                        <div class="pt-3 border-t border-white/5">
                            <strong class="text-ivory block uppercase tracking-widest text-[10px] mb-1">Private Consultations</strong>
                            <p><?= nl2br(e(get_setting('working_hours', 'Monday – Friday: 09:00 – 18:00 EST | Saturday: By Executive Appointment Only'))) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Location / Google Maps Card -->
                <?php $maps_embed = get_setting('google_maps_iframe'); if (!empty($maps_embed)): ?>
                    <div class="luxury-card p-4 overflow-hidden bg-charcoal-dark border border-brass/20">
                        <div class="aspect-[16/9] w-full overflow-hidden rounded">
                            <?php if (str_starts_with(trim($maps_embed), '<iframe')): ?>
                                <?= $maps_embed ?>
                            <?php else: ?>
                                <iframe src="<?= e($maps_embed) ?>" class="w-full h-full border-0" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="luxury-card p-6 relative overflow-hidden bg-charcoal-dark border border-brass/20">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs uppercase tracking-widest text-brass font-mono">Design District</span>
                            <span class="w-2 h-2 rounded-full bg-brass animate-pulse"></span>
                        </div>
                        <p class="text-xs text-stone-subtle leading-relaxed mb-4">
                            <?= e(get_setting('studio_address', APP_ADDRESS)) ?>
                        </p>
                        <div class="aspect-[16/9] bg-charcoal border border-white/10 flex items-center justify-center relative overflow-hidden">
                            <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: radial-gradient(circle, #554836 10%, #1A1A1A 90%);"></div>
                            <div class="relative z-10 text-center p-4">
                                <span class="font-serif text-lg text-ivory block"><?= e(get_setting('site_name', APP_NAME)) ?></span>
                                <span class="text-[10px] uppercase text-brass tracking-widest"><?= e(get_setting('site_tagline', APP_TAGLINE)) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
</section>

<?php
$extra_scripts = ['assets/js/contact-form.js'];
require_once __DIR__ . '/includes/footer.php';
?>
