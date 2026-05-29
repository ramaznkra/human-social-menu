@extends('layouts.admin')
@section('title', $category->exists ? 'Kategori Düzenle' : 'Yeni Kategori')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $category->exists ? 'Kategori Düzenle' : 'Yeni Kategori' }}</h2></div>
<div class="admin-card max-w-xl">
    @php
        $sampleImages = [
            'images/categories/samples/yiyecek.svg' => 'Yiyecek',
            'images/categories/samples/icecek.svg' => 'İçecek',
            'images/categories/samples/nargile.svg' => 'Nargile',
            'images/categories/samples/okey.svg' => 'Okey',
        ];
        $currentImage = old('preset_image', $category->image);
    @endphp
    <form method="POST" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="space-y-4">
        @csrf
        @if($category->exists) @method('PUT') @endif
        <div><label class="form-label">Ad (Türkçe) *</label><input type="text" name="name" value="{{ old('name', $category->name) }}" required class="form-input"></div>
        <div><label class="form-label">Ad (English)</label><input type="text" name="name_en" value="{{ old('name_en', $category->name_en) }}" class="form-input" placeholder="Optional"></div>
        <div><label class="form-label">Ad (Русский)</label><input type="text" name="name_ru" value="{{ old('name_ru', $category->name_ru) }}" class="form-input" placeholder="Необязательно"></div>
        <div><label class="form-label">Slug</label><input type="text" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="otomatik" class="form-input"></div>
        <div class="space-y-2">
            <label class="form-label">Kategori Görseli (Sadece Admin)</label>
            @if($category->image_url)
                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-20 w-28 rounded-lg border border-gray-200 object-cover">
            @endif
            <input type="file" name="image" accept="image/*" class="form-input">
            <p class="text-xs text-gray-500">JPG, PNG, WEBP veya GIF (maks. 3MB).</p>
            <label class="flex items-center gap-2 text-xs text-gray-600">
                <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]">
                Görseli kaldır
            </label>
        </div>
        <div class="space-y-2">
            <p class="form-label mb-0">Örnek Görseller</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach($sampleImages as $path => $label)
                    <label class="rounded-lg border border-gray-200 p-2 transition hover:border-[#E67E22]/40">
                        <input type="radio" name="preset_image" value="{{ $path }}" class="mr-2" {{ $currentImage === $path ? 'checked' : '' }}>
                        <span class="text-xs font-medium text-gray-700">{{ $label }}</span>
                        <img src="{{ asset($path) }}" alt="{{ $label }} örnek görsel" class="mt-2 h-16 w-full rounded-md object-cover">
                    </label>
                @endforeach
            </div>
        </div>
        <div><label class="form-label">Sıra</label><input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-input"></div>
        @if($category->exists)
        <p class="text-xs text-gray-500">Kategorinin aktif/pasif durumunu <a href="{{ route('admin.categories.index') }}" class="font-medium text-[#E67E22] hover:underline">Kategoriler</a> listesindeki anahtardan değiştirebilirsiniz.</p>
        @endif
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
