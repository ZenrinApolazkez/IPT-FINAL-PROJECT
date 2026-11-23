<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryOrder extends Model
{
    public const STATUSES = [
        'received',
        'washing',
        'drying',
        'folding',
        'ready_for_pickup',
        'completed',
        'cancelled',
    ];

    public const TRANSITIONS = [
        'received' => ['washing', 'cancelled'],
        'washing' => ['drying', 'cancelled'],
        'drying' => ['folding', 'cancelled'],
        'folding' => ['ready_for_pickup', 'cancelled'],
        'ready_for_pickup' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'order_number',
        'customer_id',
        'appointment_id',
        'received_by',
        'weight',
        'press_service',
        'service_type',
        'wash_price_per_kg',
        'press_price_per_kg',
        'full_service_addon_per_kg',
        'total_amount',
        'status',
        'due_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'press_service' => 'boolean',
            'full_service_addon_per_kg' => 'decimal:2',
            'wash_price_per_kg' => 'decimal:2',
            'press_price_per_kg' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): string
    {
        $paidAmount = $this->relationLoaded('payments')
            ? $this->getRelation('payments')->sum('amount')
            : $this->payments()->sum('amount');

        return (string) $paidAmount;
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function availableTransitions(): array
    {
        $transitions = self::TRANSITIONS[$this->status] ?? [];

        if ($this->status === 'drying' && $this->service_type === 'partial') {
            return array_values(array_filter($transitions, fn (string $status): bool => $status !== 'folding'));
        }

        return $transitions;
    }
}
