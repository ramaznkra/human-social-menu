<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ $settings['venue_name'] ?? 'Human' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F8F9FA] font-sans text-gray-800 antialiased">
<div class="flex min-h-screen">
    <aside class="flex w-64 shrink-0 flex-col bg-[#262220] text-brand-cream">
        <div class="border-b border-white/10 px-6 py-6">
            <h1 class="text-xl font-bold uppercase tracking-[0.15em] text-gray-100">{{ $settings['venue_name'] ?? 'Human' }}</h1>
            <span class="mt-1 block text-xs tracking-[0.2em] text-brand-cream/80">{{ $settings['venue_slogan'] ?? 'Social People' }}</span>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Panel</a>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">📁 Kategoriler</a>
            <a href="{{ route('admin.menu-slides.index') }}" class="sidebar-link {{ request()->routeIs('admin.menu-slides.*') ? 'active' : '' }}">🖼️ Menü Slaytları</a>
            <a href="{{ route('admin.cafe-galleries.index') }}" class="sidebar-link {{ request()->routeIs('admin.cafe-galleries.*') ? 'active' : '' }}">✨ Social Spotted</a>
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">🍽️ Ürünler</a>
            <a href="{{ route('admin.tables.index') }}" class="sidebar-link {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">🪑 Masalar & QR</a>
            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">📋 Siparişler</a>
            <a href="{{ route('admin.live-orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.live-orders.*') ? 'active' : '' }}">⚡ Canlı Siparişler</a>
            <a href="{{ route('kitchen.index') }}" class="sidebar-link" target="_blank">🍳 Mutfak Ekranı</a>
            <a href="{{ route('admin.bar.index') }}" class="sidebar-link {{ request()->routeIs('admin.bar.*') ? 'active' : '' }}">☕ Bar (yönlendirme)</a>
            <a href="{{ route('admin.slides.index') }}" class="sidebar-link {{ request()->routeIs('admin.slides.*') ? 'active' : '' }}">🖥️ Ekran Slaytları</a>
            <a href="{{ route('admin.events.index') }}" class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">🎉 Etkinlikler</a>
            <a href="{{ route('admin.settings.edit') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">⚙️ Ayarlar</a>
            <hr class="my-3 border-white/10">
            <a href="{{ route('menu.index') }}" target="_blank" class="sidebar-link">📱 Menüyü Gör</a>
            <a href="{{ route('display.index') }}" target="_blank" class="sidebar-link">📺 Ekranı Aç</a>
            <a href="{{ route('kitchen.index') }}" target="_blank" class="sidebar-link">👨‍🍳 Mutfak</a>
        </nav>
        <div class="border-t border-white/10 p-4">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary w-full border-white/10 bg-white/5 text-brand-cream hover:bg-white/10 hover:text-gray-100">Çıkış</button>
            </form>
        </div>
    </aside>
    <main class="flex-1 overflow-x-auto bg-[#F8F9FA] p-6 md:p-8 lg:p-10">
        <div class="mb-6 hidden border-b border-gray-200/80 pb-4 lg:block">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-[#E67E22]">Operasyon</p>
            <h2 class="mt-1 text-lg font-semibold text-gray-800">@yield('page_heading', 'Yönetim Paneli')</h2>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
