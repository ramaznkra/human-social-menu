import { showAdminToast } from '../admin-toast.js';
import { createEchoClient } from '../echo.js';

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function initWaiterDashboard() {
    const cfg = window.HSP_WAITER;
    if (!cfg) return;

    const feedEl = document.getElementById('waiterFeed');
    const statusEl = document.getElementById('waiterFeedStatus');
    const liveBadge = document.getElementById('waiterLiveBadge');
    const installBtn = document.getElementById('waiterInstallBtn');
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let pollTimer = null;
    let completing = false;
    let echoClient = null;
    let audioCtx = null;
    let deferredInstallPrompt = null;
    let knownOrderIds = new Set();
    let knownCallIds = new Set();
    let hasBootstrappedOrders = false;
    let hasBootstrappedCalls = false;
    const readyAlertIds = new Set();
    const recentlyCompletedIds = new Set();

    function setLive(ok) {
        if (!liveBadge) return;
        liveBadge.classList.toggle('waiter-live-badge--on', ok);
        liveBadge.classList.toggle('waiter-live-badge--off', !ok);
    }

    // Garson; garson çağrılarını ve hesap (POS) çağrılarını anında görür (kasayla eş zamanlı).
    function waiterShouldShowCall(c) {
        return ['waiter', 'bill_cash', 'bill_card', 'bill'].includes(String(c?.type));
    }

    function buildFeed(orders, calls) {
        const items = [];

        (calls || [])
            .filter(waiterShouldShowCall)
            .forEach((c) => {
            items.push({
                kind: 'call',
                sort: c.sort_at || c.updated_at || '',
                data: c,
            });
        });

        (orders || [])
            .filter((o) => String(o.status) === 'ready')
            .forEach((o) => {
            items.push({
                kind: 'order',
                sort: o.updated_at || '',
                data: o,
            });
        });

        items.sort((a, b) => String(b.sort).localeCompare(String(a.sort)));

        return items;
    }

    function callCardClass(type) {
        if (type === 'waiter') return 'waiter-feed-card--call-waiter';
        if (type === 'bill_cash' || type === 'bill_card' || type === 'bill') return 'waiter-feed-card--call-bill';
        return 'waiter-feed-card--call';
    }

    function callActionsHtml(call) {
        const isBill = ['bill_cash', 'bill_card', 'bill'].includes(String(call.type));
        if (isBill) {
            return `
                <p class="waiter-feed-card__pay-label">Hesabı kapat</p>
                <div class="waiter-feed-card__pay-actions">
                    <button type="button" class="waiter-feed-card__action waiter-feed-card__action--cash" data-complete="1" data-payment="cash">
                        💵 Nakit
                    </button>
                    <button type="button" class="waiter-feed-card__action waiter-feed-card__action--card" data-complete="1" data-payment="card">
                        💳 Kart
                    </button>
                </div>`;
        }

        return `
            <button type="button" class="waiter-feed-card__action" data-complete="1">
                ✓ Tamamlandı / Kapat
            </button>`;
    }

    function renderCallCard(call) {
        return `
            <article class="waiter-feed-card ${callCardClass(call.type)}" data-feed-kind="call" data-feed-id="${call.id}">
                <div class="waiter-feed-card__top">
                    <span class="waiter-feed-card__badge">Çağrı</span>
                    <span class="waiter-feed-card__time">${escapeHtml(call.created_at || '')}</span>
                </div>
                <p class="waiter-feed-card__headline">${escapeHtml(call.headline || call.type_label || 'Masa çağrısı')}</p>
                <p class="waiter-feed-card__meta">Masa ${escapeHtml(call.table ?? '—')} · ${escapeHtml(call.type_label || '')}</p>
                ${callActionsHtml(call)}
            </article>`;
    }

    function orderActionsHtml(order) {
        if (order.status === 'ready') {
            return `
                <button type="button" class="waiter-feed-card__action" data-complete="1">
                    ✓ Teslim Edildi
                </button>`;
        }

        return `<p class="waiter-feed-card__wait">Merkez panelde hazır olunca burada görünür</p>`;
    }

    function renderOrderCard(order) {
        const items = (order.items || [])
            .slice(0, 4)
            .map((i) => `${escapeHtml(i.name)} ×${i.quantity}`)
            .join(', ');
        const more = (order.items || []).length > 4 ? '…' : '';
        const waiterTag = order.is_waiter_order
            ? '<span class="waiter-feed-card__tag">Garson siparişi</span>'
            : '';

        const isReadyAlert = readyAlertIds.has(Number(order.id));
        const readyAlertBadge = isReadyAlert
            ? '<p class="waiter-ready-alert">Mutfakta Hazir!</p>'
            : '';

        return `
            <article class="waiter-feed-card waiter-feed-card--order${isReadyAlert ? ' waiter-feed-card--ready-alert' : ''}" data-feed-kind="order" data-feed-id="${order.id}">
                <div class="waiter-feed-card__top">
                    <span class="waiter-feed-card__badge">Sipariş #${escapeHtml(order.order_number)}</span>
                    <span class="waiter-feed-card__time">${escapeHtml(order.created_at || '')}</span>
                </div>
                ${waiterTag}
                ${readyAlertBadge}
                <p class="waiter-feed-card__headline">Masa ${escapeHtml(order.table ?? '—')} · ${escapeHtml(order.status_label || '')}</p>
                <p class="waiter-feed-card__meta">${items}${more}</p>
                <p class="waiter-feed-card__price">${Math.round(order.total || 0).toLocaleString('tr-TR')} ₺</p>
                ${orderActionsHtml(order)}
            </article>`;
    }

    function renderFeed(orders, calls) {
        if (!feedEl) return;

        const items = buildFeed(orders, calls);

        if (!items.length) {
            feedEl.innerHTML = '<p class="waiter-feed__empty">Bekleyen çağrı veya sipariş yok ✨</p>';
            if (statusEl) statusEl.textContent = 'Şu an bekleyen iş yok';
            return;
        }

        feedEl.innerHTML = items
            .map((item) => (item.kind === 'call' ? renderCallCard(item.data) : renderOrderCard(item.data)))
            .join('');

        if (statusEl) {
            statusEl.textContent = `${items.length} aktif kayıt · ${new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}`;
        }
    }

    function initAudioUnlock() {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx || audioCtx) return;

        const unlock = async () => {
            if (!audioCtx) {
                audioCtx = new AudioCtx();
            }

            if (audioCtx.state === 'suspended') {
                try {
                    await audioCtx.resume();
                } catch {
                    // no-op
                }
            }

            if (audioCtx.state === 'running') {
                document.removeEventListener('pointerdown', unlock);
                document.removeEventListener('keydown', unlock);
            }
        };

        document.addEventListener('pointerdown', unlock, { passive: true });
        document.addEventListener('keydown', unlock);
    }

    function playBip() {
        try {
            if (!audioCtx || audioCtx.state !== 'running') return;
            const oscillator = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.0001, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.12, audioCtx.currentTime + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.24);
            oscillator.connect(gain);
            gain.connect(audioCtx.destination);
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.25);
        } catch {
            // no-op: browser/autoplay can block sound
        }

        try {
            if (navigator.vibrate) {
                navigator.vibrate([120, 40, 120]);
            }
        } catch {
            // no-op
        }
    }

    function playReadyRing() {
        try {
            if (!audioCtx || audioCtx.state !== 'running') return;
            const tones = [1244, 1567, 1244];
            tones.forEach((freq, index) => {
                const startAt = audioCtx.currentTime + index * 0.16;
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, startAt);
                gain.gain.setValueAtTime(0.0001, startAt);
                gain.gain.exponentialRampToValueAtTime(0.18, startAt + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.0001, startAt + 0.14);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(startAt);
                osc.stop(startAt + 0.15);
            });
        } catch {
            // no-op
        }

        try {
            if (navigator.vibrate) {
                navigator.vibrate([180, 60, 180, 60, 240]);
            }
        } catch {
            // no-op
        }
    }

    function prependRealtimeOrder(order) {
        if (!feedEl || !order?.id) return;

        const existing = feedEl.querySelector(`[data-feed-kind="order"][data-feed-id="${order.id}"]`);
        existing?.remove();

        const empty = feedEl.querySelector('.waiter-feed__empty');
        empty?.remove();

        const wrap = document.createElement('div');
        wrap.innerHTML = renderOrderCard(order).trim();
        const card = wrap.firstElementChild;
        if (!card) return;
        feedEl.prepend(card);

        const total = feedEl.querySelectorAll('[data-feed-kind]').length;
        if (statusEl) {
            statusEl.textContent = `${total} aktif kayıt · ${new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}`;
        }
    }

    function prependRealtimeCall(call) {
        if (!feedEl || !call?.id) return;

        const existing = feedEl.querySelector(`[data-feed-kind="call"][data-feed-id="${call.id}"]`);
        existing?.remove();

        const empty = feedEl.querySelector('.waiter-feed__empty');
        empty?.remove();

        const wrap = document.createElement('div');
        wrap.innerHTML = renderCallCard(call).trim();
        const card = wrap.firstElementChild;
        if (!card) return;
        feedEl.prepend(card);

        const total = feedEl.querySelectorAll('[data-feed-kind]').length;
        if (statusEl) {
            statusEl.textContent = `${total} aktif kayıt · ${new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}`;
        }
    }

    function initRealtimeOrders() {
        echoClient = createEchoClient(cfg.reverb || {});
        if (!echoClient) return;

        // Hem garson çağrısı hem hesap (POS) çağrısı garsona anında düşer (kasayla eş zamanlı).
        echoClient.channel('orders').listen('.TableCallReceived', (payload) => {
            const call = payload?.call;
            if (!call?.id) return;
            knownCallIds.add(Number(call.id));
            prependRealtimeCall(call);
            playReadyRing();
            const isBill = ['bill_cash', 'bill_card', 'bill'].includes(String(call.type));
            showAdminToast({
                title: isBill ? 'Hesap İstendi · Masaya Git' : 'Garson çağrısı',
                message: call.headline || `Masa ${call.table ?? '—'} · ${call.type_label ?? ''}`,
                type: 'warning',
                durationMs: isBill ? 5000 : 4200,
            });
        });

        // Kasa hesap çağrısını ayrıca yönlendirdiğinde POS hatırlatması.
        echoClient.channel('orders').listen('.TableCallForwarded', (payload) => {
            const call = payload?.call;
            if (!call?.id) return;
            knownCallIds.add(Number(call.id));
            prependRealtimeCall(call);
            playReadyRing();
            showAdminToast({
                title: 'POS · Masaya Git',
                message: `Masa ${call.table ?? '—'} · ${call.type_label ?? 'Hesap'} · POS hazır`,
                type: 'warning',
                durationMs: 5000,
            });
        });

        echoClient.channel('orders').listen('.OrderPlaced', (payload) => {
            const order = payload?.order;
            if (!order) return;
            prependRealtimeOrder(order);
            playBip();
            showAdminToast({
                title: 'Yeni sipariş',
                message: `#${order.order_number ?? order.id} · Masa ${order.table ?? '—'}`,
                type: 'info',
                durationMs: 3200,
            });
        });

        echoClient.channel('orders').listen('.OrderStatusUpdated', (payload) => {
            const orderId = Number(payload?.order_id);
            const status = String(payload?.status || '');
            if (!Number.isFinite(orderId)) return;

            if (status !== 'ready') {
                readyAlertIds.delete(orderId);
                const existingCard = feedEl?.querySelector(`[data-feed-kind="order"][data-feed-id="${orderId}"]`);
                existingCard?.classList.remove('waiter-feed-card--ready-alert');
                existingCard?.querySelector('.waiter-ready-alert')?.remove();

                // Garson bu siparişi kendi kapattıysa tekrar bildirim gösterme.
                const selfHandled = recentlyCompletedIds.has(orderId);
                recentlyCompletedIds.delete(orderId);

                if ((status === 'delivered' || status === 'cancelled') && !selfHandled) {
                    const payment = String(payload?.payment_method || '');
                    const paymentText = payment === 'cash'
                        ? ' · Nakit ile kapatıldı'
                        : payment === 'card'
                            ? ' · Kart ile kapatıldı'
                            : '';
                    const closedTitle = status === 'cancelled' ? 'Sipariş iptal edildi' : 'Sipariş kapatıldı';

                    // Garson kartı görüyorduysa sesli + görsel bildir.
                    if (existingCard) {
                        playBip();
                    }
                    showAdminToast({
                        title: closedTitle,
                        message: `Masa ${payload?.table ?? '—'} · #${payload?.order_number ?? orderId}${paymentText}`,
                        type: status === 'cancelled' ? 'error' : 'success',
                        durationMs: 4200,
                    });

                    if (existingCard) {
                        existingCard.classList.add('waiter-feed-card--out');
                        setTimeout(() => {
                            existingCard.remove();
                            if (!feedEl?.querySelector('[data-feed-kind]')) {
                                feedEl.innerHTML = '<p class="waiter-feed__empty">Bekleyen çağrı veya sipariş yok ✨</p>';
                                if (statusEl) statusEl.textContent = 'Şu an bekleyen iş yok';
                            }
                        }, 280);
                    }
                }

                if (status === 'delivered' || status === 'cancelled') {
                    poll();
                }
                return;
            }

            readyAlertIds.add(orderId);
            playReadyRing();
            const itemsText = (payload?.items || [])
                .slice(0, 3)
                .map((i) => `${i.quantity}x ${i.name}`)
                .join(', ');
            const more = (payload?.items || []).length > 3 ? '…' : '';
            showAdminToast({
                title: 'Mutfakta Hazir!',
                message: `Masa ${payload?.table ?? '—'} · ${itemsText || `#${payload?.order_number ?? orderId}`}${more}`,
                type: 'success',
                durationMs: 4200,
            });

            const card = feedEl?.querySelector(`[data-feed-kind="order"][data-feed-id="${orderId}"]`);
            if (card) {
                card.classList.add('waiter-feed-card--ready-alert');
                if (!card.querySelector('.waiter-ready-alert')) {
                    card.querySelector('.waiter-feed-card__headline')?.insertAdjacentHTML(
                        'beforebegin',
                        '<p class="waiter-ready-alert">Mutfakta Hazir!</p>',
                    );
                }
            } else {
                poll();
            }
        });
    }

    function initInstallPrompt() {
        if (!installBtn) return;

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
            installBtn.hidden = false;
        });

        window.addEventListener('appinstalled', () => {
            deferredInstallPrompt = null;
            installBtn.hidden = true;
        });

        installBtn.addEventListener('click', async () => {
            if (!deferredInstallPrompt) return;
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            installBtn.hidden = true;
            showAdminToast({
                title: 'Kurulum',
                message: 'Uygulama yükleme istegi gonderildi.',
                type: 'info',
                durationMs: 2400,
            });
        });
    }

    async function poll() {
        try {
            const res = await fetch(cfg.feedUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('feed');
            const data = await res.json();
            const orders = (data.orders || []).filter((o) => String(o.status) === 'ready');
            const nextOrderIds = new Set(orders.map((o) => Number(o.id)).filter((id) => Number.isFinite(id)));

            const calls = (data.calls || []).filter(waiterShouldShowCall);
            const nextCallIds = new Set(calls.map((c) => Number(c.id)).filter((id) => Number.isFinite(id)));

            if (hasBootstrappedOrders) {
                const newOrders = orders.filter((o) => !knownOrderIds.has(Number(o.id)));
                if (newOrders.length) {
                    playBip();
                    const first = newOrders[0];
                    showAdminToast({
                        title: 'Yeni sipariş',
                        message: `#${first.order_number ?? first.id} · Masa ${first.table ?? '—'}`,
                        type: 'info',
                        durationMs: 3200,
                    });
                }
            }

            if (hasBootstrappedCalls) {
                const newCalls = calls.filter((c) => !knownCallIds.has(Number(c.id)));
                if (newCalls.length) {
                    playReadyRing();
                    const first = newCalls[0];
                    showAdminToast({
                        title: 'Masa çağrısı',
                        message: first.headline || `Masa ${first.table ?? '—'} · ${first.type_label ?? ''}`,
                        type: 'warning',
                        durationMs: 4000,
                    });
                }
            }

            knownOrderIds = nextOrderIds;
            knownCallIds = nextCallIds;
            hasBootstrappedOrders = true;
            hasBootstrappedCalls = true;

            renderFeed(orders, data.calls || []);
            setLive(true);
        } catch {
            setLive(false);
            if (statusEl) statusEl.textContent = 'Bağlantı koptu — yeniden deneniyor…';
        }
    }

    async function completeItem(kind, id, btn, paymentMethod = null) {
        if (completing) return;
        completing = true;
        const card = btn.closest('[data-feed-kind]');
        card?.querySelectorAll('[data-complete]').forEach((b) => {
            b.disabled = true;
        });
        const original = btn.textContent;
        btn.textContent = '…';

        const payload = { type: kind, id };
        if (paymentMethod) payload.payment_method = paymentMethod;

        try {
            const res = await fetch(cfg.completeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                showAdminToast({
                    title: 'İşlem yapılamadı',
                    message: data?.message || 'Tekrar deneyin.',
                    type: 'error',
                });
                card?.querySelectorAll('[data-complete]').forEach((b) => {
                    b.disabled = false;
                    if (b === btn) b.textContent = original;
                });
                completing = false;
                return;
            }

            if (kind === 'order') {
                readyAlertIds.delete(Number(id));
                // Realtime 'delivered' yayını geri geldiğinde çift bildirim olmasın.
                recentlyCompletedIds.add(Number(id));
                setTimeout(() => recentlyCompletedIds.delete(Number(id)), 10000);
            }
            card?.classList.add('waiter-feed-card--out');
            setTimeout(() => {
                card?.remove();
                if (!feedEl?.querySelector('[data-feed-kind]')) {
                    feedEl.innerHTML = '<p class="waiter-feed__empty">Bekleyen çağrı veya sipariş yok ✨</p>';
                    if (statusEl) statusEl.textContent = 'Şu an bekleyen iş yok';
                }
            }, 280);

            showAdminToast({
                title: 'Tamam',
                message: data.message || 'Kapatıldı.',
                type: 'success',
                durationMs: 2800,
            });

            await poll();
        } catch {
            showAdminToast({
                title: 'Bağlantı hatası',
                message: 'İnternet bağlantısını kontrol edin.',
                type: 'error',
            });
            card?.querySelectorAll('[data-complete]').forEach((b) => {
                b.disabled = false;
                if (b === btn) b.textContent = original;
            });
        }

        completing = false;
    }

    feedEl?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-complete]');
        if (!btn) return;
        const card = btn.closest('[data-feed-kind]');
        if (!card) return;
        completeItem(card.dataset.feedKind, Number(card.dataset.feedId), btn, btn.dataset.payment || null);
    });

    poll();
    pollTimer = setInterval(poll, cfg.pollMs || 4000);
    initAudioUnlock();
    initRealtimeOrders();
    initInstallPrompt();

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollTimer);
        } else {
            poll();
            pollTimer = setInterval(poll, cfg.pollMs || 4000);
        }
    });

    window.addEventListener('waiter:refresh', () => poll());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWaiterDashboard);
} else {
    initWaiterDashboard();
}
