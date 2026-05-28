import { showAdminToast } from '../admin-toast.js';
import { createEchoClient } from '../echo.js';

/**
 * Birleşik sipariş + masa çağrıları (tek API, istemci filtreleme).
 */
function escapeHtml(text) {
    const el = document.createElement('div');
    el.textContent = text;
    return el.innerHTML;
}

function playOrderDing() {
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

/** Garson / hesap çağrısı — daha tiz, dikkat çekici */
function playCallAlert() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const t = ctx.currentTime;
        [0, 0.18].forEach((offset, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'square';
            osc.frequency.setValueAtTime(1200 + i * 200, t + offset);
            gain.gain.setValueAtTime(0.0001, t + offset);
            gain.gain.exponentialRampToValueAtTime(0.14, t + offset + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + offset + 0.14);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(t + offset);
            osc.stop(t + offset + 0.16);
        });
    } catch {
        /* sessiz */
    }
}

function filterOrders(orders, tab) {
    if (tab === 'all' || tab === 'calls') return orders;
    if (tab === 'kitchen') return orders.filter((o) => o.has_kitchen);
    if (tab === 'bar') return orders.filter((o) => o.has_bar);
    if (tab === 'prepared') return orders.filter((o) => o.status === 'ready');
    return orders;
}

function itemsForTab(order, tab) {
    if (tab === 'all') return order.items;
    if (tab === 'kitchen' || tab === 'bar') {
        return order.items.filter((i) => i.type === tab);
    }
    return order.items;
}

function itemBorderClass(type) {
    return type === 'bar'
        ? 'border-l-4 border-[#E67E22]'
        : 'border-l-4 border-[#D4C5B9]/60';
}

function statusActions(status, paymentMethod = null, isWaiterOrder = false) {
    const buttons = [];

    if (status === 'pending') {
        buttons.push({
            status: 'preparing',
            label: 'Kabul Et · Hazırlanıyor',
            cls: 'live-ops-btn-primary',
        });
    }
    if (status === 'preparing') {
        buttons.push({
            status: 'ready',
            label: 'Mutfakta Hazır · Garsona Bildir',
            cls: 'live-ops-btn-primary',
        });
    }
    if (status === 'ready') {
        buttons.push({
            status: 'ready',
            label: 'Garson Teslim Edecek',
            cls: 'live-ops-btn-secondary',
            disabled: true,
        });
    }
    if (status === 'delivered' && !paymentMethod) {
        buttons.push({
            status: 'delivered',
            payment_method: 'cash',
            payment_only: true,
            label: '💵 Nakit · Kapat',
            cls: 'live-ops-btn-secondary',
        });
        buttons.push({
            status: 'delivered',
            payment_method: 'card',
            payment_only: true,
            label: '💳 Kart · Kapat',
            cls: 'live-ops-btn-secondary',
        });
    }
    return buttons;
}

function buildOrdersFingerprint(orders, tab) {
    const filtered = filterOrders(orders, tab);
    return JSON.stringify(
        filtered.map((o) => ({
            id: o.id,
            status: o.status,
            updated_at: o.updated_at,
            items: o.items.map((i) => [i.id, i.quantity, i.type]),
        })),
    );
}

function buildCallsFingerprint(calls) {
    return JSON.stringify(calls.map((c) => [c.id, c.updated_at, c.type]));
}

function buildViewFingerprint(orders, calls, completedOrders, tab) {
    if (tab === 'calls') {
        return `calls:${buildCallsFingerprint(calls)}`;
    }
    if (tab === 'completed') {
        return `completed:${JSON.stringify(completedOrders.map((o) => [o.id, o.status, o.payment_method, o.updated_at]))}`;
    }
    if (tab === 'all') {
        const feed = buildMixedFeed(orders, calls);
        return `all:${JSON.stringify(feed.map((f) => [f.kind, f.sort_at, f.data.id, f.data.updated_at ?? f.data.status]))}`;
    }
    return `orders:${buildOrdersFingerprint(orders, tab)}`;
}

