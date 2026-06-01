<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Support\CurrentRestaurant;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('waiter', function (string $value) {
            $query = User::query()
                ->whereKey($value)
                ->where('role', User::ROLE_WAITER);

            if ($restaurantId = CurrentRestaurant::id()) {
                $query->where('restaurant_id', $restaurantId);
            }

            return $query->firstOrFail();
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('settings', Setting::allCached());
        });
    }
}
