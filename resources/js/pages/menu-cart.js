/**
 * QR menü: sepet, sipariş modal, garson çağrı (DOM yüklendikten sonra).
 */
function initMenuCart() {
    const cfg = window.HSP_MENU;
    if (!cfg) return;

    const cart = {};
    let callStatusTimer = null;

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const t = cfg.i18n || {};
    const cartItemsLabel = (count) =>
        (t.cartItems || ':count items').replace(':count', String(count));

    function callQueryParams() {
        const params = new URLSearchParams();
        if (cfg.tableToken) params.set('table_token', cfg.tableToken);
        if (cfg.tableMasa) params.set('masa', cfg.tableMasa);
        if (cfg.locale) params.set('lang', cfg.locale);
        return params;
    }

    /* ── Garson / hesap ── */
    const callStatusUrl = document.getElementById('menuActionBar')?.dataset.callStatusUrl;

    function resetCallButtons() {
        document.getElementById('callActionButtons')?.classList.remove('hidden');
        document.getElementById('callSuccessMsg')?.classList.add('hidden');
        const waiter = document.getElementById('callWaiter');
        const bill = document.getElementById('callBillOpen');
        if (waiter) waiter.disabled = false;
        if (bill) bill.disabled = false;
    }

    function showCallSent(message) {
        document.getElementById('callActionButtons')?.classList.add('hidden');
        const msg = document.getElementById('callSuccessMsg');
        if (msg) {
            msg.textContent = message || t.callWaiterSent || '';
            msg.classList.remove('hidden');
            msg.classList.add('animate-fade-in-up');
        }
        startCallStatusPoll();
    }

    function stopCallStatusPoll() {
        if (callStatusTimer) {
            clearInterval(callStatusTimer);
            callStatusTimer = null;
        }
    }

    async function checkCallStatus() {
        if (!callStatusUrl || (!cfg.tableToken && !cfg.tableMasa)) return;
        try {
            const res = await fetch(`${callStatusUrl}?${callQueryParams()}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.active) {
                stopCallStatusPoll();
                resetCallButtons();
                const msg = document.getElementById('callSuccessMsg');
                if (msg) {
                    msg.textContent = t.callWaiterActive || '';
                    msg.classList.remove('hidden');
                    setTimeout(() => msg.classList.add('hidden'), 5000);
                }
            }
        } catch {
            /* sessiz */
        }
    }

    function startCallStatusPoll() {
        stopCallStatusPoll();
        checkCallStatus();
        callStatusTimer = setInterval(checkCallStatus, 4000);
    }

    async function sendTableCall(type) {
        if (!cfg.tableToken && !cfg.tableMasa) return false;
        const payload = { type };
        if (cfg.tableToken) payload.table_token = cfg.tableToken;
        if (cfg.tableMasa) payload.masa = cfg.tableMasa;
        if (cfg.locale) payload.lang = cfg.locale;
        try {
            const res = await fetch(cfg.callApiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                showCallSent(data.message);
                return true;
            }
            alert(data.message || t.callFail || '');
        } catch {
            alert(t.connection || '');
        }
        return false;
    }

    async function syncCallBarOnLoad() {
        if (!callStatusUrl || (!cfg.tableToken && !cfg.tableMasa)) return;
        try {
            const res = await fetch(`${callStatusUrl}?${callQueryParams()}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (data.active) {
                const waiting = {
                    waiter: t.callWaiterSent,
                    bill_cash: t.callBillCash,
                    bill_card: t.callBillCard,
                };
                showCallSent(waiting[data.type] || '');
            }
        } catch {
            /* sessiz */
        }
    }

    if (callStatusUrl) syncCallBarOnLoad();

    document.getElementById('callWaiter')?.addEventListener('click', async () => {
        const btn = document.getElementById('callWaiter');
        btn.disabled = true;
        await sendTableCall('waiter');
    });

    const billSheet = document.getElementById('billSheet');
    document.getElementById('callBillOpen')?.addEventListener('click', () => billSheet?.classList.add('open'));
    document.getElementById('billSheetClose')?.addEventListener('click', () => billSheet?.classList.remove('open'));
    billSheet?.addEventListener('click', (e) => {
        if (e.target === billSheet) billSheet.classList.remove('open');
    });
    document.querySelectorAll('.bill-type-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            const ok = await sendTableCall(btn.dataset.billType);
            if (ok) billSheet?.classList.remove('open');
            else btn.disabled = false;
        });
    });

    /* ── Kategori pill ── */
    const categoryPills = document.querySelectorAll('.category-pill');
    const categoryPanels = document.querySelectorAll('[data-category-panel]');

    function showCategory(catId) {
        categoryPills.forEach((p) => p.classList.toggle('is-active', p.dataset.categoryId === String(catId)));
        categoryPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.categoryPanel !== String(catId));
        });
    }

    categoryPills.forEach((pill) => {
        pill.addEventListener('click', () => showCategory(pill.dataset.categoryId));
    });

    /* ── Arama ── */
    const searchInput = document.getElementById('menuSearch');
    const searchClear = document.getElementById('searchClear');
    const noResults = document.getElementById('noResults');
    const pillsNav = document.getElementById('categoryPills');

    function applySearch(query) {
        const q = query.trim().toLowerCase();
        searchClear?.classList.toggle('visible', q.length > 0);
        let total = 0;

        if (q) {
            pillsNav?.classList.add('hidden');
            categoryPanels.forEach((panel) => {
                panel.classList.remove('hidden');
                let visible = 0;
                panel.querySelectorAll('.product-item').forEach((item) => {
                    const match = item.dataset.search.includes(q);
                    item.classList.toggle('hidden', !match);
                    if (match) visible++;
                });
                panel.classList.toggle('hidden', visible === 0);
                total += visible;
            });
        } else {
            pillsNav?.classList.remove('hidden');
            const active = document.querySelector('.category-pill.is-active');
            categoryPanels.forEach((panel) => panel.classList.add('hidden'));
            document.querySelectorAll('.product-item').forEach((item) => item.classList.remove('hidden'));
            if (active) showCategory(active.dataset.categoryId);
            else if (categoryPanels[0]) {
                categoryPanels[0].classList.remove('hidden');
                categoryPills[0]?.classList.add('is-active');
            }
            total = document.querySelectorAll('.product-item:not(.hidden)').length;
        }

        noResults?.classList.toggle('visible', q.length > 0 && total === 0);
    }

    searchInput?.addEventListener('input', () => applySearch(searchInput.value));
    searchClear?.addEventListener('click', () => {
        searchInput.value = '';
        applySearch('');
        searchInput.focus();
    });

    /* ── Sepet ── */
    function updateCartUI() {
        const items = Object.values(cart);
        const count = items.reduce((s, i) => s + i.qty, 0);
        const total = items.reduce((s, i) => s + i.price * i.qty, 0);
        const bar = document.getElementById('cartBar');
        if (!bar) return;
        const countEl = document.getElementById('cartCount');
        const totalEl = document.getElementById('cartTotal');
        if (countEl) countEl.textContent = count;
        const labelEl = document.getElementById('cartCountLabel');
        if (labelEl) {
            labelEl.textContent = ` ${cartItemsLabel(count)} · `;
        }
        if (totalEl) totalEl.textContent = `${total.toFixed(0)} ${cfg.currency}`;
        bar.classList.toggle('visible', count > 0);
    }

    document.querySelectorAll('.add-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const card = btn.closest('.product-item');
            if (!card) return;
            const id = card.dataset.id;
            if (!cart[id]) {
                cart[id] = {
                    id,
                    name: card.dataset.name,
                    price: parseFloat(card.dataset.price),
                    qty: 0,
                };
            }
            cart[id].qty++;
            card.classList.remove('animate-fade-in-up');
            void card.offsetWidth;
            card.classList.add('animate-fade-in-up');
            updateCartUI();
            const bar = document.getElementById('cartBar');
            bar?.classList.remove('animate-cart-pop');
            void bar?.offsetWidth;
            bar?.classList.add('animate-cart-pop');
            const label = btn.getAttribute('aria-label') || t.orderBtn || 'Order';
            const orderBtnText = btn.dataset.orderLabel || label;
            btn.textContent = '✓';
            setTimeout(() => {
                btn.textContent = orderBtnText;
            }, 1200);
        });
    });

    document.getElementById('openCart')?.addEventListener('click', () => {
        const items = Object.values(cart);
        if (!items.length) return;
        const list = document.getElementById('cartItems');
        if (list) {
            list.innerHTML = items
                .map(
                    (i) =>
                        `<div class="flex justify-between py-3 text-sm"><span class="text-gray-100">${i.name} ×${i.qty}</span><span class="font-semibold text-[#E67E22]">${(i.price * i.qty).toFixed(0)} ${cfg.currency}</span></div>`,
                )
                .join('');
        }
        document.getElementById('cartModal')?.classList.add('open');
    });

    document.getElementById('closeCart')?.addEventListener('click', () => {
        document.getElementById('cartModal')?.classList.remove('open');
    });

    document.getElementById('cartModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'cartModal') e.target.classList.remove('open');
    });

    document.getElementById('submitOrder')?.addEventListener('click', async () => {
        const items = Object.values(cart).map((i) => ({
            product_id: parseInt(i.id, 10),
            quantity: i.qty,
        }));
        if (!items.length) return;

        const btn = document.getElementById('submitOrder');
        const modal = document.getElementById('cartModal');
        btn.disabled = true;
        btn.textContent = t.sending || '...';

        try {
            const res = await fetch(cfg.orderStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    table_token: cfg.tableToken,
                    masa: cfg.tableMasa,
                    notes: document.getElementById('orderNotes')?.value ?? '',
                    items,
                }),
            });

            let data;
            try {
                data = await res.json();
            } catch {
                data = null;
            }

            if (res.ok && data?.success) {
                window.location.href = data.redirect;
                return;
            }

            const msg =
                data?.message ||
                (data?.errors ? Object.values(data.errors).flat().join('\n') : null) ||
                `Sipariş gönderilemedi (${res.status}).`;
            alert(msg);
        } catch {
            alert(t.connection || '');
        }

        btn.disabled = false;
        btn.textContent = t.send || 'Send';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMenuCart);
} else {
    initMenuCart();
}
