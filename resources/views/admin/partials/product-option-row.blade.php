@php
    /** @var int $gi */
    /** @var int $oi */
    /** @var array<string, mixed> $option */
    /** @var string $groupType */
    $optionNames = $option['name'] ?? ['tr' => '', 'en' => '', 'ru' => ''];
    $isDefault = filter_var($option['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);
@endphp
<div class="product-option-row" data-option-row>
    @if(!empty($option['id']))
        <input type="hidden" name="option_groups[{{ $gi }}][options][{{ $oi }}][id]" value="{{ $option['id'] }}">
    @endif
    <input type="hidden" name="option_groups[{{ $gi }}][options][{{ $oi }}][sort_order]" value="{{ $option['sort_order'] ?? $oi }}" data-option-sort-order>

    <div class="product-option-row__grid">
        <div>
            <label class="form-label text-xs">Ad (TR) *</label>
            <input type="text" name="option_groups[{{ $gi }}][options][{{ $oi }}][name][tr]" value="{{ $optionNames['tr'] ?? '' }}" class="form-input" required autocomplete="off">
        </div>
        <div>
            <label class="form-label text-xs">Ad (EN)</label>
            <input type="text" name="option_groups[{{ $gi }}][options][{{ $oi }}][name][en]" value="{{ $optionNames['en'] ?? '' }}" class="form-input" autocomplete="off">
        </div>
        <div>
            <label class="form-label text-xs">Ad (RU)</label>
            <input type="text" name="option_groups[{{ $gi }}][options][{{ $oi }}][name][ru]" value="{{ $optionNames['ru'] ?? '' }}" class="form-input" autocomplete="off">
        </div>
        <div>
            <label class="form-label text-xs">Fiyat (+₺)</label>
            <input type="number" step="0.01" min="0" name="option_groups[{{ $gi }}][options][{{ $oi }}][price_modifier]" value="{{ $option['price_modifier'] ?? 0 }}" class="form-input">
        </div>
        <div class="product-option-row__default">
            <label class="form-label text-xs">Varsayılan</label>
            <label class="mt-2 flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="option_groups[{{ $gi }}][options][{{ $oi }}][is_default]" value="0">
                <input
                    type="checkbox"
                    name="option_groups[{{ $gi }}][options][{{ $oi }}][is_default]"
                    value="1"
                    data-option-default
                    {{ $isDefault ? 'checked' : '' }}
                >
                <span data-default-hint>{{ $groupType === 'single' ? 'Seçili gelsin' : 'Önceden işaretli' }}</span>
            </label>
        </div>
        <div class="product-option-row__actions">
            <button type="button" class="product-option-row__remove" data-remove-option aria-label="Seçeneği sil">×</button>
        </div>
    </div>
</div>
