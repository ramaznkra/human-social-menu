<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Table extends Model
{
    protected $fillable = ['number', 'qr_token', 'qr_image_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(TableCall::class);
    }

    public static function generateToken(): string
    {
        return Str::random(16);
    }

    public function getMenuUrlAttribute(): string
    {
        return route('menu.index', ['masa' => $this->number]);
    }

    /** @deprecated Use menu_url — kept for compatibility */
    public function getQrUrlAttribute(): string
    {
        return $this->menu_url;
    }

    public function getQrImageUrlAttribute(): ?string
    {
        if (! $this->qr_image_path) {
            return null;
        }

        return asset('storage/'.$this->qr_image_path);
    }
}
