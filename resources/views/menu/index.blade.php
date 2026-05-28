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
    $scrollPad = 'menu-scroll-pad';
    if ($table && $orderOn) {
        $scrollPad .= ' menu-scroll-pad--table-cart';
    } elseif ($table) {
        $scrollPad .= ' menu-scroll-pad--table';
    }
@endphp
<div class="menu-page">
<main class="menu-shell {{ $scrollPad }}" id="menuApp">
    {{-- Header / Logo --}}
    <header class="menu-header relative px-5 pt-6 pb-2 text-center">
        <div class="absolute top-4 right-4 flex flex-col items-end gap-2">
            @include('menu.partials.lang-switcher', ['table' => $table, 'locale' => $locale])
            @if($table)
            <span class="rounded-full border border-[#E67E22]/30 bg-[#E67E22]/15 px-2.5 py-0.5 text-[10px] font-semibold text-[#E67E22]">
                {{ __('menu.table', ['number' => $table->number]) }}
            </span>
            @endif
        </div>
        <h1 class="menu-logo">{{ $brandMark }}</h1>
        <p class="menu-tagline">{{ $tagline }}</p>

    </header>

    @include('menu.partials.info-strip', compact('settings'))

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

    {{-- Arama (kompakt) --}}
    <div class="px-5 pb-3">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#D4C5B9]/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="search" id="menuSearch" placeholder="{{ __('menu.search_placeholder') }}" autocomplete="off" class="menu-search-input w-full pl-9">
            <button type="button" id="searchClear" class="search-clear absolute right-2 top-1/2 h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full text-[#D4C5B9]">×</button>
        </div>
    </div>

    {{-- Kategori pill'leri --}}
    @if($categories->isNotEmpty())
    <nav class="category-pills-nav px-5" aria-label="Kategoriler">
        <div class="category-pills" id="categoryPills">
            @foreach($categories as $cat)
            <button
                type="button"
                class="category-pill transition-all duration-300 ease-in-out transform active:scale-95 {{ $cat->id === $firstCategoryId ? 'is-active' : '' }}"
                data-category-id="{{ $cat->id }}"
            >{{ $cat->localizedName() }}</button>
            @endforeach
        </div>
    </nav>
    @endif

    {{-- Ürün listesi --}}
    <div class="product-list-wrap px-5 py-4 pb-6" id="menuSections">
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
                data-name="{{ $product->localizedName() }}"
                data-price="{{ $product->price }}"
                data-category-id="{{ $cat->id }}"
                data-search="{{ strtolower($product->localizedName() . ' ' . ($product->localizedDescription() ?? '') . ' ' . $cat->localizedName()) }}"
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
                        <h3 class="menu-product-title text-base">{{ $product->localizedName() }}</h3>
                        @if($product->badge)
                        <span class="product-badge shrink-0 text-[10px]">{{ $product->badge }}</span>
                        @endif
                    </div>
                    @if($product->localizedDescription())
                    <p class="menu-product-desc mt-0.5 line-clamp-2">{{ $product->localizedDescription() }}</p>
                    @endif
                    @php $todayOrders = (int) ($productPopularity[$product->id] ?? 0); @endphp
                    @if($todayOrders >= 3)
                    <span class="social-proof-badge">{{ __('menu.social_proof', ['count' => $todayOrders]) }}</span>
                    @endif
                    <div class="mt-3 flex items-end justify-between gap-2">
                        <span class="text-lg font-bold text-gray-100">{{ number_format($product->price, 0) }} <span class="text-sm font-medium text-[#D4C5B9]">{{ $settings['currency'] ?? '₺' }}</span></span>
                        @if(($settings['order_enabled'] ?? '1') === '1')
                        <button type="button" class="add-btn btn-siparis" data-order-label="{{ __('menu.order_btn') }}" aria-label="{{ __('menu.order_btn') }}">{{ __('menu.order_btn') }}</button>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @endforeach
    </div>

    <div class="no-results px-6 py-16 text-center text-sm text-[#D4C5B9]" id="noResults">
        {{ __('menu.no_results') }}
    </div>

    @if($socialWidgetsGrid)
        @include('menu.partials.social-widgets-grid', compact('spotifyUrl', 'instagramUrl', 'instagramHandle', 'settings'))
    @else
        @include('menu.partials.social-widgets-legacy', compact('spotifyUrl', 'instagramUrl', 'instagramHandle', 'settings'))
    @endif
</main>
</div>

@push('menu-overlays')
@if($table)
<div
    id="menuActionBar"
    class="menu-action-bar menu-fixed-dock bottom-0"
    data-call-status-url="{{ route('table.call.status') }}"
>
    <div class="menu-fixed-panel" style="padding-bottom: calc(12px + env(safe-area-inset-bottom))">
        <div id="callActionButtons" class="grid grid-cols-2 gap-3">
            <button type="button" id="callWaiter" class="menu-call-waiter rounded-xl border border-[#E67E22] px-3 py-3 text-sm font-semibold text-[#E67E22] transition hover:bg-[#E67E22]/10">
                🛎️ {{ __('menu.call_waiter') }}
            </button>
            <button type="button" id="callBillOpen" class="menu-call-bill rounded-xl bg-[#E67E22] px-3 py-3 text-sm font-semibold text-white shadow-lg shadow-[#E67E22]/25 transition hover:brightness-110">
                💳 {{ __('menu.request_bill') }}
            </button>
        </div>
        <p id="callSuccessMsg" class="call-success-msg hidden text-center text-sm font-light text-[#D4C5B9]"></p>
    </div>
</div>

<div class="modal-overlay menu-modal z-[210] bg-black/60" id="billSheet">
    <div class="menu-fixed-panel p-6">
        <h3 class="mb-1 text-lg font-bold text-gray-100">{{ __('menu.bill_title') }}</h3>
        <p class="mb-4 text-sm text-[#D4C5B9]">{{ __('menu.bill_subtitle') }}</p>
        <div class="space-y-3">
            <button type="button" data-bill-type="bill_cash" class="bill-type-btn w-full rounded-xl border border-white/10 bg-white/5 py-4 text-left px-4 text-gray-100 transition hover:border-[#E67E22]/40">
                <span class="block font-semibold">{{ __('menu.bill_cash_title') }}</span>
                <span class="text-xs text-[#D4C5B9]">{{ __('menu.bill_cash_hint') }}</span>
            </button>
            <button type="button" data-bill-type="bill_card" class="bill-type-btn w-full rounded-xl border border-white/10 bg-white/5 py-4 text-left px-4 text-gray-100 transition hover:border-[#E67E22]/40">
                <span class="block font-semibold">{{ __('menu.bill_card_title') }}</span>
                <span class="text-xs text-[#D4C5B9]">{{ __('menu.bill_card_hint') }}</span>
            </button>
        </div>
        <button type="button" id="billSheetClose" class="mt-4 w-full rounded-xl border border-white/10 py-3 text-sm text-[#D4C5B9]">{{ __('menu.cancel') }}</button>
    </div>
</div>
@endif

@if(($settings['order_enabled'] ?? '1') === '1')
<div class="menu-cart-dock menu-fixed-dock {{ $table ? 'bottom-[5.5rem]' : 'bottom-0' }}">
    <div
        class="cart-bar menu-fixed-panel"
        id="cartBar"
        style="padding-bottom: calc(8px + env(safe-area-inset-bottom))"
    >
        <div class="flex-1 text-sm text-[#D4C5B9]">
            <span class="text-sm text-[#D4C5B9]">
                <span id="cartCount" class="font-semibold text-gray-100">0</span>
                <span id="cartCountLabel"></span>
            </span>
            <span class="font-bold text-[#E67E22]" id="cartTotal">0 {{ $settings['currency'] ?? '₺' }}</span>
        </div>
        <button type="button" id="openCart" class="rounded-full bg-[#E67E22] px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110">{{ __('menu.place_order') }}</button>
    </div>
</div>

<div class="modal-overlay menu-modal" id="cartModal">
    <div class="menu-fixed-panel p-6">
        <h2 class="mb-4 text-xl font-bold text-gray-100">{{ __('menu.your_order') }}</h2>
        <div id="cartItems" class="cart-items-list"></div>
        <div class="cart-modal-total mt-4 flex items-center justify-between border-t border-white/10 pt-4">
            <span class="text-sm text-[#D4C5B9]">{{ __('menu.total') }}</span>
            <span id="cartModalTotal" class="text-lg font-bold text-[#E67E22]">0 {{ $settings['currency'] ?? '₺' }}</span>
        </div>
        <textarea id="orderNotes" placeholder="{{ __('menu.order_notes') }}" class="mt-4 w-full min-h-[72px] resize-y rounded-xl border border-white/5 bg-[#121110] px-3.5 py-3 text-sm text-gray-100 outline-none focus:border-[#E67E22]/40 focus:ring-2 focus:ring-[#E67E22]/15"></textarea>
        <div class="mt-4 flex gap-3">
            <button type="button" id="closeCart" class="flex-1 rounded-xl border border-white/10 bg-white/5 py-3 text-sm font-medium text-gray-100">{{ __('menu.cancel') }}</button>
            <button type="button" id="submitOrder" class="flex-1 rounded-xl bg-[#E67E22] py-3 text-sm font-semibold text-white">{{ __('menu.send') }}</button>
        </div>
    </div>
</div>
@endif
@endpush

@endsection

@push('scripts')
<script>
window.HSP_MENU = {
    tableToken: @json($table?->qr_token),
    tableMasa: @json($table?->number),
    currency: @json($settings['currency'] ?? '₺'),
    locale: @json($locale),
    orderStoreUrl: @json(route('order.store')),
    callApiUrl: @json(route('table.call.api')),
    i18n: {
        cartItems: @json(__('menu.cart_items', ['count' => ':count'])),
        cartRemove: @json(__('menu.cart_remove')),
        cartDecrease: @json(__('menu.cart_decrease')),
        cartIncrease: @json(__('menu.cart_increase')),
        send: @json(__('menu.send')),
        sending: @json(__('menu.sending')),
        callWaiterSent: @json(__('menu.call.waiter_sent')),
        callWaiterActive: @json(__('menu.call.waiter_active')),
        callBillCash: @json(__('menu.call.bill_cash_sent')),
        callBillCard: @json(__('menu.call.bill_card_sent')),
        callFail: @json(__('menu.call.fail')),
        connection: @json(__('menu.call.connection')),
        orderFail: @json(__('menu.call.fail')),
    },
};
</script>
@vite(['resources/js/pages/menu-cart.js', 'resources/js/pages/menu-spotted.js'])
@endpush
