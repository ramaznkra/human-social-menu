{{-- Masa durumu özeti — canlı sipariş ekranında (otomatik güncellenir) --}}
<section class="border-b border-white/5 bg-[#1a1816]/80 px-4 py-3 md:px-6" aria-label="Masa durumu">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs font-semibold uppercase tracking-wider text-[#D4C5B9]">Masa Haritası</p>
        <div class="flex flex-wrap gap-2 text-[10px]">
            <span class="live-map-legend live-map-legend--on">
                <span class="table-status-dot table-status-dot--on" aria-hidden="true"></span>
                Boş
            </span>
            <span class="live-map-legend live-map-legend--busy">
                <span class="table-status-dot table-status-dot--busy" aria-hidden="true"></span>
                Sipariş / çağrı
            </span>
            <span class="live-map-legend live-map-legend--off">
                <span class="table-status-dot table-status-dot--off" aria-hidden="true"></span>
                Kapalı
            </span>
        </div>
    </div>
    <div id="liveTableMapGrid" class="grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 xl:grid-cols-12">
        @foreach($tables as $t)
        @php
            $isBusy = $busyTableIds->contains($t->id);
            $isInactive = ! $t->is_active;
            $chipClass = $isInactive
                ? 'live-table-chip--off'
                : ($isBusy ? 'live-table-chip--busy' : 'live-table-chip--on');
        @endphp
        <div
            class="live-table-chip flex flex-col items-center justify-center rounded-xl border px-1 py-2 text-center transition {{ $chipClass }}"
            data-table-id="{{ $t->id }}"
            data-table-busy="{{ $isBusy ? '1' : '0' }}"
            data-table-active="{{ $t->is_active ? '1' : '0' }}"
            title="{{ $isInactive ? 'Masa kapalı' : ($isBusy ? 'Sipariş veya çağrı var' : 'Masa boş') }}"
        >
            <span class="text-[10px] font-medium uppercase tracking-wide opacity-70">Masa</span>
            <span class="text-lg font-bold leading-none">{{ $t->number }}</span>
        </div>
        @endforeach
    </div>
</section>
