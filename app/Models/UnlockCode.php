<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnlockCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'butcher_id',
        'additional_payments',
        'is_used',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'additional_payments' => 'integer',
            'is_used' => 'boolean',
            'used_at' => 'datetime',
        ];
    }

    public function butcherShop(): BelongsTo
    {
        return $this->belongsTo(ButcherShop::class, 'butcher_id');
    }

    public function markAsUsed(int $butcherId): void
    {
        $this->update([
            'butcher_id' => $butcherId,
            'is_used' => true,
            'used_at' => now(),
        ]);
    }
}
