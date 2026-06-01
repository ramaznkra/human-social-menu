<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Concerns\BelongsToRestaurant;
use App\Models\Concerns\HasMenuTranslations;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    /** @use BelongsToRestaurant — App\Models\Scopes\RestaurantScope tenant izolasyonu */
    use BelongsToRestaurant, HasMenuTranslations, HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'restaurant_id', 'category_id', 'type', 'name', 'description',
        'price', 'image', 'badge', 'sort_order', 'is_available', 'in_stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'is_available' => 'boolean',
            'in_stock' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function optionGroups(): HasMany
    {
        return $this->hasMany(ProductOptionGroup::class)->orderBy('sort_order');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function cartOptionsPayload(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return $this->optionGroups
            ->map(fn (ProductOptionGroup $group) => [
                'id' => $group->id,
                'name' => $group->localizedName($locale),
                'type' => $group->type,
                'required' => $group->required,
                'options' => $group->options->map(fn (ProductOption $option) => [
                    'id' => $option->id,
                    'name' => $option->localizedName($locale),
                    'price' => Money::normalize($option->price_modifier),
                    'default' => $option->is_default,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'http')
            ? $this->image
            : asset('storage/'.$this->image);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->orderBy('sort_order');
    }

    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }

    /** Mutfak/bar istasyonu — öncelik kategori tipi, yedek ürün tipi. */
    public function stationType(): string
    {
        $this->loadMissing('category:id,type');

        $fromCategory = $this->category?->type;
        if (in_array($fromCategory, [Category::TYPE_KITCHEN, Category::TYPE_BAR], true)) {
            return $fromCategory;
        }

        return in_array($this->type, [Category::TYPE_KITCHEN, Category::TYPE_BAR], true)
            ? $this->type
            : Category::TYPE_KITCHEN;
    }
}
