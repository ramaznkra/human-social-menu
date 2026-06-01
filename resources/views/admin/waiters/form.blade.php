@extends('layouts.admin')
@section('title', $waiter->exists ? 'Garson Düzenle' : 'Yeni Garson')
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">{{ $waiter->exists ? 'Garson Düzenle' : 'Yeni Garson Ekle' }}</h2>
    <p class="mt-1 text-sm text-gray-500">Garsonlar yalnızca mobil garson paneline erişir; admin menüsünü göremez.</p>
</div>

<div class="admin-card max-w-xl">
    <form method="POST" action="{{ $waiter->exists ? route('admin.waiters.update', $waiter) : route('admin.waiters.store') }}" class="space-y-4">
        @csrf
        @if($waiter->exists) @method('PUT') @endif

        <div>
            <label class="form-label">Ad Soyad *</label>
            <input type="text" name="name" value="{{ old('name', $waiter->name) }}" required class="form-input" placeholder="Ahmet Yılmaz">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">E-posta (giriş) *</label>
            <input type="email" name="email" value="{{ old('email', $waiter->email) }}" required class="form-input" placeholder="garson@ornek.com" autocomplete="username">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">{{ $waiter->exists ? 'Yeni Şifre' : 'Şifre *' }}</label>
            <input
                type="password"
                name="password"
                class="form-input"
                {{ $waiter->exists ? '' : 'required' }}
                minlength="8"
                autocomplete="{{ $waiter->exists ? 'new-password' : 'new-password' }}"
                placeholder="{{ $waiter->exists ? 'Boş bırakırsanız değişmez' : 'En az 8 karakter' }}"
            >
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        @if($waiter->exists)
        <p class="text-xs text-gray-500">Aktif/pasif durumunu <a href="{{ route('admin.waiters.index') }}" class="font-medium text-[#E67E22] hover:underline">Garsonlar</a> listesindeki anahtardan değiştirebilirsiniz.</p>
        @endif

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">{{ $waiter->exists ? 'Kaydet' : 'Garson Ekle' }}</button>
            <a href="{{ route('admin.waiters.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>
@endsection
