<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuSlide extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'type', 'duration', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'guest' => 'Özel Misafir',
            default => 'Mekan',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
