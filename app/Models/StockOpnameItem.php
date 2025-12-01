<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_quantity',
        'physical_quantity',
        'variance',
        'variance_percentage',
        'variance_reason',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'physical_quantity' => 'integer',
        'variance' => 'integer',
        'variance_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function ($item) {
            // Auto-calculate variance
            $item->variance = $item->physical_quantity - $item->system_quantity;

            // Auto-calculate variance percentage
            if ($item->system_quantity > 0) {
                $item->variance_percentage = ($item->variance / $item->system_quantity) * 100;
            } else {
                $item->variance_percentage = 0;
            }
        });
    }

    // Relationships
    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getNeedsReasonAttribute(): bool
    {
        // Require reason if absolute variance percentage > 5%
        return abs($this->variance_percentage) > 5;
    }

    public function getVarianceColorAttribute(): string
    {
        if ($this->variance > 0) {
            return 'green'; // Surplus
        } elseif ($this->variance < 0) {
            return 'red'; // Shortage
        }
        return 'gray'; // No variance
    }

    public function getVarianceValueAttribute(): float
    {
        return $this->variance * ($this->product->purchase_price ?? 0);
    }
}
