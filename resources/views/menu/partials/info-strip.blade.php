@php
    $showMotto = ($settings['show_motto_banner'] ?? '1') === '1' && filled($settings['daily_motto'] ?? '');
    $showWifi = ($settings['show_wifi_banner'] ?? '1') === '1' && filled($settings['wifi_password'] ?? '');
@endphp

@if($showMotto || $showWifi)
<section class="menu-info-strip px-5 pb-4" aria-label="Mekan bilgileri">
    <div class="menu-info-strip__grid {{ ($showMotto && $showWifi) ? 'menu-info-strip__grid--dual' : '' }}">
        @if($showMotto)
        <article class="menu-info-card menu-info-card--motto">
            <div class="menu-info-card__icon" aria-hidden="true">✦</div>
            <div class="min-w-0 flex-1">
                <p class="menu-info-card__label">Günün Sosyal Mottosu</p>
                <p class="menu-info-card__motto">{{ $settings['daily_motto'] }}</p>
            </div>
        </article>
        @endif

        @if($showWifi)
        <article class="menu-info-card menu-info-card--wifi">
            <div class="menu-info-card__icon menu-info-card__icon--wifi" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856a9 9 0 0 1 13.788 0M2.294 8.707a13.5 13.5 0 0 1 19.412 0M12 18.75h.008v.008H12v-.008Z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="menu-info-card__label">Wi-Fi Şifresi</p>
                <p class="menu-info-card__wifi-value" id="menuWifiPassword">{{ $settings['wifi_password'] }}</p>
            </div>
            <button
                type="button"
                class="menu-info-copy-btn"
                data-copy-target="menuWifiPassword"
                aria-label="Wi-Fi şifresini kopyala"
            >Kopyala</button>
        </article>
        @endif
    </div>
</section>
@endif

@once
@push('scripts')
<script>
document.querySelectorAll('[data-copy-target]').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const el = document.getElementById(btn.dataset.copyTarget);
        const text = el?.textContent?.trim();
        if (!text) return;

        try {
            await navigator.clipboard.writeText(text);
            const prev = btn.textContent;
            btn.textContent = 'Kopyalandı ✓';
            btn.classList.add('menu-info-copy-btn--done');
            setTimeout(() => {
                btn.textContent = prev;
                btn.classList.remove('menu-info-copy-btn--done');
            }, 2000);
        } catch {
            const range = document.createRange();
            range.selectNodeContents(el);
            window.getSelection()?.removeAllRanges();
            window.getSelection()?.addRange(range);
            document.execCommand('copy');
            window.getSelection()?.removeAllRanges();
            btn.textContent = 'Kopyalandı ✓';
            setTimeout(() => { btn.textContent = 'Kopyala'; }, 2000);
        }
    });
});
</script>
@endpush
@endonce
