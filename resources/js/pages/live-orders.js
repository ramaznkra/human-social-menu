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

const OVERDUE_MINUTES = 15;

function parseIso(iso) {
    if (!iso) return null;
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? null : d;
}

function formatRelativeAge(iso) {
    const d = parseIso(iso);
    if (!d) return '';
    const mins = Math.floor((Date.now() - d.getTime()) / 60000);
    if (mins < 1) return 'Az önce';
    if (mins < 60) return `${mins} dk önce`;
    const hrs = Math.floor(mins / 60);
    return `${hrs} sa önce`;
}

function isOverdueOrder(order) {
    if (order.status === 'pending_approval') return false;
    if (!['pending', 'preparing'].includes(order.status)) return false;
    const d = parseIso(order.created_at_iso || order.updated_at);
    if (!d) return false;
    return (Date.now() - d.getTime()) / 60000 >= OVERDUE_MINUTES;
}

function filterOrders(orders, tab) {
    if (tab === 'all' || tab === 'calls') return orders;
    const visible = orders.filter((o) => o.status !== 'pending_approval');
    if (tab === 'kitchen') return visible.filter((o) => o.has_kitchen);
    if (tab === 'bar') return visible.filter((o) => o.has_bar);
    if (tab === 'prepared') return visible.filter((o) => o.status === 'ready');
    return visible;
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

    if (status === 'pending_approval') {
        buttons.push({
            status: 'pending_approval',
            label: 'Garson Onayı Bekleniyor',
            cls: 'live-ops-btn-secondary',
            disabled: true,
        });
        return buttons;
    }

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
    return JSON.stringify(
        calls.map((c) => [c.id, c.updated_at, c.type, c.forwarded_to_waiter, c.status, c.waiter_id]),
    );
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
        return `all:${JSON.stringify(feed.map((f) => [f.kind, f.sort_at, f.data.id, f.data.updated_at ?? f.data.status, f.data.forwarded_to_waiter]))}`;
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

    const overdueClass = isOverdueOrder(order) ? ' live-ops-order-card--overdue' : '';
    const createdIso = order.created_at_iso || order.updated_at || '';

    return `
    <article class="live-ops-order-card rounded-2xl border border-white/5 bg-[#262220]/80 p-4 backdrop-blur-md ${order.is_waiter_order ? 'live-ops-order-card--waiter' : ''}${overdueClass}" data-order-id="${order.id}" data-created-at="${escapeHtml(createdIso)}" data-status="${order.status}">
        <div class="mb-3 flex items-start justify-between gap-2">
            <div>
                <span class="text-xl font-bold text-[#E67E22]">#${order.order_number}</span>
                ${order.table ? `<span class="ml-2 rounded-full bg-[#E67E22]/15 px-2.5 py-0.5 text-xs font-semibold text-[#E67E22]">Masa ${order.table}</span>` : ''}
                ${waiterBadge}
            </div>
            <div class="text-right">
                <span class="live-ops-age block text-xs text-[#D4C5B9]">${formatRelativeAge(createdIso) || order.created_at}</span>
                <span class="mt-0.5 inline-block rounded-md bg-white/5 px-2 py-0.5 text-[10px] text-gray-300">${order.status_label}</span>
            </div>
        </div>
        <ul class="space-y-2">${itemsHtml}</ul>
        ${order.notes ? `<p class="mt-3 text-xs italic text-[#D4C5B9]">📝 ${order.notes}</p>` : ''}
        <div class="mt-4 flex flex-wrap gap-2">${actions}</div>
    </article>`;
}

function isBillCall(call) {
    return call.is_bill || call.type === 'bill_cash' || call.type === 'bill_card';
}

function callAssigneeHtml(call) {
    if (call.status === 'in_progress' && call.waiter_name) {
        return `<span class="live-ops-call-assignee">👤 Garson ${escapeHtml(call.waiter_name)} ilgileniyor</span>`;
    }
    return '';
}

