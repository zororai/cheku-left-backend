<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ButcherShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'owner_id',
        'api_key',
        'subscription_plan_id',
        'subscription_start_date',
        'subscription_end_date',
        'subscription_status',
    ];

    protected function casts(): array
    {
        return [
            'subscription_start_date' => 'date',
            'subscription_end_date' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'subscription_plan_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'butcher_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'butcher_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'butcher_id');
    }

    public function platformPayments(): HasMany
    {
        return $this->hasMany(PlatformPayment::class, 'butcher_id');
    }

    public function generateApiKey(): string
    {
        $this->api_key = Str::random(64);
        $this->save();

        return $this->api_key;
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_status !== 'active') {
            return false;
        }

        if ($this->subscription_end_date && $this->subscription_end_date->isPast()) {
            return false;
        }

        return true;
    }

    public function activateSubscription(Plan $plan): void
    {
        $this->subscription_plan_id = $plan->id;
        $this->subscription_start_date = now();
        $this->subscription_end_date = now()->addDays($plan->duration_days);
        $this->subscription_status = 'active';
        $this->save();
    }

    public function suspendSubscription(): void
    {
        $this->subscription_status = 'suspended';
        $this->save();
    }

    public function expireSubscription(): void
    {
        $this->subscription_status = 'expired';
        $this->save();
    }
}
