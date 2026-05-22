@extends('layouts.admin')
@section('title', 'Masalar & QR')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Masalar & QR Kodları</h2>
    <a href="{{ route('admin.tables.create') }}" class="btn btn-primary">+ Yeni Masa</a>
</div>
<div class="admin-card overflow-x-auto">
    <p class="mb-4 text-sm text-gray-500">Her masanın benzersiz QR linki vardır. Bu linki QR kod oluşturucu ile yazdırıp masaya yerleştirin.</p>
    <table class="admin-table w-full">
        <thead><tr><th>Masa</th><th>QR Menü Linki</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @foreach($tables as $t)
        <tr>
            <td class="font-semibold text-gray-800">Masa {{ $t->number }}</td>
            <td class="max-w-xs text-xs break-all text-gray-500"><a href="{{ $t->qr_url }}" target="_blank" class="text-[#E67E22] hover:underline">{{ $t->qr_url }}</a></td>
            <td>{{ $t->is_active ? 'Aktif' : 'Pasif' }}</td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ $t->qr_url }}" target="_blank" class="btn btn-sm btn-primary">Menü</a>
                <a href="{{ route('admin.tables.edit', $t) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form action="{{ route('admin.tables.regenerate', $t) }}" method="POST" class="inline">@csrf<button class="btn btn-sm btn-secondary" title="QR yenile">🔄</button></form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