function callActionsHtml(call) {
    const id = call.id;
    const assignee = callAssigneeHtml(call);

    // Garson çağrısı: doğrudan tamamla
    if (!isBillCall(call)) {
        return `<div class="flex flex-wrap items-center justify-end gap-2">${assignee}<button type="button" class="live-ops-resolve-call shrink-0 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-md transition hover:bg-emerald-500" data-call-id="${id}">🛎️ Garsonu Gönder</button></div>`;
    }

    // Hesap (POS) çağrısı
    const forwardBtn = call.forwarded_to_waiter
        ? `<span class="inline-flex items-center gap-1 rounded-lg bg-emerald-500/15 px-3 py-2 text-xs font-semibold text-emerald-300">✓ Garsona iletildi</span>`
        : `<button type="button" class="live-ops-forward-call rounded-xl bg-[#E67E22] px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#d06f15]" data-call-id="${id}">➜ Garsona Yönlendir (POS)</button>`;

    const closeBtns = `
        <button type="button" class="live-ops-close-call rounded-xl bg-emerald-600 px-3 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-500" data-call-id="${id}" data-payment-method="cash">💵 Nakit · Kapat</button>
        <button type="button" class="live-ops-close-call rounded-xl bg-sky-600 px-3 py-2.5 text-sm font-bold text-white transition hover:bg-sky-500" data-call-id="${id}" data-payment-method="card">💳 Kart · Kapat</button>`;

    return `<div class="flex flex-wrap items-center justify-end gap-2">${assignee}${forwardBtn}${closeBtns}</div>`;
}