function buildMixedFeed(orders, calls) {
    const items = [
        ...orders.map((o) => ({
            kind: 'order',
            sort_at: o.updated_at,
            data: o,
        })),
        ...calls.map((c) => ({
            kind: 'call',
            sort_at: c.sort_at || c.updated_at,
            data: c,
        })),
    ];
    return items.sort((a, b) => String(b.sort_at).localeCompare(String(a.sort_at)));
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
        </li>`,
        )
        .join('');

    const actions = statusActions(order.status, order.payment_method, order.is_waiter_order)
        .map(
            (a) =>
                `<button type="button" class="live-ops-status-btn ${a.cls}" data-order-id="${order.id}" data-status="${a.status}"${a.payment_method ? ` data-payment-method="${a.payment_method}"` : ''}${a.payment_only ? ' data-payment-only="1"' : ''}${a.disabled ? ' disabled' : ''}>${a.label}</button>`,
        )
        .join('');

    const waiterBadge = order.is_waiter_order
        ? '<span class="live-ops-waiter-badge">🤵 Garson Siparişi</span>'
        : '';

    return `
    <article class="live-ops-order-card rounded-2xl border border-white/5 bg-[#262220]/80 p-4 backdrop-blur-md ${order.is_waiter_order ? 'live-ops-order-card--waiter' : ''}" data-order-id="${order.id}">
        <div class="mb-3 flex items-start justify-between gap-2">
            <div>
                <span class="text-xl font-bold text-[#E67E22]">#${order.order_number}</span>
                ${order.table ? `<span class="ml-2 rounded-full bg-[#E67E22]/15 px-2.5 py-0.5 text-xs font-semibold text-[#E67E22]">Masa ${order.table}</span>` : ''}
                ${waiterBadge}
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

function staffActionLabel(type) {
    const labels = {
        waiter: '🛎️ Garsonu Gönder',
        bill_cash: '💵 Hesabı Götür',
        bill_card: '💳 Pos Gönder',
    };
    return labels[type] || '✓ Tamamlandı';
}

function renderCallCard(call) {
    const actionLabel = staffActionLabel(call.type);

    return `
    <article class="live-ops-call-card animate-pulse rounded-2xl border-2 border-[#E67E22]/50 bg-[#E67E22]/15 p-5 shadow-lg shadow-[#E67E22]/10" data-call-id="${call.id}">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-2xl font-black leading-tight tracking-wide text-[#E67E22] sm:text-3xl">${call.headline || `MASA ${call.table ?? '?'}`}</p>
                <p class="mt-2 text-xs text-[#D4C5B9]">Masa ${call.table ?? '—'} · ${call.type_label} · ${call.created_at}</p>
            </div>
            <button type="button" class="live-ops-resolve-call shrink-0 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-md transition hover:bg-emerald-500" data-call-id="${call.id}">
                ${actionLabel}
            </button>
        </div>
    </article>`;
}

function renderGrid(orders, calls, completedOrders, tab) {
    if (tab === 'calls') {
        if (!calls.length) {
            return '<p class="py-20 text-center text-[#D4C5B9]">Aktif masa çağrısı yok</p>';
        }
        return `<div class="grid grid-cols-1 gap-3 lg:grid-cols-2">${calls.map((c) => renderCallCard(c)).join('')}</div>`;
    }

    if (tab === 'completed') {
        if (!completedOrders.length) {
            return '<p class="py-20 text-center text-[#D4C5B9]">Henüz tamamlanan sipariş yok</p>';
        }
        const cards = completedOrders.map((o) => renderOrderCard(o, 'all')).filter(Boolean).join('');
        return `<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">${cards}</div>`;
    }

    if (tab === 'all') {
        const feed = buildMixedFeed(orders, calls);
        if (!feed.length) {
            return '<p class="py-20 text-center text-[#D4C5B9]">Aktif sipariş veya çağrı yok ✨</p>';
        }
        const html = feed
            .map((item) =>
                item.kind === 'call'
                    ? renderCallCard(item.data)
                    : renderOrderCard(item.data, 'all'),
            )
            .filter(Boolean)
            .join('');
        return `<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">${html}</div>`;
    }

    const filtered = filterOrders(orders, tab);
    const cards = filtered.map((o) => renderOrderCard(o, tab)).filter(Boolean).join('');

    if (!cards) {
        const emptyMsg =
            tab === 'bar'
                ? 'Bekleyen içecek siparişi yok ☕'
                : tab === 'kitchen'
                  ? 'Bekleyen mutfak siparişi yok 🍽️'
                  : 'Aktif sipariş yok ✨';
        return `<p class="py-20 text-center text-[#D4C5B9]">${emptyMsg}</p>`;
    }

    return `<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">${cards}</div>`;
}

const root = document.getElementById('liveOrdersApp');
if (root) {
    const apiUrl = root.dataset.apiUrl;
    const statusUrlBase = root.dataset.statusUrl;
    const resolveCallUrlBase = root.dataset.resolveCallUrl;
    const csrf = root.dataset.csrf;
    const baseTitle = root.dataset.pageTitle || document.title;
    const reverbCfg = {
        key: root.dataset.reverbKey || '',
        host: root.dataset.reverbHost || '127.0.0.1',
        port: Number(root.dataset.reverbPort || 8080),
        scheme: root.dataset.reverbScheme || 'http',
    };
    const grid = document.getElementById('liveOrdersGrid');
    const tableMapGrid = document.getElementById('liveTableMapGrid');
    const statusEl = document.getElementById('liveOrdersStatus');
    const clockEl = document.getElementById('liveOrdersClock');
    const tabs = document.querySelectorAll('.live-ops-tab');
    const badges = {
        kitchen: document.querySelector('[data-badge="kitchen"]'),
        bar: document.querySelector('[data-badge="bar"]'),
        calls: document.querySelector('[data-badge="calls"]'),
    };

    let activeTab = 'all';
    let ordersState = [];
    let completedOrdersState = [];
    let callsState = [];
    let dataFingerprint = '';
    let knownOrderIds = new Set();
    let knownCallIds = new Set();
    /** @type {Map<number, { kitchen: boolean, bar: boolean, kitchenDismissed: boolean, barDismissed: boolean }>} */
    const orderNotifications = new Map();
    /** @type {Set<number>} */
    const callNotifications = new Set();
    let tabBadges = { kitchen: 0, bar: 0, calls: 0 };
    let titleAlertCount = 0;
    let initialized = false;
    let pollTimer = null;
    let failCount = 0;
    const intervalMs = 4000;
    const maxIntervalMs = 30000;
    let currentInterval = intervalMs;
    let lastStatusLine = '';
    let tablesState = [];
    let tablesFingerprint = '';
    let knownBusyTableIds = new Set();
    let echoClient = null;

    if (tableMapGrid) {
        tableMapGrid.querySelectorAll('[data-table-id]').forEach((chip) => {
            if (chip.dataset.tableBusy === '1') {
                knownBusyTableIds.add(Number(chip.dataset.tableId));
            }
        });
    }

    function tableChipClass(table) {
        if (!table.is_active) {
            return 'live-table-chip--off';
        }
        if (table.is_busy) {
            return 'live-table-chip--busy';
        }
        return 'live-table-chip--on';
    }

    function tableChipTitle(table) {
        if (!table.is_active) return 'Masa kapalı';
        if (table.is_busy) return 'Sipariş veya çağrı var';
        return 'Masa boş';
    }

    function renderTableMap(tables) {
        if (!tableMapGrid || !tables?.length) return;

        tableMapGrid.innerHTML = tables
            .map(
                (t) => `
            <div
                class="live-table-chip flex flex-col items-center justify-center rounded-xl border px-1 py-2 text-center transition ${tableChipClass(t)}"
                data-table-id="${t.id}"
                data-table-busy="${t.is_busy ? '1' : '0'}"
                data-table-active="${t.is_active ? '1' : '0'}"
                title="${escapeHtml(tableChipTitle(t))}"
            >
                <span class="text-[10px] font-medium uppercase tracking-wide opacity-70">Masa</span>
                <span class="text-lg font-bold leading-none">${escapeHtml(String(t.number))}</span>
            </div>`,
            )
            .join('');
    }

    function paintTableMap(tables, flashNewBusy = false) {
        if (!tableMapGrid) return;

        const fp = JSON.stringify(tables.map((t) => [t.id, t.is_busy, t.is_active]));
        if (fp === tablesFingerprint && !flashNewBusy) return;

        const newBusy = tables.filter((t) => t.is_busy).map((t) => t.id);
        const newlyBusy = flashNewBusy
            ? newBusy.filter((id) => !knownBusyTableIds.has(id))
            : [];

        renderTableMap(tables);
        tablesFingerprint = fp;
        knownBusyTableIds = new Set(newBusy);

        if (newlyBusy.length > 0) {
            newlyBusy.forEach((id) => {
                const chip = tableMapGrid.querySelector(`[data-table-id="${id}"]`);
                chip?.classList.add('live-table-chip--flash');
                setTimeout(() => chip?.classList.remove('live-table-chip--flash'), 700);
            });
        }
    }

    function refreshDocumentTitle() {
        if (document.hidden && titleAlertCount > 0) {
            const label =
                titleAlertCount === 1 ? '1 Yeni Bildirim' : `${titleAlertCount} Yeni Bildirim`;
            document.title = `(${label}!) ${baseTitle}`;
        } else {
            document.title = baseTitle;
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            titleAlertCount = 0;
            document.title = baseTitle;
        } else {
            refreshDocumentTitle();
        }
    });

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
        ['kitchen', 'bar', 'calls'].forEach((key) => {
            const el = badges[key];
            if (!el) return;
            const n = tabBadges[key];
            el.textContent = n > 99 ? '99+' : String(n);
            el.classList.toggle('hidden', n <= 0);
        });
    }

    function dismissNotificationsForTab(tab) {
        if (tab === 'kitchen') {
            for (const entry of orderNotifications.values()) {
                entry.kitchenDismissed = true;
            }
        } else if (tab === 'bar') {
            for (const entry of orderNotifications.values()) {
                entry.barDismissed = true;
            }
        } else if (tab === 'calls') {
            callNotifications.clear();
        }
        syncNotificationBadges();
    }

    function syncNotificationBadges() {
        let kitchen = 0;
        let bar = 0;
        let calls = 0;

        for (const entry of orderNotifications.values()) {
            if (entry.kitchen && !entry.kitchenDismissed) {
                kitchen += 1;
            }
            if (entry.bar && !entry.barDismissed) {
                bar += 1;
            }
        }

        if (activeTab !== 'calls') {
            calls = callNotifications.size;
        }

        tabBadges = { kitchen, bar, calls };
        updateBadgesUI();

        if (document.hidden) {
            titleAlertCount = kitchen + bar + calls;
            refreshDocumentTitle();
        } else if (titleAlertCount > 0) {
            titleAlertCount = 0;
            refreshDocumentTitle();
        }
    }

    function processUpdates(orders, calls) {
        const currentOrderIds = new Set(orders.map((o) => o.id));
        const currentCallIds = new Set(calls.map((c) => c.id));

        for (const id of orderNotifications.keys()) {
            if (!currentOrderIds.has(id)) {
                orderNotifications.delete(id);
            }
        }

        for (const id of callNotifications) {
            if (!currentCallIds.has(id)) {
                callNotifications.delete(id);
            }
        }

        let newOrders = 0;
        let newCalls = 0;

        for (const order of orders) {
            if (!knownOrderIds.has(order.id)) {
                newOrders += 1;
                orderNotifications.set(order.id, {
                    kitchen: !!order.has_kitchen,
                    bar: !!order.has_bar,
                    kitchenDismissed: activeTab === 'kitchen',
                    barDismissed: activeTab === 'bar',
                });
            } else {
                const entry = orderNotifications.get(order.id);
                if (entry) {
                    entry.kitchen = !!order.has_kitchen;
                    entry.bar = !!order.has_bar;
                }
            }
        }

        for (const call of calls) {
            if (!knownCallIds.has(call.id)) {
                newCalls += 1;
                callNotifications.add(call.id);
            }
        }

        if (initialized) {
            if (newCalls > 0) {
                playCallAlert();
            } else if (newOrders > 0) {
                playOrderDing();
            }
        }

        knownOrderIds = currentOrderIds;
        knownCallIds = currentCallIds;
        initialized = true;
        syncNotificationBadges();
    }

    function updateStatusLine() {
        const busyCount = tablesState.filter((t) => t.is_busy).length;
        const line = `${busyCount} aktif masa · ${ordersState.length} canlı · ${completedOrdersState.length} tamamlanan · ${callsState.length} çağrı · ${new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}`;
        if (line === lastStatusLine || !statusEl) return;
        lastStatusLine = line;
        statusEl.textContent = line;
    }

    function paint() {
        const fp = buildViewFingerprint(ordersState, callsState, completedOrdersState, activeTab);
        if (fp === dataFingerprint) {
            return false;
        }

        dataFingerprint = fp;
        grid.innerHTML = renderGrid(ordersState, callsState, completedOrdersState, activeTab);
        bindButtons();
        return true;
    }

    function bindButtons() {
        grid.querySelectorAll('.live-ops-status-btn').forEach((btn) => {
            btn.onclick = async () => {
                const orderId = btn.dataset.orderId;
                const status = btn.dataset.status;
                const payload = { status };
                if (btn.dataset.paymentMethod) {
                    payload.payment_method = btn.dataset.paymentMethod;
                }
                if (btn.dataset.paymentOnly === '1') {
                    payload.payment_only = true;
                }
                btn.disabled = true;
                try {
                    const res = await fetch(`${statusUrlBase}/${orderId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success !== false) {
                        const order = ordersState.find((o) => String(o.id) === String(orderId));
                        if (payload.payment_only) {
                            orderNotifications.delete(Number(orderId));
                            syncNotificationBadges();
                            showAdminToast({
                                title: 'Sipariş kapandı',
                                message: order
                                    ? `#${order.order_number} · ${payload.payment_method === 'card' ? 'Kart' : 'Nakit'}`
                                    : data.message || 'Ödeme kaydedildi',
                                type: 'info',
                            });
                        } else if (status === 'preparing') {
                            showAdminToast({
                                title: 'Hazırlanıyor',
                                message: order
                                    ? `#${order.order_number}${order.table ? ` · Masa ${order.table}` : ''}`
                                    : 'Sipariş mutfağa iletildi',
                                hint: 'Mutfak ve bar ekranında bildirim düşer',
                                type: 'success',
                            });
                        } else if (status === 'ready') {
                            showAdminToast({
                                title: 'Garsona Bildirildi',
                                message: order
                                    ? `#${order.order_number} · Masa ${order.table ?? '—'}`
                                    : 'Sipariş hazır bildirimi gönderildi',
                                hint: 'Garson ekranında masa ve ürün bildirimi açıldı',
                                type: 'success',
                            });
                        } else if (status === 'delivered') {
                            showAdminToast({
                                title: 'Afiyet Olsun',
                                message: order ? `#${order.order_number} masaya gitti` : 'Teslim edildi',
                                hint: 'Ödeme için Nakit veya Kart seçin',
                                type: 'success',
                            });
                        }
                        await poll(true);
                    } else {
                        showAdminToast({
                            title: 'İşlem yapılamadı',
                            message: data.message || 'Tekrar deneyin',
                            type: 'error',
                        });
                    }
                } finally {
                    btn.disabled = false;
                }
            };
        });

        grid.querySelectorAll('.live-ops-resolve-call').forEach((btn) => {
            btn.onclick = async () => {
                const callId = btn.dataset.callId;
                btn.disabled = true;
                try {
                    const res = await fetch(`${resolveCallUrlBase}/${callId}/resolve`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                        },
                    });
                    if (res.ok) {
                        await poll(true);
                    }
                } finally {
                    btn.disabled = false;
                }
            };
        });
    }

    function initRealtimeListeners() {
        echoClient = createEchoClient(reverbCfg);
        if (!echoClient) return;

        echoClient.channel('orders').listen('.OrderPlaced', () => {
            poll(true);
        });

        echoClient.channel('orders').listen('.OrderStatusUpdated', (payload) => {
            const status = String(payload?.status || '');
            if (status === 'ready') {
                showAdminToast({
                    title: 'Mutfakta Hazır',
                    message: `#${payload?.order_number ?? payload?.order_id ?? ''} · Masa ${payload?.table ?? '—'}`,
                    type: 'success',
                    durationMs: 2500,
                });
            }
            if (status === 'delivered') {
                showAdminToast({
                    title: 'Teslim Edildi',
                    message: `#${payload?.order_number ?? payload?.order_id ?? ''} kapatılıyor`,
                    type: 'info',
                    durationMs: 2200,
                });
            }
            poll(true);
        });
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activeTab = tab.dataset.tab;
            tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
            dismissNotificationsForTab(activeTab);
            dataFingerprint = '';
            paint();
        });
    });

    async function poll(forcePaint = false) {
        try {
            const res = await fetch(apiUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('fetch');
            const data = await res.json();
            ordersState = data.orders || [];
            completedOrdersState = data.completed_orders || [];
            callsState = data.calls || [];
            tablesState = data.tables || tablesState;
            const wasInitialized = initialized;
            processUpdates(ordersState, callsState);

            if (forcePaint) {
                dataFingerprint = '';
            }
            paint();
            paintTableMap(tablesState, wasInitialized);
            updateStatusLine();

            failCount = 0;
            currentInterval = intervalMs;
        } catch {
            failCount += 1;
            currentInterval = Math.min(intervalMs * 2 ** failCount, maxIntervalMs);
            if (statusEl && lastStatusLine !== 'err') {
                lastStatusLine = 'err';
                statusEl.textContent = 'Bağlantı bekleniyor…';
            }
        }

        pollTimer = setTimeout(() => poll(false), currentInterval);
    }

    initRealtimeListeners();
    poll(false);
}
