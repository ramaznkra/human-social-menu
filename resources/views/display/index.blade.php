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
    <div class="display-slide {{ $i === 0 ? 'is-active' : '' }}"
         data-duration="{{ $slide->duration }}">
        <div class="display-slide__media" style="background-image: url('{{ $slide->image_url }}'), linear-gradient(135deg, #262220, #121110);"></div>
        <div class="display-slide__overlay">
            @if($slide->title)<h2 class="display-slide__title text-4xl font-bold uppercase tracking-wider text-gray-100 md:text-5xl">{{ $slide->title }}</h2>@endif
            @if($slide->subtitle)<p class="display-slide__subtitle mt-2 text-2xl text-[#C6A046] md:text-3xl">{{ $slide->subtitle }}</p>@endif
        </div>
    </div>
    @empty
    <div class="display-slide is-active">
        <div class="display-slide__media" style="background-image: linear-gradient(135deg, #C6A046, #262220);"></div>
        <div class="display-slide__overlay">
            <h2 class="display-slide__title text-5xl font-bold uppercase tracking-wider text-white">{{ $settings['venue_name'] ?? 'Human' }}</h2>
            <p class="display-slide__subtitle mt-3 text-3xl text-[#D4C5B9]">{{ $settings['venue_slogan'] ?? 'Social People' }}</p>
        </div>
    </div>
    @endforelse
</div>

<script>
(function() {
    const slides = document.querySelectorAll('.display-slide');
    if (slides.length <= 1) return;
    const fallback = {{ $settings['display_interval'] ?? 10 }};
    let current = 0;

    function restartKenBurns(slide) {
        const media = slide.querySelector('.display-slide__media');
        if (!media) return;
        media.style.animation = 'none';
        // reflow ile animasyonu sıfırdan başlat
        void media.offsetWidth;
        media.style.animation = '';
    }

    function next() {
        slides[current].classList.remove('is-active');
        current = (current + 1) % slides.length;
        const slide = slides[current];
        slide.classList.add('is-active');
        restartKenBurns(slide);
        const duration = (parseInt(slide.dataset.duration) || fallback) * 1000;
        setTimeout(next, duration);
    }

    const firstDuration = (parseInt(slides[0].dataset.duration) || fallback) * 1000;
    setTimeout(next, firstDuration);
})();
</script>
</body>
</html>
