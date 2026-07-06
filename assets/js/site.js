/* Public-site JS: scroll reveals + native <dialog> lightbox. ~1.5KB, no deps. */
(() => {
    'use strict';

    /* Scroll reveals — sections fade up once; disabled for reduced motion. */
    if (matchMedia('(prefers-reduced-motion: no-preference)').matches && 'IntersectionObserver' in window) {
        const sections = document.querySelectorAll('main > * > section, main > section, main > article > section');
        const io = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    io.unobserve(entry.target);
                }
            }
        }, { rootMargin: '0px 0px -8% 0px' });
        sections.forEach((s, i) => {
            if (i === 0) return; // never hide the hero
            s.classList.add('reveal');
            io.observe(s);
        });
    }

    /* Lightbox for media blocks flagged data-lightbox. */
    const dialog = document.createElement('dialog');
    dialog.className = 'lightbox';
    dialog.innerHTML = '<img alt="">';
    document.body.append(dialog);
    dialog.addEventListener('click', () => dialog.close());

    document.querySelectorAll('[data-lightbox] .frame img').forEach((img) => {
        img.addEventListener('click', () => {
            const full = dialog.querySelector('img');
            full.src = img.src;
            full.alt = img.alt || '';
            dialog.showModal();
        });
    });
})();
