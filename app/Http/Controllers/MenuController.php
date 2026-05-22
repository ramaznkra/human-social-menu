<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\CafeGallery;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request, ?string $token = null): View
    {
        $table = null;

        if ($request->filled('masa')) {
            $table = Table::query()
                ->where('number', (string) $request->query('masa'))
                ->where('is_active', true)
                ->first();
        } elseif ($token) {
            $table = Table::where('qr_token', $token)->where('is_active', true)->first();
        }

        $categories = Category::active()
            ->with(['products' => fn ($q) => $q->available()])
            ->get()
            ->filter(fn ($c) => $c->products->isNotEmpty());

        $events = Event::active()
            ->where('event_date', '>=', now()->subDay())
            ->take(5)
            ->get();

        $settings = Setting::allCached();

        $spottedSliders = CafeGallery::active()->get();

        $productPopularity = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', today())
            ->whereNotNull('order_items.product_id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.product_id')
            ->pluck('total_qty', 'product_id');

        return view('menu.index', compact(
            'categories',
            'table',
            'events',
            'settings',
            'spottedSliders',
            'productPopularity',
        ));
    }
}
