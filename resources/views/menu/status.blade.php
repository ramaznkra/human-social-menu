@extends('layouts.menu')

@section('title', 'Sipariş Durumu')

@section('content')
@php
    $progressStep = $order->customerStatusStep();
    $progressPercent = $progressStep > 0 ? ($progressStep / 4) * 100 : 0;
@endphp

<div class="menu-page">
<div
    id="orderProgressSticky"
    class="order-progress-sticky"
    data-initial-step="{{ $progressStep }}"
>
    <div class="menu-shell px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-medium tracking-wide text-[#D4C5B9]">Siparişiniz</p>
            <p class="text-sm font-semibold text-[#E67E22]" id="progressOrderNo">#{{ $order->order_number }}</p>
        </div>
        <p class="mt-1 text-base font-semibold tracking-wide text-gray-100" id="progressStepLabel">{{ $order->customer_status_label }}</p>
        <div class="order-progress-track mt-3">
            <div class="order-progress-fill" id="progressFill" style="width: {{ $progressPercent }}%"></div>
        </div>
        <div class="mt-2 grid grid-cols-4 gap-1">
            @foreach(['Beklemede', 'Hazırlanıyor', 'Masaya Doğru', 'Afiyet Olsun'] as $i => $stepLabel)
            <span
                class="order-progress-step text-center {{ $progressStep > $i + 1 ? 'is-done' : ($progressStep === $i + 1 ? 'is-active' : '') }}"
                data-step="{{ $i + 1 }}"
            >{{ $stepLabel }}</span>
            @endforeach
        </div>
    </div>
</div>

<div
    id="order-status-root"
    class="menu-shell px-4 pb-8 pt-36 text-center"
    data-order-id="{{ $order->id }}"
    data-initial-status="{{ $order->status }}"
    data-initial-step="{{ $progressStep }}"
    data-poll-url="{{ route('order.status.api', $order) }}"
>
    <div id="pollBanner" class="mx-auto mb-4 hidden max-w-md rounded-lg border px-4 py-2 text-xs transition-all duration-500 ease-in-out" role="status"></div>

    <div id="statusIcon" class="status-icon mb-4 text-5xl transition-all duration-500 ease-in-out">⏳</div>

    <span
        class="status-label status-{{ $order->status }} status-label-pulse inline-block rounded-full px-5 py-2 text-sm font-semibold tracking-wide transition-all duration-500 ease-in-out"
        id="statusLabel"
    >{{ $order->customer_status_label }}</span>

    <div class="product-card mt-8 rounded-2xl p-5 text-left">
        @foreach($order->items as $item)
        <div class="flex justify-between border-b border-white/5 py-3 text-sm last:border-0">
            <span class="font-medium tracking-wide text-gray-100">{{ $item->product_name }} ×{{ $item->quantity }}</span>
            <span class="font-semibold text-[#E67E22]">{{ number_format($item->subtotal, 0) }} {{ $settings['currency'] ?? '₺' }}</span>
        </div>
        @endforeach
        <div class="mt-2 flex justify-between border-t border-white/10 pt-4 font-bold tracking-wide text-gray-100">
            <span>Toplam</span>
            <span class="text-[#E67E22]">{{ number_format($order->total, 0) }} {{ $settings['currency'] ?? '₺' }}</span>
        </div>
    </div>

    <p id="pollHint" class="mt-6 text-sm font-light tracking-wide text-[#D4C5B9]">Durum otomatik güncellenir</p>
    <a href="{{ route('menu.index', $order->table?->qr_token) }}" class="mt-4 inline-block text-sm font-medium tracking-wide text-[#E67E22] transition hover:underline">← Menüye Dön</a>
</div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/order-status.js')
@endpush
