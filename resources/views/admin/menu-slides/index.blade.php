@extends('layouts.admin')
@section('title', 'Menü Üst Slaytları')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-semibold text-gray-800">Menü Üst Slaytları</h2>
        <p class="mt-1 text-sm text-gray-500">QR menünün en üstündeki fotoğraf geçişleri (5–10 sn). Mekan ve özel misafir görselleri.</p>
    </div>
    <a href="{{ route('admin.menu-slides.create') }}" class="btn btn-primary">+ Yeni Slayt</a>
</div>
<div class="admin-card overflow-x-auto">
    <table class="admin-table w-full">
        <thead><tr><th>Önizleme</th><th>Başlık</th><th>Tür</th><th>Süre</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @forelse($slides as $s)
        <tr>
            <td><img src="{{ $s->image_url }}" alt="" class="h-14 w-24 rounded-lg object-cover bg-gray-100"></td>
            <td>
                <span class="font-medium text-gray-800">{{ $s->title ?? '—' }}</span>
                @if($s->subtitle)<br><span class="text-xs text-gray-500">{{ $s->subtitle }}</span>@endif
            </td>
            <td><span class="badge-status {{ $s->type === 'guest' ? 'badge-preparing' : 'badge-ready' }}">{{ $s->type_label }}</span></td>
            <td>{{ $s->duration }} sn</td>
            <td>{{ $s->sort_order }}</td>
            <td>{{ $s->is_active ? 'Aktif' : 'Pasif' }}</td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ route('admin.menu-slides.edit', $s) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form action="{{ route('admin.menu-slides.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Silinsin mi?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Sil</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-8 text-center text-gray-500">Henüz slayt yok. Örnek görseller için <code class="text-xs">php artisan db:seed --class=HumanSeeder</code></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
