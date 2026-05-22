<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Table;
use App\Services\OrderPlacementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManualOrderController extends Controller
{
    public function bootstrap(): JsonResponse
    {
        $tables = Table::query()
            ->where('is_active', true)
            ->orderBy('number')
            ->get(['id', 'number'])
            ->map(fn (Table $t) => ['id' => $t->id, 'number' => $t->number]);

        $settings = Setting::allCached();

        return response()->json([
            'tables' => $tables,
            'currency' => $settings['currency'] ?? '₺',
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->available()
            ->with('category:id,name')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$q}%"));
                });
            })
            ->limit(30)
            ->get(['id', 'name', 'price', 'type', 'category_id']);

        return response()->json([
            'products' => $products->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'type' => $p->type ?? 'kitchen',
                'category' => $p->category?->name,
            ]),
        ]);
    }

    public function store(Request $request, OrderPlacementService $placement): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:20',
        ]);

        $order = $placement->createOrder(
            (int) $validated['table_id'],
            $validated['items'],
            Order::SOURCE_WAITER,
            $validated['notes'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => "Sipariş #{$order->order_number} mutfağa iletildi.",
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'table' => $order->table?->number,
                'source' => $order->source,
            ],
        ], 201);
    }
}
