@extends('layouts.admin')
@section('title', $product->exists ? 'Ürün Düzenle' : 'Yeni Ürün')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $product->exists ? 'Ürün Düzenle' : 'Yeni Ürün' }}</h2></div>
<div class="admin-card max-w-xl">
    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($product->exists) @method('PUT') @endif
        <div>
            <label class="form-label">Departman *</label>
            <select name="type" required class="form-input">
                <option value="kitchen" {{ old('type', $product->type ?? 'kitchen') === 'kitchen' ? 'selected' : '' }}>Mutfak / Yemek</option>
                <option value="bar" {{ old('type', $product->type) === 'bar' ? 'selected' : '' }}>Bar / İçecek</option>
            </select>
        </div>
        <div>
            <label class="form-label">Kategori *</label>
            <select name="category_id" required class="form-input">
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="form-label">Ad *</label><input type="text" name="name" value="{{ old('name', $product->name) }}" required class="form-input"></div>
        <div><label class="form-label">Açıklama</label><textarea name="description" class="form-input min-h-[100px]">{{ old('description', $product->description) }}</textarea></div>
        <div><label class="form-label">Fiyat (₺) *</label><input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="form-input"></div>
        <div><label class="form-label">Rozet (Popüler, Yeni...)</label><input type="text" name="badge" value="{{ old('badge', $product->badge) }}" class="form-input"></div>
        <div>
            <label class="form-label">Görsel</label>
            <input type="file" name="image" accept="image/*" class="form-input file:mr-3 file:rounded-lg file:border-0 file:bg-[#E67E22]/10 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#E67E22]">
            @if($product->image)<img src="{{ $product->image_url }}" class="mt-2 h-16 w-16 rounded-lg object-cover">@endif
        </div>
        <div><label class="form-label">Sıra</label><input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-input"></div>
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_available" value="1" {{ old('is_available', $product->is_available ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]"> Mevcut</label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
