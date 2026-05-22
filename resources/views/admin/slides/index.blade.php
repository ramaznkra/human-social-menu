@extends('layouts.admin')
@section('title', 'Ekran Slaytları')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">TV Ekran Slaytları</h2>
    <div class="flex gap-2">
        <a href="{{ route('display.index') }}" target="_blank" class="btn btn-secondary">Ekranı Önizle</a>
        <a href="{{ route('admin.slides.create') }}" class="btn btn-primary">+ Yeni Slayt</a>
    </div>
</div>
<div class="admin-card overflow-x-auto">
    <p class="mb-4 text-sm text-gray-500">Tam ekran slider. TV'de <strong class="text-gray-700">/ekran</strong> adresini açın.</p>
    <table class="admin-table w-full">
        <thead><tr><th>Önizleme</th><th>Başlık</th><th>Süre</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @foreach($slides as $s)
        <tr>
            <td><img src="{{ $s->image_url }}" class="h-14 w-24 rounded-lg object-cover bg-gray-100" onerror="this.classList.add('bg-gray-200')"></td>
            <td><span class="font-medium text-gray-800">{{ $s->title }}</span><br><span class="text-xs text-gray-500">{{ $s->subtitle }}</span></td>
            <td>{{ $s->duration }} sn</td>
            <td>{{ $s->sort_order }}</td>
            <td>{{ $s->is_active ? 'Aktif' : 'Pasif' }}</td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ route('admin.slides.edit', $s) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form
                    action="{{ route('admin.slides.destroy', $s) }}"
                    method="POST"
                    class="inline"
                    @include('admin.partials.confirm-form', [
                        'title' => 'Slaytı sil',
                        'message' => 'Bu slayt kalıcı olarak silinecek.',
                        'type' => 'danger',
                        'confirmLabel' => 'Sil',
                    ])
                >
                    @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Sil</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
