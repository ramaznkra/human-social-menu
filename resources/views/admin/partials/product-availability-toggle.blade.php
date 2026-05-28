@props(['product', 'compact' => false])
<div class="flex items-center gap-2 {{ $compact ? 'justify-center' : '' }}">
    <label class="relative inline-flex cursor-pointer items-center">
        <input
            type="checkbox"
            class="peer sr-only"
            data-product-toggle
            data-toggle-url="{{ route('admin.products.toggle-availability', $product) }}"
            {{ $product->is_available ? 'checked' : '' }}
            aria-label="{{ $product->name }} menüde göster"
        >
        <span class="relative h-6 w-11 shrink-0 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#E67E22] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#E67E22]/30"></span>
    </label>
    @unless($compact)
    <span
        data-availability-label
        class="text-xs font-medium {{ $product->is_available ? 'text-emerald-600' : 'text-gray-400' }}"
    >{{ $product->is_available ? 'Menüde' : 'Gizli' }}</span>
    @endunless
</div>
