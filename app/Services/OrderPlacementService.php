<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Services\TableStatusService;
use App\Support\CurrentRestaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function __construct(
        private readonly ProductOptionPricing $optionPricing,
    ) {}
    public function generateOrderNumber(): string
    {
        $seq = Order::whereDate('created_at', today())->count() + 1;

        return 'H'.date('ymd').str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, notes?: string|null, options?: array<int, array{group_id: int, option_id: int}>}>  $items
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

        $tableStatus = app(TableStatusService::class);

        return DB::transaction(function () use ($tableId, $items, $source, $notes, $tableStatus) {
            $restaurantId = CurrentRestaurant::resolveId();

            if ($tableId !== null) {
                $table = Table::query()->find($tableId);
                if (! $table) {
                    throw ValidationException::withMessages([
                        'table_id' => 'Geçerli ve aktif bir masa seçin.',
                    ]);
                }
                $restaurantId = (int) $table->restaurant_id;
            }

            if ($restaurantId === null) {
                throw ValidationException::withMessages([
                    'restaurant' => 'Sipariş için restoran bağlamı bulunamadı.',
                ]);
            }

            $initialStatus = $source === Order::SOURCE_QR
                ? Order::STATUS_PENDING_APPROVAL
                : Order::STATUS_PREPARING;

            $order = Order::create([
                'restaurant_id' => $restaurantId,
                'table_id' => $tableId,
                'order_number' => $this->generateOrderNumber(),
                'status' => $initialStatus,
                'source' => $source,
                'notes' => $notes,
                'total' => 0,
            ]);

            $total = 0;
            $added = 0;

            foreach ($items as $item) {
                $product = Product::query()->find($item['product_id'] ?? null);
                if (! $product || ! $product->is_available || ! $product->in_stock) {
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 1);
                $locale = app()->getLocale();
                $pricing = $this->optionPricing->resolve(
                    $product,
                    $item['options'] ?? [],
                    $locale,
                );

                $productName = $product->getTranslation('name', $locale, false)
                    ?: $product->getTranslation('name', 'tr');
                $productName .= $pricing['display_name_suffix'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $pricing['unit_price'],
                    'product_name' => $productName,
                    'notes' => $item['notes'] ?? null,
                    'options' => $pricing['options'] !== [] ? $pricing['options'] : null,
                ]);
                $total += $pricing['unit_price'] * $qty;
                $added++;
            }

            if ($added === 0) {
                throw ValidationException::withMessages([
                    'items' => 'En az bir müsait ürün ekleyin.',
                ]);
            }

            $order->update(['total' => $total]);

            $order = $order->load(['items.product:id,type', 'table:id,number']);

            if ($tableId !== null) {
                $tableStatus->markOccupied($tableId);
            }

            event(new OrderCreated($order));

            return $order;
        });
    }
}
