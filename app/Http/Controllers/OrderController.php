<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\Table;
use App\Services\OrderPlacementService;
use App\Support\MenuLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const FINAL_STATUSES = ['cancelled', 'completed'];

    public function store(Request $request, OrderPlacementService $placement): JsonResponse
    {
        $locale = MenuLocale::resolve($request);
        MenuLocale::apply($request, $locale);

        $validated = $request->validate([
            'table_token' => 'nullable|string',
            'lang' => 'nullable|string|in:tr,en,ru',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:20',
            'items.*.notes' => 'nullable|string|max:200',
        ]);

        $tableId = null;
        if (! empty($validated['table_token'])) {
            $tableId = Table::where('qr_token', $validated['table_token'])->value('id');
        }

        $order = $placement->createOrder(
            $tableId,
            $validated['items'],
            Order::SOURCE_QR,
            $validated['notes'] ?? null,
        );

        event(new OrderPlaced($order));

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $order->total,
            'redirect' => route('order.status', $order->id).'?lang='.$locale,
        ]);
    }

    public function status(Request $request, Order $order): View
    {
        $locale = MenuLocale::resolve($request);
        MenuLocale::apply($request, $locale);

        $order = Order::query()
            ->select(['id', 'order_number', 'status', 'total', 'table_id', 'updated_at', 'payment_method'])
            ->with([
                'items:id,order_id,product_name,quantity,unit_price',
                'table:id,number,qr_token',
            ])
            ->findOrFail($order->id);

        $settings = \App\Models\Setting::allCached();

        return view('menu.status', compact('order', 'settings', 'locale'));
    }

    public function statusApi(Request $request, Order $order): JsonResponse
    {
        $locale = MenuLocale::resolve($request);
        MenuLocale::apply($request, $locale);

        $order = Order::query()
            ->select(['id', 'order_number', 'status', 'total', 'updated_at', 'table_id', 'payment_method'])
            ->with(['table:id,number'])
            ->findOrFail($order->id);

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'customer_status_label' => $order->customer_status_label,
            'customer_status_message' => $order->customer_status_message,
            'status_step' => $order->customerStatusStep(),
            'payment_method' => $order->payment_method,
            'payment_method_label' => $order->payment_method_label,
            'total' => $order->total,
            'table' => $order->table?->number,
            'updated_at' => $order->updated_at->toIso8601String(),
            'is_final' => in_array($order->status, self::FINAL_STATUSES, true)
                || $order->isClosed(),
        ]);
    }
}
