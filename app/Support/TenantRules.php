<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class TenantRules
{
    public static function existsInCurrentRestaurant(string $table, string $column = 'id'): \Illuminate\Validation\Rules\Exists
    {
        $restaurantId = CurrentRestaurant::id();

        return Rule::exists($table, $column)->where(
            fn ($query) => $query->where('restaurant_id', $restaurantId),
        );
    }
}
