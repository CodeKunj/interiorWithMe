/**
 * Amak Interior - Admin Panel Client Logic
 * Slug generation, image previews, dynamic image fields & modal confirmations
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-generate Slug from Title
    const titleInput = document.getElementById('project_title');
    const slugInput = document.getElementById('project_slug');

    if (titleInput && slugInput && slugInput.value.trim() === '') {
        titleInput.addEventListener('input', () => {
            const slug = titleInput.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        });
    }

    // 2. Thumbnail Preview
    const thumbInput = document.getElementById('project_thumbnail');
    const thumbPreview = document.getElementById('thumbnail_preview');

    if (thumbInput && thumbPreview) {
        thumbInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    thumbPreview.src = e.target.result;
                    thumbPreview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // 3. Delete Confirmation Guard
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const itemName = this.getAttribute('data-item-name') || 'this item';
            if (!confirm(`Are you sure you wish to permanently delete ${itemName}? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    });

    // 4. Auto-dismiss Flash Alerts
    const flashAlert = document.getElementById('admin-flash-alert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.opacity = '0';
            flashAlert.style.transition = 'opacity 0.6s ease';
            setTimeout(() => flashAlert.remove(), 600);
        }, 4000);
    }
});
