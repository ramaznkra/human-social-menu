<?php

namespace App\Support;

use App\Models\Restaurant;

/**
 * İstek bazlı aktif restoran bağlamı (SaaS tenant context).
 */
class CurrentRestaurant
{
    private static ?Restaurant $restaurant = null;

    public static function set(?Restaurant $restaurant): void
    {
        self::$restaurant = $restaurant;
    }

    public static function get(): ?Restaurant
    {
        return self::$restaurant;
    }

    public static function id(): ?int
    {
        return self::$restaurant?->id;
    }

    public static function has(): bool
    {
        return self::$restaurant !== null;
    }

    public static function clear(): void
    {
        self::$restaurant = null;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function run(Restaurant $restaurant, callable $callback): mixed
    {
        $previous = self::$restaurant;
        self::$restaurant = $restaurant;

        try {
            return $callback();
        } finally {
            self::$restaurant = $previous;
        }
    }
}
