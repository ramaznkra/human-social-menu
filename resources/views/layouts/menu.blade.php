<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#121110">
    <title>@yield('title', ($settings['venue_name'] ?? 'Human') . ' — QR Menü')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-dvh bg-[#121110] font-sans text-gray-100 antialiased lg:bg-[#0a0909]">
    <div class="menu-page menu-page--framed">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
