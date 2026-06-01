<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Canlı Siparişler — Human</title>
    @vite(['resources/css/app.css', 'resources/js/pages/live-orders.js'])
</head>
<body class="min-h-dvh bg-[#121110] font-sans text-gray-100 antialiased">
    @include('admin.live-orders._app', [
        'fullscreen' => true,
        'tables' => $tables,
        'busyTableIds' => $busyTableIds,
        'defaultStation' => $defaultStation ?? 'all',
    ])
</body>
</html>
