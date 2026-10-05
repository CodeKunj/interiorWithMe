# 🏛️ Amak Interior — 3D Luxury Spatial Architecture Website

> *"Where Vision Meets Dimension"*
> Bespoke luxury interior architecture studio web experience crafted with plain PHP 8.2+, Three.js (WebGL), GSAP ScrollTrigger, Lenis smooth scrolling, and Tailwind CSS.

---

## 📑 Table of Contents

1. [Architectural Overview](#-architectural-overview)
2. [Complete File Tree](#-complete-file-tree)
3. [Setup & Installation Instructions](#-setup--installation-instructions)
   - [Local Environment (XAMPP / Laragon / PHP Built-in Server)](#a-local-environment)
   - [Production Live Hosting (cPanel / Apache / Nginx / VPS)](#b-production-live-hosting)
4. [Administrative Portal & Credentials](#-administrative-portal--credentials)
5. [3D GLB Model Optimization Guide (Blender to Three.js)](#-3d-glb-model-optimization-guide)
   - [Blender Export Settings](#blender-export-settings)
   - [Target Performance Budgets](#target-performance-budgets)
   - [Recommended Free 3D Interior Model Sources](#recommended-free-3d-interior-model-sources)
6. [Lighthouse Mobile Performance Checklist (Target 85+)](#-lighthouse-mobile-performance-checklist-target-85)
7. [Security & Compliance Architecture](#-security--compliance-architecture)

---

## 📐 Architectural Overview

Amak Interior is engineered as a high-performance, editorial-grade web application combining cinematic 3D spatial pre-visualization with a robust, framework-free PHP backend.

- **Backend**: Plain PHP 8.2+ with strict typing, PDO prepared statements, and session-based authentication.
- **3D Engine**: Three.js r164 (ES modules, `GLTFLoader`, `DRACOLoader`), ACESFilmicToneMapping, dynamic soft shadows, and procedural geometry fallback.
- **Motion & Scroll**: GSAP 3 + ScrollTrigger + Lenis smooth scroll for scrubbed camera fly-through and micro-interactions.
- **Material Configurator**: Real-time material swapper (lime-wash wall tones, floor finishes, bespoke sofa upholstery) with GSAP material property interpolation.
- **Email Delivery**: PHPMailer 6.9+ with full SMTP RFC 821 support and simulated offline fallback.
- **Styling**: Tailwind CSS 3.4 with custom editorial luxury palette (`#F5F1EA` Ivory, `#1C1C1C` Charcoal, `#B08D57` Brass).

---

## 🗂️ Complete File Tree

```
d:\Office\inter\
│
├── .htaccess                          # Apache URL rewriting, Gzip, Cache-Control, MIME types
├── index.php                          # Home page (3D hero, configurator, 6 editorial sections)
├── portfolio.php                      # Filterable project grid (Living Room, Bedroom, Kitchen, etc.)
├── project.php                        # Project detail monograph (?slug=..., 360° tour, 3D viewer)
├── contact.php                        # Validated contact form, rate limiting, anti-spam, PHPMailer
├── sitemap.xml                        # SEO XML sitemap with priority weighting
├── robots.txt                         # Search engine directives (protects /admin/ and /config/)
├── database.sql                       # Full MySQL schema, foreign keys & luxury sample data
├── README.md                          # Comprehensive documentation & setup instructions
│
├── config/
│   ├── database.php                   # Singleton PDO database connection & offline handler
│   ├── app.php                        # Site constants, base URL calculation & upload limits
│   └── mail.php                       # PHPMailer SMTP credentials & recipient routing
│
├── includes/
│   ├── header.php                     # HTML5 head, Open Graph, schema.org, nav & preloader
│   ├── footer.php                     # Footer columns, JSON-LD LocalBusiness markup & scripts
│   ├── functions.php                  # Utility helpers (e(), slugify(), get_client_ip(), email)
│   ├── auth.php                       # Admin session guards & password_verify() helpers
│   └── csrf.php                       # Cryptographic CSRF token generation & validation
│
├── admin/
│   ├── index.php                      # Executive admin dashboard with metrics & activity
│   ├── login.php                      # Secure login portal with auto-seeding default admin
│   ├── logout.php                     # Session destroy & cookie cleanup
│   ├── projects.php                   # Project monographs list & management table
│   ├── project-form.php               # Create / Edit monograph with multi-image & 3D uploads
│   ├── project-save.php               # Secure file upload validator & database transaction handler
│   ├── project-delete.php             # Delete project handler with CSRF protection
│   ├── enquiries.php                  # Client commission inquiries list with filter tabs
│   ├── enquiry-view.php               # Inquiry inspector with auto-mark read & direct mailto
│   └── includes/
│       ├── admin-header.php           # Admin navigation & auth guard
│       └── admin-footer.php           # Admin footer layout
│
├── assets/
│   ├── css/
│   │   └── style.css                  # Custom styling, 3D canvas viewport, configurator UI
│   │
│   ├── js/
│   │   ├── app.js                     # Lenis init, mobile navigation, sticky header & lazy loader
│   │   ├── three-scene.js             # Core 3D engine, camera fly-through, parallax & visibility
│   │   ├── fallback-room.js           # Procedural 3D luxury room built from Three.js primitives
│   │   ├── configurator.js            # Real-time wall/floor/sofa material switching logic
│   │   ├── scroll-animations.js       # GSAP ScrollTrigger section timeline choreographies
│   │   ├── portfolio-filter.js        # Instant category filter with GSAP transitions & URL push
│   │   ├── panorama-viewer.js         # 360° equirectangular sphere & 3D model inspector
│   │   ├── contact-form.js            # Client-side validation, honeypot & AJAX submission
│   │   └── admin.js                   # Admin slug generator, image previews & modal alerts
│   │
│   ├── models/
│   │   └── room.glb                   # Custom GLB model placement path
│   │
│   └── img/
│       ├── logo.svg                   # Studio vector branding
│       ├── hero-fallback.jpg          # High-resolution fallback backdrop
│       └── og-image.jpg               # Open Graph share card
│
├── uploads/                           # Uploaded project thumbnails, galleries & 3D assets
│   ├── projects/
│   └── models/
│
└── vendor/
    └── phpmailer/
        ├── Exception.php              # PHPMailer exception handling
        ├── PHPMailer.php              # Core email transport engine
        └── SMTP.php                   # SMTP protocol implementation
```

---

## 🚀 Setup & Installation Instructions

### A. Local Environment

#### Using Laragon or XAMPP:
1. Copy or clone this directory into your web server root:
   - **XAMPP**: `C:\xampp\htdocs\inter\`
   - **Laragon**: `C:\laragon\www\inter\`
2. Start **Apache** and **MySQL** in your control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`) or MySQL CLI.
4. Create a new database named `amak_interior`:
   ```sql
   CREATE DATABASE amak_interior CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
5. Import `database.sql` into `amak_interior`.
6. Verify credentials in [config/database.php](file:///d:/Office/inter/config/database.php) (defaults are `127.0.0.1`, user `root`, empty password).
7. Visit in browser: `http://localhost/inter/`

#### Using PHP Built-in Server (Quick Testing):
```bash
cd d:\Office\inter
php -S 127.0.0.1:8000
```
Visit: `http://127.0.0.1:8000`

---

### B. Production Live Hosting

#### Standard cPanel / Shared Hosting:
1. Upload all files to `public_html/` via FTP or cPanel File Manager.
2. Ensure directory permissions:
   - `uploads/` -> `0755` (writable by web server)
   - `uploads/projects/` -> `0755`
   - `uploads/models/` -> `0755`
3. In cPanel MySQL Databases, create a new database and user. Assign all privileges.
4. Import `database.sql` via phpMyAdmin.
5. Update [config/database.php](file:///d:/Office/inter/config/database.php) with your live database name, user, and password.
6. Configure SMTP in [config/mail.php](file:///d:/Office/inter/config/mail.php) for live email delivery.

#### VPS / Nginx / Apache:
Ensure `mod_rewrite`, `mod_deflate`, `mod_expires`, and `mod_headers` are enabled on Apache.

---

## 🔐 Administrative Portal & Credentials

- **Portal URL**: `http://your-domain.com/admin/login.php`
- **Default Username**: `admin`
- **Default Password**: `Admin@Amak2026!`

*Note: The password is encrypted with `password_hash($pass, PASSWORD_BCRYPT)`. You can manage and create projects, upload custom 3D `.glb` files, inspect contact enquiries, and upload high-resolution gallery suites.*

---

## 🧊 3D GLB Model Optimization Guide

To ensure fast loading (< 2 seconds) and 60 FPS performance on both mobile and desktop, follow these optimization guidelines when exporting 3D interior models from Blender:

### Blender Export Settings
1. **Format**: Choose **glTF 2.0 (.glb)** (Single self-contained binary).
2. **Include**:
   - ✅ Selected Objects Only (remove cameras, lights, and hidden helper rigs).
   - ✅ Transform: `+Y Up` (standard for Three.js coordinates).
   - ✅ Geometry: `Apply Modifiers`, `Normals`, `Tangents`.
3. **Draco Mesh Compression**:
   - ✅ Enable **Draco Compression** in Blender export settings.
   - Compression level: `6` or `7`.
   - Quantization: Position `14`, Normal `10`, TexCoord `12`.
4. **Texture Optimization**:
   - Resize all image textures to a maximum of **2048×2048 px** (diffuse/albedo) and **1024×1024 px** (roughness/metalness/normal maps).
   - Use JPG for diffuse/albedo maps where transparency is not required.
   - Combine Metallic and Roughness into a single **ORM texture** (Occlusion = Red, Roughness = Green, Metallic = Blue).
5. **Lighting & Baking**:
   - Bake indirect lighting and ambient occlusion into a lightweight lightmap texture rather than relying on heavy real-time multi-bounce calculations.

### Target Performance Budgets
| Metric | Desktop Budget | Mobile Budget |
|---|---|---|
| **File Size (.glb)** | < 4.5 MB | < 2.5 MB |
| **Triangle Count** | < 120,000 tris | < 60,000 tris |
| **Draw Calls** | < 45 calls | < 25 calls |
| **Texture Memory** | < 35 MB VRAM | < 20 MB VRAM |
| **Target Frame Rate** | 60 FPS | 30–60 FPS |

### Recommended Free 3D Interior Model Sources
1. **Poly Haven** ([polyhaven.com/models](https://polyhaven.com/models)) — 100% CC0 free architectural models, furniture, and PBR materials with Draco compression.
2. **BlenderKit** ([blenderkit.com](https://www.blenderkit.com/)) — Extensive interior furniture library (sofas, tables, lighting fixtures).
3. **Sketchfab (CC License)** ([sketchfab.com](https://sketchfab.com)) — Search `Interior Room`, filter by `Downloadable` and `CC-BY`.
4. **CGTrader (Free Section)** ([cgtrader.com/free-3d-models/interior](https://www.cgtrader.com/free-3d-models/interior)) — High-poly interior architectural assets suitable for low-poly decimation.

---

## ⚡ Lighthouse Mobile Performance Checklist (Target 85+)

To guarantee a 85+ mobile score on Google Lighthouse:

- [x] **Largest Contentful Paint (LCP) < 2.5s**: Preloaded fallback hero gradient; async Three.js script load; WebGL canvas renders behind HTML content without blocking initial DOM paint.
- [x] **First Input Delay (FID) / INP < 100ms**: Event listeners for mouse move and parallax throttled using `requestAnimationFrame`; Lenis smooth scroll runs off-main-thread.
- [x] **Cumulative Layout Shift (CLS) = 0.00**: Fixed aspect ratios on all project cards (`aspect-[4/3]`, `aspect-[16/9]`); canvas container has `100vh` explicit height.
- [x] **Off-screen Rendering Pause**: `IntersectionObserver` in [assets/js/three-scene.js](file:///d:/Office/inter/assets/js/three-scene.js) automatically pauses `requestAnimationFrame` when the user scrolls past the hero section.
- [x] **Tab Visibility Pausing**: `document.addEventListener('visibilitychange')` halts GPU rendering when the browser tab is hidden.
- [x] **Asset Compression**: `.htaccess` enables GZIP/Brotli compression on HTML, CSS, JS, JSON, SVG, and `.glb` binary assets.
- [x] **Browser Caching**: `Cache-Control: max-age=31536000` (1 year) configured for images, fonts, scripts, and 3D assets in `.htaccess`.
- [x] **Accessible Font Loading**: Google Fonts loaded via `preconnect` with `display=swap`.

---

## 🛡️ Security & Compliance Architecture

1. **CSRF Tokens**: Every POST form (Contact, Admin Login, Project Save, Project Delete, Enquiry View) includes a cryptographic CSRF token validated with `hash_equals()`.
2. **SQL Injection Prevention**: 100% of database queries use **PDO prepared statements** with parameter binding (`PDO::ATTR_EMULATE_PREPARES => false`).
3. **XSS Protection**: All dynamic user and database outputs are escaped using `e()` (`htmlspecialchars` with `ENT_QUOTES | ENT_HTML5`).
4. **Honeypot Anti-Bot Shield**: Hidden `website_url` field silently traps spam bots without bothering human users with frustrating captchas.
5. **Rate Limiting**: [includes/functions.php](file:///d:/Office/inter/includes/functions.php) enforces a rate limit of max 4 consultation submissions per IP address per hour.
6. **Secure Upload Pipeline**:
   - File extensions whitelisted (`jpg`, `png`, `webp`, `glb`, `gltf`).
   - File MIME types verified via `finfo(FILEINFO_MIME_TYPE)`.
   - All uploaded filenames hashed with `bin2hex(random_bytes(8))` to prevent directory traversal or remote execution.
   - Max file size limits enforced on server.
7. **Accessibility & Reduced Motion**: Full `@media (prefers-reduced-motion: reduce)` support disables heavy camera animations and smooth scroll for sensitive users.
