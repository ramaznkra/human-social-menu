@extends('layouts.admin')
@section('title', 'Kategoriler')
@section('section_label', 'Menü')
@section('page_heading', 'Kategoriler')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
    <p class="text-sm text-gray-500">Kategorileri tepsi veya liste görünümünde yönetin.</p>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary shrink-0">+ Yeni Kategori</a>
</div>

<div class="admin-catalog" data-catalog-root="categories">
    <div class="admin-catalog__toolbar admin-card mb-4 p-4">
        @include('admin.partials.catalog-view-toggle', ['scope' => 'categories'])
    </div>
    <div data-view-panel="list" class="hidden">
        <div class="admin-card overflow-x-auto">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th>Sıra</th>
                        <th>Ad (TR)</th>
                        <th>EN / RU</th>
                        <th>Slug</th>
                        <th>Ürün</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $cat)
                <tr class="{{ $cat->is_active ? '' : 'opacity-50' }}">
                    <td>{{ $cat->sort_order }}</td>
                    <td class="font-medium">
                        <span class="inline-flex items-center gap-2">
                            <span class="table-status-dot {{ $cat->is_active ? 'table-status-dot--on' : 'table-status-dot--off' }}" aria-hidden="true"></span>
                            {{ $cat->name }}
                        </span>
                    </td>
                    <td class="text-xs text-gray-500">{{ $cat->name_en ?: '—' }} · {{ $cat->name_ru ?: '—' }}</td>
                    <td class="text-gray-500">{{ $cat->slug }}</td>
                    <td>{{ $cat->products_count }}</td>
                    <td class="space-x-1 whitespace-nowrap">
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                        @include('admin.partials.category-delete-form', ['category' => $cat])
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-12 text-center text-gray-500">Kategori yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div data-view-panel="tray">
        @if($categories->isEmpty())
        <div class="admin-card py-16 text-center text-gray-500">
            Henüz kategori yok. <a href="{{ route('admin.categories.create') }}" class="font-medium text-[#E67E22] hover:underline">İlk kategoriyi ekleyin</a>.
        </div>
        @else
        <div class="admin-catalog-tray">
            @foreach($categories as $cat)
            <article class="admin-tray-card admin-tray-card--category {{ $cat->is_active ? '' : 'admin-tray-card--hidden' }}">
                <div class="admin-tray-card__media admin-tray-card__media--category">
                    <div class="admin-tray-card__placeholder admin-tray-card__placeholder--icon" aria-hidden="true">
                        {{ $cat->icon ?: '📁' }}
                    </div>
                    <span class="table-status-dot {{ $cat->is_active ? 'table-status-dot--on' : 'table-status-dot--off' }} admin-tray-card__status-dot" aria-hidden="true"></span>
                </div>
                <div class="admin-tray-card__body">
                    <h3 class="admin-tray-card__title" title="{{ $cat->name }}">{{ $cat->name }}</h3>
                    <p class="admin-tray-card__meta">Sıra {{ $cat->sort_order }} · {{ $cat->products_count }} ürün</p>
                    <p class="admin-tray-card__meta text-[11px]">{{ $cat->slug }}</p>
                    @if($cat->name_en || $cat->name_ru)
                    <p class="admin-tray-card__meta text-[10px] text-gray-400">{{ $cat->name_en ?: '—' }} / {{ $cat->name_ru ?: '—' }}</p>
                    @endif
                    <div class="admin-tray-card__actions">
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-secondary w-full">Düzenle</a>
                        @include('admin.partials.category-delete-form', ['category' => $cat, 'block' => true])
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
@vite('resources/js/pages/admin-catalog-view.js')
@endpush
