<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DisplaySlideController;
use App\Http\Controllers\Admin\MenuSlideController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('menu.index'));

Route::get('/menu/{token?}', [MenuController::class, 'index'])->name('menu.index');
Route::post('/siparis', [OrderController::class, 'store'])->name('order.store');
Route::get('/siparis/{order}/durum', [OrderController::class, 'status'])->name('order.status');
Route::get('/api/siparis/{order}/durum', [OrderController::class, 'statusApi'])->name('order.status.api');

Route::get('/ekran', [DisplayController::class, 'index'])->name('display.index');
Route::get('/api/ekran', [DisplayController::class, 'api'])->name('display.api');

Route::get('/mutfak', [KitchenController::class, 'index'])->name('kitchen.index');
Route::get('/api/mutfak', [KitchenController::class, 'api'])->name('kitchen.api');
Route::patch('/api/mutfak/{order}/durum', [KitchenController::class, 'updateStatus'])->name('kitchen.status');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/giris', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris', [AuthController::class, 'login']);
    Route::post('/cikis', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('tables', TableController::class)->except(['show']);
        Route::post('tables/{table}/regenerate', [TableController::class, 'regenerate'])->name('tables.regenerate');

        Route::get('orders', [OrderAdminController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.status');

        Route::resource('slides', DisplaySlideController::class)->except(['show']);
        Route::resource('menu-slides', MenuSlideController::class)->except(['show']);
        Route::resource('events', EventController::class)->except(['show']);

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
