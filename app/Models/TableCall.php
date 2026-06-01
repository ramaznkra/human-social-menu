<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableCall extends Model
{
    use BelongsToRestaurant;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = ['restaurant_id', 'table_id', 'type', 'status', 'forwarded_to_waiter'];

    protected function casts(): array
    {
        return ['forwarded_to_waiter' => 'boolean'];
    }

    /** Hesap (POS) tipli çağrı mı? */
    public function isBill(): bool
    {
        return in_array($this->type, ['bill_cash', 'bill_card', 'bill'], true);
    }

    /** @deprecated linkedTable() kullanın — table adı Model::$table ile çakışır */
    public function table(): BelongsTo
    {
        return $this->linkedTable();
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

    /** Masa modeli — $this->table Laravel'de DB tablo adıdır, ilişki için getRelation kullanılır. */
    public function linkedTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
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

    /** @deprecated use scopeActive */
    public function scopePending($query)
    {
        return $this->scopeActive($query);
    }
}
