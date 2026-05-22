@extends('layouts.admin')
@section('title', 'Masalar')
@section('page_heading', 'Masalar & QR')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-semibold text-gray-800">🪑 Masalar</h2>
        <p class="mt-1 text-sm text-gray-500">QR kodlarını indirip masaya yapıştırın. Canlı masa durumu için <a href="{{ route('admin.live-orders.index') }}" class="font-medium text-[#E67E22] hover:underline">Canlı Siparişler</a> ekranına bakın.</p>
    </div>
    <a href="{{ route('admin.tables.create') }}" class="btn btn-primary">+ Yeni Masa</a>
</div>

<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    @foreach($tables as $t)
    <article
        class="table-map-card flex flex-col rounded-2xl border p-4 shadow-sm transition
            {{ $t->is_active ? 'border-gray-200 bg-white' : 'border-gray-200 bg-gray-50 opacity-60' }}"
    >
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Masa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $t->number }}</p>
            </div>
            @if($t->is_active)
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">Aktif</span>
            @else
            <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold text-gray-500">Pasif</span>
            @endif
        </div>

        @if($t->qr_image_url)
        <div class="mx-auto my-3 flex h-24 w-24 items-center justify-center rounded-xl border border-gray-100 bg-white p-2 shadow-inner">
            <img src="{{ $t->qr_image_url }}" alt="QR Masa {{ $t->number }}" class="h-full w-full object-contain">
        </div>
        @else
        <div class="my-3 flex h-24 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 text-xs text-gray-400">QR yok</div>
        @endif

        <p class="mb-3 break-all text-center text-[10px] leading-tight text-gray-500">
            <a href="{{ $t->menu_url }}" target="_blank" class="text-[#E67E22] hover:underline">{{ $t->menu_url }}</a>
        </p>

        <div class="mt-auto flex flex-col gap-2">
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('admin.tables.qr.png', $t) }}" class="btn btn-sm btn-secondary text-center" download>PNG</a>
                <a href="{{ route('admin.tables.qr.svg', $t) }}" class="btn btn-sm btn-secondary text-center" download>SVG</a>
            </div>
            <a href="{{ route('admin.tables.qr.png', $t) }}" class="btn btn-sm btn-primary w-full text-center" download>
                QR Kodu İndir
            </a>
            <div class="flex gap-2">
                <a href="{{ $t->menu_url }}" target="_blank" class="btn btn-sm btn-secondary flex-1 text-center">Menü</a>
                <a href="{{ route('admin.tables.edit', $t) }}" class="btn btn-sm btn-secondary flex-1 text-center">Düzenle</a>
            </div>
            <form action="{{ route('admin.tables.regenerate', $t) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-secondary w-full" title="QR yeniden üret">↻ QR Yenile</button>
            </form>
        </div>
    </article>
    @endforeach
</div>

@if($tables->isEmpty())
<div class="admin-card py-12 text-center text-gray-500">
    Henüz masa yok. <a href="{{ route('admin.tables.create') }}" class="font-medium text-[#E67E22] hover:underline">İlk masayı ekleyin</a>.
</div>
@endif
@endsection
