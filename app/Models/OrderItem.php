<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** unit_price: sipariş oluşturulurken ürün+varyasyon fiyatının anlık kopyası (admin fiyat değişse bile geçmiş ciro korunur). */
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit_price', 'product_name', 'notes', 'options',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => MoneyCast::class,
            'options' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): string
    {
        return Money::mul($this->unit_price ?? '0', $this->quantity);
    }
}
