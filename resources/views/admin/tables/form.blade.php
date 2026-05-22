@extends('layouts.admin')
@section('title', $table->exists ? 'Masa Düzenle' : 'Yeni Masa')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $table->exists ? 'Masa Düzenle' : 'Yeni Masa Ekle' }}</h2></div>
<div class="grid gap-6 lg:grid-cols-2">
    <div class="admin-card max-w-xl">
        <form method="POST" action="{{ $table->exists ? route('admin.tables.update', $table) : route('admin.tables.store') }}" class="space-y-4">
            @csrf
            @if($table->exists) @method('PUT') @endif
            <div>
                <label class="form-label">Masa No *</label>
                <input type="text" name="number" value="{{ old('number', $table->number) }}" required class="form-input" placeholder="15">
                <p class="mt-1 text-xs text-gray-500">QR linki: {{ url('/menu') }}?masa=<strong>NUMARA</strong></p>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $table->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]"> Aktif
            </label>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary">{{ $table->exists ? 'Kaydet' : 'Masa Ekle & QR Oluştur' }}</button>
                <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </div>
    @if($table->exists)
    <div class="admin-card">
        <h3 class="mb-4 font-semibold text-gray-800">QR Kod — Masa {{ $table->number }}</h3>
        @if($table->qr_image_url)
        <div class="flex flex-col items-center rounded-xl border border-gray-100 bg-white p-6">
            <img src="{{ $table->qr_image_url }}" alt="QR" class="h-48 w-48 object-contain">
            <p class="mt-3 break-all text-center text-xs text-gray-500">{{ $table->menu_url }}</p>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('admin.tables.qr.png', $table) }}" class="btn btn-sm btn-primary" download>PNG İndir</a>
                <a href="{{ route('admin.tables.qr.svg', $table) }}" class="btn btn-sm btn-secondary" download>SVG İndir</a>
            </div>
        </div>
        @else
        <p class="text-sm text-gray-500">QR henüz yok. <form action="{{ route('admin.tables.regenerate', $table) }}" method="POST" class="inline">@csrf<button type="submit" class="text-[#E67E22] hover:underline">Oluştur</button></form></p>
        @endif
    </div>
    @endif
</div>
@endsection
