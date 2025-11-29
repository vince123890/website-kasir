<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'unit',
        'purchase_price',
        'selling_price',
        'min_stock',
        'max_stock',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function storeSpecificPrices(): HasMany
    {
        return $this->hasMany(ProductStorePrice::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Accessors
     */
    public function getStockStatusAttribute(): string
    {
        $totalStock = $this->stocks->sum('quantity');

        if ($totalStock == 0) {
            return 'out_of_stock';
        } elseif ($totalStock < $this->min_stock) {
            return 'low_stock';
        } elseif ($totalStock > $this->max_stock) {
            return 'overstock';
        }

        return 'normal';
    }

    public function getTotalStockValueAttribute(): float
    {
        return $this->stocks->sum('quantity') * $this->purchase_price;
    }

    /**
     * Methods
     */
    public function getStockByStore(int $storeId): ?Stock
    {
        return $this->stocks()->where('store_id', $storeId)->first();
    }

    public function getPriceByStore(int $storeId): float
    {
        $storePrice = $this->storeSpecificPrices()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->first();

        return $storePrice ? $storePrice->price : $this->selling_price;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('stocks', function ($q) {
            $q->whereRaw('quantity < min_stock');
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereHas('stocks', function ($q) {
            $q->where('quantity', 0);
        });
    }
}
