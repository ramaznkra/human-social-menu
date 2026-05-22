@extends('layouts.menu')

@section('title', ($settings['venue_name'] ?? 'Human') . ' — Menü')

@section('content')
<div class="menu-shell pb-28 lg:pb-32" id="menuApp">
    {{-- Hero slider --}}
    <div class="relative h-52 w-full overflow-hidden bg-[#262220] sm:h-56 md:h-72 lg:h-80 xl:h-[22rem] 2xl:h-96" id="heroSlider">
        @forelse($menuSlides as $i => $slide)
        <div class="hero-slide absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-700 {{ $i === 0 ? 'active z-[1] !opacity-100' : '' }}"
             data-duration="{{ $slide->duration }}"
             style="background-image: url('{{ $slide->image_url }}')">
            <div class="absolute inset-0 bg-gradient-to-t from-[#121110] via-[#121110]/40 to-black/30"></div>
            <div class="absolute inset-x-0 bottom-0 p-4">
                @if($slide->type === 'guest')
                <span class="mb-2 inline-block rounded-md bg-[#E67E22]/20 px-2 py-0.5 text-[10px] font-semibold tracking-wider text-[#E67E22] uppercase">Özel Misafir</span>
                @endif
                @if($slide->title)<h2 class="text-lg font-bold tracking-wide text-gray-100 uppercase md:text-2xl lg:text-3xl">{{ $slide->title }}</h2>@endif
                @if($slide->subtitle)<p class="text-sm font-light text-[#D4C5B9] md:text-base lg:text-lg">{{ $slide->subtitle }}</p>@endif
            </div>
        </div>
        @empty
        <div class="hero-slide active absolute inset-0 z-[1] flex items-end bg-gradient-to-br from-[#262220] to-[#121110] p-4 !opacity-100">
            <div>
                <h2 class="text-2xl font-bold uppercase tracking-wider text-gray-100">{{ $settings['venue_name'] ?? 'Human' }}</h2>
                <p class="text-sm text-[#D4C5B9]">{{ $settings['venue_slogan'] ?? 'Social People' }}</p>
            </div>
        </div>
        @endforelse
        @if($menuSlides->count() > 1)
        <div class="absolute bottom-3 right-4 z-10 flex gap-1.5" id="heroDots">
            @foreach($menuSlides as $i => $slide)
            <button type="button" class="hero-dot h-1.5 rounded-full transition-all {{ $i === 0 ? 'w-6 bg-[#E67E22]' : 'w-1.5 bg-white/40' }}" data-index="{{ $i }}" aria-label="Slayt {{ $i + 1 }}"></button>
            @endforeach
        </div>
        @endif
        <div class="absolute top-0 right-0 left-0 z-10 flex items-center justify-between px-4 py-3 md:px-6 lg:px-8">
            @if($table)
            <span class="rounded-full border border-white/10 bg-black/40 px-3 py-1 text-xs font-semibold text-[#E67E22] backdrop-blur-sm">Masa {{ $table->number }}</span>
            @else
            <span></span>
            @endif
            <span class="rounded-full border border-white/10 bg-black/40 px-3 py-1 text-xs font-medium text-[#D4C5B9] backdrop-blur-sm">{{ $settings['venue_name'] ?? 'Human' }}</span>
        </div>
    </div>

    {{-- Search --}}
    <div class="sticky top-0 z-40 border-b border-white/5 bg-[#121110]/95 px-4 py-3 backdrop-blur-md md:px-6 lg:px-8 lg:py-4">
        <div class="relative mx-auto w-full lg:max-w-3xl">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#D4C5B9]/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="search" id="menuSearch" placeholder="Menüde ara..." autocomplete="off"
                class="w-full rounded-xl border border-white/5 bg-[#262220] py-2.5 pr-10 pl-10 text-sm text-gray-100 placeholder:text-[#D4C5B9]/40 outline-none focus:border-[#E67E22]/40 focus:ring-2 focus:ring-[#E67E22]/15 md:py-3 md:text-base">
            <button type="button" id="searchClear" class="search-clear absolute right-2 top-1/2 h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-sm text-[#D4C5B9]">×</button>
        </div>
    </div>

    @if($events->isNotEmpty())
    <div class="mx-4 mt-3 rounded-xl border border-white/5 bg-[#262220]/80 px-4 py-3">
        @foreach($events as $event)
        <p class="text-xs text-[#D4C5B9]"><span class="font-medium text-[#E67E22]">{{ $event->title }}</span> — {{ $event->event_date?->format('d.m H:i') }}</p>
        @endforeach
    </div>
    @endif

    {{-- Category accordion (PIER style) --}}
    <div class="space-y-2 px-3 py-4 md:space-y-3 md:px-6 lg:space-y-4 lg:px-8 lg:py-6" id="menuSections">
        @foreach($categories as $cat)
        <div class="category-block" data-category="{{ strtolower($cat->name) }}" id="cat-{{ $cat->id }}">
            <button type="button" class="category-toggle group relative flex min-h-[4.5rem] w-full items-center overflow-hidden rounded-xl border border-white/5 text-left transition-all duration-300 hover:border-[#E67E22]/20 md:min-h-[5.5rem] lg:min-h-[6.5rem] lg:rounded-2xl"
                data-target="panel-{{ $cat->id }}" aria-expanded="false">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                     style="background-image: url('{{ $cat->image_url ?? asset('images/menu/categories/yiyecek.jpg') }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/65 to-black/50"></div>
                @if($cat->image_url)
                <img src="{{ $cat->image_url }}" alt="" class="relative z-[1] m-2 h-14 w-14 shrink-0 rounded-lg object-cover shadow-lg ring-1 ring-white/10 md:m-3 md:h-20 md:w-20 lg:h-24 lg:w-24 lg:rounded-xl" loading="lazy">
                @endif
                <span class="relative z-[1] flex-1 py-4 text-center text-sm font-bold tracking-[0.2em] text-gray-100 uppercase md:text-base lg:text-xl lg:tracking-[0.25em]">{{ $cat->name }}</span>
                <span class="relative z-[1] flex h-10 w-10 shrink-0 items-center justify-center pr-2">
                    <svg class="chevron-icon h-5 w-5 text-[#D4C5B9] transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </span>
            </button>
            <div id="panel-{{ $cat->id }}" class="category-panel hidden border-x border-b border-white/5 rounded-b-xl bg-[#1a1816] px-3 pb-3 md:px-4 md:pb-4 lg:rounded-b-2xl">
                <div class="grid grid-cols-1 gap-2 pt-2 md:grid-cols-2 md:gap-3 lg:gap-4">
                    @foreach($cat->products as $product)
                    <article class="product-item flex gap-3 rounded-lg border border-white/5 bg-[#262220] p-3 md:p-4 lg:rounded-xl"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ $product->price }}"
                        data-search="{{ strtolower($product->name . ' ' . ($product->description ?? '') . ' ' . $cat->name) }}">
                        @if($product->image)
                        <img src="{{ $product->image_url }}" alt="" class="h-16 w-16 shrink-0 rounded-lg object-cover md:h-20 md:w-20 lg:h-24 lg:w-24" loading="lazy">
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start gap-2">
                                <h3 class="font-bold text-gray-100 md:text-base lg:text-lg">{{ $product->name }}</h3>
                                @if($product->badge)
                                <span class="rounded-md bg-[#E67E22]/15 px-2 py-0.5 text-[10px] font-medium text-[#E67E22]">{{ $product->badge }}</span>
                                @endif
                            </div>
                            @if($product->description)
                            <p class="mt-0.5 line-clamp-2 text-xs font-light text-[#D4C5B9]">{{ $product->description }}</p>
                            @endif
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-base font-semibold text-[#E67E22] md:text-lg">{{ number_format($product->price, 0) }} {{ $settings['currency'] ?? '₺' }}</span>
                                @if(($settings['order_enabled'] ?? '1') === '1')
                                <button type="button" class="add-btn flex h-8 w-8 items-center justify-center rounded-full bg-[#E67E22] text-lg font-medium text-white transition-all duration-300 hover:scale-105" aria-label="Sepete ekle">+</button>
                                @endif
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="no-results px-6 py-16 text-center text-sm text-[#D4C5B9]" id="noResults">
        <span class="mb-2 block text-3xl opacity-30">🔍</span>
        Aramanızla eşleşen ürün bulunamadı.
    </div>
</div>

@if(($settings['order_enabled'] ?? '1') === '1')
<div class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex justify-center">
<div class="cart-bar menu-shell pointer-events-auto w-full border-t border-white/10 bg-[#262220]/95 px-4 py-3 backdrop-blur-lg md:px-6 lg:px-8 lg:py-4" id="cartBar" style="padding-bottom: calc(12px + env(safe-area-inset-bottom))">
    <div class="flex-1 text-sm text-[#D4C5B9]">
        <span id="cartCount" class="font-semibold text-gray-100">0</span> ürün ·
        <span class="font-bold text-[#E67E22]" id="cartTotal">0 {{ $settings['currency'] ?? '₺' }}</span>
    </div>
    <button type="button" id="openCart" class="rounded-xl bg-[#E67E22] px-5 py-2.5 text-sm font-medium text-white transition-all duration-300 hover:scale-105 md:px-6 md:py-3 md:text-base">Sipariş Ver</button>
</div>
</div>

<div class="modal-overlay fixed inset-0 z-[200] items-end justify-center bg-black/70 lg:items-center lg:p-8" id="cartModal">
    <div class="menu-shell max-h-[85vh] w-full overflow-y-auto rounded-t-2xl border-t border-white/10 bg-[#262220] p-6 lg:max-h-[80vh] lg:rounded-2xl lg:border lg:p-8">
        <h2 class="mb-4 text-xl font-bold text-gray-100">Siparişiniz</h2>
        <div id="cartItems" class="divide-y divide-white/5"></div>
        <textarea id="orderNotes" placeholder="Notunuz (isteğe bağlı)" class="mt-4 w-full min-h-[72px] resize-y rounded-xl border border-white/5 bg-[#121110] px-3.5 py-3 text-sm text-gray-100 outline-none focus:border-[#E67E22]/40 focus:ring-2 focus:ring-[#E67E22]/15"></textarea>
        <div class="mt-4 flex gap-3">
            <button type="button" id="closeCart" class="flex-1 rounded-xl border border-white/10 bg-white/5 py-3 text-sm font-medium text-gray-100">İptal</button>
            <button type="button" id="submitOrder" class="flex-1 rounded-xl bg-[#E67E22] py-3 text-sm font-medium text-white transition-all duration-300 hover:scale-105">Gönder</button>
        </div>
    </div>
</div>
@endif

<script>
const tableToken = @json($table?->qr_token);
const currency = @json($settings['currency'] ?? '₺');
const cart = {};

/* Hero slider */
(function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    if (slides.length <= 1) return;
    let current = 0;
    function goTo(index) {
        slides[current].classList.remove('active', 'z-[1]', '!opacity-100');
        dots[current]?.classList.remove('w-6', 'bg-[#E67E22]');
        dots[current]?.classList.add('w-1.5', 'bg-white/40');
        current = index;
        slides[current].classList.add('active', 'z-[1]', '!opacity-100');
        dots[current]?.classList.add('w-6', 'bg-[#E67E22]');
        dots[current]?.classList.remove('w-1.5', 'bg-white/40');
    }
    function next() {
        goTo((current + 1) % slides.length);
        const duration = (parseInt(slides[current].dataset.duration) || 8) * 1000;
        setTimeout(next, duration);
    }
    dots.forEach(dot => dot.addEventListener('click', () => goTo(parseInt(dot.dataset.index))));
    const firstDuration = (parseInt(slides[0].dataset.duration) || 8) * 1000;
    setTimeout(next, firstDuration);
})();

