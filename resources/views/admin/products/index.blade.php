@extends('layouts.admin')
@section('title', 'Ürünler')
@section('page_heading', 'Ürünler')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Ürünler</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Yeni Ürün</a>
</div>
<p class="mb-4 text-sm text-gray-500">Anahtarı kapatınca ürün QR menüde anında gizlenir (sayfa yenilemeden).</p>
<div class="admin-card overflow-x-auto">
    <table class="admin-table w-full">
        <thead>
            <tr>
                <th></th>
                <th>Ad</th>
                <th>Kategori</th>
                <th>Fiyat</th>
                <th>Menüde</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($products as $p)
        <tr
            data-product-row
            class="{{ $p->is_available ? '' : 'opacity-50' }}"
        >
            <td>@if($p->image)<img src="{{ $p->image_url }}" alt="" class="h-14 w-14 rounded-lg object-cover">@endif</td>
            <td class="font-medium text-gray-800">
                {{ $p->name }}
                @if($p->badge)<span class="badge-status badge-ready ml-1">{{ $p->badge }}</span>@endif
            </td>
            <td>{{ $p->category->name }}</td>
            <td class="font-semibold text-[#E67E22]">{{ number_format($p->price, 0) }} ₺</td>
            <td>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            type="checkbox"
                            class="peer sr-only"
                            data-product-toggle
                            data-toggle-url="{{ route('admin.products.toggle-availability', $p) }}"
                            {{ $p->is_available ? 'checked' : '' }}
                            aria-label="{{ $p->name }} menüde göster"
                        >
                        <span class="relative h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#E67E22] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#E67E22]/30"></span>
                    </label>
                    <span
                        data-availability-label
                        class="text-xs font-medium {{ $p->is_available ? 'text-emerald-600' : 'text-gray-400' }}"
                    >{{ $p->is_available ? 'Menüde' : 'Gizli' }}</span>
                </div>
            </td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form
                    action="{{ route('admin.products.destroy', $p) }}"
                    method="POST"
                    class="inline"
                    @include('admin.partials.confirm-form', [
                        'title' => 'Ürünü sil',
                        'message' => $p->name.' kalıcı olarak silinecek.',
                        'type' => 'danger',
                        'confirmLabel' => 'Sil',
                    ])
                >
                    @csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Sil</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/admin-products.js')
@endpush
