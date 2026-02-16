<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'butcher_id',
        'user_id',
        'device_id',
        'device_name',
        'device_model',
        'os_version',
        'app_version',
        'last_active_at',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
            'registered_at' => 'datetime',
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
}
