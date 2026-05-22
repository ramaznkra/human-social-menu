@extends('layouts.menu')

@section('title', ($settings['venue_name'] ?? 'Human') . ' — Menü')

@section('content')
@php
    $brandMark = $settings['brand_mark'] ?? 'HSP';
    $tagline = $settings['venue_tagline'] ?? 'Human Social Person — Coffee, Community, Experiences.';
    $firstCategoryId = $categories->first()?->id;
@endphp

@php
    $spotifyUrl = trim($settings['spotify_url'] ?? '');
    $instagramUrl = trim($settings['instagram_url'] ?? '');
    $instagramHandle = $settings['instagram_handle'] ?? '@ramaznkra';
    $orderOn = ($settings['order_enabled'] ?? '1') === '1';
    /** Sosyal widget düzeni: true = 2'li grid kartlar (v2), false = eski dikey düzen */
    $socialWidgetsGrid = true;
    $scrollPad = 'menu-scroll-pad pb-36';
    if ($table && $orderOn) {
        $scrollPad .= ' menu-scroll-pad--table-cart';
    } elseif ($table) {
        $scrollPad .= ' menu-scroll-pad--table';
    }
@endphp
<main class="menu-shell {{ $scrollPad }}" id="menuApp">
    {{-- Header / Logo --}}
    <header class="menu-header relative px-5 pt-6 pb-2 text-center md:px-8">
        @if($table)
        <span class="absolute top-4 right-4 rounded-full border border-[#E67E22]/30 bg-[#E67E22]/15 px-2.5 py-0.5 text-[10px] font-semibold text-[#E67E22]">
            Masa {{ $table->number }}
        </span>
        @endif
        <h1 class="menu-logo">{{ $brandMark }}</h1>
        <p class="menu-tagline">{{ $tagline }}</p>

    </header>

    {{-- Social Spotted --}}
    @if($spottedSliders->isNotEmpty())
    <section class="spotted-section pt-2 pb-1" aria-label="Social Spotted">
        <div id="spottedCarousel" class="spotted-hero">
            <div class="spotted-hero-track" data-spotted-track>
                @foreach($spottedSliders as $slider)
                <article class="spotted-hero-slide" data-spotted-card>
                    <div class="spotted-hero-card">
                        <img
                            src="{{ $slider->image_url }}"
                            alt="{{ $slider->title ?? 'HSP Moments' }}"
                            class="spotted-hero-img"
                            loading="lazy"
                            draggable="false"
                        >
                        <span class="spotted-hero-badge">{{ $slider->badge_text ?: 'Spotted at HSP ✨' }}</span>
                        @if($slider->description)
                        <div class="spotted-hero-caption">
                            <p>{{ $slider->description }}</p>
                        </div>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
            @if($spottedSliders->count() > 1)
            <div class="mt-3 flex justify-center gap-1.5">
                @foreach($spottedSliders as $i => $slider)
                <button
                    type="button"
                    data-spotted-dot
                    data-index="{{ $i }}"
                    class="h-1.5 rounded-full transition-all {{ $i === 0 ? 'w-5 bg-[#E67E22]' : 'w-1.5 bg-white/35' }}"
                    aria-label="Slayt {{ $i + 1 }}"
                ></button>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endif

    @if(($settings['show_motto_banner'] ?? '0') === '1' && !empty($settings['daily_motto']))
    <p class="mx-5 mb-2 text-center text-xs font-light italic text-[#D4C5B9]/90">{{ $settings['daily_motto'] }}</p>
    @endif

    {{-- Arama (kompakt) --}}
    <div class="px-5 pb-3 md:px-8">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#D4C5B9]/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="search" id="menuSearch" placeholder="Menüde ara..." autocomplete="off" class="menu-search-input w-full pl-9">
            <button type="button" id="searchClear" class="search-clear absolute right-2 top-1/2 h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full text-[#D4C5B9]">×</button>
        </div>
    </div>

    {{-- Kategori pill'leri --}}
    @if($categories->isNotEmpty())
    <nav class="category-pills-nav px-5 md:px-8" aria-label="Kategoriler">
        <div class="category-pills" id="categoryPills">
            @foreach($categories as $cat)
            <button
                type="button"
                class="category-pill {{ $cat->id === $firstCategoryId ? 'is-active' : '' }}"
                data-category-id="{{ $cat->id }}"
            >{{ $cat->name }}</button>
            @endforeach
        </div>
    </nav>
    @endif

    {{-- Ürün listesi --}}
    <div class="product-list-wrap px-5 py-4 pb-6 md:px-8" id="menuSections">
        @foreach($categories as $cat)
        <div
            class="menu-category-panel space-y-3 {{ $cat->id !== $firstCategoryId ? 'hidden' : '' }}"
            data-category-panel="{{ $cat->id }}"
            id="cat-panel-{{ $cat->id }}"
        >
            @foreach($cat->products as $product)
            <article
                class="product-item product-row-card"
                data-id="{{ $product->id }}"
                data-name="{{ $product->name }}"
                data-price="{{ $product->price }}"
                data-category-id="{{ $cat->id }}"
                data-search="{{ strtolower($product->name . ' ' . ($product->description ?? '') . ' ' . $cat->name) }}"
            >
                <div class="product-row-thumb-wrap">
                    @if($product->image)
                    <img src="{{ $product->image_url }}" alt="" class="product-row-thumb" loading="lazy">
                    @else
                    <div class="product-row-thumb product-row-thumb--placeholder flex items-center justify-center text-lg text-[#D4C5B9]/30">☕</div>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="menu-product-title text-base">{{ $product->name }}</h3>
                        @if($product->badge)
                        <span class="product-badge shrink-0 text-[10px]">{{ $product->badge }}</span>
                        @endif
                    </div>
                    @if($product->description)
                    <p class="menu-product-desc mt-0.5 line-clamp-2">{{ $product->description }}</p>
                    @endif
                    @php $todayOrders = (int) ($productPopularity[$product->id] ?? 0); @endphp
                    @if($todayOrders >= 3)
                    <span class="social-proof-badge">Bugün {{ $todayOrders }} kişi tercih etti 🔥</span>
                    @endif
                    <div class="mt-3 flex items-end justify-between gap-2">
                        <span class="text-lg font-bold text-gray-100">{{ number_format($product->price, 0) }} <span class="text-sm font-medium text-[#D4C5B9]">{{ $settings['currency'] ?? '₺' }}</span></span>
                        @if(($settings['order_enabled'] ?? '1') === '1')
                        <button type="button" class="add-btn btn-siparis" aria-label="Sipariş et">Sipariş Et</button>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @endforeach
    </div>

    <div class="no-results px-6 py-16 text-center text-sm text-[#D4C5B9]" id="noResults">
        Aramanızla eşleşen ürün bulunamadı.
    </div>

    @if($socialWidgetsGrid)
        @include('menu.partials.social-widgets-grid', compact('spotifyUrl', 'instagramUrl', 'instagramHandle', 'settings'))
    @else
        @include('menu.partials.social-widgets-legacy', compact('spotifyUrl', 'instagramUrl', 'instagramHandle', 'settings'))
    @endif
</main>

@if($table)
<div
    id="menuActionBar"
    class="menu-action-bar menu-shell pointer-events-auto fixed inset-x-0 bottom-0 z-40 flex justify-center"
    data-call-status-url="{{ route('table.call.status') }}"
>
    <div class="w-full rounded-t-3xl border-t border-white/10 bg-[#262220]/80 p-4 backdrop-blur-md md:px-8" style="padding-bottom: calc(12px + env(safe-area-inset-bottom))">
        <div id="callActionButtons" class="grid grid-cols-2 gap-3">
            <button type="button" id="callWaiter" class="menu-call-waiter rounded-xl border border-[#E67E22] px-3 py-3 text-sm font-semibold text-[#E67E22] transition hover:bg-[#E67E22]/10">
                🛎️ Garson Çağır
            </button>
            <button type="button" id="callBillOpen" class="menu-call-bill rounded-xl bg-[#E67E22] px-3 py-3 text-sm font-semibold text-white shadow-lg shadow-[#E67E22]/25 transition hover:brightness-110">
                💳 Hesap İste
            </button>
        </div>
        <p id="callSuccessMsg" class="call-success-msg hidden text-center text-sm font-light text-[#D4C5B9]"></p>
    </div>
</div>

<div class="modal-overlay fixed inset-0 z-[210] items-end justify-center bg-black/60" id="billSheet">
    <div class="menu-shell w-full rounded-t-3xl border-t border-white/10 bg-[#262220] p-6 md:px-8">
        <h3 class="mb-1 text-lg font-bold text-gray-100">Hesap İste</h3>
        <p class="mb-4 text-sm text-[#D4C5B9]">Garsonumuz hazırlıklı gelsin</p>
        <div class="space-y-3">
            <button type="button" data-bill-type="bill_cash" class="bill-type-btn w-full rounded-xl border border-white/10 bg-white/5 py-4 text-left px-4 text-gray-100 transition hover:border-[#E67E22]/40">
                <span class="block font-semibold">Nakit Ödeyeceğim</span>
                <span class="text-xs text-[#D4C5B9]">Hesabı nakit olarak getirelim</span>
            </button>
            <button type="button" data-bill-type="bill_card" class="bill-type-btn w-full rounded-xl border border-white/10 bg-white/5 py-4 text-left px-4 text-gray-100 transition hover:border-[#E67E22]/40">
                <span class="block font-semibold">Kredi Kartı / Pos Cihazı</span>
                <span class="text-xs text-[#D4C5B9]">Pos cihazı masanıza getirilsin</span>
            </button>
        </div>
        <button type="button" id="billSheetClose" class="mt-4 w-full rounded-xl border border-white/10 py-3 text-sm text-[#D4C5B9]">İptal</button>
    </div>
</div>
@endif

@if(($settings['order_enabled'] ?? '1') === '1')
<div class="pointer-events-none fixed inset-x-0 z-50 flex justify-center {{ $table ? 'bottom-[5.5rem]' : 'bottom-0' }}">
<div class="cart-bar menu-shell pointer-events-auto w-full border-t border-white/10 bg-[#262220]/95 px-4 py-3 backdrop-blur-lg md:px-8" id="cartBar" style="padding-bottom: calc(8px + env(safe-area-inset-bottom))">
    <div class="flex-1 text-sm text-[#D4C5B9]">
        <span id="cartCount" class="font-semibold text-gray-100">0</span> ürün ·
        <span class="font-bold text-[#E67E22]" id="cartTotal">0 {{ $settings['currency'] ?? '₺' }}</span>
    </div>
    <button type="button" id="openCart" class="rounded-full bg-[#E67E22] px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110">Sipariş Ver</button>
</div>
</div>

<div class="modal-overlay fixed inset-0 z-[200] items-end justify-center bg-black/70 lg:items-center lg:p-8" id="cartModal">
    <div class="menu-shell max-h-[85vh] w-full overflow-y-auto rounded-t-2xl border-t border-white/10 bg-[#262220] p-6 lg:rounded-2xl lg:border lg:p-8">
        <h2 class="mb-4 text-xl font-bold text-gray-100">Siparişiniz</h2>
        <div id="cartItems" class="divide-y divide-white/5"></div>
        <textarea id="orderNotes" placeholder="Notunuz (isteğe bağlı)" class="mt-4 w-full min-h-[72px] resize-y rounded-xl border border-white/5 bg-[#121110] px-3.5 py-3 text-sm text-gray-100 outline-none focus:border-[#E67E22]/40 focus:ring-2 focus:ring-[#E67E22]/15"></textarea>
        <div class="mt-4 flex gap-3">
            <button type="button" id="closeCart" class="flex-1 rounded-xl border border-white/10 bg-white/5 py-3 text-sm font-medium text-gray-100">İptal</button>
            <button type="button" id="submitOrder" class="flex-1 rounded-xl bg-[#E67E22] py-3 text-sm font-semibold text-white">Gönder</button>
        </div>
    </div>
</div>
@endif

<script>
const tableToken = @json($table?->qr_token);
const tableMasa = @json($table?->number);
const currency = @json($settings['currency'] ?? '₺');
const cart = {};

let callStatusTimer = null;
const callStatusUrl = document.getElementById('menuActionBar')?.dataset.callStatusUrl;

function resetCallButtons() {
    const buttons = document.getElementById('callActionButtons');
    const msg = document.getElementById('callSuccessMsg');
    buttons?.classList.remove('hidden');
    msg?.classList.add('hidden');
    document.getElementById('callWaiter') && (document.getElementById('callWaiter').disabled = false);
    document.getElementById('callBillOpen') && (document.getElementById('callBillOpen').disabled = false);
}

function showCallSent(message) {
    const buttons = document.getElementById('callActionButtons');
    const msg = document.getElementById('callSuccessMsg');
    if (buttons) buttons.classList.add('hidden');
    if (msg) {
        msg.textContent = message || 'Garsonumuz masanıza yönlendirildi…';
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
    if (!callStatusUrl || (!tableToken && !tableMasa)) return;
    const params = new URLSearchParams();
    if (tableToken) params.set('table_token', tableToken);
    if (tableMasa) params.set('masa', tableMasa);
    try {
        const res = await fetch(`${callStatusUrl}?${params}`, { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.active) {
            stopCallStatusPoll();
            resetCallButtons();
            const msg = document.getElementById('callSuccessMsg');
            if (msg) {
                msg.textContent = 'Garsonunuz masanıza yönlendirildi. İhtiyacınız olursa tekrar çağırabilirsiniz.';
                msg.classList.remove('hidden');
                setTimeout(() => msg.classList.add('hidden'), 5000);
            }
        }
    } catch { /* sessiz */ }
}

function startCallStatusPoll() {
    stopCallStatusPoll();
    checkCallStatus();
    callStatusTimer = setInterval(checkCallStatus, 4000);
}

async function sendTableCall(type) {
    if (!tableToken && !tableMasa) return false;
    const payload = { type };
    if (tableToken) payload.table_token = tableToken;
    if (tableMasa) payload.masa = tableMasa;
    try {
        const res = await fetch('{{ route("table.call.api") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            showCallSent(data.message);
            return true;
        }
        alert(data.message || 'İstek gönderilemedi.');
    } catch {
        alert('Bağlantı hatası, tekrar deneyin.');
    }
    return false;
}

async function syncCallBarOnLoad() {
    if (!callStatusUrl || (!tableToken && !tableMasa)) return;
    const params = new URLSearchParams();
    if (tableToken) params.set('table_token', tableToken);
    if (tableMasa) params.set('masa', tableMasa);
    try {
        const res = await fetch(`${callStatusUrl}?${params}`, { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        if (data.active) {
            const waiting = {
                waiter: 'Garsonumuz masanıza yönlendirildi…',
                bill_cash: 'Nakit hesap talebiniz iletildi. Garsonumuz geliyor…',
                bill_card: 'Kart ile ödeme talebiniz iletildi. Pos getiriliyor…',
            };
            showCallSent(waiting[data.type] || 'Talebiniz iletildi…');
        }
    } catch { /* sessiz */ }
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
billSheet?.addEventListener('click', (e) => { if (e.target === billSheet) billSheet.classList.remove('open'); });
document.querySelectorAll('.bill-type-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        const ok = await sendTableCall(btn.dataset.billType);
        if (ok) billSheet?.classList.remove('open');
        else btn.disabled = false;
    });
});

/* Kategori pill geçişi */
const categoryPills = document.querySelectorAll('.category-pill');
const categoryPanels = document.querySelectorAll('[data-category-panel]');

function showCategory(catId) {
    categoryPills.forEach(p => p.classList.toggle('is-active', p.dataset.categoryId === String(catId)));
    categoryPanels.forEach(panel => {
        panel.classList.toggle('hidden', panel.dataset.categoryPanel !== String(catId));
    });
}

categoryPills.forEach(pill => {
    pill.addEventListener('click', () => showCategory(pill.dataset.categoryId));
});

/* Arama */
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
        categoryPanels.forEach(panel => {
            panel.classList.remove('hidden');
            let visible = 0;
            panel.querySelectorAll('.product-item').forEach(item => {
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
        categoryPanels.forEach(panel => panel.classList.add('hidden'));
        document.querySelectorAll('.product-item').forEach(item => item.classList.remove('hidden'));
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
searchClear?.addEventListener('click', () => { searchInput.value = ''; applySearch(''); searchInput.focus(); });

function updateCartUI() {
    const items = Object.values(cart);
    const count = items.reduce((s, i) => s + i.qty, 0);
    const total = items.reduce((s, i) => s + i.price * i.qty, 0);
    const bar = document.getElementById('cartBar');
    if (!bar) return;
    document.getElementById('cartCount').textContent = count;
    document.getElementById('cartTotal').textContent = total.toFixed(0) + ' ' + currency;
    bar.classList.toggle('visible', count > 0);
}

document.querySelectorAll('.add-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const card = btn.closest('.product-item');
        const id = card.dataset.id;
        if (!cart[id]) cart[id] = { id, name: card.dataset.name, price: parseFloat(card.dataset.price), qty: 0 };
        cart[id].qty++;
        card.classList.remove('animate-fade-in-up');
        void card.offsetWidth;
        card.classList.add('animate-fade-in-up');
        updateCartUI();
        const bar = document.getElementById('cartBar');
        bar?.classList.remove('animate-cart-pop');
        void bar?.offsetWidth;
        bar?.classList.add('animate-cart-pop');
        btn.textContent = 'Eklendi ✓';
        setTimeout(() => { btn.textContent = 'Sipariş Et'; }, 1200);
    });
});

document.getElementById('openCart')?.addEventListener('click', () => {
    document.getElementById('cartItems').innerHTML = Object.values(cart).map(i => `
        <div class="flex justify-between py-3 text-sm"><span class="text-gray-100">${i.name} ×${i.qty}</span><span class="font-semibold text-[#E67E22]">${(i.price * i.qty).toFixed(0)} ${currency}</span></div>
    `).join('');
    document.getElementById('cartModal').classList.add('open');
});
document.getElementById('closeCart')?.addEventListener('click', () => document.getElementById('cartModal').classList.remove('open'));
document.getElementById('cartModal')?.addEventListener('click', e => { if (e.target.id === 'cartModal') e.target.classList.remove('open'); });
document.getElementById('submitOrder')?.addEventListener('click', async () => {
    const items = Object.values(cart).map(i => ({ product_id: parseInt(i.id), quantity: i.qty }));
    if (!items.length) return;
    const btn = document.getElementById('submitOrder');
    btn.disabled = true;
    btn.textContent = 'Gönderiliyor...';
    try {
        const res = await fetch('{{ route("order.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ table_token: tableToken, masa: tableMasa, notes: document.getElementById('orderNotes').value, items }),
        });
        const data = await res.json();
        if (data.success) window.location.href = data.redirect;
        else alert('Sipariş gönderilemedi.');
    } catch { alert('Bağlantı hatası.'); }
    btn.disabled = false;
    btn.textContent = 'Gönder';
});
</script>
@endsection

@if(isset($spottedSliders) && $spottedSliders->isNotEmpty())
@push('scripts')
@vite('resources/js/pages/menu-spotted.js')
@endpush
@endif
