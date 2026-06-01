/**
 * Admin ürün listesi — menü görünürlüğü + stok toggle (AJAX).
 */
function bindProductToggle(selector, labelSelector, onSuccess) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    document.querySelectorAll(selector).forEach((input) => {
        input.addEventListener('change', async () => {
            const url = input.dataset.toggleUrl;
            const row = input.closest('[data-product-item]');
            const label = row?.querySelector(labelSelector);
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
                    alert(data?.message || 'Güncellenemedi.');
                    return;
                }

                onSuccess({ input, row, label, data });
            } catch {
                input.checked = prev;
                alert('Bağlantı hatası.');
            } finally {
                input.disabled = false;
            }
        });
    });
}

function initProductToggles() {
    bindProductToggle('[data-product-toggle]', '[data-availability-label]', ({ input, row, label, data }) => {
        input.checked = data.is_available;
        if (label) {
            label.textContent = data.label;
            label.classList.toggle('text-emerald-600', data.is_available);
            label.classList.toggle('text-gray-400', !data.is_available);
        }
        row?.classList.toggle('opacity-50', !data.is_available);
        row?.classList.toggle('admin-tray-card--hidden', !data.is_available);
    });

    bindProductToggle('[data-product-stock-toggle]', '[data-stock-label]', ({ input, label, data }) => {
        input.checked = data.in_stock;
        if (label) {
            label.textContent = data.label;
            label.classList.toggle('text-emerald-600', data.in_stock);
            label.classList.toggle('text-red-500', !data.in_stock);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductToggles);
} else {
    initProductToggles();
}
