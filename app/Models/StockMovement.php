<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'product_id',
        'product_name',
        'opening_grams',
        'sold_grams',
        'closing_grams',
        'expected_closing_grams',
        'variance_grams',
    ];

    protected function casts(): array
    {
        return [
            'opening_grams' => 'integer',
            'sold_grams' => 'integer',
            'closing_grams' => 'integer',
            'expected_closing_grams' => 'integer',
            'variance_grams' => 'integer',
        ];
    }

    public function stockSession(): BelongsTo
    {
        return $this->belongsTo(StockSession::class, 'session_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