/* Category accordion */
document.querySelectorAll('.category-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const panel = document.getElementById(btn.dataset.target);
        const open = !panel.classList.contains('hidden');
        document.querySelectorAll('.category-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.category-toggle').forEach(b => {
            b.setAttribute('aria-expanded', 'false');
            b.querySelector('.chevron-icon')?.classList.remove('rotate-180');
            b.classList.remove('rounded-b-none', 'border-[#E67E22]/30');
        });
        if (!open) {
            panel.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
            btn.querySelector('.chevron-icon')?.classList.add('rotate-180');
            btn.classList.add('rounded-b-none', 'border-[#E67E22]/30');
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
});

/* Search */
const searchInput = document.getElementById('menuSearch');
const searchClear = document.getElementById('searchClear');
const noResults = document.getElementById('noResults');

function applySearch(query) {
    const q = query.trim().toLowerCase();
    searchClear?.classList.toggle('visible', q.length > 0);
    let total = 0;
    document.querySelectorAll('.category-block').forEach(block => {
        let visible = 0;
        block.querySelectorAll('.product-item').forEach(item => {
            const match = !q || item.dataset.search.includes(q);
            item.classList.toggle('hidden', !match);
            if (match) visible++;
        });
        if (q) {
            block.classList.toggle('hidden', visible === 0);
            if (visible > 0) {
                block.querySelector('.category-panel')?.classList.remove('hidden');
                block.querySelector('.category-toggle')?.setAttribute('aria-expanded', 'true');
            }
        } else {
            block.classList.remove('hidden');
            block.querySelector('.category-panel')?.classList.add('hidden');
            block.querySelector('.category-toggle')?.setAttribute('aria-expanded', 'false');
            block.querySelector('.chevron-icon')?.classList.remove('rotate-180');
        }
        total += visible;
    });
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
        updateCartUI();
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
            body: JSON.stringify({ table_token: tableToken, notes: document.getElementById('orderNotes').value, items }),
        });
        const data = await res.json();
        if (data.success) window.location.href = data.redirect;
        else alert('Sipariş gönderilemedi.');
    } catch (e) { alert('Bağlantı hatası.'); }
    btn.disabled = false;
    btn.textContent = 'Gönder';
});
</script>
@endsection
