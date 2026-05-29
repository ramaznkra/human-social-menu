/**
 * Admin kategoriler — aktif / pasif toggle (AJAX, kalıcı).
 * Değişiklik anında DB'ye yazılır; F5 sonrası durum korunur.
 */
function initCategoryActiveToggles() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    document.querySelectorAll('[data-category-toggle]').forEach((input) => {
        input.addEventListener('change', async () => {
            const url = input.dataset.toggleUrl;
            const prev = !input.checked;

            input.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    input.checked = prev;
                    alert(data?.message || 'Kategori durumu güncellenemedi.');
                    return;
                }

                // Aynı kategoriye ait tüm görünümleri (liste + tepsi) güncelle.
                document
                    .querySelectorAll(`[data-category-toggle][data-toggle-url="${url}"]`)
                    .forEach((el) => {
                        el.checked = data.is_active;
                        applyState(el, data.is_active);
                    });
            } catch {
                input.checked = prev;
                alert('Bağlantı hatası.');
            } finally {
                input.disabled = false;
            }
        });
    });

    function applyState(input, isActive) {
        const item = input.closest('[data-category-item]');
        if (!item) return;

        const dot = item.querySelector('[data-category-dot]');
        if (dot) {
            dot.classList.toggle('table-status-dot--on', isActive);
            dot.classList.toggle('table-status-dot--off', !isActive);
        }

        if (item.matches('tr')) {
            item.classList.toggle('opacity-50', !isActive);
        } else {
            item.classList.toggle('admin-tray-card--hidden', !isActive);
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCategoryActiveToggles);
} else {
    initCategoryActiveToggles();
}
