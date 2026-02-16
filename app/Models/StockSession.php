<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'butcher_id',
        'user_id',
        'local_session_id',
        'status',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'local_session_id' => 'integer',
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

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'session_id');
    }

    public function getTotalOpeningGramsAttribute(): int
    {
        return $this->stockMovements->sum('opening_grams');
    }

    public function getTotalSoldGramsAttribute(): int
    {
        return $this->stockMovements->sum('sold_grams');
    }

    public function getTotalClosingGramsAttribute(): int
    {
        return $this->stockMovements->sum('closing_grams') ?? 0;
    }

    public function getTotalVarianceGramsAttribute(): int
    {
        return $this->stockMovements->sum('variance_grams') ?? 0;
    }
}
