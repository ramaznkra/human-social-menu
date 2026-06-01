<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class TenantRules
{
    /**
     * Eloquent global scope üzerinden doğrulama (restaurant_id manuel filtresi gerekmez).
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function existsModel(string $modelClass, string $column = 'id'): Exists
    {
        /** @var Model $model */
        $model = new $modelClass;
        $table = $model->getTable();

        $restaurantId = CurrentRestaurant::resolveId();

        return Rule::exists($table, $column)->where(
            fn ($query) => $restaurantId !== null
                ? $query->where("{$table}.restaurant_id", $restaurantId)
                : $query->whereRaw('1 = 0'),
        );
    }

    /**
     * @deprecated existsModel() kullanın — scope ile uyumlu aynı mantık.
     */
    public static function existsInCurrentRestaurant(string $table, string $column = 'id'): Exists
    {
        $restaurantId = CurrentRestaurant::resolveId();

        return Rule::exists($table, $column)->where(
            fn ($query) => $restaurantId !== null
                ? $query->where("{$table}.restaurant_id", $restaurantId)
                : $query->whereRaw('1 = 0'),
        );
    }
}
