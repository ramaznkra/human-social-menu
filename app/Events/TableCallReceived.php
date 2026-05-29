<?php

namespace App\Events;

use App\Models\TableCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TableCallReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TableCall $call)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    public function broadcastAs(): string
    {
        return 'TableCallReceived';
    }

    public function broadcastWith(): array
    {
        $call = $this->call->loadMissing('linkedTable:id,number');

        return [
            'call' => [
                'id' => $call->id,
                'kind' => 'call',
                'type' => $call->type,
                'type_label' => $call->type_label,
                'headline' => $call->headline,
                'table' => $call->tableNumber(),
                'status' => $call->status,
                'forwarded_to_waiter' => (bool) $call->forwarded_to_waiter,
                'created_at' => $call->created_at?->format('H:i'),
                'updated_at' => $call->updated_at?->toIso8601String(),
                'sort_at' => $call->created_at?->toIso8601String(),
            ],
        ];
    }
}