function renderCallCard(call) {
    const forwarded = isBillCall(call) && call.forwarded_to_waiter;

    return `
    <article class="live-ops-call-card ${forwarded ? '' : 'animate-pulse'} rounded-2xl border-2 border-[#E67E22]/50 bg-[#E67E22]/15 p-5 shadow-lg shadow-[#E67E22]/10" data-call-id="${call.id}">
        <div class="flex flex-col gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-2xl font-black leading-tight tracking-wide text-[#E67E22] sm:text-3xl">${call.headline || `MASA ${call.table ?? '?'}`}</p>
                <p class="mt-2 text-xs text-[#D4C5B9]">Masa ${call.table ?? '—'} · ${call.type_label} · ${call.created_at}${forwarded ? ' · Garson yolda' : ''}${call.status === 'in_progress' && call.waiter_name ? ` · Garson ${escapeHtml(call.waiter_name)}` : ''}</p>
            </div>
            ${callActionsHtml(call)}
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
    const restaurantId = root.dataset.restaurantId || '';
    const ordersChannelName = restaurantId ? `orders.${restaurantId}` : 'orders';
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

    let activeTab = root.dataset.defaultTab || 'all';
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
    let realtimeConnected = false;
    const fallbackIntervalMs = 30000;

    function normalizeRealtimeOrder(raw) {
        if (!raw?.id) return null;

        const items = (raw.items || []).map((i) => ({
            id: i.id,
            name: i.name,
            quantity: i.quantity,
            notes: i.notes ?? null,
            type: i.type ?? 'kitchen',
        }));
        const types = new Set(items.map((i) => i.type));

        return {
            id: raw.id,
            order_number: raw.order_number,
            status: raw.status,
            status_label: raw.status_label,
            source: raw.source,
            source_label: raw.source_label,
            is_waiter_order: !!raw.is_waiter_order,
            payment_method: raw.payment_method ?? null,
            table: raw.table ?? null,
            notes: raw.notes ?? null,
            total: raw.total,
            created_at: raw.created_at,
            created_at_iso: raw.created_at_iso ?? raw.updated_at,
            updated_at: raw.updated_at ?? new Date().toISOString(),
            has_kitchen: raw.has_kitchen ?? types.has('kitchen'),
            has_bar: raw.has_bar ?? types.has('bar'),
            items,
        };
    }

    function upsertOrder(order) {
        const idx = ordersState.findIndex((o) => o.id === order.id);
        if (idx >= 0) {
            ordersState[idx] = { ...ordersState[idx], ...order };
        } else {
            ordersState.unshift(order);
        }
        ordersState.sort((a, b) => String(b.updated_at).localeCompare(String(a.updated_at)));
    }

    function applyOrderStatusUpdate(payload) {
        const orderId = Number(payload?.order_id);
        const status = String(payload?.status || '');
        if (!Number.isFinite(orderId) || !status) return false;

        const order = ordersState.find((o) => o.id === orderId);
        if (!order) return false;

        order.status = status;
        order.updated_at = new Date().toISOString();
        if (payload?.payment_method) {
            order.payment_method = payload.payment_method;
        }

        if (status === 'delivered' || status === 'cancelled') {
            ordersState = ordersState.filter((o) => o.id !== orderId);
            orderNotifications.delete(orderId);
        }

        return true;
    }

    function handleOrderCreated(payload) {
        const order = normalizeRealtimeOrder(payload?.order);
        if (!order) {
            poll(true);
            return;
        }

        if (order.status === 'pending_approval') {
            upsertOrder(order);
            knownOrderIds.add(order.id);
            dataFingerprint = '';
            paint();
            updateStatusLine();
            showAdminToast({
                title: 'Onay Bekleyen Sipariş',
                message: `#${order.order_number} · Masa ${order.table ?? '—'} · Garson onayı`,
                type: 'warning',
                durationMs: 3200,
            });
            return;
        }

        upsertOrder(order);
        knownOrderIds.add(order.id);
        orderNotifications.set(order.id, {
            kitchen: !!order.has_kitchen,
            bar: !!order.has_bar,
            kitchenDismissed: activeTab === 'kitchen',
            barDismissed: activeTab === 'bar',
        });

        if (initialized) {
            playOrderDing();
        }

        syncNotificationBadges();
        dataFingerprint = '';
        paint();
        updateStatusLine();

        showAdminToast({
            title: 'Yeni Sipariş',
            message: `#${order.order_number} · Masa ${order.table ?? '—'}`,
            type: 'success',
            durationMs: 2500,
        });
    }

    function setRealtimeInterval(connected) {
        realtimeConnected = connected;
        currentInterval = connected ? fallbackIntervalMs : intervalMs;
        lastStatusLine = '';
        updateStatusLine();
    }

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
        const wsPrefix = realtimeConnected ? 'WebSocket · ' : '';
        const line = `${wsPrefix}${busyCount} aktif masa · ${ordersState.length} canlı · ${completedOrdersState.length} tamamlanan · ${callsState.length} çağrı · ${new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}`;
        if (line === lastStatusLine || !statusEl) return;
        lastStatusLine = line;
        statusEl.textContent = line;
    }

    function refreshOrderAgeLabels() {
        if (!grid) return;
        grid.querySelectorAll('.live-ops-order-card[data-created-at]').forEach((card) => {
            const iso = card.dataset.createdAt;
            const status = card.dataset.status;
            const ageEl = card.querySelector('.live-ops-age');
            if (ageEl) {
                ageEl.textContent = formatRelativeAge(iso) || ageEl.textContent;
            }
            const overdue =
                ['pending', 'preparing'].includes(status) &&
                parseIso(iso) &&
                (Date.now() - parseIso(iso).getTime()) / 60000 >= OVERDUE_MINUTES;
            card.classList.toggle('live-ops-order-card--overdue', !!overdue);
        });
    }

    function handleCallUpdated(payload) {
        const call = payload?.call;
        if (!call?.id) {
            poll(true);
            return;
        }

        if (call.status === 'completed') {
            callsState = callsState.filter((c) => c.id !== call.id);
            callNotifications.delete(Number(call.id));
        } else {
            const idx = callsState.findIndex((c) => c.id === call.id);
            if (idx >= 0) {
                callsState[idx] = { ...callsState[idx], ...call };
            } else {
                callsState.unshift(call);
                if (initialized) {
                    playCallAlert();
                }
                callNotifications.add(Number(call.id));
            }
        }

        syncNotificationBadges();
        dataFingerprint = '';
        paint();
        updateStatusLine();
    }

    function paint() {
        const fp = buildViewFingerprint(ordersState, callsState, completedOrdersState, activeTab);
        if (fp === dataFingerprint) {
            refreshOrderAgeLabels();
            return false;
        }

        dataFingerprint = fp;
        grid.innerHTML = renderGrid(ordersState, callsState, completedOrdersState, activeTab);
        bindButtons();
        refreshOrderAgeLabels();
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

        grid.querySelectorAll('.live-ops-forward-call').forEach((btn) => {
            btn.onclick = async () => {
                const callId = btn.dataset.callId;
                btn.disabled = true;
                try {
                    const res = await fetch(`${resolveCallUrlBase}/${callId}/forward`, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success !== false) {
                        showAdminToast({
                            title: 'Garsona Yönlendirildi',
                            message: 'POS / masa bildirimi gönderildi',
                            type: 'success',
                            durationMs: 2200,
                        });
                        await poll(true);
                    } else {
                        showAdminToast({
                            title: 'Yönlendirilemedi',
                            message: data.message || 'Tekrar deneyin',
                            type: 'error',
                        });
                    }
                } finally {
                    btn.disabled = false;
                }
            };
        });

        grid.querySelectorAll('.live-ops-close-call').forEach((btn) => {
            btn.onclick = async () => {
                const callId = btn.dataset.callId;
                const paymentMethod = btn.dataset.paymentMethod || 'cash';
                btn.disabled = true;
                try {
                    const res = await fetch(`${resolveCallUrlBase}/${callId}/resolve`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ payment_method: paymentMethod }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success !== false) {
                        callNotifications.delete(Number(callId));
                        syncNotificationBadges();
                        showAdminToast({
                            title: 'Hesap Kapatıldı',
                            message: `${paymentMethod === 'card' ? 'Kart' : 'Nakit'} · ${data.message || ''}`.trim(),
                            type: 'info',
                            durationMs: 2400,
                        });
                        await poll(true);
                    } else {
                        showAdminToast({
                            title: 'Kapatılamadı',
                            message: data.message || 'Tekrar deneyin',
                            type: 'error',
                        });
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

        const connection = echoClient.connector?.pusher?.connection;
        if (connection) {
            connection.bind('connected', () => setRealtimeInterval(true));
            connection.bind('disconnected', () => {
                setRealtimeInterval(false);
                poll(true);
            });
            if (connection.state === 'connected') {
                setRealtimeInterval(true);
            }
        }

        echoClient.channel(ordersChannelName).listen('.OrderCreated', (payload) => {
            handleOrderCreated(payload);
        });

        echoClient.channel(ordersChannelName).listen('.OrderStatusUpdated', (payload) => {
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

            if (!applyOrderStatusUpdate(payload)) {
                poll(true);
                return;
            }

            if (status === 'delivered' || status === 'cancelled') {
                poll(true);
                return;
            }

            syncNotificationBadges();
            dataFingerprint = '';
            paint();
            updateStatusLine();
        });

        echoClient.channel(ordersChannelName).listen('.TableCallReceived', (payload) => {
            const call = payload?.call;
            if (call && isBillCall(call)) {
                playCallAlert();
                showAdminToast({
                    title: 'Hesap İsteniyor',
                    message: `Masa ${call.table ?? '—'} · ${call.type_label ?? ''} · POS hazırla`,
                    type: 'warning',
                    durationMs: 3000,
                });
            }
            poll(true);
        });

        echoClient.channel(ordersChannelName).listen('.TableCallForwarded', () => {
            poll(true);
        });

        echoClient.channel(ordersChannelName).listen('.TableCallUpdated', (payload) => {
            handleCallUpdated(payload);
        });
    }

    tabs.forEach((tab) => {
        tab.classList.toggle('is-active', tab.dataset.tab === activeTab);
        tab.addEventListener('click', () => {
            activeTab = tab.dataset.tab;
            tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
            dismissNotificationsForTab(activeTab);
            dataFingerprint = '';
            paint();
        });
    });

    setInterval(refreshOrderAgeLabels, 30000);

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
