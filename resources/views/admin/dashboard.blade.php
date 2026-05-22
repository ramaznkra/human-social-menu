@extends('layouts.admin')

@section('title', 'Panel')
@section('page_heading', 'Kontrol Paneli')

@section('content')
<div class="mb-8 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Hoş geldiniz{{ session('admin_name') ? ', ' . session('admin_name') : '' }}</h2>
    <p id="liveOpsStatus" class="text-sm text-gray-500">Canlı bağlanıyor…</p>
</div>

{{-- Canlı operasyon --}}
<div
    id="admin-live-ops"
    class="mb-8"
    data-api-url="{{ route('admin.operations.live') }}"
    data-acknowledge-url="{{ str_replace('/0/', '/__ID__/', route('admin.operations.acknowledge', ['call' => 0])) }}"
>
    <div class="admin-card border-[#E67E22]/20">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-800">Canlı Operasyon</h3>
                <span id="liveOpsBadge" class="hidden min-w-[1.5rem] rounded-full bg-[#E67E22] px-2 py-0.5 text-center text-xs font-bold text-white">0</span>
            </div>
            <span class="text-xs text-gray-500">Siparişler ve masa çağrıları anlık güncellenir</span>
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <h4 class="mb-2 text-xs font-semibold tracking-wider text-gray-500 uppercase">Yeni / Aktif Siparişler</h4>
                <div id="liveOrdersList" class="max-h-72 space-y-2 overflow-y-auto transition-all duration-500 ease-in-out">
                    <p class="py-4 text-center text-sm text-gray-500">Yükleniyor…</p>
                </div>
            </div>
            <div>
                <h4 class="mb-2 text-xs font-semibold tracking-wider text-[#E67E22] uppercase">Garson & Hesap Çağrıları</h4>
                <div id="liveCallsList" class="max-h-72 space-y-2 overflow-y-auto transition-all duration-500 ease-in-out">
                    <p class="py-4 text-center text-sm text-gray-500">Yükleniyor…</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
    <div class="stat-card"><div class="value">{{ $stats['categories'] }}</div><div class="label">Kategori</div></div>
    <div class="stat-card"><div class="value">{{ $stats['products'] }}</div><div class="label">Ürün</div></div>
    <div class="stat-card"><div class="value">{{ $stats['tables'] }}</div><div class="label">Masa</div></div>
    <div class="stat-card"><div class="value">{{ $stats['orders_today'] }}</div><div class="label">Bugünkü Sipariş</div></div>
    <div class="stat-card border-[#E67E22]/20"><div class="value">{{ $stats['pending_orders'] }}</div><div class="label">Bekleyen</div></div>
</div>

<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('menu.index') }}" target="_blank" class="text-sm font-medium text-[#E67E22] hover:underline">📱 Genel Menü</a>
    <a href="{{ route('display.index') }}" target="_blank" class="text-sm font-medium text-[#E67E22] hover:underline">📺 TV Ekranı</a>
    <a href="{{ route('kitchen.index') }}" target="_blank" class="text-sm font-medium text-[#E67E22] hover:underline">👨‍🍳 Mutfak Paneli</a>
</div>

<div class="admin-card">
    <h3 class="mb-4 text-lg font-semibold text-gray-800">Son Siparişler</h3>
    <div class="overflow-x-auto">
        <table class="admin-table w-full">
            <thead><tr><th>No</th><th>Masa</th><th>Tutar</th><th>Durum</th><th>Saat</th></tr></thead>
            <tbody>
            @forelse($recentOrders as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-[#E67E22] hover:underline">#{{ $order->order_number }}</a></td>
                <td>{{ $order->table?->number ?? '—' }}</td>
                <td class="font-medium">{{ number_format($order->total, 0) }} ₺</td>
                <td><span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span></td>
                <td class="text-gray-500">{{ $order->created_at->format('H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-8 text-center text-gray-500">Henüz sipariş yok</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/admin-dashboard.js')
@endpush
