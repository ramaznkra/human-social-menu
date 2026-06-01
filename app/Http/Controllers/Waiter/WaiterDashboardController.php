<?php

namespace App\Http\Controllers\Waiter;

use App\Events\OrderStatusUpdated;
use App\Events\TableCallUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\TableCall;
use App\Services\TableStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaiterDashboardController extends Controller
{
    public function index(): View
    {
        $settings = Setting::allCached();

        return view('waiter.dashboard', [
            'venueName' => $settings['venue_name'] ?? 'Human',
            'venueTagline' => $settings['venue_tagline'] ?? 'Human Social People',
            'waiterName' => session('admin_name'),
        ]);
    }

    /** Garson çağrısını üstlenir — diğer garsonlar anında görür. */
    public function claimCall(TableCall $call): JsonResponse
    {
        if ($call->status === TableCall::STATUS_COMPLETED) {
            return response()->json(['success' => false, 'message' => 'Çağrı zaten kapatılmış.'], 422);
        }

        $userId = (int) session('admin_user_id');

        if ($call->status === TableCall::STATUS_IN_PROGRESS && (int) $call->waiter_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Garson '.($call->waiter?->name ?? 'başka bir personel').' ilgileniyor.',
                'call' => TableCallUpdated::callPayload($call->loadMissing(['linkedTable:id,number', 'waiter:id,name'])),
            ], 422);
        }

        if ($call->status === TableCall::STATUS_PENDING) {
            $call->update([
                'status' => TableCall::STATUS_IN_PROGRESS,
                'waiter_id' => $userId,
            ]);
            $call->refresh();
            event(new TableCallUpdated($call));
        }

        return response()->json([
            'success' => true,
            'message' => 'Çağrı size atandı.',
            'call' => TableCallUpdated::callPayload($call->loadMissing(['linkedTable:id,number', 'waiter:id,name'])),
        ]);
    }

    /**
     * Garson: çağrı veya siparişi tek dokunuşla kapat (kasa ekranıyla senkron).
     */
    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:call,order',
            'id' => 'required|integer|min:1',
            'payment_method' => 'nullable|in:cash,card',
        ]);

        $tableStatus = app(TableStatusService::class);
        $userId = (int) session('admin_user_id');

        if ($validated['type'] === 'call') {
            $call = TableCall::query()->findOrFail($validated['id']);

            if ($call->status === TableCall::STATUS_COMPLETED) {
                return response()->json(['success' => true, 'message' => 'Zaten kapatılmış.']);
            }

            if ($call->status === TableCall::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Önce «İlgileniyorum» ile çağrıyı üstlenin.',
                ], 422);
            }

            if ((int) $call->waiter_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu çağrıyı yalnızca üstlenen garson tamamlayabilir.',
                ], 422);
            }

            $call->update(['status' => TableCall::STATUS_COMPLETED]);
            $tableStatus->sync($call->table_id);
            $call->refresh();
            event(new TableCallUpdated($call));

            if (in_array($call->type, ['bill_cash', 'bill_card', 'bill'], true)) {
                $paymentMethod = match ($validated['payment_method'] ?? null) {
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
                    $tableStatus->sync($call->table_id);

                    return response()->json([
                        'success' => true,
                        'message' => $paymentMethod === Order::PAYMENT_CARD
                            ? 'Çağrı tamamlandı. Ödeme kart olarak kapatıldı.'
                            : 'Çağrı tamamlandı. Ödeme nakit olarak kapatıldı.',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Çağrı tamamlandı.',
            ]);
        }

        $order = Order::query()->findOrFail($validated['id']);

        if ($order->status === Order::STATUS_READY) {
            $order->update(['status' => Order::STATUS_DELIVERED]);
            $order->refresh();
            event(OrderStatusUpdated::fromOrder($order));

            return response()->json([
                'success' => true,
                'message' => 'Sipariş masaya teslim edildi (Afiyet Olsun).',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Garson yalnızca merkezde hazır olan siparişi teslim edebilir.',
        ], 422);
    }
}
