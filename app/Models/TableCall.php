<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableCall extends Model
{
    use BelongsToRestaurant;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'restaurant_id',
        'table_id',
        'type',
        'status',
        'forwarded_to_waiter',
        'assigned_user_id',
    ];

    protected function casts(): array
    {
        return ['forwarded_to_waiter' => 'boolean'];
    }

    public function isBill(): bool
    {
        return in_array($this->type, ['bill_cash', 'bill_card', 'bill'], true);
    }

    public function table(): BelongsTo
    {
        return $this->linkedTable();
    }

    public function linkedTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'waiter' => 'Garson Çağrısı',
            'bill_cash' => 'Nakit Hesap',
            'bill_card' => 'Kart / Pos Hesap',
            'bill' => 'Hesap İsteme',
            default => $this->type,
        };
    }

    public function tableNumber(): ?string
    {
        $tableModel = $this->relationLoaded('linkedTable')
            ? $this->getRelation('linkedTable')
            : $this->linkedTable()->first();

        $number = $tableModel?->number;

        return $number !== null && $number !== ''
            ? (string) $number
            : ($this->table_id ? (string) $this->table_id : null);
    }

    public function getHeadlineAttribute(): string
    {
        $table = $this->tableNumber() ?? '?';

        return match ($this->type) {
            'waiter' => "MASA {$table}: GARSON ÇAĞIRIYOR",
            'bill_cash' => "MASA {$table}: HESAP İSTİYOR (NAKİT)",
            'bill_card' => "MASA {$table}: HESAP İSTİYOR (KART)",
            'bill' => "MASA {$table}: HESAP İSTİYOR",
            default => "MASA {$table}: ÇAĞRI",
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Aktif veya garson ilgileniyor — henüz kapanmamış çağrılar. */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_IN_PROGRESS]);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_IN_PROGRESS], true);
    }

    /** @deprecated use scopeActive */
    public function scopePending($query)
    {
        return $this->scopeActive($query);
    }
}
