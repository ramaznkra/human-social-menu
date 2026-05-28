<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $status,
        public ?int $tableNumber = null,
        public ?string $orderNumber = null,
        public array $items = [],
    ) {
    }

    public static function fromOrder(Order $order): self
    {
        $order->loadMissing('table:id,number', 'items:id,order_id,product_name,quantity');

        return new self(
            orderId: (int) $order->id,
            status: (string) $order->status,
            tableNumber: $order->table?->number ? (int) $order->table->number : null,
            orderNumber: $order->order_number,
            items: $order->items
                ->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                ])
                ->values()
                ->all(),
        );
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
            'table' => $this->tableNumber,
            'order_number' => $this->orderNumber,
            'items' => $this->items,
        ];
    }
}
