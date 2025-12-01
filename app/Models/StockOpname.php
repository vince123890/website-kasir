<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class StockOpname extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'opname_number',
        'opname_date',
        'status',
        'total_variance_value',
        'notes',
        'created_by_user_id',
        'submitted_by_user_id',
        'submitted_at',
        'approved_by_user_id',
        'approved_at',
        'rejected_by_user_id',
        'rejected_at',
        'rejection_reason',
        'finalized_at',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'finalized_at' => 'datetime',
        'total_variance_value' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    // Accessors
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'draft' => ['color' => 'gray', 'label' => 'Draft'],
            'submitted' => ['color' => 'yellow', 'label' => 'Submitted'],
            'approved' => ['color' => 'blue', 'label' => 'Approved'],
            'rejected' => ['color' => 'red', 'label' => 'Rejected'],
            'finalized' => ['color' => 'green', 'label' => 'Finalized'],
            default => ['color' => 'gray', 'label' => ucfirst($this->status)],
        };
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanSubmitAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'submitted';
    }

    public function getCanFinalizeAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getTotalVarianceAttribute(): int
    {
        return $this->items->sum('variance');
    }

    public function getItemsWithVarianceCountAttribute(): int
    {
        return $this->items->where('variance', '!=', 0)->count();
    }

    public function getItemsWithShortageCountAttribute(): int
    {
        return $this->items->where('variance', '<', 0)->count();
    }

    public function getItemsWithSurplusCountAttribute(): int
    {
        return $this->items->where('variance', '>', 0)->count();
    }

    // Methods
    public function calculateTotalVariance(): void
    {
        $totalVariance = 0;
        foreach ($this->items as $item) {
            $product = $item->product;
            $varianceValue = $item->variance * ($product->purchase_price ?? 0);
            $totalVariance += $varianceValue;
        }

        $this->total_variance_value = $totalVariance;
        $this->save();
    }
}
