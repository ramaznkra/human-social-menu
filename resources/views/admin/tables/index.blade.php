@extends('layouts.admin')
@section('title', 'Masalar & QR')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Masalar & QR Kodları</h2>
    <a href="{{ route('admin.tables.create') }}" class="btn btn-primary">+ Yeni Masa Ekle</a>
</div>
<div class="admin-card overflow-x-auto">
    <p class="mb-4 text-sm text-gray-500">
        Her masa için otomatik QR üretilir. Link formatı: <code class="rounded bg-gray-100 px-1 text-xs">/menu?masa=NUMARA</code>
        — PNG (GD varsa) ve SVG indirilebilir.
    </p>
    <table class="admin-table w-full">
        <thead><tr><th>QR</th><th>Masa</th><th>Menü linki</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @foreach($tables as $t)
        <tr>
            <td>
                @if($t->qr_image_url)
                <img src="{{ $t->qr_image_url }}" alt="QR Masa {{ $t->number }}" class="h-16 w-16 rounded border border-gray-100 bg-white p-1 object-contain">
                @else
                <span class="text-xs text-gray-400">—</span>
                @endif
            </td>
            <td class="font-semibold text-gray-800">Masa {{ $t->number }}</td>
            <td class="max-w-[200px] text-xs break-all">
                <a href="{{ $t->menu_url }}" target="_blank" class="text-[#E67E22] hover:underline">{{ $t->menu_url }}</a>
            </td>
            <td>{{ $t->is_active ? 'Aktif' : 'Pasif' }}</td>
            <td>
                <div class="flex flex-wrap gap-1">
                    <a href="{{ $t->menu_url }}" target="_blank" class="btn btn-sm btn-primary">Menü</a>
                    <a href="{{ route('admin.tables.qr.png', $t) }}" class="btn btn-sm btn-secondary" download>PNG</a>
                    <a href="{{ route('admin.tables.qr.svg', $t) }}" class="btn btn-sm btn-secondary" download>SVG</a>
                    <a href="{{ route('admin.tables.edit', $t) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                    <form action="{{ route('admin.tables.regenerate', $t) }}" method="POST" class="inline">@csrf<button type="submit" class="btn btn-sm btn-secondary" title="QR yeniden üret">↻</button></form>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
