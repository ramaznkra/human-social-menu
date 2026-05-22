<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveOrdersController extends Controller
{
    public function index(): View
    {
        return view('admin.live-orders.index', $this->liveOrdersViewData());
    }

    /** Mutfak / operasyon tableti (tam ekran, giriş gerektirmez). */
    public function screen(): View
    {
        return view('admin.live-orders.screen', $this->liveOrdersViewData());
    }

    /**
     * @return array{tables: \Illuminate\Database\Eloquent\Collection<int, Table>, busyTableIds: \Illuminate\Support\Collection<int, int>}
     */
    private function liveOrdersViewData(): array
    {
        return [
            'tables' => Table::orderBy('number')->get(['id', 'number', 'is_active']),
            'busyTableIds' => Table::busyTableIds(),
        ];
    }

    /**
     * Tek endpoint: mutfak + bar siparişleri (ürün type bilgisiyle).
     */
    public function liveOrders(): JsonResponse
    {
        $orders = Order::query()
            ->select(['id', 'order_number', 'status', 'source', 'notes', 'total', 'table_id', 'created_at', 'updated_at'])
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
                    'source' => $order->source ?? Order::SOURCE_QR,
                    'source_label' => $order->source_label,
                    'is_waiter_order' => $order->isWaiterOrder(),
                    'payment_method' => $order->payment_method,
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

        $busyTableIds = Table::busyTableIds();

        $tables = Table::query()
            ->orderBy('number')
            ->get(['id', 'number', 'is_active'])
            ->map(fn (Table $table) => [
                'id' => $table->id,
                'number' => $table->number,
                'is_active' => $table->is_active,
                'is_busy' => $busyTableIds->contains($table->id),
            ]);

        return response()->json([
            'orders' => $orders,
            'calls' => $calls,
            'tables' => $tables,
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
            'payment_only' => 'nullable|boolean',
        ]);

        if ($request->boolean('payment_only')) {
            if ($order->isWaiterOrder()) {
                $order->update([
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => $request->payment_method,
                ]);
            } else {
                if ($order->status !== Order::STATUS_DELIVERED) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ödeme yalnızca teslim edilmiş siparişlerde kaydedilir.',
                    ], 422);
                }

                $order->update(['payment_method' => $request->payment_method]);
            }

            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Sipariş kapatıldı.',
                'status' => $order->status,
                'status_label' => $order->status_label,
                'payment_method' => $order->payment_method,
            ]);
        }

        if ($order->isWaiterOrder()) {
            return response()->json([
                'success' => false,
                'message' => 'Garson siparişinde yalnızca Nakit veya Kart ile kapatılır.',
            ], 422);
        }

        $newStatus = $request->status;

        if (! $order->canTransitionTo($newStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu durum geçişi izinli değil. Sıra: Kabul (Hazırlanıyor) → Afiyet Olsun → Nakit/Kart ile kapat.',
            ], 422);
        }

        $order->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'status_label' => $order->fresh()->status_label,
        ]);
    }
}
