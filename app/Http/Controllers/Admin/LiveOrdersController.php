<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TableCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveOrdersController extends Controller
{
    public function index(): View
    {
        return view('admin.live-orders.index');
    }

    /** Mutfak / operasyon tableti (tam ekran, giriş gerektirmez). */
    public function screen(): View
    {
        return view('admin.live-orders.screen');
    }

    /**
     * Tek endpoint: mutfak + bar siparişleri (ürün type bilgisiyle).
     */
    public function liveOrders(): JsonResponse
    {
        $orders = Order::query()
            ->select(['id', 'order_number', 'status', 'notes', 'total', 'table_id', 'created_at', 'updated_at'])
            ->with([
                'table:id,number',
                'items' => fn ($q) => $q->select(['id', 'order_id', 'product_id', 'product_name', 'quantity', 'notes']),
                'items.product:id,type,category_id',
            ])
            ->live()
            ->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->map(function (Order $order) {
                $items = $order->items->map(function ($item) {
                    $type = $item->product?->type ?? 'kitchen';

                    return [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                        'type' => $type,
                    ];
                });

                $types = $items->pluck('type')->unique();

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'table' => $order->table?->number,
                    'notes' => $order->notes,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at->format('H:i'),
                    'updated_at' => $order->updated_at->toIso8601String(),
                    'has_kitchen' => $types->contains('kitchen'),
                    'has_bar' => $types->contains('bar'),
                    'items' => $items->values(),
                ];
            });

        $calls = TableCall::query()
            ->with(['linkedTable'])
            ->active()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (TableCall $call) => [
                'id' => $call->id,
                'kind' => 'call',
                'type' => $call->type,
                'type_label' => $call->type_label,
                'headline' => $call->headline,
                'table' => $call->tableNumber(),
                'status' => $call->status,
                'created_at' => $call->created_at->format('H:i'),
                'updated_at' => $call->updated_at->toIso8601String(),
                'sort_at' => $call->created_at->toIso8601String(),
            ]);

        return response()->json([
            'orders' => $orders,
            'calls' => $calls,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function resolveCall(TableCall $call): JsonResponse
    {
        if ($call->status === TableCall::STATUS_RESOLVED) {
            return response()->json(['success' => true, 'message' => 'Çağrı zaten kapatılmış.']);
        }

        $call->update(['status' => TableCall::STATUS_RESOLVED]);

        return response()->json(['success' => true, 'message' => 'Çağrı tamamlandı.']);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,delivered,cancelled',
            'payment_method' => 'nullable|in:cash,card',
        ]);

        $payload = ['status' => $request->status];
        if ($request->status === Order::STATUS_DELIVERED && $request->filled('payment_method')) {
            $payload['payment_method'] = $request->payment_method;
        }

        $order->update($payload);

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'status_label' => $order->fresh()->status_label,
        ]);
    }
}
