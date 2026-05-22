<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableCall extends Model
{
    protected $fillable = ['table_id', 'type', 'status'];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'waiter' => 'Garson Çağrısı',
            'bill' => 'Hesap İsteme',
            default => $this->type,
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
