/**
 * Social Spotted — tam genişlik dokunmatik carousel
 */
export function initSpottedCarousel(root) {
    if (!root) return;

    const track = root.querySelector('[data-spotted-track]');
    const dots = root.querySelectorAll('[data-spotted-dot]');
    const slides = root.querySelectorAll('[data-spotted-card]');

    if (!track || slides.length === 0) return;

    let activeIndex = 0;

    function setActive(index) {
        activeIndex = Math.max(0, Math.min(index, slides.length - 1));
        dots.forEach((dot, i) => {
            dot.classList.toggle('w-5', i === activeIndex);
            dot.classList.toggle('bg-[#E67E22]', i === activeIndex);
            dot.classList.toggle('w-1.5', i !== activeIndex);
            dot.classList.toggle('bg-white/35', i !== activeIndex);
        });
    }

    function scrollToIndex(index) {
        const slide = slides[index];
        if (!slide) return;
        track.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
        setActive(index);
    }

    function indexFromScroll() {
        const center = track.scrollLeft + track.clientWidth / 2;
        let closest = 0;
        let minDist = Infinity;
        slides.forEach((slide, i) => {
            const slideCenter = slide.offsetLeft + slide.clientWidth / 2;
            const dist = Math.abs(center - slideCenter);
            if (dist < minDist) {
                minDist = dist;
                closest = i;
            }
        });
        return closest;
    }

    let scrollRaf = null;
    track.addEventListener(
        'scroll',
        () => {
            if (scrollRaf) cancelAnimationFrame(scrollRaf);
            scrollRaf = requestAnimationFrame(() => {
                const idx = indexFromScroll();
                if (idx !== activeIndex) setActive(idx);
            });
        },
        { passive: true },
    );

    dots.forEach((dot) => {
        dot.addEventListener('click', () => scrollToIndex(parseInt(dot.dataset.index, 10)));
    });

    setActive(0);
}

document.addEventListener('DOMContentLoaded', () => {
    initSpottedCarousel(document.getElementById('spottedCarousel'));
});
