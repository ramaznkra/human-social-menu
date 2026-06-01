<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Table;
use App\Models\TableCall;

class TableStatusService
{
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_OCCUPIED = 'occupied';

    public function markOccupied(?int $tableId): void
    {
        if ($tableId === null) {
            return;
        }

        Table::query()
            ->whereKey($tableId)
            ->update(['status' => self::STATUS_OCCUPIED]);
    }

    /** Masa boşsa available, aksi halde occupied. */
    public function sync(?int $tableId): void
    {
        if ($tableId === null) {
            return;
        }

        $hasLiveOrder = Order::query()
            ->where('table_id', $tableId)
            ->live()
            ->exists();

        $hasOpenCall = TableCall::query()
            ->where('table_id', $tableId)
            ->open()
            ->exists();

        $status = ($hasLiveOrder || $hasOpenCall)
            ? self::STATUS_OCCUPIED
            : self::STATUS_AVAILABLE;

        Table::query()->whereKey($tableId)->update(['status' => $status]);
    }
}
