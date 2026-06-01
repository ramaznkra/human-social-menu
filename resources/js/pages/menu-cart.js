/**
 * QR menü: sepet (localStorage), ürün varyasyon modalı, sipariş, garson çağrı.
 */
function initMenuCart() {
    const cfg = window.HSP_MENU;
    if (!cfg) return;

    const cart = {};
    let callStatusTimer = null;
    let activeProduct = null;
    let modalSelections = {};

    const CART_STORAGE_VERSION = 1;
    const cartStorageKey = () => {
        const restaurant = cfg.restaurantId ?? '0';
        const table = cfg.tableToken ?? 'guest';
        const locale = cfg.locale ?? 'tr';
        return `hsp_cart_v${CART_STORAGE_VERSION}_${restaurant}_${table}_${locale}`;
    };

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const t = cfg.i18n || {};
    const cartItemsLabel = (count) =>
        (t.cartItems || ':count items').replace(':count', String(count));

    function formatPrice(amount) {
        return `${Math.round(amount)} ${cfg.currency}`;
    }

    function addToCartLabel(amount) {
        const tpl = t.addToCartPrice || 'Add to Cart (:price :currency)';
        return tpl
            .replace(':price', String(Math.round(amount)))
            .replace(':currency', cfg.currency);
    }

    function callQueryParams() {
        const params = new URLSearchParams();
        if (cfg.tableToken) params.set('table_token', cfg.tableToken);
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
        if (!callStatusUrl || !cfg.tableToken) return;
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
        if (!cfg.tableToken) return false;
        const payload = { type };
        if (cfg.tableToken) payload.table_token = cfg.tableToken;
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
        if (!callStatusUrl || !cfg.tableToken) return;
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

    /* ── Sepet (localStorage) ── */
    let orderSubmitting = false;

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function parseProductOptions(card) {
        try {
            return JSON.parse(card.dataset.options || '[]');
        } catch {
            return [];
        }
    }

    function makeLineKey(productId, options) {
        const optionIds = options.map((o) => o.option_id).sort((a, b) => a - b);
        return `${productId}:${optionIds.join(',')}`;
    }

    function buildSelectedOptions(groups, selections) {
        const resolved = [];

        groups.forEach((group) => {
            const selected = selections[group.id];
            if (group.type === 'single') {
                if (!selected) return;
                const option = group.options.find((o) => o.id === selected);
                if (!option) return;
                resolved.push({
                    group_id: group.id,
                    group_name: group.name,
                    option_id: option.id,
                    name: option.name,
                    price: Number(option.price) || 0,
                });
            } else {
                const ids = Array.isArray(selected) ? selected : [];
                ids.forEach((optionId) => {
                    const option = group.options.find((o) => o.id === optionId);
                    if (!option) return;
                    resolved.push({
                        group_id: group.id,
                        group_name: group.name,
                        option_id: option.id,
                        name: option.name,
                        price: Number(option.price) || 0,
                    });
                });
            }
        });

        return resolved;
    }

    function unitPriceFromOptions(basePrice, options) {
        const extras = options.reduce((sum, o) => sum + (Number(o.price) || 0), 0);
        return Number(basePrice) + extras;
    }

    function displayNameWithOptions(name, options) {
        if (!options.length) return name;
        return `${name} (${options.map((o) => o.name).join(', ')})`;
    }

    function defaultSelections(groups) {
        const selections = {};
        groups.forEach((group) => {
            if (group.type === 'single') {
                const defaults = group.options.filter((o) => o.default);
                const pick = defaults[0] ?? group.options[0];
                if (pick) selections[group.id] = pick.id;
            } else {
                selections[group.id] = group.options.filter((o) => o.default).map((o) => o.id);
            }
        });
        return selections;
    }

    function validateSelections(groups, selections) {
        for (const group of groups) {
            if (!group.required) continue;
            const selected = selections[group.id];
            if (group.type === 'single') {
                if (!selected) return false;
            } else if (!Array.isArray(selected) || selected.length === 0) {
                return false;
            }
        }
        return true;
    }

    function saveCartToStorage() {
        try {
            localStorage.setItem(
                cartStorageKey(),
                JSON.stringify({
                    v: CART_STORAGE_VERSION,
                    items: cart,
                    savedAt: Date.now(),
                }),
            );
        } catch {
            /* depolama dolu veya gizli mod */
        }
    }

    function loadCartFromStorage() {
        try {
            const raw = localStorage.getItem(cartStorageKey());
            if (!raw) return;
            const data = JSON.parse(raw);
            if (!data || data.v !== CART_STORAGE_VERSION || !data.items) return;
            Object.keys(cart).forEach((key) => delete cart[key]);
            Object.entries(data.items).forEach(([key, item]) => {
                if (!item || !item.productId) return;
                cart[key] = {
                    lineKey: key,
                    productId: String(item.productId),
                    name: item.name,
                    basePrice: Number(item.basePrice) || 0,
                    price: Number(item.price) || 0,
                    options: Array.isArray(item.options) ? item.options : [],
                    qty: Number(item.qty) || 1,
                };
            });
        } catch {
            /* bozuk kayıt */
        }
    }

    function persistCart() {
        saveCartToStorage();
        updateCartUI();
    }

    function setOrderFormLocked(locked) {
        const submitBtn = document.getElementById('submitOrder');
        const closeBtn = document.getElementById('closeCart');
        const notes = document.getElementById('orderNotes');
        if (submitBtn) submitBtn.disabled = locked;
        if (closeBtn) closeBtn.disabled = locked;
        if (notes) notes.disabled = locked;
        document.querySelectorAll('.cart-qty-btn, .cart-remove-btn, .add-btn, #openCart, #productModalAdd').forEach((el) => {
            el.disabled = locked;
        });
    }

    function cartTotals() {
        const items = Object.values(cart);
        const count = items.reduce((s, i) => s + i.qty, 0);
        const total = items.reduce((s, i) => s + i.price * i.qty, 0);
        return { items, count, total };
    }

    function setCartQty(lineKey, qty) {
        if (!cart[lineKey]) return;
        if (qty <= 0) {
            delete cart[lineKey];
        } else {
            cart[lineKey].qty = qty;
        }
        persistCart();
    }

    function addCartLine(productId, name, basePrice, options, qty = 1) {
        const lineKey = makeLineKey(productId, options);
        const unitPrice = unitPriceFromOptions(basePrice, options);
        const displayName = displayNameWithOptions(name, options);

        if (!cart[lineKey]) {
            cart[lineKey] = {
                lineKey,
                productId: String(productId),
                name: displayName,
                basePrice: Number(basePrice),
                price: unitPrice,
                options,
                qty: 0,
            };
        }

        cart[lineKey].qty += qty;
        persistCart();
        return lineKey;
    }

    function updateCartUI() {
        const { count, total } = cartTotals();
        const bar = document.getElementById('cartBar');
        if (!bar) return;

        const countEl = document.getElementById('cartCount');
        const totalEl = document.getElementById('cartTotal');
        if (countEl) countEl.textContent = count;
        const labelEl = document.getElementById('cartCountLabel');
        if (labelEl) labelEl.textContent = ` ${cartItemsLabel(count)} · `;
        if (totalEl) totalEl.textContent = formatPrice(total);

        const modalTotal = document.getElementById('cartModalTotal');
        if (modalTotal) modalTotal.textContent = formatPrice(total);

        bar.classList.toggle('visible', count > 0);

        const modal = document.getElementById('cartModal');
        if (modal?.classList.contains('open') && count === 0) {
            modal.classList.remove('open');
        }
    }

    function renderCartModal() {
        const list = document.getElementById('cartItems');
        if (!list) return;

        const { items, total } = cartTotals();
        if (!items.length) {
            list.innerHTML = '';
            updateCartUI();
            return;
        }

        list.innerHTML = items
            .map((i) => {
                const optionsHtml = i.options?.length
                    ? `<p class="cart-line__options">${escapeHtml(i.options.map((o) => o.name).join(' · '))}</p>`
                    : '';

                return `
                <div class="cart-line" data-cart-id="${escapeHtml(i.lineKey)}">
                    <div class="cart-line__top">
                        <span class="cart-line__name">${escapeHtml(i.name)}</span>
                        <span class="cart-line__subtotal">${formatPrice(i.price * i.qty)}</span>
                    </div>
                    ${optionsHtml}
                    <div class="cart-line__actions">
                        <div class="cart-line__qty">
                            <button type="button" class="cart-qty-btn" data-cart-action="dec" data-id="${escapeHtml(i.lineKey)}" aria-label="${escapeHtml(t.cartDecrease || '-')}">−</button>
                            <span class="cart-qty-value">${i.qty}</span>
                            <button type="button" class="cart-qty-btn" data-cart-action="inc" data-id="${escapeHtml(i.lineKey)}" aria-label="${escapeHtml(t.cartIncrease || '+')}">+</button>
                        </div>
                        <button type="button" class="cart-remove-btn" data-cart-action="remove" data-id="${escapeHtml(i.lineKey)}">${escapeHtml(t.cartRemove || 'Remove')}</button>
                    </div>
                </div>`;
            })
            .join('');

        const modalTotal = document.getElementById('cartModalTotal');
        if (modalTotal) modalTotal.textContent = formatPrice(total);
    }

    function animateProductAdded(card) {
        card.classList.remove('animate-fade-in-up');
        void card.offsetWidth;
        card.classList.add('animate-fade-in-up');

        const bar = document.getElementById('cartBar');
        bar?.classList.remove('animate-cart-pop');
        void bar?.offsetWidth;
        bar?.classList.add('animate-cart-pop');
    }

    function flashAddButton(btn) {
        const orderBtnText = btn.dataset.orderLabel || btn.getAttribute('aria-label') || t.orderBtn || 'Order';
        btn.textContent = '✓';
        setTimeout(() => {
            btn.textContent = orderBtnText;
        }, 1200);
    }

    function quickAddProduct(card, btn) {
        addCartLine(card.dataset.id, card.dataset.name, card.dataset.price, [], 1);
        animateProductAdded(card);
        flashAddButton(btn);
    }

    /* ── Ürün modalı ── */
    const productModal = document.getElementById('productModal');
    const productModalTitle = document.getElementById('productModalTitle');
    const productModalBasePrice = document.getElementById('productModalBasePrice');
    const productModalOptions = document.getElementById('productModalOptions');
    const productModalAdd = document.getElementById('productModalAdd');
    const productModalError = document.getElementById('productModalError');

    function formatOptionPrice(price) {
        if (!price || Number(price) <= 0) return '';
        return `+${Math.round(price)} ${cfg.currency}`;
    }

    function renderProductModalOptions() {
        if (!activeProduct || !productModalOptions) return;

        productModalOptions.innerHTML = activeProduct.groups
            .map((group) => {
                const requiredHint = group.required
                    ? `<span class="product-option-group__hint">*</span>`
                    : '';

                const choices = group.options
                    .map((option) => {
                        const inputType = group.type === 'single' ? 'radio' : 'checkbox';
                        const inputName = `option-group-${group.id}`;
                        const isChecked =
                            group.type === 'single'
                                ? modalSelections[group.id] === option.id
                                : (modalSelections[group.id] || []).includes(option.id);
                        const priceLabel = formatOptionPrice(option.price);

                        return `
                        <label class="product-option-choice ${isChecked ? 'is-selected' : ''}">
                            <span class="product-option-choice__left">
                                <input
                                    type="${inputType}"
                                    name="${escapeHtml(inputName)}"
                                    data-group-id="${group.id}"
                                    data-option-id="${option.id}"
                                    data-group-type="${group.type}"
                                    ${isChecked ? 'checked' : ''}
                                >
                                <span class="product-option-choice__label">${escapeHtml(option.name)}</span>
                            </span>
                            ${priceLabel ? `<span class="product-option-choice__price">${escapeHtml(priceLabel)}</span>` : ''}
                        </label>`;
                    })
                    .join('');

                return `
                <section class="product-option-group" data-group-id="${group.id}">
                    <h3 class="product-option-group__title">${escapeHtml(group.name)}${requiredHint}</h3>
                    <div class="product-option-list">${choices}</div>
                </section>`;
            })
            .join('');

        productModalOptions.querySelectorAll('input[data-group-id]').forEach((input) => {
            input.addEventListener('change', onProductOptionChange);
        });
    }

    function updateProductModalPrice() {
        if (!activeProduct || !productModalAdd) return;

        const options = buildSelectedOptions(activeProduct.groups, modalSelections);
        const total = unitPriceFromOptions(activeProduct.basePrice, options);
        const valid = validateSelections(activeProduct.groups, modalSelections);

        productModalAdd.textContent = addToCartLabel(total);
        productModalAdd.disabled = !valid;

        if (productModalError) {
            productModalError.classList.toggle('hidden', valid);
            if (!valid) productModalError.textContent = t.optionRequired || '';
        }
    }

    function onProductOptionChange(event) {
        const input = event.target;
        const groupId = Number(input.dataset.groupId);
        const optionId = Number(input.dataset.optionId);
        const groupType = input.dataset.groupType;

        if (groupType === 'single') {
            modalSelections[groupId] = optionId;
        } else {
            const current = new Set(modalSelections[groupId] || []);
            if (input.checked) current.add(optionId);
            else current.delete(optionId);
            modalSelections[groupId] = Array.from(current);
        }

        productModalOptions?.querySelectorAll(`input[data-group-id="${groupId}"]`).forEach((el) => {
            el.closest('.product-option-choice')?.classList.toggle('is-selected', el.checked);
        });

        updateProductModalPrice();
    }

    function openProductModal(card) {
        if (!productModal) return;

        const groups = parseProductOptions(card);
        activeProduct = {
            card,
            productId: card.dataset.id,
            name: card.dataset.name,
            basePrice: Number(card.dataset.price) || 0,
            groups,
        };
        modalSelections = defaultSelections(groups);

        if (productModalTitle) productModalTitle.textContent = activeProduct.name;
        if (productModalBasePrice) {
            const baseTpl = t.basePrice || 'Base: :price :currency';
            productModalBasePrice.textContent = baseTpl
                .replace(':price', String(Math.round(activeProduct.basePrice)))
                .replace(':currency', cfg.currency);
        }

        renderProductModalOptions();
        updateProductModalPrice();
        productModal.classList.add('open');
    }

    function closeProductModal() {
        productModal?.classList.remove('open');
        activeProduct = null;
        modalSelections = {};
        if (productModalError) productModalError.classList.add('hidden');
    }

    productModal?.addEventListener('click', (e) => {
        if (e.target === productModal) closeProductModal();
    });
    document.getElementById('productModalClose')?.addEventListener('click', closeProductModal);

    productModalAdd?.addEventListener('click', () => {
        if (!activeProduct) return;
        if (!validateSelections(activeProduct.groups, modalSelections)) {
            if (productModalError) {
                productModalError.textContent = t.optionRequired || '';
                productModalError.classList.remove('hidden');
            }
            return;
        }

        const options = buildSelectedOptions(activeProduct.groups, modalSelections);
        addCartLine(activeProduct.productId, activeProduct.name, activeProduct.basePrice, options, 1);
        animateProductAdded(activeProduct.card);

        const btn = activeProduct.card.querySelector('.add-btn');
        if (btn) flashAddButton(btn);

        closeProductModal();
    });

    document.querySelectorAll('.add-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const card = btn.closest('.product-item');
            if (!card) return;

            const hasOptions = card.dataset.hasOptions === '1';
            if (hasOptions) {
                openProductModal(card);
                return;
            }

            quickAddProduct(card, btn);
        });
    });

    document.getElementById('cartItems')?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-cart-action]');
        if (!btn) return;

        const id = btn.dataset.id;
        const action = btn.dataset.cartAction;
        if (!id || !cart[id]) return;

        if (action === 'inc') {
            setCartQty(id, cart[id].qty + 1);
        } else if (action === 'dec') {
            setCartQty(id, cart[id].qty - 1);
        } else if (action === 'remove') {
            delete cart[id];
            persistCart();
        }

        renderCartModal();
    });

    document.getElementById('openCart')?.addEventListener('click', () => {
        if (!cartTotals().count) return;
        renderCartModal();
        document.getElementById('cartModal')?.classList.add('open');
    });

    document.getElementById('closeCart')?.addEventListener('click', () => {
        document.getElementById('cartModal')?.classList.remove('open');
    });

    document.getElementById('cartModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'cartModal') e.target.classList.remove('open');
    });

    document.getElementById('submitOrder')?.addEventListener('click', async () => {
        if (orderSubmitting) return;

        const items = Object.values(cart).map((i) => ({
            product_id: parseInt(i.productId, 10),
            quantity: i.qty,
            options: (i.options || []).map((o) => ({
                group_id: o.group_id,
                option_id: o.option_id,
            })),
        }));
        if (!items.length) return;

        const btn = document.getElementById('submitOrder');
        orderSubmitting = true;
        setOrderFormLocked(true);
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
                    lang: cfg.locale || 'tr',
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
                Object.keys(cart).forEach((key) => delete cart[key]);
                saveCartToStorage();
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

        orderSubmitting = false;
        setOrderFormLocked(false);
        btn.textContent = t.send || 'Send';
    });

    loadCartFromStorage();
    updateCartUI();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMenuCart);
} else {
    initMenuCart();
}
