<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    public function broadcastAs(): string
    {
        return 'OrderPlaced';
    }

    public function broadcastWith(): array
    {
        $order = $this->order->loadMissing([
            'table:id,number',
            'items:id,order_id,product_name,quantity,notes',
        ]);

        return [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'source' => $order->source ?? Order::SOURCE_QR,
                'source_label' => $order->source_label,
                'is_waiter_order' => $order->isWaiterOrder(),
                'payment_method' => $order->payment_method,
                'table' => $order->table?->number,
                'total' => (float) $order->total,
                'created_at' => $order->created_at?->format('H:i'),
                'updated_at' => $order->updated_at?->toIso8601String(),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                ])->values()->all(),
            ],
        ];
    }
}
