{{-- Masa durumu özeti — canlı sipariş ekranında (otomatik güncellenir) --}}
<section class="border-b border-white/5 bg-[#1a1816]/80 px-4 py-3 md:px-6" aria-label="Masa durumu">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs font-semibold uppercase tracking-wider text-[#D4C5B9]">Masa Haritası</p>
        <div class="flex flex-wrap gap-2 text-[10px]">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-emerald-300">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Boş
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-[#E67E22]/40 bg-[#E67E22]/15 px-2 py-0.5 text-[#E67E22]">
                <span class="h-2 w-2 animate-pulse rounded-full bg-[#E67E22]"></span> Aktif
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[#D4C5B9]/60">
                <span class="h-2 w-2 rounded-full bg-white/25"></span> Pasif
            </span>
        </div>
    </div>
    <div id="liveTableMapGrid" class="grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 xl:grid-cols-12">
        @foreach($tables as $t)
        @php
            $isBusy = $busyTableIds->contains($t->id);
            $isInactive = ! $t->is_active;
        @endphp
        <div
            class="live-table-chip flex flex-col items-center justify-center rounded-xl border px-1 py-2 text-center transition
                {{ $isInactive ? 'border-white/10 bg-white/5 text-[#D4C5B9]/40' : ($isBusy ? 'live-table-chip--busy border-[#E67E22]/60 bg-[#E67E22]/20 text-[#E67E22]' : 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300') }}"
            data-table-id="{{ $t->id }}"
            data-table-busy="{{ $isBusy ? '1' : '0' }}"
            data-table-active="{{ $t->is_active ? '1' : '0' }}"
        >
            <span class="text-[10px] font-medium uppercase tracking-wide opacity-70">Masa</span>
            <span class="text-lg font-bold leading-none">{{ $t->number }}</span>
        </div>
        @endforeach
    </div>
</section>
