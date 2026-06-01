<?php

namespace App\Models\Scopes;

use App\Support\CurrentRestaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * SaaS tenant izolasyonu: aktif restoran bağlamındaki kayıtları otomatik filtreler.
 * Bağlam yoksa fail-safe olarak hiçbir satır döndürülmez (veri sızıntısı engeli).
 */
class RestaurantScope implements Scope
{
    public const NAME = 'restaurant';

    public function apply(Builder $builder, Model $model): void
    {
        $restaurantId = CurrentRestaurant::resolveId();

        if ($restaurantId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where(
            $model->qualifyColumn('restaurant_id'),
            $restaurantId,
        );
    }
}
