<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class StoreSession extends Model
{
    protected $fillable = [
        'tenant_id',
        'store_id',
        'cash_register_id',
        'cashier_id',
        'session_number',
        'session_date',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'actual_cash',
        'variance',
        'variance_reason',
        'status',
        'opened_at',
        'closed_at',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'variance' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('store_sessions.tenant_id', auth()->user()->tenant_id);
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

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function hasVariance(): bool
    {
        return $this->variance != 0;
    }

    public function needsApproval(): bool
    {
        return $this->status === 'pending_approval';
    }
}
