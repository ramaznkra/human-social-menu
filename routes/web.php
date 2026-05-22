<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DisplaySlideController;
use App\Http\Controllers\Admin\CafeGalleryController;
use App\Http\Controllers\Admin\MenuSlideController;
use App\Http\Controllers\Admin\BarScreenController;
use App\Http\Controllers\Admin\LiveOrdersController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\TableCallController;
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
Route::post('/api/masa/cagri', [TableCallController::class, 'store'])->name('table.call.store');

Route::get('/ekran', [DisplayController::class, 'index'])->name('display.index');
Route::get('/api/ekran', [DisplayController::class, 'api'])->name('display.api');

Route::get('/mutfak', [LiveOrdersController::class, 'screen'])->name('kitchen.index');
Route::get('/api/admin/live-orders', [LiveOrdersController::class, 'liveOrders'])->name('live-orders.api');
Route::patch('/api/admin/live-orders/{order}/status', [LiveOrdersController::class, 'updateStatus'])->name('live-orders.status');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/giris', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris', [AuthController::class, 'login']);
    Route::post('/cikis', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/operasyon', [OperationsController::class, 'live'])->name('operations.live');
        Route::get('live-orders', [LiveOrdersController::class, 'index'])->name('live-orders.index');
        Route::patch('/api/cagri/{call}/onayla', [OperationsController::class, 'acknowledgeCall'])->name('operations.acknowledge');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('tables', TableController::class)->except(['show']);
        Route::post('tables/{table}/regenerate', [TableController::class, 'regenerate'])->name('tables.regenerate');
        Route::get('tables/{table}/qr.png', [TableController::class, 'qrPng'])->name('tables.qr.png');
        Route::get('tables/{table}/qr.svg', [TableController::class, 'qrSvg'])->name('tables.qr.svg');

        Route::get('bar', fn () => redirect()->route('admin.live-orders.index'))->name('bar.index');
        Route::get('api/bar/siparisler', [BarScreenController::class, 'orders'])->name('bar.orders');
        Route::patch('api/bar/siparis/{order}/hazir', [BarScreenController::class, 'markReady'])->name('bar.ready');

        Route::get('orders', [OrderAdminController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.status');

        Route::resource('slides', DisplaySlideController::class)->except(['show']);
        Route::resource('menu-slides', MenuSlideController::class)->except(['show']);
        Route::resource('cafe-galleries', CafeGalleryController::class)->except(['show']);
        Route::resource('events', EventController::class)->except(['show']);

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
