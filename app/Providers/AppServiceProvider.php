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
use Illuminate\Support\Facades\Vite;
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
        $this->configureViteForLanClients();
        $this->registerTenantRouteBindings();

        Route::bind('waiter', function (string $value) {
            return User::query()
                ->whereKey($value)
                ->where('role', User::ROLE_WAITER)
                ->firstOrFail();
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('settings', Setting::allCached());
        });
    }

    private function configureViteForLanClients(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $host = request()->getHost();
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);

        if (! $isLocalHost) {
            // LAN / telefon: public/hot → 127.0.0.1:5173 CSS'i kırar; build kullan.
            Vite::useHotFile(storage_path('framework/vite-hot-disabled'));
        }
    }

    /**
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
            User::class,
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
