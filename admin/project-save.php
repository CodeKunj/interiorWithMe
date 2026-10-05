<?php
/**
 * Amak Interior - Admin Project Save Action Handler
 * Handles validation, secure file uploads, image hashing, and PDO transaction
 * PHP 8.2+
 */

declare(strict_types=1);

define('AMAK_INIT', true);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . asset_url('admin/projects.php'));
    exit;
}

// 1. Verify CSRF Token
require_csrf_token();

$id                = (int)($_POST['id'] ?? 0);
$is_edit           = $id > 0;
$title             = trim($_POST['title'] ?? '');
$slug              = slugify($_POST['slug'] ?? $title);
$category          = trim($_POST['category'] ?? 'Living Room');
$location          = trim($_POST['location'] ?? 'Manhattan, New York');
$year              = trim($_POST['year'] ?? (string)date('Y'));
$area              = trim($_POST['area'] ?? '3,500 sq ft');
$client_type       = trim($_POST['client_type'] ?? 'Private Residence');
$short_description = trim($_POST['short_description'] ?? '');
$full_description  = trim($_POST['full_description'] ?? '');
$display_order     = (int)($_POST['display_order'] ?? 0);
$is_featured       = isset($_POST['is_featured']) ? 1 : 0;

$pdo = get_db_connection();

// 2. Validate Required Fields
if (empty($title) || empty($short_description) || empty($full_description)) {
    set_flash_message('error', 'Please fill in all required text fields.');
    header('Location: ' . asset_url($is_edit ? "admin/project-form.php?id={$id}" : 'admin/project-form.php'));
    exit;
}

// 3. Check Slug Uniqueness
$slugCheck = $pdo->prepare("SELECT id FROM projects WHERE slug = ? AND id != ? LIMIT 1");
$slugCheck->execute([$slug, $id]);
if ($slugCheck->fetch()) {
    $slug = $slug . '-' . time();
}

// Fetch existing record if editing
$existing = null;
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        set_flash_message('error', 'Project not found.');
        header('Location: ' . asset_url('admin/projects.php'));
        exit;
    }
}

$thumbnail_path = $existing['thumbnail'] ?? '';
$glb_path       = $existing['glb_model'] ?? null;
$pano_path      = $existing['panorama_image'] ?? null;

// Helper: Secure Image Upload Processor
function process_image_upload(array $file, string $prefix = 'img'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        return null;
    }

    if ($file['size'] > MAX_IMAGE_SIZE) {
        return null;
    }

    $mime = null;
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    } else {
        $mime = $file['type'] ?? 'image/jpeg';
    }

    if (!$mime || !in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        return null;
    }

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    $ext = $extensions[$mime] ?? 'jpg';
    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target = DIR_PROJECT_UPLOADS . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/projects/' . $filename;
    }

    return null;
}

// 4. Handle Primary Thumbnail Upload
if (!empty($_FILES['thumbnail']['name'])) {
    $uploaded_thumb = process_image_upload($_FILES['thumbnail'], 'thumb');
    if ($uploaded_thumb) {
        $thumbnail_path = $uploaded_thumb;
    } elseif (!$is_edit) {
        set_flash_message('error', 'Thumbnail file upload failed. Please upload a valid JPG, PNG, or WebP under 8MB.');
        header('Location: ' . asset_url('admin/project-form.php'));
        exit;
    }
}

// 5. Handle Optional GLB 3D Model Upload
if (!empty($_FILES['glb_model']['name']) && $_FILES['glb_model']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['glb_model'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (in_array($ext, ALLOWED_MODEL_EXTENSIONS, true) && $file['size'] <= MAX_MODEL_SIZE) {
        $filename = 'model_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target = DIR_MODEL_UPLOADS . DIRECTORY_SEPARATOR . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $glb_path = 'uploads/models/' . $filename;
        }
    }
}

// 6. Handle Optional 360 Panorama Image Upload
if (!empty($_FILES['panorama_image']['name'])) {
    $uploaded_pano = process_image_upload($_FILES['panorama_image'], 'pano');
    if ($uploaded_pano) {
        $pano_path = $uploaded_pano;
    }
}

// 7. Execute Database Transaction
try {
    $pdo->beginTransaction();

    if ($is_edit) {
        $updateStmt = $pdo->prepare("
            UPDATE projects SET 
                title = ?, slug = ?, category = ?, location = ?, year = ?, area = ?, client_type = ?,
                short_description = ?, full_description = ?, thumbnail = ?, glb_model = ?, panorama_image = ?,
                display_order = ?, is_featured = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([
            $title, $slug, $category, $location, $year, $area, $client_type,
            $short_description, $full_description, $thumbnail_path, $glb_path, $pano_path,
            $display_order, $is_featured, $id
        ]);
        $project_id = $id;
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO projects (
                title, slug, category, location, year, area, client_type,
                short_description, full_description, thumbnail, glb_model, panorama_image,
                display_order, is_featured, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $insertStmt->execute([
            $title, $slug, $category, $location, $year, $area, $client_type,
            $short_description, $full_description, $thumbnail_path, $glb_path, $pano_path,
            $display_order, $is_featured
        ]);
        $project_id = (int)$pdo->lastInsertId();
    }

    // 8. Delete selected gallery images if checked
    if (!empty($_POST['delete_gallery_ids']) && is_array($_POST['delete_gallery_ids'])) {
        $delPlaceholders = implode(',', array_fill(0, count($_POST['delete_gallery_ids']), '?'));
        $delStmt = $pdo->prepare("DELETE FROM project_images WHERE project_id = ? AND id IN ($delPlaceholders)");
        $delParams = array_merge([$project_id], array_map('intval', $_POST['delete_gallery_ids']));
        $delStmt->execute($delParams);
    }

    // 9. Process Multiple Gallery File Uploads
    if (!empty($_FILES['gallery_files']['name'][0])) {
        $total_files = count($_FILES['gallery_files']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            $single_file = [
                'name'     => $_FILES['gallery_files']['name'][$i],
                'type'     => $_FILES['gallery_files']['type'][$i],
                'tmp_name' => $_FILES['gallery_files']['tmp_name'][$i],
                'error'    => $_FILES['gallery_files']['error'][$i],
                'size'     => $_FILES['gallery_files']['size'][$i],
            ];
            $saved_gallery = process_image_upload($single_file, 'gallery');
            if ($saved_gallery) {
                $gIns = $pdo->prepare("INSERT INTO project_images (project_id, image_path, alt_text, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())");
                $alt_text = $title . ' - Detail view ' . ($i + 1);
                $gIns->execute([$project_id, $saved_gallery, $alt_text, $i + 1]);
            }
        }
    }

    $pdo->commit();
    set_flash_message('success', "Monograph '{$title}' saved successfully.");
    header('Location: ' . asset_url('admin/projects.php'));
    exit;

} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Project save database error: " . $e->getMessage());
    set_flash_message('error', 'Database save error: ' . $e->getMessage());
    header('Location: ' . asset_url($is_edit ? "admin/project-form.php?id={$id}" : 'admin/project-form.php'));
    exit;
}
