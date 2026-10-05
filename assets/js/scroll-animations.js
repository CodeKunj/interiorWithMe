/**
 * Amak Interior - GSAP ScrollTrigger Editorial Animations
 * Choreographs section entrances, parallax imagery, and timeline indicators
 */

document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    
    gsap.registerPlugin(ScrollTrigger);

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    // 0. Hero Video Scroll-Driven Frame-by-Frame Scrubbing
    const heroVideo = document.getElementById('hero-bg-video');
    const heroSection = document.getElementById('hero-canvas-container');

    if (heroVideo && heroSection) {
        heroVideo.pause();
        heroVideo.currentTime = 0;

        let scrubberBound = false;
        function bindVideoScrubber() {
            if (scrubberBound) return;
            scrubberBound = true;
            heroVideo.pause();

            const videoTimeline = { time: 0 };
            const scrubTl = gsap.timeline({
                scrollTrigger: {
                    trigger: heroSection,
                    start: 'top top',
                    end: '+=300%',
                    pin: true,
                    scrub: 1.0,
                    anticipatePin: 1,
                    onEnter: () => {
                        if (!window.heroVideoAutoPlaying) heroVideo.pause();
                    }
                }
            });

            // 1. Frame-by-Frame Video Scrub
            scrubTl.to(videoTimeline, {
                time: () => (heroVideo.duration && !isNaN(heroVideo.duration) && heroVideo.duration > 0) ? heroVideo.duration : 12,
                ease: 'none',
                onUpdate: () => {
                    if (heroVideo.readyState >= 2 && !window.heroVideoAutoPlaying) {
                        heroVideo.currentTime = videoTimeline.time;
                    }
                }
            }, 0);

            // 2. Headline fadeout on scroll
            scrubTl.to('#hero-headline-wrap', {
                opacity: 0,
                y: -60,
                ease: 'power1.out'
            }, 0);

            // 3. Subtle video scale
            scrubTl.to(heroVideo, {
                scale: 1.15,
                ease: 'none'
            }, 0);

            ScrollTrigger.refresh();
        }

        if (heroVideo.readyState >= 1) {
            bindVideoScrubber();
        } else {
            heroVideo.addEventListener('loadedmetadata', bindVideoScrubber, { once: true });
            setTimeout(bindVideoScrubber, 400);
        }
    }

    // 1. Generic Section Header Fade & Slide Up
    gsap.utils.toArray('.reveal-header').forEach((header) => {
        gsap.from(header.children, {
            scrollTrigger: {
                trigger: header,
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
            y: 40,
            opacity: 0,
            duration: 1.0,
            stagger: 0.15,
            ease: 'power3.out',
        });
    });

    // 2. Services Stagger Grid
    const servicesGrid = document.querySelector('.services-grid');
    if (servicesGrid) {
        gsap.from(servicesGrid.children, {
            scrollTrigger: {
                trigger: servicesGrid,
                start: 'top 80%',
                toggleActions: 'play none none none',
            },
            y: 50,
            opacity: 0,
            duration: 0.9,
            stagger: 0.12,
            ease: 'power2.out',
        });
    }

    // 3. Featured Projects Cards Stagger & Parallax Image
    gsap.utils.toArray('.project-card').forEach((card, i) => {
        gsap.from(card, {
            scrollTrigger: {
                trigger: card,
                start: 'top 88%',
                toggleActions: 'play none none none',
            },
            y: 60,
            opacity: 0,
            duration: 1.1,
            delay: (i % 3) * 0.15,
            ease: 'power3.out',
        });

        const img = card.querySelector('.project-card-image');
        if (img) {
            gsap.fromTo(img, 
                { yPercent: -8 },
                {
                    yPercent: 8,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: card,
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: 1.5,
                    }
                }
            );
        }
    });

    // 4. Process Timeline Step Reveal & Connector Line
    const processTimeline = document.querySelector('.process-timeline');
    if (processTimeline) {
        const steps = processTimeline.querySelectorAll('.process-step');
        steps.forEach((step, i) => {
            gsap.from(step, {
                scrollTrigger: {
                    trigger: step,
                    start: 'top 82%',
                    toggleActions: 'play none none none',
                },
                x: i % 2 === 0 ? -30 : 30,
                opacity: 0,
                duration: 0.9,
                ease: 'power2.out',
            });
        });

        const progressLine = document.querySelector('#process-progress-line');
        if (progressLine) {
            gsap.fromTo(progressLine, 
                { scaleY: 0 },
                {
                    scaleY: 1,
                    transformOrigin: 'top center',
                    ease: 'none',
                    scrollTrigger: {
                        trigger: processTimeline,
                        start: 'top 70%',
                        end: 'bottom 80%',
                        scrub: 1.0,
                    }
                }
            );
        }
    }

    // 5. Testimonial Quote Cards
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    if (testimonialCards.length > 0) {
        gsap.from(testimonialCards, {
            scrollTrigger: {
                trigger: '#testimonials',
                start: 'top 78%',
                toggleActions: 'play none none none',
            },
            y: 40,
            opacity: 0,
            duration: 1.0,
            stagger: 0.2,
            ease: 'power3.out',
        });
    }

    // 6. About Stats Count Up
    const statCounters = document.querySelectorAll('.stat-counter');
    statCounters.forEach((counter) => {
        const target = parseInt(counter.getAttribute('data-target') || '0', 10);
        ScrollTrigger.create({
            trigger: counter,
            start: 'top 85%',
            once: true,
            onEnter: () => {
                const obj = { val: 0 };
                gsap.to(obj, {
                    val: target,
                    duration: 2.0,
                    ease: 'power2.out',
                    onUpdate: () => {
                        counter.textContent = Math.floor(obj.val).toLocaleString();
                    }
                });
            }
        });
    });
});
