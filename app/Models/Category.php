<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use App\Models\Concerns\HasMenuTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    public const TYPE_KITCHEN = 'kitchen';

    public const TYPE_BAR = 'bar';

    /** @use BelongsToRestaurant — App\Models\Scopes\RestaurantScope tenant izolasyonu */
    use BelongsToRestaurant, HasMenuTranslations, HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'restaurant_id', 'name', 'description', 'slug', 'type', 'icon', 'image', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_BAR => 'Bar / İçecek',
            default => 'Mutfak / Yemek',
        };
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = $this->image;

        if (filled($image)) {
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }

            if (str_starts_with($image, 'images/')) {
                if (file_exists(public_path($image))) {
                    return asset($image);
                }
            } elseif (Storage::disk('public')->exists($image)) {
                return asset('storage/'.$image);
            }
        }

        $fallbacks = [
            'yiyecek' => 'images/categories/samples/yiyecek.svg',
            'icecek' => 'images/categories/samples/icecek.svg',
            'nargile' => 'images/categories/samples/nargile.svg',
            'okey' => 'images/categories/samples/okey.svg',
        ];

        $fallback = $fallbacks[$this->slug ?? ''] ?? null;

        return $fallback ? asset($fallback) : null;
    }
}
