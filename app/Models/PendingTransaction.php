<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PendingTransaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'store_id',
        'cashier_id',
        'hold_number',
        'transaction_data',
        'held_at',
    ];

    protected $casts = [
        'transaction_data' => 'array',
        'held_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('pending_transactions.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('held_at', '>=', now()->subDays($days));
    }
}
