<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use App\Services\DashboardFinanceStats;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardFinanceStats $financeStats): View
    {
        $finance = $financeStats->forToday();

        $stats = [
            'categories' => Category::count(),
            'products' => Product::count(),
            'tables' => Table::count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
        ];

        $recentOrders = Order::query()
            ->select(['id', 'order_number', 'status', 'source', 'total', 'table_id', 'created_at'])
            ->with(['table:id,number'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $topProducts = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereNotNull('product_id')
            ->whereHas('order', function ($q) {
                $q->whereDate('created_at', today())
                    ->where('status', Order::STATUS_DELIVERED);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->with('product:id,name,image')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'finance', 'topProducts'));
    }
}
