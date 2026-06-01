<?php

namespace App\Services;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableCall;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TableTransferService
{
    public function __construct(
        private readonly TableStatusService $tableStatus,
    ) {}

    /**
     * Aktif masadaki canlı siparişleri boş bir masaya taşır.
     */
    public function transfer(int $fromTableId, int $toTableId): int
    {
        if ($fromTableId === $toTableId) {
            throw ValidationException::withMessages([
                'to_table_id' => 'Kaynak ve hedef masa farklı olmalı.',
            ]);
        }

        $fromTable = Table::query()->where('is_active', true)->find($fromTableId);
        $toTable = Table::query()->where('is_active', true)->find($toTableId);

        if (! $fromTable || ! $toTable) {
            throw ValidationException::withMessages([
                'table_id' => 'Geçerli ve aktif masalar seçin.',
            ]);
        }

        if ($this->tableHasLiveActivity($toTableId)) {
            throw ValidationException::withMessages([
                'to_table_id' => 'Hedef masa boş olmalı (aktif sipariş veya çağrı yok).',
            ]);
        }

        return DB::transaction(function () use ($fromTableId, $toTableId) {
            $orders = Order::query()
                ->where('table_id', $fromTableId)
                ->live()
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                throw ValidationException::withMessages([
                    'from_table_id' => 'Kaynak masada aktarılacak sipariş yok.',
                ]);
            }

            Order::query()
                ->whereIn('id', $orders->pluck('id'))
                ->update(['table_id' => $toTableId]);

            $this->tableStatus->sync($fromTableId);
            $this->tableStatus->sync($toTableId);

            foreach ($orders as $order) {
                $order->refresh();
                event(OrderStatusUpdated::fromOrder($order));
            }

            return $orders->count();
        });
    }

    private function tableHasLiveActivity(int $tableId): bool
    {
        $hasLiveOrder = Order::query()
            ->where('table_id', $tableId)
            ->live()
            ->exists();

        $hasOpenCall = TableCall::query()
            ->where('table_id', $tableId)
            ->open()
            ->exists();

        return $hasLiveOrder || $hasOpenCall;
    }
}
