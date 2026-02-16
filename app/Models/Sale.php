<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'butcher_id',
        'user_id',
        'device_sale_id',
        'sale_number',
        'total_amount',
        'payment_method',
        'sale_date',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'sale_date' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function butcherShop(): BelongsTo
    {
        return $this->belongsTo(ButcherShop::class, 'butcher_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
