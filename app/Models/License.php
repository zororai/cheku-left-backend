<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'butcher_id',
        'plan',
        'status',
        'payment_count',
        'payment_limit',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_count' => 'integer',
            'payment_limit' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function butcherShop(): BelongsTo
    {
        return $this->belongsTo(ButcherShop::class, 'butcher_id');
    }

    public function getRemainingPaymentsAttribute(): int
    {
        return max(0, $this->payment_limit - $this->payment_count);
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked' || $this->payment_count >= $this->payment_limit;
    }

    public function incrementPaymentCount(): void
    {
        $this->increment('payment_count');
        
        if ($this->payment_count >= $this->payment_limit) {
            $this->update(['status' => 'locked']);
        }
    }
}
