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
        <div><label class="form-label">Ad (Türkçe) *</label><input type="text" name="name" value="{{ old('name', $product->name) }}" required class="form-input"></div>
        <div><label class="form-label">Ad (English)</label><input type="text" name="name_en" value="{{ old('name_en', $product->name_en) }}" class="form-input"></div>
        <div><label class="form-label">Ad (Русский)</label><input type="text" name="name_ru" value="{{ old('name_ru', $product->name_ru) }}" class="form-input"></div>
        <div><label class="form-label">Açıklama (Türkçe)</label><textarea name="description" class="form-input min-h-[80px]">{{ old('description', $product->description) }}</textarea></div>
        <div><label class="form-label">Açıklama (English)</label><textarea name="description_en" class="form-input min-h-[60px]">{{ old('description_en', $product->description_en) }}</textarea></div>
        <div><label class="form-label">Açıklama (Русский)</label><textarea name="description_ru" class="form-input min-h-[60px]">{{ old('description_ru', $product->description_ru) }}</textarea></div>
        <div><label class="form-label">Fiyat (₺) *</label><input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="form-input"></div>
        <div>
            <label class="form-label">Rozet (Popüler, Yeni...)</label>
            @php $currentBadge = old('badge', $product->badge); @endphp
            <input type="text" id="badgeInput" name="badge" value="{{ $currentBadge }}" class="form-input" placeholder="Rozet seç veya yaz" autocomplete="off" data-badge-input>
            @if(!empty($badgeSuggestions))
            <div class="mt-2 flex flex-wrap gap-2" data-badge-chips>
                @foreach($badgeSuggestions as $badge)
                <button
                    type="button"
                    class="product-badge-chip {{ $currentBadge === $badge ? 'is-active' : '' }}"
                    data-badge-chip
                    data-badge-value="{{ $badge }}"
                >{{ $badge }}</button>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-gray-500">Bir rozete tıkla otomatik seçilsin; tekrar tıklarsan kaldırılır.</p>
            @endif
        </div>
        <div>
            <label class="form-label">Görsel</label>
            <input type="file" name="image" accept="image/*" class="form-input file:mr-3 file:rounded-lg file:border-0 file:bg-[#E67E22]/10 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#E67E22]">
            @if($product->image)<img src="{{ $product->image_url }}" class="mt-2 h-16 w-16 rounded-lg object-cover">@endif
        </div>
        <div><label class="form-label">Sıra</label><input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-input"></div>
        @if($product->exists)
        <p class="text-xs text-gray-500">Ürünün menüde görünürlüğünü <a href="{{ route('admin.products.index') }}" class="font-medium text-[#E67E22] hover:underline">Ürünler</a> listesindeki anahtardan değiştirebilirsiniz.</p>
        @endif
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/admin-product-form.js')
@endpush
