<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeGallery extends Model
{
    protected $fillable = [
        'image_path',
        'title',
        'description',
        'badge_text',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->image_path;

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
