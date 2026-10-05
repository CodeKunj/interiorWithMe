/**
 * Amak Interior - Portfolio Filter & Grid Animator
 * Seamless category filtering with GSAP layout animation and history state
 */

document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.portfolio-filter-btn');
    const projectItems = document.querySelectorAll('.portfolio-item');
    const projectGrid = document.getElementById('portfolio-grid');
    const noResultsMsg = document.getElementById('no-projects-message');

    if (!filterButtons.length || !projectItems.length) return;

    // Filter Logic
    function filterCategory(selectedCategory) {
        let visibleCount = 0;

        projectItems.forEach((item) => {
            const itemCategory = item.getAttribute('data-category');
            const matches = selectedCategory === 'all' || itemCategory === selectedCategory;

            if (matches) {
                visibleCount++;
                item.style.display = 'block';
                if (window.gsap) {
                    gsap.fromTo(item, 
                        { opacity: 0, scale: 0.96, y: 20 },
                        { opacity: 1, scale: 1, y: 0, duration: 0.5, ease: 'power2.out' }
                    );
                } else {
                    item.style.opacity = '1';
                }
            } else {
                if (window.gsap) {
                    gsap.to(item, {
                        opacity: 0,
                        scale: 0.96,
                        duration: 0.3,
                        ease: 'power2.in',
                        onComplete: () => {
                            item.style.display = 'none';
                        }
                    });
                } else {
                    item.style.display = 'none';
                }
            }
        });

        // Update active filter button styling
        filterButtons.forEach((btn) => {
            const btnCategory = btn.getAttribute('data-category');
            if (btnCategory === selectedCategory) {
                btn.classList.add('bg-brass', 'text-charcoal-dark', 'border-brass');
                btn.classList.remove('text-ivory/70', 'border-white/10');
            } else {
                btn.classList.remove('bg-brass', 'text-charcoal-dark', 'border-brass');
                btn.classList.add('text-ivory/70', 'border-white/10');
            }
        });

        // Toggle Empty Results Message
        if (noResultsMsg) {
            if (visibleCount === 0) {
                noResultsMsg.classList.remove('hidden');
            } else {
                noResultsMsg.classList.add('hidden');
            }
        }
    }

    // Attach Click Handlers
    filterButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const category = btn.getAttribute('data-category');
            filterCategory(category);

            // Update URL without reload
            const url = new URL(window.location);
            if (category === 'all') {
                url.searchParams.delete('cat');
            } else {
                url.searchParams.set('cat', category);
            }
            window.history.pushState({}, '', url);
        });
    });

    // Check URL parameters on initial load
    const urlParams = new URLSearchParams(window.location.search);
    const initialCat = urlParams.get('cat');
    if (initialCat) {
        filterCategory(initialCat);
    }
});
