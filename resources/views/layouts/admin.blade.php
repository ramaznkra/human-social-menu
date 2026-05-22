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
    @vite(['resources/css/app.css', 'resources/js/pages/admin-shell.js', 'resources/js/pages/admin-manual-order.js'])
</head>
<body class="admin-body min-h-screen bg-[#F8F9FA] font-sans text-gray-800 antialiased">
<div class="flex min-h-screen">
    @include('admin.partials.sidebar')

    <main class="admin-main flex-1 overflow-x-auto p-6 md:p-8 lg:p-10">
        <header class="admin-page-header mb-6 hidden lg:block">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-[#E67E22]">@yield('section_label', 'Yönetim')</p>
            <h2 class="mt-1 text-xl font-semibold text-gray-800">@yield('page_heading', 'Yönetim Paneli')</h2>
        </header>

        @if(session('success'))
            <span
                data-admin-flash
                data-admin-flash-type="success"
                data-admin-flash-title="Tamam"
                data-admin-flash-message="{{ session('success') }}"
                hidden
            ></span>
        @endif
        @if(session('error'))
            <span
                data-admin-flash
                data-admin-flash-type="error"
                data-admin-flash-title="Hata"
                data-admin-flash-message="{{ session('error') }}"
                hidden
            ></span>
        @endif

        @yield('content')
    </main>
</div>
@include('admin.partials.manual-order-modal')
@include('admin.partials.admin-toast-host')
@stack('scripts')
</body>
</html>
