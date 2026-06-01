<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use App\Models\Concerns\HasMenuTranslations;
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
        'price', 'image', 'badge', 'sort_order', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
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
                    'price' => (float) $option->price_modifier,
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
}
