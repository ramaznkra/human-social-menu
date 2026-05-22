@extends('layouts.admin')
@section('title', 'Ürünler')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Ürünler</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Yeni Ürün</a>
</div>
<div class="admin-card overflow-x-auto">
    <table class="admin-table w-full">
        <thead><tr><th></th><th>Ad</th><th>Kategori</th><th>Fiyat</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @foreach($products as $p)
        <tr>
            <td>@if($p->image)<img src="{{ $p->image_url }}" class="h-14 w-14 rounded-lg object-cover">@endif</td>
            <td class="font-medium text-gray-800">{{ $p->name }} @if($p->badge)<span class="badge-status badge-ready ml-1">{{ $p->badge }}</span>@endif</td>
            <td>{{ $p->category->name }}</td>
            <td class="font-semibold text-[#E67E22]">{{ number_format($p->price, 0) }} ₺</td>
            <td>{{ $p->is_available ? 'Mevcut' : 'Tükendi' }}</td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form action="{{ route('admin.products.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Silinsin mi?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Sil</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
