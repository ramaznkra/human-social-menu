@extends('layouts.admin')
@section('title', 'Geçmiş Adisyonlar')
@section('page_heading', 'Geçmiş Adisyonlar')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Geçmiş Adisyonlar</h2>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.live-orders.index') }}" class="btn btn-primary">⚡ Canlı Siparişler</a>
    </div>
</div>

<div class="admin-card">
    <form method="GET" action="{{ route('admin.orders.archive') }}" class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" data-archive-filter-form>
        <div class="sm:col-span-2">
            <label class="form-label">Ara</label>
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Adisyon no veya masa no…"
                class="form-input w-full"
                data-archive-auto
            >
        </div>
        <div>
            <label class="form-label">Durum</label>
            <select name="status" class="form-input w-full" data-archive-auto>
                <option value="">Tamamlandı + İptal</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Tamamlandı</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>İptal</option>
            </select>
        </div>
        <div>
            <label class="form-label">Başlangıç</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-full" data-archive-auto>
        </div>
        <div>
            <label class="form-label">Bitiş</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-full" data-archive-auto>
        </div>
        <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" class="btn btn-primary">Filtrele</button>
            <a href="{{ route('admin.orders.archive') }}" class="btn btn-secondary">Filtreleri Sıfırla</a>
        </div>
    </form>

    @if($filteredTotal > 0)
    <form
        method="POST"
        action="{{ route('admin.orders.archive.purge') }}"
        class="mb-4 inline-block"
        onsubmit="return confirm('Mevcut filtreye uyan {{ $filteredTotal }} adisyon kalıcı olarak silinsin mi? Bu işlem geri alınamaz.')"
    >
        @csrf
        @foreach(request()->only(['q', 'status', 'date_from', 'date_to']) as $key => $value)
            @if($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <button type="submit" class="btn btn-danger">Arşivi Temizle ({{ $filteredTotal }})</button>
    </form>
    @endif

    <p class="mb-4 text-xs text-gray-500">
        Durum ve tarih seçildiğinde liste otomatik güncellenir.
        @if(request()->hasAny(['q', 'status', 'date_from', 'date_to']))
            <span class="font-medium text-gray-700">Aktif filtre: {{ $filteredTotal }} kayıt.</span>
        @endif
    </p>

    <div class="overflow-x-auto">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Adisyon</th>
                    <th>Masa</th>
                    <th>Tutar</th>
                    <th>Ödeme</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td class="font-semibold text-gray-800">#{{ $order->order_number }}</td>
                    <td>{{ $order->table?->number ?? '—' }}</td>
                    <td class="font-semibold text-[#E67E22]">{{ number_format($order->total, 0, ',', '.') }} ₺</td>
                    <td>
                        @if($order->payment_method_label)
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $order->payment_method_label }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td><span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span></td>
                    <td class="whitespace-nowrap text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    <td class="whitespace-nowrap">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-secondary">Detay</a>
                            <form
                                action="{{ route('admin.orders.destroy', $order) }}"
                                method="POST"
                                class="inline"
                                onsubmit="return confirm('#{{ $order->order_number }} adisyonu kalıcı olarak silinsin mi?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-500">Arşivde adisyon bulunamadı.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="mt-6 border-t border-gray-100 pt-4">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.querySelector('[data-archive-filter-form]');
    if (!form) return;

    let searchTimer = null;

    form.querySelectorAll('[data-archive-auto]').forEach((el) => {
        if (el.type === 'search' || el.type === 'text') {
            el.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => form.requestSubmit(), 450);
            });
            return;
        }
        el.addEventListener('change', () => form.requestSubmit());
    });
})();
</script>
@endpush
