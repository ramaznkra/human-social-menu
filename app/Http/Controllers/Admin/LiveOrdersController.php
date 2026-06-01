<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Events\TableCallForwarded;
use App\Events\TableCallUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableCall;
use App\Services\TableStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveOrdersController extends Controller
{
    public function index(): View
    {
        return view('admin.live-orders.index', $this->liveOrdersViewData());
    }

    /** Mutfak / operasyon tableti (tam ekran). ?station=kitchen|bar ile filtre. */
    public function screen(Request $request): View
    {
        $station = in_array($request->query('station'), ['kitchen', 'bar'], true)
            ? $request->query('station')
            : 'all';

        return view('admin.live-orders.screen', array_merge(
            $this->liveOrdersViewData(),
            ['defaultStation' => $station],
        ));
    }

    /**
     * @return array{tables: \Illuminate\Database\Eloquent\Collection<int, Table>, busyTableIds: \Illuminate\Support\Collection<int, int>}
     */
    private function liveOrdersViewData(): array
    {
        return [
            'tables' => Table::orderBy('number')->get(['id', 'number', 'is_active', 'status']),
            'busyTableIds' => Table::busyTableIds(),
            'defaultStation' => 'all',
        ];
    }

    /**
     * Tek endpoint: mutfak + bar siparişleri (ürün type bilgisiyle).
     */
    public function liveOrders(): JsonResponse
    {
        $mapOrder = function (Order $order): array {
            $items = $order->items->map(function ($item) {
                $type = $item->product?->stationType() ?? 'kitchen';

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
                'total' => $order->total,
                'created_at' => $order->created_at->format('H:i'),
                'created_at_iso' => $order->created_at->toIso8601String(),
                'updated_at' => $order->updated_at->toIso8601String(),
                'has_kitchen' => $types->contains('kitchen'),
                'has_bar' => $types->contains('bar'),
                'items' => $items->values(),
            ];
        };

        $orders = Order::query()
            ->select(['id', 'order_number', 'status', 'source', 'notes', 'total', 'table_id', 'created_at', 'updated_at'])
            ->with([
                'table:id,number',
                'items' => fn ($q) => $q->select(['id', 'order_id', 'product_id', 'product_name', 'quantity', 'notes']),
                'items.product:id,type,category_id',
                'items.product.category:id,type',
            ])
            ->live()
            ->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->map($mapOrder);

        $completedOrders = Order::query()
            ->select(['id', 'order_number', 'status', 'source', 'notes', 'total', 'table_id', 'created_at', 'updated_at', 'payment_method'])
            ->with([
                'table:id,number',
                'items' => fn ($q) => $q->select(['id', 'order_id', 'product_id', 'product_name', 'quantity', 'notes']),
                'items.product:id,type,category_id',
                'items.product.category:id,type',
            ])
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', Order::STATUS_DELIVERED)
                        ->whereNotNull('payment_method');
                })->orWhere('status', Order::STATUS_CANCELLED);
            })
            ->orderByDesc('updated_at')
            ->limit(40)
            ->get()
            ->map($mapOrder);

        $calls = TableCall::query()
            ->with(['linkedTable', 'waiter:id,name'])
            ->open()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (TableCall $call) => TableCallUpdated::callPayload($call));

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
            'completed_orders' => $completedOrders,
            'calls' => $calls,
            'tables' => $tables,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /** Kasa: hesap (POS) çağrısını garsona yönlendirir. */
    public function forwardCall(TableCall $call): JsonResponse
    {
        if ($call->status === TableCall::STATUS_COMPLETED) {
            return response()->json(['success' => false, 'message' => 'Çağrı kapatılmış.'], 422);
        }

        if (! $call->forwarded_to_waiter) {
            $call->update(['forwarded_to_waiter' => true]);
            event(new TableCallForwarded($call->fresh()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Garsona iletildi. POS / masa bildirimi gönderildi.',
        ]);
    }

    public function resolveCall(Request $request, TableCall $call): JsonResponse
    {
        $request->validate([
            'payment_method' => 'nullable|in:cash,card',
        ]);

        if ($call->status === TableCall::STATUS_COMPLETED) {
            return response()->json(['success' => true, 'message' => 'Çağrı zaten kapatılmış.']);
        }

        $call->update(['status' => TableCall::STATUS_COMPLETED]);

        $this->syncTable($call->table_id);

        // Hesap çağrısı kapatılıyorsa, ilgili teslim edilmiş siparişin ödemesini de kapat.
        if ($call->isBill()) {
            $paymentMethod = match ($request->input('payment_method')) {
                'card' => Order::PAYMENT_CARD,
                'cash' => Order::PAYMENT_CASH,
                default => $call->type === 'bill_card' ? Order::PAYMENT_CARD : Order::PAYMENT_CASH,
            };

            $orderForPayment = Order::query()
                ->where('table_id', $call->table_id)
                ->where('status', Order::STATUS_DELIVERED)
                ->whereNull('payment_method')
                ->latest('updated_at')
                ->first();

            if ($orderForPayment) {
                $orderForPayment->update(['payment_method' => $paymentMethod]);
                $orderForPayment->refresh();
                event(OrderStatusUpdated::fromOrder($orderForPayment));
                $this->syncTable($call->table_id);
                event(new TableCallUpdated($call->fresh()));

                return response()->json([
                    'success' => true,
                    'message' => $paymentMethod === Order::PAYMENT_CARD
                        ? 'Hesap kart ile kapatıldı.'
                        : 'Hesap nakit ile kapatıldı.',
                ]);
            }
        }

        event(new TableCallUpdated($call->fresh()));

        return response()->json(['success' => true, 'message' => 'Çağrı tamamlandı.']);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending_approval,pending,preparing,ready,delivered,cancelled',
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
            event(OrderStatusUpdated::fromOrder($order));
            $this->syncTable($order->table_id);

            return response()->json([
                'success' => true,
                'message' => 'Sipariş kapatıldı.',
                'status' => $order->status,
                'status_label' => $order->status_label,
                'payment_method' => $order->payment_method,
            ]);
        }

        $newStatus = $request->status;

        if (! $order->canTransitionTo($newStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu durum geçişi izinli değil. Sıra: Kabul (Hazırlanıyor) → Afiyet Olsun → Nakit/Kart ile kapat.',
            ], 422);
        }

        $order->update(['status' => $newStatus]);
        $order->refresh();
        event(OrderStatusUpdated::fromOrder($order));

        if (in_array($newStatus, [Order::STATUS_CANCELLED], true) || $order->isClosed()) {
            $this->syncTable($order->table_id);
        }

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'status_label' => $order->status_label,
        ]);
    }

    private function syncTable(?int $tableId): void
    {
        if ($tableId !== null) {
            app(TableStatusService::class)->sync($tableId);
        }
    }
}
