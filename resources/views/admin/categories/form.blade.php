@extends('layouts.admin')
@section('title', $category->exists ? 'Kategori Düzenle' : 'Yeni Kategori')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $category->exists ? 'Kategori Düzenle' : 'Yeni Kategori' }}</h2></div>
<div class="admin-card max-w-xl">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($category->exists) @method('PUT') @endif
        <div><label class="form-label">Ad *</label><input type="text" name="name" value="{{ old('name', $category->name) }}" required class="form-input"></div>
        <div><label class="form-label">Slug</label><input type="text" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="otomatik" class="form-input"></div>
        <div>
            <label class="form-label">Kapak görseli * (menüde büyük kart)</label>
            <input type="file" name="image" accept="image/*" class="form-input">
            @if($category->image_url)<img src="{{ $category->image_url }}" alt="" class="mt-2 h-24 w-full max-w-sm rounded-lg object-cover">@endif
            <p class="mt-1 text-xs text-gray-500">Yatay fotoğraf önerilir. Emoji kullanmayın.</p>
        </div>
        <div><label class="form-label">Sıra</label><input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-input"></div>
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]"> Aktif</label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
