<div class="{{ ($fullscreen ?? false) ? 'min-h-dvh' : 'live-ops-screen -mx-6 -mt-2 min-h-[calc(100vh-8rem)] rounded-t-2xl bg-[#121110] text-gray-100 md:-mx-8' }}">
    <div
        id="liveOrdersApp"
        class="flex {{ ($fullscreen ?? false) ? 'min-h-dvh' : 'min-h-[calc(100vh-8rem)]' }} flex-col"
        data-api-url="{{ route('live-orders.api') }}"
        data-status-url="{{ url('/api/admin/live-orders') }}"
        data-resolve-call-url="{{ url('/api/admin/call') }}"
        data-csrf="{{ csrf_token() }}"
        data-page-title="HSP Canlı Siparişler"
    >
        <header class="sticky top-0 z-40 border-b border-white/5 bg-[#121110]/95 px-4 py-4 backdrop-blur-md md:px-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold tracking-wide text-gray-100">Canlı Siparişler</h2>
                    <p id="liveOrdersStatus" class="text-xs text-[#D4C5B9]">Bağlanıyor…</p>
                </div>
                <div class="flex items-center gap-3">
                    <span id="liveOrdersClock" class="text-xl font-semibold tabular-nums text-[#D4C5B9]"></span>
                    @if($fullscreen ?? false)
                    <a href="{{ route('admin.login') }}" class="rounded-full border border-white/10 px-3 py-1 text-xs text-[#D4C5B9] hover:text-white">Admin</a>
                    @endif
                </div>
            </div>

            <nav class="mt-4 flex gap-2 overflow-x-auto pb-1" id="liveOrdersTabs" role="tablist">
                <button type="button" class="live-ops-tab is-active" data-tab="all" role="tab">Tümü</button>
                <button type="button" class="live-ops-tab relative" data-tab="kitchen" role="tab">
                    Mutfak / Yemek
                    <span class="live-ops-badge hidden" data-badge="kitchen">0</span>
                </button>
                <button type="button" class="live-ops-tab relative" data-tab="bar" role="tab">
                    Bar / İçecek
                    <span class="live-ops-badge hidden" data-badge="bar">0</span>
                </button>
                <button type="button" class="live-ops-tab relative" data-tab="calls" role="tab">
                    🛎️ Çağrılar
                    <span class="live-ops-badge hidden" data-badge="calls">0</span>
                </button>
            </nav>
        </header>

        @isset($tables)
            @include('admin.live-orders._table-map', ['tables' => $tables, 'busyTableIds' => $busyTableIds])
        @endisset

        <main id="liveOrdersGrid" class="flex-1 space-y-3 overflow-y-auto p-4 md:p-6">
            <p class="py-16 text-center text-[#D4C5B9]">Siparişler yükleniyor…</p>
        </main>
    </div>
</div>
