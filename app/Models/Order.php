<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['table_id', 'order_number', 'status', 'notes', 'total'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Bekliyor',
            'preparing' => 'Hazırlanıyor',
            'ready' => 'Hazır',
            'delivered' => 'Teslim Edildi',
            'cancelled' => 'İptal',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'preparing' => 'info',
            'ready' => 'success',
            'delivered' => 'secondary',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
