@extends('layouts.admin')
@section('title', 'Kategoriler')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Kategoriler</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">+ Yeni Kategori</a>
</div>
<div class="admin-card overflow-x-auto">
    <table class="admin-table w-full">
        <thead><tr><th>Sıra</th><th>Ad (TR)</th><th>EN / RU</th><th>Slug</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @foreach($categories as $cat)
        <tr>
            <td>{{ $cat->sort_order }}</td>
            <td class="font-medium">{{ $cat->name }}</td>
            <td class="text-xs text-gray-500">{{ $cat->name_en ?: '—' }} · {{ $cat->name_ru ?: '—' }}</td>
            <td class="text-gray-500">{{ $cat->slug }}</td>
            <td>{{ $cat->is_active ? 'Aktif' : 'Pasif' }}</td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form
                    action="{{ route('admin.categories.destroy', $cat) }}"
                    method="POST"
                    class="inline"
                    @include('admin.partials.confirm-form', [
                        'title' => 'Kategoriyi sil',
                        'message' => $cat->name.' kalıcı olarak silinecek.',
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
