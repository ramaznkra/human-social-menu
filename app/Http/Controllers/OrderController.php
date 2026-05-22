<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_token' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:20',
            'items.*.notes' => 'nullable|string|max:200',
        ]);

        $table = null;
        if (! empty($validated['table_token'])) {
            $table = Table::where('qr_token', $validated['table_token'])->first();
        }

        $order = DB::transaction(function () use ($validated, $table) {
            $orderNumber = 'H'.date('ymd').str_pad((Order::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'table_id' => $table?->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'total' => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if (! $product->is_available) {
                    continue;
                }
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'product_name' => $product->name,
                    'notes' => $item['notes'] ?? null,
                ]);
                $total += $product->price * $item['quantity'];
            }

            $order->update(['total' => $total]);

            return $order->load('items');
        });

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $order->total,
            'redirect' => route('order.status', $order->id),
        ]);
    }

    public function status(Order $order): View
    {
        $order->load(['items', 'table']);
        $settings = \App\Models\Setting::allCached();

        return view('menu.status', compact('order', 'settings'));
    }

    public function statusApi(Order $order): JsonResponse
    {
        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'total' => $order->total,
            'table' => $order->table?->number,
            'updated_at' => $order->updated_at->toIso8601String(),
        ]);
    }
}
