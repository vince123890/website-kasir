<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIncoming($query)
    {
        return $query->whereIn('type', ['IN', 'TRANSFER']);
    }

    public function scopeOutgoing($query)
    {
        return $query->where('type', 'OUT');
    }

    public function scopeAdjustments($query)
    {
        return $query->where('type', 'ADJ');
    }

    public function scopeOpname($query)
    {
        return $query->where('type', 'OPNAME');
    }
}
