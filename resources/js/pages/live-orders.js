/**
 * Birleşik mutfak + bar canlı sipariş ekranı (tek API, istemci filtreleme).
 */
function playDing() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const t = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, t);
        osc.frequency.exponentialRampToValueAtTime(660, t + 0.12);
        gain.gain.setValueAtTime(0.0001, t);
        gain.gain.exponentialRampToValueAtTime(0.12, t + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.35);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(t);
        osc.stop(t + 0.4);
    } catch {
        /* sessiz */
    }
}

function filterOrders(orders, tab) {
    if (tab === 'all') return orders;
    if (tab === 'kitchen') return orders.filter((o) => o.has_kitchen);
    if (tab === 'bar') return orders.filter((o) => o.has_bar);
    return orders;
}

function itemsForTab(order, tab) {
    if (tab === 'all') return order.items;
    return order.items.filter((i) => i.type === tab);
}

function itemBorderClass(type) {
    return type === 'bar'
        ? 'border-l-4 border-[#E67E22]'
        : 'border-l-4 border-[#D4C5B9]/60';
}

function statusActions(status) {
    const buttons = [];
    if (status === 'pending') {
        buttons.push({ status: 'preparing', label: 'Hazırlanıyor', cls: 'live-ops-btn-secondary' });
    }
    if (status === 'pending' || status === 'preparing') {
        buttons.push({ status: 'ready', label: 'Hazır', cls: 'live-ops-btn-secondary' });
    }
    if (status === 'ready' || status === 'preparing') {
        buttons.push({ status: 'delivered', label: 'Teslim', cls: 'live-ops-btn-primary' });
    }
    return buttons;
}

function renderOrderCard(order, tab) {
    const items = itemsForTab(order, tab);
    if (!items.length) return '';

    const itemsHtml = items
        .map(
            (i) => `
        <li class="live-ops-item ${itemBorderClass(i.type)} rounded-r-lg bg-white/[0.03] px-3 py-2">
            <span class="font-medium text-gray-100">${i.quantity}× ${i.name}</span>
            ${i.notes ? `<span class="mt-0.5 block text-xs text-[#D4C5B9]">${i.notes}</span>` : ''}
            <span class="mt-0.5 block text-[10px] uppercase tracking-wider ${i.type === 'bar' ? 'text-[#E67E22]' : 'text-[#D4C5B9]/70'}">${i.type === 'bar' ? 'Bar' : 'Mutfak'}</span>
        </li>`,
        )
        .join('');

    const actions = statusActions(order.status)
        .map((a) => `<button type="button" class="live-ops-status-btn ${a.cls}" data-order-id="${order.id}" data-status="${a.status}">${a.label}</button>`)
        .join('');

    return `
    <article class="live-ops-order-card rounded-2xl border border-white/5 bg-[#262220]/80 p-4 backdrop-blur-md transition-all duration-300" data-order-id="${order.id}">
        <div class="mb-3 flex items-start justify-between gap-2">
            <div>
                <span class="text-xl font-bold text-[#E67E22]">#${order.order_number}</span>
                ${order.table ? `<span class="ml-2 rounded-full bg-[#E67E22]/15 px-2.5 py-0.5 text-xs font-semibold text-[#E67E22]">Masa ${order.table}</span>` : ''}
            </div>
            <div class="text-right">
                <span class="block text-xs text-[#D4C5B9]">${order.created_at}</span>
                <span class="mt-0.5 inline-block rounded-md bg-white/5 px-2 py-0.5 text-[10px] text-gray-300">${order.status_label}</span>
            </div>
        </div>
        <ul class="space-y-2">${itemsHtml}</ul>
        ${order.notes ? `<p class="mt-3 text-xs italic text-[#D4C5B9]">📝 ${order.notes}</p>` : ''}
        <div class="mt-4 flex flex-wrap gap-2">${actions}</div>
    </article>`;
}

