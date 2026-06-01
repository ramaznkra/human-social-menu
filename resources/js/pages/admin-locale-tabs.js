/**
 * Admin formları — TR/EN çeviri sekmeleri.
 */
function initLocaleTabs() {
    document.querySelectorAll('[data-locale-tabs]').forEach((root) => {
        const buttons = root.querySelectorAll('[data-locale-tab]');
        const panels = root.querySelectorAll('[data-locale-panel]');

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const locale = btn.dataset.localeTab;
                buttons.forEach((b) => {
                    const active = b === btn;
                    b.classList.toggle('is-active', active);
                    b.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                panels.forEach((panel) => {
                    const active = panel.dataset.localePanel === locale;
                    panel.classList.toggle('is-active', active);
                    panel.hidden = !active;
                });
            });
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLocaleTabs);
} else {
    initLocaleTabs();
}
