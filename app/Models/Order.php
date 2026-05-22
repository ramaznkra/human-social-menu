<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_CARD = 'card';

    /** Canlı ekran: beklemede, hazırlanıyor, masada */
    public static function liveStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PREPARING,
            self::STATUS_READY,
        ];
    }

    /** Arşiv: tamamlandı, iptal */
    public static function archivedStatuses(): array
    {
        return [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ];
    }

    protected $fillable = [
        'table_id',
        'order_number',
        'status',
        'notes',
        'total',
        'payment_method',
    ];

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

    public function scopeLive(Builder $query): Builder
    {
        return $query->whereIn('status', self::liveStatuses());
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereIn('status', self::archivedStatuses());
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Beklemede',
            self::STATUS_PREPARING => 'Hazırlanıyor',
            self::STATUS_READY => 'Masada',
            self::STATUS_DELIVERED => 'Tamamlandı',
            self::STATUS_CANCELLED => 'İptal',
            default => $this->status,
        };
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        return match ($this->payment_method) {
            self::PAYMENT_CASH => 'Nakit',
            self::PAYMENT_CARD => 'Kart',
            default => null,
        };
    }

    /** Müşteri menüsü / sipariş takibi metinleri */
    public function getCustomerStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Beklemede',
            self::STATUS_PREPARING => 'Hazırlanıyor',
            self::STATUS_READY => 'Masaya Doğru',
            self::STATUS_DELIVERED => 'Afiyet Olsun',
            self::STATUS_CANCELLED => 'İptal Edildi',
            default => $this->status,
        };
    }

    public function customerStatusStep(): int
    {
        return match ($this->status) {
            self::STATUS_PREPARING => 2,
            self::STATUS_READY => 3,
            self::STATUS_DELIVERED => 4,
            self::STATUS_CANCELLED => 0,
            default => 1,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PREPARING => 'info',
            self::STATUS_READY => 'success',
            self::STATUS_DELIVERED => 'secondary',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }
}
