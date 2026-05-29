<button
    type="button"
    id="manualOrderFab"
    class="manual-order-fab"
    aria-label="Yeni sipariş ekle"
    title="Yeni Sipariş Ekle"
>
    ➕ Yeni Sipariş Ekle
</button>

<div id="manualOrderModal" class="manual-order-modal" aria-hidden="true" inert>
    <div class="manual-order-modal__backdrop" data-manual-order-close></div>
    <div class="manual-order-modal__panel" role="dialog" aria-labelledby="manualOrderTitle">
        <header class="manual-order-modal__header">
            <div>
                <h2 id="manualOrderTitle" class="text-lg font-bold text-gray-900">Hızlı Sipariş Al</h2>
                <p class="text-xs text-gray-500">Masa seç → ürün ekle → onayla</p>
            </div>
            <button type="button" class="manual-order-modal__close" data-manual-order-close aria-label="Kapat">✕</button>
        </header>

        <div class="manual-order-modal__body">
            <section>
                <label class="manual-order-label">Masa</label>
                <div id="manualOrderTables" class="manual-order-tables">
                    <p class="text-sm text-gray-400">Masalar yükleniyor…</p>
                </div>
            </section>

            <section class="mt-4">
                <label class="manual-order-label" for="manualOrderSearch">Ürün ara</label>
                <input
                    type="search"
                    id="manualOrderSearch"
                    class="manual-order-input"
                    placeholder="Örn: kahve, nachos…"
                    autocomplete="off"
                >
                <ul id="manualOrderProductResults" class="manual-order-products"></ul>
            </section>

            <section class="mt-4">
                <div class="flex items-center justify-between">
                    <label class="manual-order-label mb-0">Sepet</label>
                    <span id="manualOrderCartTotal" class="text-sm font-bold text-[#E67E22]">0 ₺</span>
                </div>
                <ul id="manualOrderCart" class="manual-order-cart">
                    <li class="manual-order-cart-empty text-sm text-gray-400">Henüz ürün yok</li>
                </ul>
            </section>

            <section class="mt-3">
                <label class="manual-order-label" for="manualOrderNotes">Not (isteğe bağlı)</label>
                <input type="text" id="manualOrderNotes" class="manual-order-input" maxlength="500" placeholder="Örn: az şekerli">
            </section>
        </div>

        <footer class="manual-order-modal__footer">
            <p id="manualOrderError" class="manual-order-error hidden"></p>
            <button type="button" id="manualOrderSubmit" class="manual-order-submit" disabled>
                Siparişi Onayla
            </button>
        </footer>

        <div id="manualOrderSuccess" class="manual-order-success hidden" aria-hidden="true">
            <div class="manual-order-success__ring">
                <span class="manual-order-success__icon">👨‍🍳</span>
            </div>
            <p class="manual-order-success__title">Hazırlanıyor</p>
            <p id="manualOrderSuccessMsg" class="manual-order-success__message">Sipariş mutfağa iletildi</p>
            <p class="manual-order-success__hint">Canlı sipariş ekranında görünecek</p>
        </div>
    </div>
</div>

@once
<script>
    window.HSP_MANUAL_ORDER = {
        bootstrapUrl: @json(route('admin.manual-order.bootstrap')),
        productsUrl: @json(route('admin.manual-order.products')),
        storeUrl: @json(route('admin.manual-order.store')),
    };
</script>
@endonce
