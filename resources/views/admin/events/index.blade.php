@extends('layouts.admin')
@section('title', 'Etkinlikler')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold text-gray-800">Etkinlikler</h2>
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">+ Yeni Etkinlik</a>
</div>
<div class="admin-card overflow-x-auto">
    <table class="admin-table w-full">
        <thead><tr><th>Başlık</th><th>Tarih</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @foreach($events as $e)
        <tr>
            <td class="font-medium text-gray-800">{{ $e->title }}</td>
            <td class="text-gray-500">{{ $e->event_date?->format('d.m.Y H:i') ?? '—' }}</td>
            <td>{{ $e->is_active ? 'Aktif' : 'Pasif' }}</td>
            <td class="space-x-1 whitespace-nowrap">
                <a href="{{ route('admin.events.edit', $e) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                <form action="{{ route('admin.events.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('Silinsin mi?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Sil</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
