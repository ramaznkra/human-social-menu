<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#121110">
    <title>@yield('title', ($settings['venue_name'] ?? 'Human') . ' — QR Menü')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-dvh bg-[#121110] font-sans text-gray-100 antialiased">
    <div class="menu-ambient pointer-events-none fixed inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 -right-16 h-72 w-72 rounded-full bg-[#E67E22]/10 blur-[120px]"></div>
        <div class="absolute top-1/3 -left-20 h-80 w-80 rounded-full bg-[#E67E22]/6 blur-[120px]"></div>
        <div class="absolute -bottom-32 right-1/4 h-96 w-96 rounded-full bg-[#3d2f28]/40 blur-[120px]"></div>
        <div class="absolute bottom-1/4 left-1/3 h-64 w-64 rounded-full bg-[#262220]/50 blur-[120px]"></div>
    </div>

    <div class="menu-device relative z-[1]">
        @yield('content')
    </div>

    @stack('menu-overlays')
    @stack('scripts')
</body>
</html>
