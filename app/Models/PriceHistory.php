<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'store_id',
        'old_price',
        'new_price',
        'changed_by_user_id',
        'changed_at',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'changed_at' => 'datetime',
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

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    /**
     * Accessors
     */
    public function getPriceChangeAttribute(): float
    {
        return $this->new_price - $this->old_price;
    }

    public function getPriceChangePercentageAttribute(): float
    {
        if ($this->old_price == 0) {
            return 0;
        }

        return (($this->new_price - $this->old_price) / $this->old_price) * 100;
    }

    /**
     * Scopes
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeTenantLevel($query)
    {
        return $query->whereNull('store_id');
    }
}
