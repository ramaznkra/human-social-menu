/**
 * Admin ürün formu — rozet (badge) hızlı seçim etiketleri.
 * Etikete tıklayınca input'a yazılır; tekrar tıklanırsa kaldırılır.
 */
function initBadgeChips() {
    const input = document.querySelector('[data-badge-input]');
    const chips = document.querySelectorAll('[data-badge-chip]');
    if (!input || !chips.length) return;

    function syncActive() {
        const current = input.value.trim();
        chips.forEach((chip) => {
            chip.classList.toggle('is-active', chip.dataset.badgeValue === current);
        });
    }

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const value = chip.dataset.badgeValue;
            input.value = input.value.trim() === value ? '' : value;
            syncActive();
        });
    });

    input.addEventListener('input', syncActive);
    syncActive();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBadgeChips);
} else {
    initBadgeChips();
}
