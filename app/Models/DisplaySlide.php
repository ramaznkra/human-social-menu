<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplaySlide extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'duration', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getImageUrlAttribute(): string
    {
        return str_starts_with($this->image, 'http')
            ? $this->image
            : asset('storage/'.$this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
