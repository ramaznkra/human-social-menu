@extends('layouts.admin')
@section('title', 'Ayarlar')
@section('content')
<div class="mb-6"><h2 class="text-2xl font-semibold text-gray-800">Mekan Ayarları</h2></div>
<div class="grid gap-6 lg:grid-cols-2">
    <div class="admin-card">
        <h3 class="mb-4 font-semibold text-gray-800">Genel</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="form-label">Mekan Adı</label><input type="text" name="venue_name" value="{{ $settings['venue_name'] ?? 'Human' }}" class="form-input"></div>
            <div><label class="form-label">Menü Logo Yazısı (HSP)</label><input type="text" name="brand_mark" value="{{ $settings['brand_mark'] ?? 'HSP' }}" class="form-input" placeholder="HSP"></div>
            <div><label class="form-label">Menü Alt Slogan</label><input type="text" name="venue_tagline" value="{{ $settings['venue_tagline'] ?? 'Human Social Person — Coffee, Community, Experiences.' }}" class="form-input"></div>
            <div><label class="form-label">Slogan (kısa)</label><input type="text" name="venue_slogan" value="{{ $settings['venue_slogan'] ?? 'Social People' }}" class="form-input"></div>
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
            <div><label class="form-label">TV Ekran Geçiş Süresi (sn)</label><input type="number" name="display_interval" value="{{ $settings['display_interval'] ?? 10 }}" min="3" max="60" class="form-input"></div>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
    </div>
    <div class="admin-card">
        <h3 class="mb-4 font-semibold text-gray-800">QR Menü — Canlı Banner</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Günün Sosyal Mottosu</label>
                <input type="text" name="daily_motto" value="{{ $settings['daily_motto'] ?? '' }}" class="form-input" placeholder="Bugün sosyalleşme günü ☕">
                <label class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                    <input type="hidden" name="show_motto_banner" value="0">
                    <input type="checkbox" name="show_motto_banner" value="1" {{ ($settings['show_motto_banner'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22]"> Menüde göster
                </label>
            </div>
            <div>
                <label class="form-label">Wi-Fi Şifresi</label>
                <input type="text" name="wifi_password" value="{{ $settings['wifi_password'] ?? '' }}" class="form-input" placeholder="HumanSocial2026">
                <label class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                    <input type="hidden" name="show_wifi_banner" value="0">
                    <input type="checkbox" name="show_wifi_banner" value="1" {{ ($settings['show_wifi_banner'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-[#E67E22]"> Menüde göster
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Banner Kaydet</button>
        </form>
        <p class="mt-4 text-xs text-gray-500">Banner menünün üst kısmında (slider altında) canlı güncellenir.</p>
    </div>
    <div class="admin-card lg:col-span-2">
        <h3 class="mb-4 font-semibold text-gray-800">QR Menü — Sosyal & Müzik</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf @method('PUT')
            <div class="sm:col-span-2">
                <label class="form-label">Spotify Playlist URL</label>
                <input type="url" name="spotify_url" value="{{ $settings['spotify_url'] ?? '' }}" class="form-input" placeholder="https://open.spotify.com/playlist/...">
                <p class="mt-1 text-xs text-gray-500">Boş bırakırsanız Spotify kartı menüde görünmez.</p>
            </div>
            <div>
                <label class="form-label">Spotify Kart Başlığı</label>
                <input type="text" name="spotify_title" value="{{ $settings['spotify_title'] ?? 'HSP Vibes' }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Instagram Profil URL</label>
                <input type="url" name="instagram_url" value="{{ $settings['instagram_url'] ?? '' }}" class="form-input" placeholder="https://www.instagram.com/...">
            </div>
            <div>
                <label class="form-label">Menüde Görünen Etiket</label>
                <input type="text" name="instagram_handle" value="{{ $settings['instagram_handle'] ?? '@ramaznkra' }}" class="form-input" placeholder="@ramaznkra">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">Sosyal Ayarları Kaydet</button>
            </div>
        </form>
        <p class="mt-4 text-xs text-gray-500">Kartlar menünün altında, garson çağır çubuğunun üstünde listelenir.</p>
    </div>
</div>
@endsection
