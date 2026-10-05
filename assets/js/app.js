/**
 * Amak Interior - Core Application Orchestrator
 * Lenis Smooth Scroll, Mobile Navigation, Header State & Global Utilities
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Lenis Smooth Scroll
    let lenis = null;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion && typeof Lenis !== 'undefined') {
        lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            orientation: 'vertical',
            gestureOrientation: 'vertical',
            smoothWheel: true,
            wheelMultiplier: 0.9,
            touchMultiplier: 1.5,
        });

        if (typeof ScrollTrigger !== 'undefined') {
            lenis.on('scroll', ScrollTrigger.update);
        }

        gsap.ticker.add((time) => {
            lenis.raf(time * 1000);
        });
        gsap.ticker.lagSmoothing(0);

        window.lenisInstance = lenis;
    }

    // 2. Sticky Header Scroll State
    const header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('py-4', 'bg-charcoal/90', 'shadow-2xl');
                header.classList.remove('py-6', 'bg-charcoal/40');
            } else {
                header.classList.add('py-6', 'bg-charcoal/40');
                header.classList.remove('py-4', 'bg-charcoal/90', 'shadow-2xl');
            }
        });
    }

    // 3. Fullscreen Mobile Navigation
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

    function openMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.remove('opacity-0', 'pointer-events-none');
        mobileMenu.classList.add('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = 'hidden';
        if (lenis) lenis.stop();
    }

    function closeMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.add('opacity-0', 'pointer-events-none');
        mobileMenu.classList.remove('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = '';
        if (lenis) lenis.start();
    }

    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileMenu);
    if (mobileMenuClose) mobileMenuClose.addEventListener('click', closeMobileMenu);
    mobileNavLinks.forEach(link => link.addEventListener('click', closeMobileMenu));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('opacity-100')) {
            closeMobileMenu();
        }
    });

    // 4. Smooth Anchor Link Scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();
                if (lenis) {
                    lenis.scrollTo(targetEl, { offset: -80 });
                } else {
                    targetEl.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // 5. Image Lazy Loading with Skeleton Fade-in
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    lazyImages.forEach(img => {
        if (img.complete) {
            img.classList.remove('opacity-0');
        } else {
            img.addEventListener('load', () => {
                img.classList.remove('opacity-0');
                const skeleton = img.previousElementSibling;
                if (skeleton && skeleton.classList.contains('image-skeleton')) {
                    skeleton.classList.add('hidden');
                }
            });
        }
    });

    // 6. Hero Mode Switcher (Cinematic Film vs 3D Spatial Twin)
    const btnModeFilm = document.getElementById('mode-btn-film');
    const btnMode3D = document.getElementById('mode-btn-3d');
    const heroVideoWrap = document.getElementById('hero-video-wrap');
    const heroBgVideo = document.getElementById('hero-bg-video');
    const webglCanvas = document.getElementById('webgl-canvas');
    const configuratorDock = document.getElementById('configurator-dock');
    const videoControlsPill = document.getElementById('video-controls-pill');

    function setHeroMode(mode) {
        if (mode === 'film') {
            if (btnModeFilm) {
                btnModeFilm.classList.add('bg-brass', 'text-charcoal-dark', 'font-semibold');
                btnModeFilm.classList.remove('text-stone-subtle', 'font-medium');
            }
            if (btnMode3D) {
                btnMode3D.classList.remove('bg-brass', 'text-charcoal-dark', 'font-semibold');
                btnMode3D.classList.add('text-stone-subtle', 'font-medium');
            }
            if (heroVideoWrap) heroVideoWrap.classList.remove('opacity-0', 'pointer-events-none');
            if (videoControlsPill) videoControlsPill.classList.remove('opacity-0', 'pointer-events-none');
            if (webglCanvas) {
                webglCanvas.classList.remove('opacity-100', 'pointer-events-auto');
                webglCanvas.classList.add('opacity-0', 'pointer-events-none');
            }
            if (configuratorDock) {
                configuratorDock.classList.remove('opacity-100', 'pointer-events-auto');
                configuratorDock.classList.add('opacity-0', 'pointer-events-none');
            }
            if (heroBgVideo) {
                if (!window.heroVideoAutoPlaying) {
                    heroBgVideo.pause();
                }
            }
        } else if (mode === '3d') {
            if (btnMode3D) {
                btnMode3D.classList.add('bg-brass', 'text-charcoal-dark', 'font-semibold');
                btnMode3D.classList.remove('text-stone-subtle', 'font-medium');
            }
            if (btnModeFilm) {
                btnModeFilm.classList.remove('bg-brass', 'text-charcoal-dark', 'font-semibold');
                btnModeFilm.classList.add('text-stone-subtle', 'font-medium');
            }
            if (heroVideoWrap) heroVideoWrap.classList.add('opacity-0', 'pointer-events-none');
            if (videoControlsPill) videoControlsPill.classList.add('opacity-0', 'pointer-events-none');
            if (webglCanvas) {
                webglCanvas.classList.remove('opacity-0', 'pointer-events-none');
                webglCanvas.classList.add('opacity-100', 'pointer-events-auto');
            }
            if (configuratorDock) {
                configuratorDock.classList.remove('opacity-0', 'pointer-events-none');
                configuratorDock.classList.add('opacity-100', 'pointer-events-auto');
            }
            if (heroBgVideo) {
                heroBgVideo.pause();
            }
        }
    }

    if (btnModeFilm) btnModeFilm.addEventListener('click', () => setHeroMode('film'));
    if (btnMode3D) btnMode3D.addEventListener('click', () => setHeroMode('3d'));

    // Video Auto-Play vs Scroll-Scrub Toggle
    window.heroVideoAutoPlaying = false;
    const btnToggleVideo = document.getElementById('btn-toggle-video');
    const playIcon = document.getElementById('video-play-icon');
    const playText = document.getElementById('video-play-text');

    if (btnToggleVideo && heroBgVideo) {
        btnToggleVideo.addEventListener('click', () => {
            window.heroVideoAutoPlaying = !window.heroVideoAutoPlaying;
            if (window.heroVideoAutoPlaying) {
                heroBgVideo.play().catch(() => {});
                if (playIcon) playIcon.textContent = '📜';
                if (playText) playText.textContent = 'Scroll Mode';
            } else {
                heroBgVideo.pause();
                if (playIcon) playIcon.textContent = '▶';
                if (playText) playText.textContent = 'Auto Play';
            }
        });
    }

    const btnToggleSound = document.getElementById('btn-toggle-sound');
    const soundIcon = document.getElementById('video-sound-icon');
    const soundText = document.getElementById('video-sound-text');

    if (btnToggleSound && heroBgVideo) {
        btnToggleSound.addEventListener('click', () => {
            heroBgVideo.muted = !heroBgVideo.muted;
            if (heroBgVideo.muted) {
                if (soundIcon) soundIcon.textContent = '🔇';
                if (soundText) soundText.textContent = 'Muted';
            } else {
                if (soundIcon) soundIcon.textContent = '🔊';
                if (soundText) soundText.textContent = 'Sound';
            }
        });
    }

    // 7. Global Preloader Safety Fallback
    setTimeout(() => {
        const loader = document.getElementById('site-loader');
        if (loader && window.getComputedStyle(loader).display !== 'none') {
            loader.style.opacity = '0';
            loader.style.transition = 'opacity 0.6s ease';
            setTimeout(() => { loader.style.display = 'none'; }, 600);
        }
    }, 1200);
});
