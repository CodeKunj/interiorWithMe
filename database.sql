-- ==============================================================================
-- Amak Interior - Database Schema & Initial Data
-- MySQL 8.0+ / MariaDB 10.4+
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist (for clean re-installation)
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `project_images`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `enquiries`;
DROP TABLE IF EXISTS `testimonials`;
DROP TABLE IF EXISTS `admins`;

-- ------------------------------------------------------------------------------
-- Table structure for `settings`
-- ------------------------------------------------------------------------------
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` LONGTEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
-- General Settings
('site_name', 'Amak Interior', 'general'),
('site_tagline', 'Where Vision Meets Dimension', 'general'),
('site_description', 'Bespoke luxury interior architecture and experiential spatial design studio based in Manhattan, New York. Crafting timeless sanctuaries through vision and dimension.', 'general'),
('site_logo', '', 'general'),
('site_favicon', '', 'general'),
('currency_symbol', '$', 'general'),
('copyright_text', '© 2026 Amak Interior Studio LLC. All rights reserved.', 'general'),
('primary_color', '#B08D57', 'general'),

-- Contact Information
('contact_email', 'concierge@amakinterior.com', 'contact'),
('admin_notification_email', 'admin@amakinterior.com', 'contact'),
('contact_phone', '+1 (212) 555-0198', 'contact'),
('whatsapp_number', '+1 (212) 555-0198', 'contact'),
('whatsapp_floating_button', '1', 'contact'),
('studio_address', '482 Broome Street, Studio 4A, SoHo, New York, NY 10013', 'contact'),
('working_hours', 'By Private Appointment Only | Mon - Sat: 9:00 AM - 7:00 PM', 'contact'),
('google_maps_iframe', '', 'contact'),

-- Social Media Links
('social_instagram', 'https://instagram.com/amakinterior', 'social'),
('social_pinterest', 'https://pinterest.com/amakinterior', 'social'),
('social_linkedin', 'https://linkedin.com/company/amakinterior', 'social'),
('social_facebook', 'https://facebook.com/amakinterior', 'social'),
('social_archdaily', 'https://archdaily.com/professionals/amakinterior', 'social'),
('social_youtube', '', 'social'),
('social_twitter', '', 'social'),

-- SEO & Webmaster
('meta_title', 'Amak Interior | Where Vision Meets Dimension', 'seo'),
('meta_description', 'Bespoke luxury interior architecture and experiential spatial design studio based in Manhattan, New York. Crafting timeless sanctuaries through vision and dimension.', 'seo'),
('meta_keywords', 'luxury interior design, 3D interior architecture, high-end residential design, Manhattan interior designer, custom furniture design, Amak Interior', 'seo'),
('og_image', 'assets/img/og-image.jpg', 'seo'),
('google_analytics_id', '', 'seo'),
('header_scripts', '', 'seo'),
('footer_scripts', '', 'seo'),

-- Email & SMTP
('smtp_enabled', '0', 'smtp'),
('smtp_host', 'smtp.gmail.com', 'smtp'),
('smtp_port', '587', 'smtp'),
('smtp_encryption', 'tls', 'smtp'),
('smtp_username', '', 'smtp'),
('smtp_password', '', 'smtp'),
('smtp_from_email', 'concierge@amakinterior.com', 'smtp'),
('smtp_from_name', 'Amak Interior Concierge', 'smtp'),

-- Hero & 3D Experience
('hero_badge', 'HAUTE COUTURE ARCHITECTURE', 'hero'),
('hero_heading', 'Where Vision Meets Dimension', 'hero'),
('hero_subheading', 'Bespoke residential sanctuaries and spatial architecture crafted with three-dimensional precision.', 'hero'),
('hero_cta_text', 'Explore Commissions', 'hero'),
('hero_cta_link', 'portfolio.php', 'hero'),
('enable_3d_viewer', '1', 'hero'),
('default_glb_model', 'assets/models/room.glb', 'hero'),
('hero_video_url', 'https://storage.googleapis.com/webild/default/templates/marbella/hero/hero.mp4', 'hero');

-- ------------------------------------------------------------------------------
-- Table structure for `admins`
-- Default login: admin / Admin@Amak2026!
-- Hash generated via password_hash('Admin@Amak2026!', PASSWORD_BCRYPT)
-- ------------------------------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL DEFAULT 'Amak Studio Administrator',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `full_name`, `created_at`) VALUES
(1, 'admin', 'admin@amakinterior.com', '$2y$10$d6tQ6rZzI0hU2j5W6X7ZYe0L8U4K7E3F1o9G6Y5W2b8R3t1O4P7qK', 'Amak Studio Principal', NOW());

-- ------------------------------------------------------------------------------
-- Table structure for `projects`
-- ------------------------------------------------------------------------------
CREATE TABLE `projects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `category` VARCHAR(50) NOT NULL,
  `location` VARCHAR(150) NOT NULL DEFAULT 'Manhattan, New York',
  `year` VARCHAR(10) NOT NULL DEFAULT '2025',
  `area` VARCHAR(50) NOT NULL DEFAULT '3,800 sq ft',
  `client_type` VARCHAR(100) NOT NULL DEFAULT 'Private Residence',
  `short_description` TEXT NOT NULL,
  `full_description` LONGTEXT NOT NULL,
  `thumbnail` VARCHAR(255) NOT NULL,
  `glb_model` VARCHAR(255) DEFAULT NULL,
  `panorama_image` VARCHAR(255) DEFAULT NULL,
  `display_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `project_images`
-- ------------------------------------------------------------------------------
CREATE TABLE `project_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(255) DEFAULT 'Interior architectural view',
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_project_images_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `enquiries`
-- ------------------------------------------------------------------------------
CREATE TABLE `enquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `project_type` VARCHAR(100) NOT NULL,
  `budget_range` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `testimonials`
-- ------------------------------------------------------------------------------
CREATE TABLE `testimonials` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(100) NOT NULL,
  `client_title` VARCHAR(150) NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `quote` TEXT NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Sample Projects Data
-- ------------------------------------------------------------------------------
INSERT INTO `projects` (`id`, `title`, `slug`, `category`, `location`, `year`, `area`, `client_type`, `short_description`, `full_description`, `thumbnail`, `glb_model`, `panorama_image`, `display_order`, `is_featured`, `created_at`) VALUES
(1, 'The Tribeca Loft', 'the-tribeca-loft', 'Living Room', 'Tribeca, New York', '2025', '4,200 sq ft', 'Private Collector', 
'A bespoke synthesis of brutalist concrete forms, fluted travertine, and brushed brass fixtures designed for light-drenched downtown living.',
'Situated in a historic 19th-century cast-iron warehouse in Tribeca, this residence underwent a complete spatial reconfiguration. Our goal was to preserve the industrial pedigree while introducing soft architectural silhouettes, natural tactile materiality, and integrated ambient illumination.\n\nThe centerpiece is a 30-foot open salon anchored by monolithic travertine fireplace hearths, custom Italian boucle upholstery, and continuous smoked French oak plank flooring. Acoustic dampening wall panels wrapped in raw linen create an intimate sanctuary within the vibrant city center.',
'assets/img/projects/tribeca-loft-thumb.jpg', 'assets/models/room.glb', 'assets/img/projects/tribeca-loft-pano.jpg', 1, 1, NOW()),

(2, 'The Upper East Penthouse', 'the-upper-east-penthouse', 'Living Room', 'Upper East Side, New York', '2025', '6,100 sq ft', 'Private Family Office',
'Quiet luxury defined through custom fluted walnut paneling, bespoke brass joinery, and panoramic views of Central Park.',
'This duplex penthouse commands sweeping panoramic views over Central Park. We orchestrated a serene architectural envelope utilizing French limestone, bookmatched Calacatta marble, and hand-finished bronze reveals.\n\nEvery lighting element was commissioned as a bespoke fixture to cast warm sculptural gradients across muted plaster walls, highlighting curated modernist art collections and bespoke bronze furniture pieces.',
'assets/img/projects/upper-east-thumb.jpg', NULL, NULL, 2, 1, NOW()),

(3, 'Hudson Sanctuary Suite', 'hudson-sanctuary-suite', 'Bedroom', 'Hudson Valley, New York', '2024', '2,800 sq ft', 'Executive Retreat',
'A restorative master sanctuary harmonizing natural cedar, textured boucle fabrics, and floor-to-ceiling forest vistas.',
'Nestled amongst mature woodland along the Hudson River, this suite is designed as an acoustic and visual sanctuary. Floor-to-ceiling motorized low-iron glazing merges the bedroom with the forest canopy.\n\nCustom integrated millwork houses hidden circadian lighting that shifts color temperature with the solar cycle, complementing hand-loomed wool textiles and solid white ash joinery.',
'assets/img/projects/hudson-suite-thumb.jpg', NULL, NULL, 3, 1, NOW()),

(4, 'SoHo Minimalist Kitchen & Dining', 'soho-minimalist-kitchen', 'Kitchen', 'SoHo, New York', '2024', '1,950 sq ft', 'Culinary Enthusiast',
'Seamless monolith island in fluted quartzite with integrated induction cooking and concealed matte lacquer cabinetry.',
'Designed for entertaining without visual clutter, this culinary space conceals heavy appliances behind architectural pocket doors in hand-rubbed ebonized oak.\n\nThe sculptural 14-foot kitchen island is carved from a single slab of honed Taj Mahal quartzite, suspended visually above recessed warm LED base reveals.',
'assets/img/projects/soho-kitchen-thumb.jpg', NULL, NULL, 4, 0, NOW()),

(5, 'The Madison Spa Residence', 'the-madison-spa-residence', 'Bathroom', 'Madison Avenue, New York', '2025', '1,200 sq ft', 'Private Client',
'A sensory bathing pavilion with freestanding Nero Marquina bath, steam shower, and brass rainfall fixtures.',
'Translating high-end Japanese onsen philosophy into a Manhattan master bathroom. Monolithic dark limestone walls, concealed drain channels, and custom unlacquered brass hardware create a meditative atmosphere for daily decompression.',
'assets/img/projects/madison-bath-thumb.jpg', NULL, NULL, 5, 0, NOW()),

(6, 'Greenwich Creative Studio', 'greenwich-creative-studio', 'Office', 'Greenwich Village, New York', '2024', '3,100 sq ft', 'Architecture Firm HQ',
'An inspiring executive workspace featuring acoustic felt partitions, sculptural lighting, and ergonomic bespoke timber desks.',
'A dual-purpose creative workspace and VIP client lounge in Greenwich Village. Featuring custom soundproof meeting pods, micro-cement seamless flooring, and integrated motorized presentation systems concealed behind raw silk wall hangings.',
'assets/img/projects/greenwich-office-thumb.jpg', NULL, NULL, 6, 0, NOW());

-- ------------------------------------------------------------------------------
-- Sample Project Images
-- ------------------------------------------------------------------------------
INSERT INTO `project_images` (`project_id`, `image_path`, `alt_text`, `sort_order`) VALUES
(1, 'assets/img/projects/tribeca-1.jpg', 'Tribeca Loft main salon with custom furniture and lighting', 1),
(1, 'assets/img/projects/tribeca-2.jpg', 'Detail view of fluted travertine fireplace and bronze accents', 2),
(1, 'assets/img/projects/tribeca-3.jpg', 'Dining alcove with custom oak table and linen drapes', 3),
(2, 'assets/img/projects/upper-east-1.jpg', 'Upper East Penthouse central living area overlooking the park', 1),
(2, 'assets/img/projects/upper-east-2.jpg', 'Custom walnut millwork and marble bar detail', 2),
(3, 'assets/img/projects/hudson-1.jpg', 'Hudson Sanctuary bedroom with panoramic floor-to-ceiling glass', 1),
(3, 'assets/img/projects/hudson-2.jpg', 'Reading corner with bouclé armchair and minimal architectural lamp', 2),
(4, 'assets/img/projects/soho-1.jpg', 'SoHo kitchen island in honed Taj Mahal quartzite', 1),
(5, 'assets/img/projects/madison-1.jpg', 'Madison bathroom with Nero Marquina freestanding tub', 1),
(6, 'assets/img/projects/greenwich-1.jpg', 'Greenwich creative studio conference and lounge zone', 1);

-- ------------------------------------------------------------------------------
-- Sample Testimonials Data
-- ------------------------------------------------------------------------------
INSERT INTO `testimonials` (`id`, `client_name`, `client_title`, `location`, `quote`, `rating`, `is_active`, `sort_order`) VALUES
(1, 'Eleanor Vance-Sterling', 'Art Patron & Collector', 'Tribeca, New York', 
'Amak Interior transformed our raw loft into a sublime, tactile work of living art. The 3D spatial pre-visualization was so precise that stepping into the completed space felt like walking into our dream made tangible.', 5, 1, 1),

(2, 'Julian & Clara Beaumont', 'Founders, Beaumont Capital', 'Upper East Side, New York', 
'Their uncompromising command of proportions, rare stones, and custom lighting fixtures sets Amak apart. They delivered our penthouse on schedule with an editorial level of refinement that exceeded all expectations.', 5, 1, 2),

(3, 'Marcus Thorne', 'Architect & Developer', 'Hudson Valley, New York', 
'Collaborating with Amak on our private estate was an absolute masterclass in craft. The integration between organic landscape and interior serenity is seamless. Truly world-class designers.', 5, 1, 3);

SET FOREIGN_KEY_CHECKS = 1;
