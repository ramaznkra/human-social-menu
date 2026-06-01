<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Table;
use App\Services\OrderPlacementService;
use App\Support\CurrentRestaurant;
use App\Support\MenuLocale;
use App\Support\TenantRules;
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
            'items.*.product_id' => ['required', TenantRules::existsInCurrentRestaurant('products', 'id')],
            'items.*.quantity' => 'required|integer|min:1|max:20',
            'items.*.notes' => 'nullable|string|max:200',
        ]);

        $tableId = null;
        if (! empty($validated['table_token'])) {
            $tableId = Table::query()
                ->where('uuid', $validated['table_token'])
                ->orWhere('qr_token', $validated['table_token'])
                ->value('id');
        }

        $order = $placement->createOrder(
            $tableId,
            $validated['items'],
            Order::SOURCE_QR,
            $validated['notes'] ?? null,
        );

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $order->total,
            'redirect' => route('order.status', $order->public_token).'?lang='.$locale,
        ]);
    }

    public function status(Request $request, string $orderToken): View
    {
        $locale = MenuLocale::resolve($request);
        MenuLocale::apply($request, $locale);

        $order = $this->findPublicOrder($orderToken);

        $settings = \App\Models\Setting::allCached();

        return view('menu.status', compact('order', 'settings', 'locale'));
    }

    public function statusApi(Request $request, string $orderToken): JsonResponse
    {
        MenuLocale::apply($request, MenuLocale::resolve($request));

        $order = $this->findPublicOrder($orderToken);

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

    private function findPublicOrder(string $orderToken): Order
    {
        $order = Order::withoutGlobalScopes()
            ->where('public_token', $orderToken)
            ->with([
                'restaurant',
                'items:id,order_id,product_name,quantity,unit_price',
                'table:id,number,qr_token,restaurant_id',
            ])
            ->firstOrFail();

        if ($order->restaurant?->is_active) {
            CurrentRestaurant::set($order->restaurant);
        }

        return $order;
    }
}
