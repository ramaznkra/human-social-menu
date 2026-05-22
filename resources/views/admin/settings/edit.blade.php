@extends('layouts.admin')
@section('title', 'Ayarlar')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">Mekan Ayarları</h2></div>
<div class="admin-card max-w-xl">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
        @csrf @method('PUT')
        <div><label class="form-label">Mekan Adı</label><input type="text" name="venue_name" value="{{ $settings['venue_name'] ?? 'Human' }}" class="form-input"></div>
        <div><label class="form-label">Slogan</label><input type="text" name="venue_slogan" value="{{ $settings['venue_slogan'] ?? 'Social People' }}" class="form-input"></div>
        <div><label class="form-label">Telefon</label><input type="text" name="venue_phone" value="{{ $settings['venue_phone'] ?? '' }}" class="form-input"></div>
        <div><label class="form-label">Adres</label><input type="text" name="venue_address" value="{{ $settings['venue_address'] ?? '' }}" class="form-input"></div>
        <div><label class="form-label">Para Birimi</label><input type="text" name="currency" value="{{ $settings['currency'] ?? '₺' }}" class="form-input"></div>
        <div>
            <label class="form-label">Sipariş Özelliği</label>
            <select name="order_enabled" class="form-input">
                <option value="1" {{ ($settings['order_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Açık</option>
                <option value="0" {{ ($settings['order_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Kapalı (sadece menü)</option>
            </select>
        </div>
        <div><label class="form-label">Varsayılan Ekran Geçiş Süresi (sn)</label><input type="number" name="display_interval" value="{{ $settings['display_interval'] ?? 10 }}" min="3" max="60" class="form-input"></div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
    </form>
</div>
@endsection
