<aside class="admin-sidebar flex w-64 shrink-0 flex-col bg-[#262220] text-brand-cream">
    <div class="admin-sidebar__brand border-b border-white/10 px-5 py-5">
        <h1 class="text-lg font-bold uppercase tracking-[0.12em] text-gray-100">{{ $settings['venue_name'] ?? 'Human' }}</h1>
        @if(filled($settings['venue_slogan'] ?? null))
        <p class="mt-1 text-[11px] tracking-wide text-brand-cream/70">{{ $settings['venue_slogan'] }}</p>
        @endif
    </div>

    <nav class="admin-sidebar__nav flex-1 overflow-y-auto px-3 py-4" aria-label="Admin menü">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link sidebar-link--panel {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            Panel
        </a>

        <div class="admin-sidebar__divider" role="separator"></div>

        <div class="admin-sidebar__group">
            <a href="{{ route('admin.live-orders.index') }}" class="sidebar-link sidebar-link--live {{ request()->routeIs('admin.live-orders.*') ? 'active' : '' }}">
                <span class="sidebar-link__main">
                    <span>Canlı Siparişler</span>
                    @include('admin.partials.icons.live-orders')
                </span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.index', 'admin.orders.show') ? 'active' : '' }}">
                Siparişler
            </a>
            <a href="{{ route('admin.orders.archive') }}" class="sidebar-link {{ request()->routeIs('admin.orders.archive') ? 'active' : '' }}">
                Geçmiş Adisyonlar
            </a>
        </div>

        <div class="admin-sidebar__divider" role="separator"></div>

        <div class="admin-sidebar__group">
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                Kategoriler
            </a>
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                Ürünler
            </a>
            <a href="{{ route('admin.tables.index') }}" class="sidebar-link {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">
                Masalar
            </a>
        </div>

        <div class="admin-sidebar__divider" role="separator"></div>

        <div class="admin-sidebar__group">
            <a href="{{ route('admin.cafe-galleries.index') }}" class="sidebar-link {{ request()->routeIs('admin.cafe-galleries.*') ? 'active' : '' }}">
                Social Spotted
            </a>
            <a href="{{ route('admin.slides.index') }}" class="sidebar-link {{ request()->routeIs('admin.slides.*') ? 'active' : '' }}">
                Ekran Slaytları
            </a>
        </div>

        <div class="admin-sidebar__divider" role="separator"></div>

        <div class="admin-sidebar__group admin-sidebar__group--footer">
            <a href="{{ route('menu.index') }}" target="_blank" rel="noopener" class="sidebar-link sidebar-link--external">
                Menüyü Gör
                <span class="sidebar-link__ext" aria-hidden="true">↗</span>
            </a>
            <a href="{{ route('display.index') }}" target="_blank" rel="noopener" class="sidebar-link sidebar-link--external">
                Ekranı Aç
                <span class="sidebar-link__ext" aria-hidden="true">↗</span>
            </a>
            <a href="{{ route('admin.settings.edit') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                Ayarlar
            </a>
            <form action="{{ route('admin.logout') }}" method="POST" class="mt-0.5">
                @csrf
                <button type="submit" class="sidebar-link sidebar-link--logout w-full text-left">
                    Çıkış
                </button>
            </form>
        </div>
    </nav>
</aside>
