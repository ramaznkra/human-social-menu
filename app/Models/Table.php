<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Table extends Model
{
    protected $fillable = ['number', 'qr_token', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function generateToken(): string
    {
        return Str::random(16);
    }

    public function getQrUrlAttribute(): string
    {
        return url('/menu/'.$this->qr_token);
    }
}
