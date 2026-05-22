@extends('layouts.admin')
@section('title', $slide->exists ? 'Slayt Düzenle' : 'Yeni Slayt')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $slide->exists ? 'Slayt Düzenle' : 'Yeni Slayt' }}</h2></div>
<div class="admin-card max-w-xl">
    <form method="POST" action="{{ $slide->exists ? route('admin.slides.update', $slide) : route('admin.slides.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($slide->exists) @method('PUT') @endif
        <div><label class="form-label">Başlık</label><input type="text" name="title" value="{{ old('title', $slide->title) }}" class="form-input"></div>
        <div><label class="form-label">Alt Başlık</label><input type="text" name="subtitle" value="{{ old('subtitle', $slide->subtitle) }}" class="form-input"></div>
        <div>
            <label class="form-label">Görsel * (1920×1080 önerilir)</label>
            <input type="file" name="image" accept="image/*" {{ $slide->exists ? '' : 'required' }} class="form-input">
            @if($slide->image)<img src="{{ $slide->image_url }}" class="mt-2 max-w-xs rounded-lg">@endif
        </div>
        <div><label class="form-label">Geçiş Süresi (sn)</label><input type="number" name="duration" value="{{ old('duration', $slide->duration ?? 10) }}" min="3" max="60" class="form-input"></div>
        <div><label class="form-label">Sıra</label><input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order ?? 0) }}" class="form-input"></div>
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $slide->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]"> Aktif</label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.slides.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
