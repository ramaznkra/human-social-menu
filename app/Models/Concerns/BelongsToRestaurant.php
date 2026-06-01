<?php

namespace App\Models\Concerns;

use App\Models\Restaurant;
use App\Support\CurrentRestaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToRestaurant
{
    public static function bootBelongsToRestaurant(): void
    {
        static::addGlobalScope('restaurant', function (Builder $builder) {
            $restaurantId = CurrentRestaurant::id();
            if ($restaurantId === null) {
                return;
            }

            $builder->where(
                $builder->getModel()->qualifyColumn('restaurant_id'),
                $restaurantId,
            );
        });

        static::creating(function (Model $model) {
            if (! empty($model->restaurant_id)) {
                return;
            }

            $restaurantId = CurrentRestaurant::id();
            if ($restaurantId !== null) {
                $model->restaurant_id = $restaurantId;
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
