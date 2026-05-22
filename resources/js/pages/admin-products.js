/**
 * Admin ürün listesi — stok / menü görünürlüğü toggle (AJAX).
 */
function initProductAvailabilityToggles() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    document.querySelectorAll('[data-product-toggle]').forEach((input) => {
        input.addEventListener('change', async () => {
            const url = input.dataset.toggleUrl;
            const row = input.closest('[data-product-row]');
            const label = row?.querySelector('[data-availability-label]');
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

                input.checked = data.is_available;
                if (label) {
                    label.textContent = data.label;
                    label.classList.toggle('text-emerald-600', data.is_available);
                    label.classList.toggle('text-gray-400', !data.is_available);
                }
                row?.classList.toggle('opacity-50', !data.is_available);
            } catch {
                input.checked = prev;
                alert('Bağlantı hatası.');
            } finally {
                input.disabled = false;
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductAvailabilityToggles);
} else {
    initProductAvailabilityToggles();
}
