<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['venue_name'] ?? 'Human' }} — Ekran</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-hidden bg-black font-sans antialiased">
<div class="fixed top-8 right-10 z-10 text-2xl font-bold uppercase tracking-[0.2em] text-white drop-shadow-lg">{{ $settings['venue_name'] ?? 'Human' }}</div>
<div class="relative h-screen w-screen" id="slider">
    @forelse($slides as $i => $slide)
    <div class="display-slide absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-1000 {{ $i === 0 ? 'active !opacity-100 z-[1]' : '' }}"
         data-duration="{{ $slide->duration }}"
         style="background-image: url('{{ $slide->image_url }}'), linear-gradient(135deg, #262220, #121110);">
        <div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/80 via-transparent to-transparent p-16">
            @if($slide->title)<h2 class="text-4xl font-bold uppercase tracking-wider text-gray-100 md:text-5xl">{{ $slide->title }}</h2>@endif
            @if($slide->subtitle)<p class="mt-2 text-2xl text-[#E67E22] md:text-3xl">{{ $slide->subtitle }}</p>@endif
        </div>
    </div>
    @empty
    <div class="display-slide active absolute inset-0 z-[1] bg-gradient-to-br from-[#E67E22] to-[#262220] !opacity-100">
        <div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/60 to-transparent p-16">
            <h2 class="text-5xl font-bold uppercase tracking-wider text-white">{{ $settings['venue_name'] ?? 'Human' }}</h2>
            <p class="mt-3 text-3xl text-[#D4C5B9]">{{ $settings['venue_slogan'] ?? 'Social People' }}</p>
        </div>
    </div>
    @endforelse
</div>

<script>
(function() {
    const slides = document.querySelectorAll('.display-slide');
    if (slides.length <= 1) return;
    let current = 0;
    function next() {
        slides[current].classList.remove('active', '!opacity-100', 'z-[1]');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active', '!opacity-100', 'z-[1]');
        const duration = (parseInt(slides[current].dataset.duration) || {{ $settings['display_interval'] ?? 10 }}) * 1000;
        setTimeout(next, duration);
    }
    const firstDuration = (parseInt(slides[0].dataset.duration) || {{ $settings['display_interval'] ?? 10 }}) * 1000;
    setTimeout(next, firstDuration);
})();
</script>
</body>
</html>
