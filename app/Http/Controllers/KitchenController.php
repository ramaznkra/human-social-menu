<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['items', 'table'])
            ->live()
            ->orderByDesc('created_at')
            ->get();

        $settings = Setting::allCached();

        return view('kitchen.index', compact('orders', 'settings'));
    }

    public function api(): JsonResponse
    {
        $orders = Order::query()
            ->select(['id', 'order_number', 'status', 'notes', 'total', 'table_id', 'created_at'])
            ->with([
                'table:id,number',
                'items:id,order_id,product_name,quantity,notes',
            ])
            ->live()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'status_label' => $o->status_label,
                'table' => $o->table?->number,
                'notes' => $o->notes,
                'total' => $o->total,
                'items' => $o->items->map(fn ($i) => [
                    'name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'notes' => $i->notes,
                ]),
                'created_at' => $o->created_at->format('H:i'),
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json(['success' => true, 'status' => $order->status]);
    }
}
