<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundrySetting extends Model
{
    protected $fillable = [
        'wash_price_per_kg',
        'press_price_per_kg',
        'full_service_addon_per_kg',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'wash_price_per_kg' => 'decimal:2',
            'press_price_per_kg' => 'decimal:2',
            'full_service_addon_per_kg' => 'decimal:2',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'wash_price_per_kg' => 26,
            'press_price_per_kg' => 35,
            'full_service_addon_per_kg' => 15,
        ]);
    }
}
