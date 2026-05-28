<?php

namespace App\Http\Controllers\Waiter;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\TableCall;
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
            'venueTagline' => $settings['venue_tagline'] ?? 'Social People',
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
        ]);

        if ($validated['type'] === 'call') {
            $call = TableCall::query()->findOrFail($validated['id']);

            if ($call->status === TableCall::STATUS_RESOLVED) {
                return response()->json(['success' => true, 'message' => 'Zaten kapatılmış.']);
            }

            $call->update(['status' => TableCall::STATUS_RESOLVED]);

            if (in_array($call->type, ['bill_cash', 'bill_card', 'bill'], true)) {
                $paymentMethod = $call->type === 'bill_card' ? Order::PAYMENT_CARD : Order::PAYMENT_CASH;
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
