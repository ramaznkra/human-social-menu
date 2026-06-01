<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Table;
use App\Models\TableCall;
use App\Models\User;
use App\Support\CurrentRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $this->registerTenantRouteBindings();

        Route::bind('waiter', function (string $value) {
            $query = User::query()
                ->whereKey($value)
                ->where('role', User::ROLE_WAITER);

            if ($restaurantId = CurrentRestaurant::resolveId()) {
                $query->where('restaurant_id', $restaurantId);
            }

            return $query->firstOrFail();
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('settings', Setting::allCached());
        });
    }

    /**
     * Tenant modelleri: global scope ile başka restoranın ID'si 404 üretir.
     *
     * @return list<class-string<Model>>
     */
    private function tenantModels(): array
    {
        return [
            Product::class,
            Category::class,
            Order::class,
            Table::class,
            TableCall::class,
        ];
    }

    private function registerTenantRouteBindings(): void
    {
        foreach ($this->tenantModels() as $modelClass) {
            $parameter = Str::camel(class_basename($modelClass));

            Route::bind($parameter, function (string $value) use ($modelClass) {
                /** @var Model $modelClass */
                return $modelClass::query()->whereKey($value)->firstOrFail();
            });
        }
    }
}
