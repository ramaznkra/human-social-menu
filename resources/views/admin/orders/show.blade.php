@extends('layouts.admin')
@section('title', 'Sipariş #' . $order->order_number)
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Sipariş #{{ $order->order_number }}</h2>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">← Liste</a>
</div>
<div class="admin-card max-w-2xl">
    <div class="mb-6 grid gap-2 text-sm text-gray-600">
        <p><strong class="text-gray-800">Masa:</strong> {{ $order->table?->number ?? 'Belirtilmedi' }}</p>
        <p><strong class="text-gray-800">Tarih:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
        <p><strong class="text-gray-800">Not:</strong> {{ $order->notes ?? '—' }}</p>
        <p><span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span></p>
    </div>
    <table class="admin-table w-full">
        <thead><tr><th>Ürün</th><th>Adet</th><th>Fiyat</th><th>Toplam</th></tr></thead>
        <tbody>
        @foreach($order->items as $item)
        <tr>
            <td class="font-medium">{{ $item->product_name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 0) }} ₺</td>
            <td class="font-semibold text-[#E67E22]">{{ number_format($item->subtotal, 0) }} ₺</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <p class="mt-4 text-xl font-bold text-gray-800">Toplam: <span class="text-[#E67E22]">{{ number_format($order->total, 0) }} ₺</span></p>

    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-6 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-6">
        @csrf @method('PATCH')
        <label class="text-sm font-medium text-gray-700">Durum:</label>
        <select name="status" class="form-input max-w-[200px]">
            @foreach(['pending','preparing','ready','delivered','cancelled'] as $s)
            <option value="{{ $s }}" {{ $order->status==$s?'selected':'' }}>
                @switch($s)
                    @case('pending') Bekliyor @break
                    @case('preparing') Hazırlanıyor @break
                    @case('ready') Hazır @break
                    @case('delivered') Teslim Edildi @break
                    @case('cancelled') İptal @break
                @endswitch
            </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Güncelle</button>
    </form>
</div>
@endsection
