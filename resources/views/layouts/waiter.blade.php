<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#121110">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ asset('manifest-waiter.json') }}">
    <link rel="icon" href="{{ asset('icons/waiter-app-icon.svg') }}" type="image/svg+xml">
    <title>@yield('title', 'Garson') — {{ $settings['venue_name'] ?? 'Human' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/pages/waiter-dashboard.js'])
</head>
<body class="waiter-body min-h-screen bg-[#121110] font-sans text-gray-100 antialiased">
    @if(session('success'))
        <span data-admin-flash data-admin-flash-type="success" data-admin-flash-title="Tamam" data-admin-flash-message="{{ session('success') }}" hidden></span>
    @endif
    @if(session('error'))
        <span data-admin-flash data-admin-flash-type="error" data-admin-flash-title="Hata" data-admin-flash-message="{{ session('error') }}" hidden></span>
    @endif

    <div class="waiter-shell mx-auto min-h-screen max-w-md">
        @yield('content')
    </div>

    @include('admin.partials.admin-toast-host')
    @stack('scripts')
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('staff-sw.js') }}', { scope: '/waiter/' })
                .catch(function () {});
        });
    }
    </script>
</body>
</html>
