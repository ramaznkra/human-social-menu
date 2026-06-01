<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Table extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'number', 'uuid', 'qr_token', 'qr_image_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Her yeni masaya tahmin edilemez bir UUID atanır (sıralı id'yi gizlemek için).
        static::creating(function (Table $table) {
            if (empty($table->uuid)) {
                $table->uuid = (string) Str::uuid();
            }
        });
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

    /** Canlı sipariş veya aktif çağrısı olan masa id'leri. */
    public static function busyTableIds(): Collection
    {
        $orderTables = Order::query()
            ->whereNotNull('table_id')
            ->live()
            ->pluck('table_id');

        $callTables = TableCall::query()->active()->pluck('table_id');

        return $orderTables->merge($callTables)->unique()->values();
    }

    public function getMenuUrlAttribute(): string
    {
        return route('menu.index', ['token' => $this->uuid]);
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
