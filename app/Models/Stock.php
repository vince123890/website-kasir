<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'quantity',
        'min_stock',
        'max_stock',
        'last_stock_opname_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'last_stock_opname_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Accessors
     */
    public function getIsLowStockAttribute(): bool
    {
        if ($this->min_stock === null) {
            return false;
        }

        return $this->quantity < $this->min_stock;
    }

    public function getIsOverstockAttribute(): bool
    {
        if ($this->max_stock === null) {
            return false;
        }

        return $this->quantity > $this->max_stock;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->quantity == 0;
    }

    /**
     * Scopes
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity < min_stock')
            ->whereNotNull('min_stock');
    }

    public function scopeOverstock($query)
    {
        return $query->whereRaw('quantity > max_stock')
            ->whereNotNull('max_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
}
