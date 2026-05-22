<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function generateOrderNumber(): string
    {
        $seq = Order::whereDate('created_at', today())->count() + 1;

        return 'H'.date('ymd').str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, notes?: string|null}>  $items
     */
    public function createOrder(
        ?int $tableId,
        array $items,
        string $source = Order::SOURCE_QR,
        ?string $notes = null,
    ): Order {
        if ($tableId !== null && ! Table::whereKey($tableId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'table_id' => 'Geçerli ve aktif bir masa seçin.',
            ]);
        }

        return DB::transaction(function () use ($tableId, $items, $source, $notes) {
            $order = Order::create([
                'table_id' => $tableId,
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'source' => $source,
                'notes' => $notes,
                'total' => 0,
            ]);

            $total = 0;
            $added = 0;

            foreach ($items as $item) {
                $product = Product::query()->find($item['product_id'] ?? null);
                if (! $product || ! $product->is_available) {
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 1);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'product_name' => $product->name,
                    'notes' => $item['notes'] ?? null,
                ]);
                $total += $product->price * $qty;
                $added++;
            }

            if ($added === 0) {
                throw ValidationException::withMessages([
                    'items' => 'En az bir müsait ürün ekleyin.',
                ]);
            }

            $order->update(['total' => $total]);

            return $order->load(['items', 'table:id,number']);
        });
    }
}
