@extends('layouts.admin')

@section('title', 'Panel')
@section('page_heading', 'Kontrol Paneli')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Hoş geldiniz{{ session('admin_name') ? ', ' . session('admin_name') : '' }}</h2>
</div>

<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="dashboard-finance-card">
        <p class="dashboard-finance-label">Günlük Toplam Ciro</p>
        <p class="dashboard-finance-value">{{ $finance['daily_revenue_formatted'] }}</p>
        <p class="dashboard-finance-hint">Bugün tamamlanan adisyonlar</p>
    </div>
    <div class="dashboard-finance-card">
        <p class="dashboard-finance-label">Aktif Masa Sayısı</p>
        <p class="dashboard-finance-value">{{ $finance['active_tables'] }} <span class="text-lg font-semibold text-gray-500">Masa</span></p>
        <p class="dashboard-finance-hint">Canlı sipariş veya çağrı</p>
    </div>
    <div class="dashboard-finance-card">
        <p class="dashboard-finance-label">Tamamlanan Siparişler</p>
        <p class="dashboard-finance-value">{{ $finance['completed_orders'] }}</p>
        <p class="dashboard-finance-hint">Bugün kasadan geçen</p>
    </div>
    <div class="dashboard-finance-card">
        <p class="dashboard-finance-label">Ödeme Türü Dağılımı</p>
        <p class="dashboard-finance-value text-xl">{{ $finance['payment_split'] }}</p>
        @if(($finance['payment_cash_count'] + $finance['payment_card_count']) > 0)
        <p class="dashboard-finance-hint">{{ $finance['payment_card_count'] }} kart · {{ $finance['payment_cash_count'] }} nakit</p>
        @else
        <p class="dashboard-finance-hint">Teslimde ödeme türü seçin</p>
        @endif
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
    <a href="{{ route('admin.live-orders.index') }}" class="text-sm font-medium text-[#E67E22] hover:underline">⚡ Canlı Siparişler</a>
    <a href="{{ route('menu.index') }}" target="_blank" class="text-sm font-medium text-[#E67E22] hover:underline">📱 Genel Menü</a>
    <a href="{{ route('display.index') }}" target="_blank" class="text-sm font-medium text-[#E67E22] hover:underline">📺 TV Ekranı</a>
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
