@extends('layouts.admin')
@section('title', 'Geçmiş Adisyonlar')
@section('section_label', 'Siparişler')
@section('page_heading', 'Geçmiş Adisyonlar')

@section('content')
@php
    $exportQuery = request()->only(['q', 'status', 'date_from', 'date_to']);
@endphp
<div class="orders-archive">
    <div class="orders-archive__top mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-xl text-sm text-gray-500">
            Ödenmiş veya iptal edilmiş adisyonlar. Arama ve tarih alanları seçildiğinde liste otomatik güncellenir.
        </p>
        <div class="orders-archive__top-actions flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Siparişler</a>
            <a href="{{ route('admin.live-orders.index') }}" class="btn btn-primary inline-flex items-center gap-2">
                Canlı Siparişler
                @include('admin.partials.icons.live-orders', ['class' => 'h-4 w-4 shrink-0'])
            </a>
        </div>
    </div>

    <div class="admin-card orders-archive__card">
        <form
            method="GET"
            action="{{ route('admin.orders.archive') }}"
            class="orders-archive__filters"
            data-archive-filter-form
        >
            <div class="orders-archive__filter-grid">
                <div class="orders-archive__filter-field">
                    <label class="form-label" for="archive-q">Ara</label>
                    <input
                        type="search"
                        id="archive-q"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Adisyon no veya masa no…"
                        class="form-input w-full max-w-none"
                        data-archive-auto
                    >
                </div>
                <div class="orders-archive__filter-field">
                    <label class="form-label" for="archive-status">Durum</label>
                    <select id="archive-status" name="status" class="form-input w-full max-w-none" data-archive-auto>
                        <option value="">Tamamlandı + İptal</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Tamamlandı</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>İptal</option>
                    </select>
                </div>
                <div class="orders-archive__filter-field">
                    <label class="form-label" for="archive-date-from">Başlangıç</label>
                    <input
                        type="date"
                        id="archive-date-from"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="form-input w-full max-w-none"
                        data-archive-auto
                    >
                </div>
                <div class="orders-archive__filter-field">
                    <label class="form-label" for="archive-date-to">Bitiş</label>
                    <input
                        type="date"
                        id="archive-date-to"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="form-input w-full max-w-none"
                        data-archive-auto
                    >
                </div>
            </div>

            <div class="orders-archive__filter-actions">
                <p class="orders-archive__filter-hint text-xs text-gray-500">
                    @if(request()->hasAny(['q', 'status', 'date_from', 'date_to']))
                        <span class="font-medium text-gray-700">{{ $filteredTotal }} kayıt</span>
                        <span class="text-gray-400">·</span>
                        Filtre aktif
                    @else
                        Tüm arşiv kayıtları listeleniyor
                    @endif
                </p>
                <div class="orders-archive__filter-buttons flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Filtrele</button>
                    @if(request()->hasAny(['q', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.orders.archive') }}" class="btn btn-secondary">Filtreleri Sıfırla</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="orders-archive__summary">
            <div class="orders-archive__summary-item">
                <p class="orders-archive__summary-label">Net ciro (ödenen)</p>
                <p class="orders-archive__summary-value orders-archive__summary-value--accent">{{ $summary['net_revenue_formatted'] }}</p>
            </div>
            <div class="orders-archive__summary-item">
                <p class="orders-archive__summary-label">Nakit · Kart</p>
                <p class="orders-archive__summary-value text-base">
                    {{ $summary['cash_revenue_formatted'] }}
                    <span class="font-normal text-gray-400">·</span>
                    {{ $summary['card_revenue_formatted'] }}
                </p>
            </div>
            <div class="orders-archive__summary-item">
                <p class="orders-archive__summary-label">Ödenen / İptal</p>
                <p class="orders-archive__summary-value text-base">
                    {{ $summary['paid_orders'] }}
                    <span class="font-normal text-gray-400">/</span>
                    {{ $summary['cancelled_orders'] }}
                </p>
            </div>
        </div>

        <div class="orders-archive__export-row mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">
                PDF indirmeleri mevcut filtreyi kullanır. Günlük dosya
                <strong>{{ $exportDayLabel }}</strong> gününü içerir.
            </p>
            <div class="orders-archive__downloads">
                <a
                    href="{{ route('admin.orders.archive.export', array_merge(['mode' => 'daily'], $exportQuery)) }}"
                    class="btn btn-secondary"
                >Günlük PDF</a>
                <a
                    href="{{ route('admin.orders.archive.export', array_merge(['mode' => 'report'], $exportQuery)) }}"
                    class="btn btn-primary"
                >Özet &amp; Liste PDF</a>
            </div>
        </div>

        @if($filteredTotal > 0)
        <div class="orders-archive__purge">
            <div class="orders-archive__purge-text">
                <p class="text-sm font-medium text-gray-800">Arşivi temizle</p>
                <p class="mt-0.5 text-xs text-gray-500">
                    Mevcut filtreye uyan <strong>{{ $filteredTotal }}</strong> adisyon kalıcı olarak silinir. Geri alınamaz.
                </p>
            </div>
            <form
                method="POST"
                action="{{ route('admin.orders.archive.purge') }}"
                class="orders-archive__purge-form shrink-0"
                @include('admin.partials.confirm-form', [
                    'title' => 'Arşivi temizle',
                    'message' => "Mevcut filtreye uyan {$filteredTotal} adisyon kalıcı olarak silinecek.",
                    'hint' => 'Bu işlem geri alınamaz.',
                    'type' => 'danger',
                    'confirmLabel' => 'Temizle',
                ])
            >
                @csrf
                @foreach(request()->only(['q', 'status', 'date_from', 'date_to']) as $key => $value)
                    @if($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <button type="submit" class="btn btn-danger whitespace-nowrap">
                    Temizle ({{ $filteredTotal }})
                </button>
            </form>
        </div>
        @endif

        <div class="orders-archive__table-wrap overflow-x-auto">
            <table class="admin-table orders-archive__table w-full min-w-[720px]">
                <thead>
                    <tr>
                        <th>Adisyon</th>
                        <th>Kaynak</th>
                        <th>Masa</th>
                        <th>Tutar</th>
                        <th>Ödeme</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th class="orders-archive__th-actions">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    <tr class="{{ $order->isWaiterOrder() ? 'order-row--waiter' : '' }}">
                        <td class="font-semibold text-gray-800">#{{ $order->order_number }}</td>
                        <td>@include('admin.partials.waiter-order-badge', ['order' => $order])</td>
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
                        <td class="orders-archive__td-actions">
                            <div class="orders-archive__row-actions">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-secondary">Detay</a>
                                <form
                                    action="{{ route('admin.orders.destroy', $order) }}"
                                    method="POST"
                                    class="orders-archive__delete-form"
                                    @include('admin.partials.confirm-form', [
                                        'title' => 'Adisyonu sil',
                                        'message' => "#{$order->order_number} kalıcı olarak silinecek.",
                                        'hint' => 'Bu işlem geri alınamaz.',
                                        'type' => 'danger',
                                        'confirmLabel' => 'Sil',
                                    ])
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
                        <td colspan="8" class="orders-archive__empty py-12 text-center text-gray-500">
                            Arşivde adisyon bulunamadı.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="orders-archive__pagination mt-6 border-t border-gray-100 pt-4">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
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
