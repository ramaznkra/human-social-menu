@php
    /** @var \App\Models\Product $product */
    $optionGroups = old('option_groups');

    if ($optionGroups === null && $product->exists) {
        $product->loadMissing(['optionGroups.options']);
        $optionGroups = $product->optionGroups->map(fn ($group) => [
            'id' => $group->id,
            'name' => $group->getTranslations('name'),
            'type' => $group->type,
            'required' => $group->required,
            'sort_order' => $group->sort_order,
            'options' => $group->options->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->getTranslations('name'),
                'price_modifier' => $option->price_modifier,
                'is_default' => $option->is_default,
                'sort_order' => $option->sort_order,
            ])->values()->all(),
        ])->values()->all();
    }

    $optionGroups = is_array($optionGroups) ? array_values($optionGroups) : [];
@endphp

<section class="product-options-editor" data-product-options>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-gray-800">Varyasyonlar</h3>
            <p class="mt-1 text-xs text-gray-500">Boy, sos, ekstra malzeme gibi seçenekleri buradan tanımlayın. Müşteri menüsünde ürün modalında görünür.</p>
        </div>
        <button type="button" class="btn btn-secondary btn-sm shrink-0" data-add-option-group>+ Grup Ekle</button>
    </div>

    <div class="mt-4 space-y-4" data-option-groups-list>
        @foreach($optionGroups as $gi => $group)
            @include('admin.partials.product-option-group-row', ['gi' => $gi, 'group' => $group])
        @endforeach
    </div>

    <p class="mt-3 text-xs text-gray-400" data-option-groups-empty {{ count($optionGroups) ? 'hidden' : '' }}>
        Henüz varyasyon yok. Örn. “Boy” grubu altında Normal / Büyük seçenekleri ekleyebilirsiniz.
    </p>

    <template data-option-group-template>
        @include('admin.partials.product-option-group-row', [
            'gi' => '__GI__',
            'group' => ['type' => 'single', 'required' => false, 'options' => []],
        ])
    </template>

    <template data-option-row-template>
        @include('admin.partials.product-option-row', [
            'gi' => '__GI__',
            'oi' => '__OI__',
            'option' => ['price_modifier' => 0, 'is_default' => false],
            'groupType' => 'single',
        ])
    </template>
</section>
