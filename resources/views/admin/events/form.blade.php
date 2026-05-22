@extends('layouts.admin')
@section('title', $event->exists ? 'Etkinlik Düzenle' : 'Yeni Etkinlik')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $event->exists ? 'Etkinlik Düzenle' : 'Yeni Etkinlik' }}</h2></div>
<div class="admin-card max-w-xl">
    <form method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($event->exists) @method('PUT') @endif
        <div><label class="form-label">Başlık *</label><input type="text" name="title" value="{{ old('title', $event->title) }}" required class="form-input"></div>
        <div><label class="form-label">Açıklama</label><textarea name="description" class="form-input min-h-[100px]">{{ old('description', $event->description) }}</textarea></div>
        <div><label class="form-label">Tarih & Saat</label><input type="datetime-local" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d\TH:i')) }}" class="form-input"></div>
        <div><label class="form-label">Görsel</label><input type="file" name="image" accept="image/*" class="form-input"></div>
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $event->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]"> Aktif</label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
