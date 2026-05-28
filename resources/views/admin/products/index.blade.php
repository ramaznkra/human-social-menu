@extends('layouts.admin')
@section('title', 'Ürünler')
@section('section_label', 'Menü')
@section('page_heading', 'Ürünler')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-gray-500">Anahtarı kapatınca ürün QR menüde anında gizlenir.</p>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary shrink-0">+ Yeni Ürün</a>
</div>

<div class="admin-catalog" data-catalog-root="products">
    <div class="admin-catalog__toolbar admin-card mb-4 flex flex-col gap-4 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        @include('admin.partials.catalog-view-toggle', ['scope' => 'products'])

        <form method="GET" action="{{ route('admin.products.index') }}" class="flex w-full flex-wrap items-end gap-3 sm:w-auto sm:justify-end">
            <div class="min-w-[200px] flex-1 sm:max-w-xs">
                <label class="form-label" for="filter-category">Kategori</label>
                <select id="filter-category" name="category_id" class="form-input w-full max-w-none" onchange="this.form.submit()">
                    <option value="">Tümü</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('category_id'))
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Filtreyi kaldır</a>
            @endif
        </form>
    </div>

    <div data-view-panel="list" class="hidden">
        <div class="admin-card overflow-x-auto p-0">
            <table class="admin-table w-full min-w-[640px]">
                <thead>
                    <tr>
                        <th class="w-16"></th>
                        <th>Ad</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Menüde</th>
                        <th class="text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                <tr data-product-item class="{{ $p->is_available ? '' : 'opacity-50' }}">
                    <td>
                        @if($p->image)
                        <img src="{{ $p->image_url }}" alt="" class="h-12 w-12 rounded-lg object-cover">
                        @endif
                    </td>
                    <td class="font-medium text-gray-800">
                        {{ $p->name }}
                        @if($p->badge)<span class="badge-status badge-ready ml-1">{{ $p->badge }}</span>@endif
                    </td>
                    <td>{{ $p->category->name }}</td>
                    <td class="font-semibold text-[#E67E22] whitespace-nowrap">{{ number_format($p->price, 0, ',', '.') }} ₺</td>
                    <td>
                        @include('admin.partials.product-availability-toggle', ['product' => $p])
                    </td>
                    <td class="text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1">
                            <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                            @include('admin.partials.product-delete-form', ['product' => $p])
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-500">Ürün bulunamadı.</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div data-view-panel="tray">
        @if($products->isEmpty())
        <div class="admin-card py-16 text-center text-gray-500">Ürün bulunamadı.</div>
        @else
        <div class="admin-catalog-tray">
            @foreach($products as $p)
            <article
                data-product-item
                class="admin-tray-card {{ $p->is_available ? '' : 'admin-tray-card--hidden' }}"
            >
                <div class="admin-tray-card__media">
                    @if($p->image)
                    <img src="{{ $p->image_url }}" alt="" class="admin-tray-card__img" loading="lazy">
                    @else
                    <div class="admin-tray-card__placeholder" aria-hidden="true">🍽️</div>
                    @endif
                    @if($p->badge)
                    <span class="admin-tray-card__badge">{{ $p->badge }}</span>
                    @endif
                </div>
                <div class="admin-tray-card__body">
                    <h3 class="admin-tray-card__title" title="{{ $p->name }}">{{ $p->name }}</h3>
                    <p class="admin-tray-card__meta">{{ $p->category->name }}</p>
                    <p class="admin-tray-card__price">{{ number_format($p->price, 0, ',', '.') }} ₺</p>
                    <div class="admin-tray-card__toggle">
                        @include('admin.partials.product-availability-toggle', ['product' => $p, 'compact' => true])
                    </div>
                    <div class="admin-tray-card__actions">
                        <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-secondary w-full">Düzenle</a>
                        @include('admin.partials.product-delete-form', ['product' => $p, 'block' => true])
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/pages/admin-catalog-view.js', 'resources/js/pages/admin-products.js'])
@endpush
