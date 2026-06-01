<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use App\Support\CurrentRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $restaurantId = CurrentRestaurant::resolveId() ?? 0;
        $cacheKey = "setting.{$restaurantId}.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::query()->where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        $restaurantId = CurrentRestaurant::resolveId();
        if ($restaurantId === null) {
            throw new \RuntimeException('Setting yazmak için restoran bağlamı gerekli.');
        }

        static::updateOrCreate(
            ['restaurant_id' => $restaurantId, 'key' => $key],
            ['value' => $value],
        );
        static::clearCache();
    }

    public static function allCached(): array
    {
        $restaurantId = CurrentRestaurant::resolveId() ?? 0;

        return Cache::remember("settings.all.{$restaurantId}", 3600, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function clearCache(): void
    {
        $restaurantId = CurrentRestaurant::resolveId() ?? 0;
        Cache::forget("settings.all.{$restaurantId}");
        foreach (static::query()->pluck('key') as $key) {
            Cache::forget("setting.{$restaurantId}.{$key}");
        }
    }
}
