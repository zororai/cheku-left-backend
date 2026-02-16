<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration_days',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
        ];
    }

    public function butcherShops(): HasMany
    {
        return $this->hasMany(ButcherShop::class, 'subscription_plan_id');
    }

    public function platformPayments(): HasMany
    {
        return $this->hasMany(PlatformPayment::class);
    }
}
