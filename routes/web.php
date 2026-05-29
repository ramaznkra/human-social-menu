<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DisplaySlideController;
use App\Http\Controllers\Admin\CafeGalleryController;
use App\Http\Controllers\Admin\BarScreenController;
use App\Http\Controllers\Admin\LiveOrdersController;
use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\TableCallController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\OrderArchiveController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Waiter\WaiterDashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\WaiterOnlyMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('menu.index'));

Route::get('/menu/{token?}', [MenuController::class, 'index'])->name('menu.index');
Route::post('/siparis', [OrderController::class, 'store'])->name('order.store');
Route::get('/siparis/{order}/durum', [OrderController::class, 'status'])->name('order.status');
Route::get('/api/siparis/{order}/durum', [OrderController::class, 'statusApi'])->name('order.status.api');
Route::post('/api/table/call', [TableCallController::class, 'store'])->name('table.call.api');
Route::get('/api/table/call/status', [TableCallController::class, 'status'])->name('table.call.status');
Route::post('/api/masa/cagri', [TableCallController::class, 'store'])->name('table.call.store');

Route::get('/ekran', [DisplayController::class, 'index'])->name('display.index');
Route::get('/api/ekran', [DisplayController::class, 'api'])->name('display.api');

Route::get('/mutfak', [LiveOrdersController::class, 'screen'])->name('kitchen.index');
Route::get('/api/admin/live-orders', [LiveOrdersController::class, 'liveOrders'])->name('live-orders.api');
Route::patch('/api/admin/live-orders/{order}/status', [LiveOrdersController::class, 'updateStatus'])->name('live-orders.status');
Route::patch('/api/admin/call/{call}/resolve', [LiveOrdersController::class, 'resolveCall'])->name('admin.call.resolve');
Route::patch('/api/admin/call/{call}/forward', [LiveOrdersController::class, 'forwardCall'])->name('admin.call.forward');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/giris', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris', [AuthController::class, 'login']);
});

Route::middleware(AdminMiddleware::class)->group(function () {
    Route::post('/admin/cikis', [AuthController::class, 'logout'])->name('admin.logout');

    Route::post('/api/waiter/complete', [WaiterDashboardController::class, 'complete'])->name('waiter.complete');

    Route::get('/admin/api/admin/manual-order/bootstrap', [ManualOrderController::class, 'bootstrap'])->name('admin.manual-order.bootstrap');
    Route::get('/admin/api/admin/manual-order/products', [ManualOrderController::class, 'searchProducts'])->name('admin.manual-order.products');
    Route::post('/admin/api/admin/manual-order', [ManualOrderController::class, 'store'])->name('admin.manual-order.store');

    Route::middleware(WaiterOnlyMiddleware::class)->prefix('waiter')->name('waiter.')->group(function () {
        Route::get('/dashboard', [WaiterDashboardController::class, 'index'])->name('dashboard');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/operasyon', [OperationsController::class, 'live'])->name('operations.live');
        Route::get('live-orders', [LiveOrdersController::class, 'index'])->name('live-orders.index');
        Route::patch('/api/cagri/{call}/onayla', [OperationsController::class, 'acknowledgeCall'])->name('operations.acknowledge');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('api/admin/categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])
            ->name('categories.toggle-active');
        Route::resource('products', ProductController::class)->except(['show']);
        Route::patch('api/admin/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])
            ->name('products.toggle-availability');
        Route::resource('tables', TableController::class)->except(['show']);
        Route::patch('api/admin/tables/{table}/toggle-active', [TableController::class, 'toggleActive'])
            ->name('tables.toggle-active');
        Route::post('tables/{table}/regenerate', [TableController::class, 'regenerate'])->name('tables.regenerate');
        Route::get('tables/{table}/qr.png', [TableController::class, 'qrPng'])->name('tables.qr.png');
        Route::get('tables/{table}/qr.svg', [TableController::class, 'qrSvg'])->name('tables.qr.svg');

        Route::get('bar', fn () => redirect()->route('admin.live-orders.index'))->name('bar.index');
        Route::get('api/bar/siparisler', [BarScreenController::class, 'orders'])->name('bar.orders');
        Route::patch('api/bar/siparis/{order}/hazir', [BarScreenController::class, 'markReady'])->name('bar.ready');

        Route::get('orders/archive', [OrderArchiveController::class, 'index'])->name('orders.archive');
        Route::get('orders/archive/export/{mode}', [OrderArchiveController::class, 'export'])
            ->name('orders.archive.export')
            ->where('mode', 'daily|report');
        Route::post('orders/archive/purge', [OrderArchiveController::class, 'purge'])->name('orders.archive.purge');
        Route::delete('orders/{order}', [OrderArchiveController::class, 'destroy'])->name('orders.destroy');
        Route::get('orders', [OrderAdminController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.status');

        Route::resource('slides', DisplaySlideController::class)->except(['show']);
        Route::resource('cafe-galleries', CafeGalleryController::class)->except(['show']);
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
