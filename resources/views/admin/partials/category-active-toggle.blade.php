@props(['category'])
<label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Kategoriyi aç / kapat">
    <input
        type="checkbox"
        class="peer sr-only"
        data-category-toggle
        data-toggle-url="{{ route('admin.categories.toggle-active', $category) }}"
        {{ $category->is_active ? 'checked' : '' }}
        aria-label="{{ $category->name }} aktif"
    >
    <span class="relative h-6 w-11 shrink-0 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500/30"></span>
</label>
