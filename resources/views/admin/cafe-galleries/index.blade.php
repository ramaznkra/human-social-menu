@extends('layouts.admin')
@section('title', 'Social Spotted')
@section('page_heading', 'Social Spotted Galeri')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-semibold text-gray-800">Social Spotted</h2>
        <p class="mt-1 text-sm text-gray-500">Menüde logonun altında görünen dokunmatik galeri kartları (HSP Moments).</p>
    </div>
    <a href="{{ route('admin.cafe-galleries.create') }}" class="btn btn-primary">+ Yeni Kart</a>
</div>
<div class="admin-card overflow-x-auto">
    <table class="admin-table w-full">
        <thead><tr><th>Önizleme</th><th>Başlık</th><th>Açıklama</th><th>Rozet</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @forelse($galleries as $g)
        <tr>
            <td><img src="{{ $g->image_url }}" alt="" class="h-16 w-24 rounded-2xl object-cover bg-gray-100"></td>
            <td class="font-medium text-gray-800">{{ $g->title ?? '—' }}</td>
            <td class="max-w-xs truncate text-sm text-gray-500">{{ $g->description ?? '—' }}</td>
            <td class="text-xs text-gray-600">{{ $g->badge_text }}</td>
            <td>{{ $g->sort_order }}</td>
            <td>{{ $g->is_active ? 'Aktif' : 'Pasif' }}</td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ route('admin.cafe-galleries.edit', $g) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form action="{{ route('admin.cafe-galleries.destroy', $g) }}" method="POST" class="inline" onsubmit="return confirm('Silinsin mi?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Sil</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-8 text-center text-gray-500">Henüz kart yok. <code class="text-xs">php artisan migrate</code> ve seed çalıştırın.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
