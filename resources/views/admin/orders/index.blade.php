@extends('layouts.admin')
@section('title', 'Siparişler')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Siparişler</h2>
    <a href="{{ route('admin.live-orders.index') }}" class="btn btn-primary">⚡ Canlı Siparişler</a>
</div>
<div class="admin-card overflow-x-auto">
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <select name="status" onchange="this.form.submit()" class="form-input max-w-[200px]">
            <option value="">Tüm Durumlar</option>
            @foreach(['pending' => 'Beklemede', 'preparing' => 'Hazırlanıyor', 'ready' => 'Masada', 'delivered' => 'Tamamlandı', 'cancelled' => 'İptal'] as $s => $label)
            <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="form-input max-w-[180px]">
    </form>
    <table class="admin-table w-full">
        <thead><tr><th>No</th><th>Masa</th><th>Tutar</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
        <tbody>
        @foreach($orders as $order)
        <tr>
            <td class="font-medium">#{{ $order->order_number }}</td>
            <td>{{ $order->table?->number ?? '—' }}</td>
            <td class="font-semibold text-[#E67E22]">{{ number_format($order->total, 0) }} ₺</td>
            <td><span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span></td>
            <td class="text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }}</td>
            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-secondary">Detay</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