function renderGrid(orders, tab) {
    const filtered = filterOrders(orders, tab);
    const html = filtered
        .map((o) => renderOrderCard(o, tab))
        .filter(Boolean)
        .join('');

    if (!html) {
        const emptyMsg =
            tab === 'bar'
                ? 'Bekleyen içecek siparişi yok ☕'
                : tab === 'kitchen'
                  ? 'Bekleyen mutfak siparişi yok 🍽️'
                  : 'Aktif sipariş yok ✨';
        return `<p class="py-20 text-center text-[#D4C5B9]">${emptyMsg}</p>`;
    }

    return `<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">${html}</div>`;
}

const root = document.getElementById('liveOrdersApp');
if (root) {
    const apiUrl = root.dataset.apiUrl;
    const statusUrlBase = root.dataset.statusUrl;
    const csrf = root.dataset.csrf;
    const grid = document.getElementById('liveOrdersGrid');
    const statusEl = document.getElementById('liveOrdersStatus');
    const clockEl = document.getElementById('liveOrdersClock');
    const tabs = document.querySelectorAll('.live-ops-tab');
    const badges = {
        kitchen: document.querySelector('[data-badge="kitchen"]'),
        bar: document.querySelector('[data-badge="bar"]'),
    };

    let activeTab = 'all';
    let ordersState = [];
    let renderSnapshot = '';
    let knownOrderIds = new Set();
    let tabBadges = { kitchen: 0, bar: 0 };
    let initialized = false;
    let pollTimer = null;
    let failCount = 0;
    const intervalMs = 4000;
    const maxIntervalMs = 30000;
    let currentInterval = intervalMs;

    function tickClock() {
        if (clockEl) {
            clockEl.textContent = new Date().toLocaleTimeString('tr-TR', {
                hour: '2-digit',
                minute: '2-digit',
            });
        }
    }
    tickClock();
    setInterval(tickClock, 1000);

    function updateBadgesUI() {
        ['kitchen', 'bar'].forEach((key) => {
            const el = badges[key];
            if (!el) return;
            const n = tabBadges[key];
            el.textContent = n > 99 ? '99+' : String(n);
            el.classList.toggle('hidden', n <= 0);
        });
    }

    function clearBadgeForTab(tab) {
        if (tab === 'kitchen' || tab === 'bar') {
            tabBadges[tab] = 0;
            updateBadgesUI();
        }
    }

    function processNewOrders(orders) {
        const currentIds = new Set(orders.map((o) => o.id));
        let hasNew = false;

        for (const order of orders) {
            if (!knownOrderIds.has(order.id)) {
                hasNew = true;
                if (order.has_kitchen && activeTab !== 'kitchen') {
                    tabBadges.kitchen += 1;
                }
                if (order.has_bar && activeTab !== 'bar') {
                    tabBadges.bar += 1;
                }
            }
        }

        if (initialized && hasNew) {
            playDing();
        }

        knownOrderIds = currentIds;
        initialized = true;
        updateBadgesUI();
    }

    function paint() {
        const html = renderGrid(ordersState, activeTab);
        if (html !== renderSnapshot) {
            grid.innerHTML = html;
            renderSnapshot = html;
            bindStatusButtons();
        }
    }

    function bindStatusButtons() {
        grid.querySelectorAll('.live-ops-status-btn').forEach((btn) => {
            btn.onclick = async () => {
                const orderId = btn.dataset.orderId;
                const status = btn.dataset.status;
                btn.disabled = true;
                try {
                    const res = await fetch(`${statusUrlBase}/${orderId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status }),
                    });
                    if (res.ok) {
                        await poll();
                    }
                } finally {
                    btn.disabled = false;
                }
            };
        });
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activeTab = tab.dataset.tab;
            tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
            clearBadgeForTab(activeTab);
            paint();
        });
    });

    async function poll() {
        try {
            const res = await fetch(apiUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('fetch');
            const data = await res.json();
            ordersState = data.orders || [];
            processNewOrders(ordersState);
            paint();

            if (statusEl) {
                statusEl.textContent = `${ordersState.length} sipariş · ${new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}`;
            }
            failCount = 0;
            currentInterval = intervalMs;
        } catch {
            failCount += 1;
            currentInterval = Math.min(intervalMs * 2 ** failCount, maxIntervalMs);
            if (statusEl) statusEl.textContent = 'Bağlantı bekleniyor…';
        }

        pollTimer = setTimeout(poll, currentInterval);
    }

    poll();
}
