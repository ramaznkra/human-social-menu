@extends('layouts.admin')
@section('title', $slide->exists ? 'Menü Slaytı Düzenle' : 'Yeni Menü Slaytı')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $slide->exists ? 'Menü Slaytı Düzenle' : 'Yeni Menü Slaytı' }}</h2></div>
<div class="admin-card max-w-xl">
    <form method="POST" action="{{ $slide->exists ? route('admin.menu-slides.update', $slide) : route('admin.menu-slides.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($slide->exists) @method('PUT') @endif
        <div><label class="form-label">Başlık</label><input type="text" name="title" value="{{ old('title', $slide->title) }}" class="form-input" placeholder="Human Lounge"></div>
        <div><label class="form-label">Alt başlık</label><input type="text" name="subtitle" value="{{ old('subtitle', $slide->subtitle) }}" class="form-input" placeholder="Social People"></div>
        <div>
            <label class="form-label">Tür *</label>
            <select name="type" class="form-input" required>
                <option value="venue" {{ old('type', $slide->type ?? 'venue') === 'venue' ? 'selected' : '' }}>Mekan fotoğrafı</option>
                <option value="guest" {{ old('type', $slide->type) === 'guest' ? 'selected' : '' }}>Özel misafir / ünlü</option>
            </select>
        </div>
        <div>
            <label class="form-label">Görsel * (yatay, 16:9 önerilir)</label>
            <input type="file" name="image" accept="image/*" class="form-input" {{ $slide->exists ? '' : 'required' }}>
            @if($slide->image)<img src="{{ $slide->image_url }}" alt="" class="mt-2 max-h-40 rounded-lg object-cover">@endif
        </div>
        <div>
            <label class="form-label">Geçiş süresi (5–10 sn) *</label>
            <input type="number" name="duration" value="{{ old('duration', $slide->duration ?? 8) }}" min="5" max="10" required class="form-input">
        </div>
        <div><label class="form-label">Sıra</label><input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order ?? 0) }}" class="form-input"></div>
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $slide->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]"> Aktif</label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.menu-slides.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
