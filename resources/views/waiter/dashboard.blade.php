@extends('layouts.waiter')

@section('title', 'Garson Paneli')

@section('content')
<header class="waiter-header">
    <div class="waiter-header__brand">
        <p class="waiter-header__venue">{{ $venueName }}</p>
        <h1 class="waiter-header__title">Human Social People — Garson Paneli</h1>
        @if(filled($venueTagline))
        <p class="waiter-header__tagline">{{ $venueTagline }}</p>
        @endif
    </div>
    <div class="waiter-header__right">
        <span id="waiterLiveBadge" class="waiter-live-badge waiter-live-badge--on">
            <span class="waiter-live-badge__dot" aria-hidden="true"></span>
            • Canlı Bağlantı
        </span>
    </div>
</header>

<div class="waiter-top-actions">
    <button type="button" id="waiterInstallBtn" class="waiter-install-btn" hidden>
        ⬇ Uygulamayı Yükle
    </button>
    <form action="{{ route('admin.logout') }}" method="POST" class="waiter-logout-form">
        @csrf
        <button type="submit" class="waiter-logout-btn" aria-label="Oturumu kapat ve çıkış yap">
            <span class="waiter-logout-btn__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </span>
            <span class="waiter-logout-btn__text">Çıkış</span>
        </button>
    </form>
</div>

<section class="waiter-feed-section" aria-label="Canlı akış">
    <div class="waiter-feed-section__head">
        <h2 class="waiter-feed-section__title">Canlı Akış</h2>
        <p id="waiterFeedStatus" class="waiter-feed-section__status">Yükleniyor…</p>
    </div>
    <div id="waiterFeed" class="waiter-feed">
        <p class="waiter-feed__empty">Bekleyen çağrı veya sipariş yok ✨</p>
    </div>
</section>

@include('admin.partials.manual-order-modal')
@endsection

@push('scripts')
<script>
window.HSP_WAITER = {
    feedUrl: @json(route('live-orders.api')),
    completeUrl: @json(route('waiter.complete')),
    pollMs: 3000,
    reverb: {
        key: @json(env('REVERB_APP_KEY')),
        host: @json(env('REVERB_HOST', '127.0.0.1')),
        port: @json((int) env('REVERB_PORT', 8080)),
        scheme: @json(env('REVERB_SCHEME', 'http')),
    },
};
</script>
@vite(['resources/js/pages/admin-shell.js', 'resources/js/pages/admin-manual-order.js'])
@endpush
