@extends('layouts.menu')

@section('title', 'Sipariş Durumu')

@section('content')
<div class="menu-shell px-4 py-8 text-center md:px-6 lg:px-8 lg:py-12">
    <header class="mb-8 border-b border-white/5 pb-6">
        <div class="flex items-center justify-center gap-3">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-gray-100">{{ $settings['venue_name'] ?? 'Human' }}</h1>
            @if($order->table)
            <span class="rounded-full border border-[#E67E22]/30 bg-[#E67E22]/15 px-3 py-1 text-xs font-semibold text-[#E67E22]">Masa {{ $order->table->number }}</span>
            @endif
        </div>
        <p class="mt-2 text-xs tracking-[0.3em] text-[#D4C5B9] uppercase">Sipariş Takibi</p>
    </header>

    <div class="status-icon mb-4 text-5xl">⏳</div>
    <p class="text-2xl font-bold text-[#E67E22]">#{{ $order->order_number }}</p>

    <span class="status-label mt-4 inline-block rounded-full px-5 py-2 text-sm font-semibold status-{{ $order->status }}" id="statusLabel">{{ $order->status_label }}</span>

    <div class="mt-8 rounded-2xl border border-white/5 bg-[#262220] p-5 text-left">
        @foreach($order->items as $item)
        <div class="flex justify-between border-b border-white/5 py-3 text-sm last:border-0">
            <span class="text-gray-100">{{ $item->product_name }} ×{{ $item->quantity }}</span>
            <span class="font-semibold text-[#E67E22]">{{ number_format($item->subtotal, 0) }} {{ $settings['currency'] ?? '₺' }}</span>
        </div>
        @endforeach
        <div class="mt-2 flex justify-between border-t border-white/10 pt-4 font-bold text-gray-100">
            <span>Toplam</span>
            <span class="text-[#E67E22]">{{ number_format($order->total, 0) }} {{ $settings['currency'] ?? '₺' }}</span>
        </div>
    </div>

    <p class="mt-6 text-sm font-light text-[#D4C5B9]">Durum otomatik güncellenir</p>
    <a href="{{ route('menu.index', $order->table?->qr_token) }}" class="mt-4 inline-block text-sm font-medium text-[#E67E22] transition hover:underline">← Menüye Dön</a>
</div>

<style>
.status-pending { background: rgba(230,126,34,0.15); color: #E67E22; }
.status-preparing { background: rgba(212,197,185,0.15); color: #D4C5B9; }
.status-ready { background: rgba(230,126,34,0.25); color: #f39c12; }
.status-delivered { background: rgba(255,255,255,0.08); color: #9ca3af; }
.status-cancelled { background: rgba(239,68,68,0.15); color: #f87171; }
</style>

<script>
const orderId = {{ $order->id }};
const icons = { pending: '⏳', preparing: '👨‍🍳', ready: '✅', delivered: '🎉', cancelled: '❌' };

async function poll() {
    const res = await fetch(`/api/siparis/${orderId}/durum`);
    const data = await res.json();
    document.getElementById('statusLabel').textContent = data.status_label;
    document.getElementById('statusLabel').className = 'status-label mt-4 inline-block rounded-full px-5 py-2 text-sm font-semibold status-' + data.status;
    document.querySelector('.status-icon').textContent = icons[data.status] || '⏳';
    if (!['delivered','cancelled'].includes(data.status)) setTimeout(poll, 5000);
}
setTimeout(poll, 5000);
</script>
@endsection
