@props(['product', 'block' => false])
<form
    action="{{ route('admin.products.destroy', $product) }}"
    method="POST"
    class="{{ $block ? 'w-full' : 'inline' }}"
    @include('admin.partials.confirm-form', [
        'title' => 'Ürünü sil',
        'message' => $product->name.' kalıcı olarak silinecek.',
        'type' => 'danger',
        'confirmLabel' => 'Sil',
    ])
>
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger {{ $block ? 'w-full' : '' }}">Sil</button>
</form>
