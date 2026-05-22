import { showAdminToast } from '../admin-toast.js';

/**
 * Garson / kasa: hızlı manuel sipariş modalı.
 */
function initManualOrder() {
    const cfg = window.HSP_MANUAL_ORDER;
    if (!cfg) return;

    const fab = document.getElementById('manualOrderFab');
    const modal = document.getElementById('manualOrderModal');
    if (!fab || !modal) return;

    const tablesEl = document.getElementById('manualOrderTables');
    const searchEl = document.getElementById('manualOrderSearch');
    const resultsEl = document.getElementById('manualOrderProductResults');
    const cartEl = document.getElementById('manualOrderCart');
    const totalEl = document.getElementById('manualOrderCartTotal');
    const notesEl = document.getElementById('manualOrderNotes');
    const submitBtn = document.getElementById('manualOrderSubmit');
    const errorEl = document.getElementById('manualOrderError');
    const successEl = document.getElementById('manualOrderSuccess');
    const successMsgEl = document.getElementById('manualOrderSuccessMsg');
    const panelEl = modal.querySelector('.manual-order-modal__panel');

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let tables = [];
    let currency = '₺';
    let selectedTableId = null;
    /** @type {Map<number, { id: number, name: string, price: number, qty: number }>} */
    const cart = new Map();
    let searchTimer = null;
    let bootstrapLoaded = false;

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('manual-order-open');
        if (!bootstrapLoaded) {
            loadBootstrap();
        }
        searchEl?.focus();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('manual-order-open');
        hideError();
        successEl?.classList.add('hidden');
        panelEl?.classList.remove('manual-order-modal__panel--success');
    }

    function showSuccessOverlay(message) {
        if (successMsgEl) {
            successMsgEl.textContent = message;
        }
        panelEl?.classList.add('manual-order-modal__panel--success');
        successEl?.classList.remove('hidden');
    }

    function showError(msg) {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.classList.remove('hidden');
    }

    function hideError() {
        errorEl?.classList.add('hidden');
    }

    function formatMoney(n) {
        return `${Math.round(n).toLocaleString('tr-TR')} ${currency}`;
    }

    function updateSubmitState() {
        const ok = selectedTableId && cart.size > 0;
        if (submitBtn) {
            submitBtn.disabled = !ok;
        }
    }

    function renderTables() {
        if (!tablesEl) return;
        if (!tables.length) {
            tablesEl.innerHTML = '<p class="text-sm text-amber-600">Aktif masa yok. Önce masa ekleyin.</p>';
            return;
        }
        tablesEl.innerHTML = tables
            .map(
                (t) => `
            <button type="button" class="manual-order-table-btn ${selectedTableId === t.id ? 'is-selected' : ''}" data-table-id="${t.id}">
                Masa ${t.number}
            </button>`,
            )
            .join('');

        tablesEl.querySelectorAll('[data-table-id]').forEach((btn) => {
            btn.addEventListener('click', () => {
                selectedTableId = Number(btn.dataset.tableId);
                renderTables();
                updateSubmitState();
            });
        });
    }

    function renderCart() {
        if (!cartEl || !totalEl) return;

        if (cart.size === 0) {
            cartEl.innerHTML = '<li class="manual-order-cart-empty text-sm text-gray-400">Henüz ürün yok</li>';
            totalEl.textContent = formatMoney(0);
            updateSubmitState();
            return;
        }

        let total = 0;
        cartEl.innerHTML = [...cart.values()]
            .map((line) => {
                const sub = line.price * line.qty;
                total += sub;
                return `
            <li class="manual-order-cart-line">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-gray-800">${line.name}</p>
                    <p class="text-xs text-gray-500">${formatMoney(line.price)} × ${line.qty}</p>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" class="manual-order-qty-btn" data-cart-qty="${line.id}" data-delta="-1">−</button>
                    <span class="w-6 text-center text-sm font-semibold">${line.qty}</span>
                    <button type="button" class="manual-order-qty-btn" data-cart-qty="${line.id}" data-delta="1">+</button>
                </div>
            </li>`;
            })
            .join('');

        totalEl.textContent = formatMoney(total);

        cartEl.querySelectorAll('[data-cart-qty]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.cartQty);
                const delta = Number(btn.dataset.delta);
                const line = cart.get(id);
                if (!line) return;
                line.qty += delta;
                if (line.qty <= 0) {
                    cart.delete(id);
                }
                renderCart();
            });
        });

        updateSubmitState();
    }

    function addToCart(product) {
        const existing = cart.get(product.id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.set(product.id, {
                id: product.id,
                name: product.name,
                price: product.price,
                qty: 1,
            });
        }
        renderCart();
    }

    function renderProducts(products) {
        if (!resultsEl) return;
        if (!products.length) {
            resultsEl.innerHTML = '<li class="px-3 py-4 text-sm text-gray-400">Ürün bulunamadı</li>';
            return;
        }

        resultsEl.innerHTML = products
            .map(
                (p) => `
            <li class="manual-order-product-row">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-gray-800">${p.name}</p>
                    <p class="text-xs text-gray-500">${p.category ?? ''} · ${formatMoney(p.price)}</p>
                </div>
                <button type="button" class="manual-order-add-btn" data-product-id="${p.id}">+</button>
            </li>`,
            )
            .join('');

        resultsEl.querySelectorAll('[data-product-id]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const product = products.find((p) => p.id === Number(btn.dataset.productId));
                if (product) {
                    addToCart(product);
                }
            });
        });
    }

    async function loadBootstrap() {
        try {
            const res = await fetch(cfg.bootstrapUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error();
            const data = await res.json();
            tables = data.tables || [];
            currency = data.currency || '₺';
            bootstrapLoaded = true;
            renderTables();
            searchProducts('');
        } catch {
            if (tablesEl) {
                tablesEl.innerHTML = '<p class="text-sm text-red-600">Masalar yüklenemedi.</p>';
            }
        }
    }

    async function searchProducts(q) {
        try {
            const params = new URLSearchParams();
            if (q) params.set('q', q);
            const res = await fetch(`${cfg.productsUrl}?${params}`, {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            renderProducts(data.products || []);
        } catch {
            /* sessiz */
        }
    }

    function resetForm() {
        selectedTableId = null;
        cart.clear();
        if (searchEl) searchEl.value = '';
        if (notesEl) notesEl.value = '';
        renderTables();
        renderCart();
        searchProducts('');
    }

    async function submitOrder() {
        if (!selectedTableId || cart.size === 0) return;

        hideError();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Gönderiliyor…';

        const items = [...cart.values()].map((line) => ({
            product_id: line.id,
            quantity: line.qty,
        }));

        try {
            const res = await fetch(cfg.storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    table_id: selectedTableId,
                    notes: notesEl?.value?.trim() || null,
                    items,
                }),
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok || data.success === false) {
                const msg =
                    data.message ||
                    (data.errors ? Object.values(data.errors).flat().join(' ') : null) ||
                    'Sipariş kaydedilemedi.';
                showError(msg);
                return;
            }

            const msg = data.message || `Sipariş #${data.order?.order_number ?? ''} mutfağa iletildi.`;
            showSuccessOverlay(msg);
            showAdminToast({
                title: 'Hazırlanıyor',
                message: msg,
                hint: 'Garson siparişi · Canlı panelde Nakit/Kart ile kapatılır',
                type: 'success',
            });
            setTimeout(() => {
                closeModal();
                resetForm();
            }, 1400);
        } catch {
            showError('Bağlantı hatası. Tekrar deneyin.');
        } finally {
            submitBtn.textContent = 'Siparişi Onayla';
            updateSubmitState();
        }
    }

    fab.addEventListener('click', openModal);

    modal.querySelectorAll('[data-manual-order-close]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    searchEl?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = searchEl.value.trim();
        searchTimer = setTimeout(() => searchProducts(q), 180);
    });

    submitBtn?.addEventListener('click', submitOrder);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initManualOrder);
} else {
    initManualOrder();
}
