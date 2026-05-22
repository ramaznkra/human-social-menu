@extends('layouts.admin')
@section('title', $table->exists ? 'Masa Düzenle' : 'Yeni Masa')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">{{ $table->exists ? 'Masa Düzenle' : 'Yeni Masa' }}</h2></div>
<div class="admin-card max-w-xl">
    <form method="POST" action="{{ $table->exists ? route('admin.tables.update', $table) : route('admin.tables.store') }}" class="space-y-4">
        @csrf
        @if($table->exists) @method('PUT') @endif
        <div><label class="form-label">Masa No *</label><input type="text" name="number" value="{{ old('number', $table->number) }}" required class="form-input"></div>
        @if($table->exists)
        <div><label class="form-label">QR Link</label><input type="text" readonly value="{{ $table->qr_url }}" class="form-input bg-gray-50 text-gray-500"></div>
        @endif
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $table->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22] focus:ring-[#E67E22]"> Aktif</label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
