<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'butcher_id',
        'plan_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function butcherShop(): BelongsTo
    {
        return $this->belongsTo(ButcherShop::class, 'butcher_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
