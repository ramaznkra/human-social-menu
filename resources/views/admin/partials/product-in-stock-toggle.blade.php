@props(['product', 'compact' => false])
<div class="flex items-center gap-2 {{ $compact ? 'justify-center' : '' }}">
    <label class="relative inline-flex cursor-pointer items-center">
        <input
            type="checkbox"
            class="peer sr-only"
            data-product-stock-toggle
            data-toggle-url="{{ route('admin.products.toggle-in-stock', $product) }}"
            {{ $product->in_stock ? 'checked' : '' }}
            aria-label="{{ $product->name }} stok durumu"
        >
        <span class="relative h-6 w-11 shrink-0 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500/30"></span>
    </label>
    @unless($compact)
    <span
        data-stock-label
        class="text-xs font-medium {{ $product->in_stock ? 'text-emerald-600' : 'text-red-500' }}"
    >{{ $product->in_stock ? 'Stokta' : 'Tükendi' }}</span>
    @endunless
</div>
