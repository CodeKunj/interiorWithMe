/**
 * Amak Interior - Contact Form Validation & Submission
 * Client-side validation, honeypot verification, and seamless AJAX submission
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('studio-contact-form');
    if (!form) return;

    const submitBtn = document.getElementById('form-submit-btn');
    const submitText = document.getElementById('submit-btn-text');
    const submitSpinner = document.getElementById('submit-btn-spinner');
    const feedbackBox = document.getElementById('form-feedback');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Honeypot Anti-Spam Check
        const honeypot = form.querySelector('input[name="website_url"]');
        if (honeypot && honeypot.value.trim() !== '') {
            console.warn('Spam submission suppressed.');
            return false;
        }

        // 2. Client-Side Field Validation
        const name = form.querySelector('#client_name')?.value.trim();
        const email = form.querySelector('#client_email')?.value.trim();
        const message = form.querySelector('#client_message')?.value.trim();

        if (!name || !email || !message) {
            showFeedback('error', 'Please complete all required fields (Name, Email, and Project Details).');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showFeedback('error', 'Please provide a valid email address.');
            return;
        }

        // 3. UI Loading State
        setLoadingState(true);

        // 4. Submit via Fetch / FormData
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action || 'contact.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json().catch(() => null);

            if (response.ok && result && result.success) {
                showFeedback('success', result.message || 'Thank you. Your consultation request has been received. Our principal concierge will be in touch within 24 hours.');
                form.reset();
            } else {
                showFeedback('error', result?.message || 'An error occurred while transmitting your request. Please call our studio directly.');
            }
        } catch (err) {
            console.error('Submission error:', err);
            // In case fetch fails or non-JSON fallback
            form.submit();
        } finally {
            setLoadingState(false);
        }
    });

    function setLoadingState(isLoading) {
        if (!submitBtn) return;
        submitBtn.disabled = isLoading;
        if (isLoading) {
            if (submitText) submitText.textContent = 'Transmitting Vision...';
            if (submitSpinner) submitSpinner.classList.remove('hidden');
        } else {
            if (submitText) submitText.textContent = 'Submit Private Commission';
            if (submitSpinner) submitSpinner.classList.add('hidden');
        }
    }

    function showFeedback(type, message) {
        if (!feedbackBox) return;
        feedbackBox.innerHTML = message;
        feedbackBox.classList.remove('hidden', 'bg-emerald-950/80', 'border-emerald-500/50', 'text-emerald-200', 'bg-red-950/80', 'border-red-500/50', 'text-red-200');

        if (type === 'success') {
            feedbackBox.classList.add('bg-emerald-950/80', 'border', 'border-emerald-500/50', 'text-emerald-200');
        } else {
            feedbackBox.classList.add('bg-red-950/80', 'border', 'border-red-500/50', 'text-red-200');
        }

        feedbackBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
