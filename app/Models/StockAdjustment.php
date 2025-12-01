<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'adjustment_number',
        'adjustment_date',
        'product_id',
        'type',
        'quantity',
        'reason',
        'notes',
        'status',
        'created_by_user_id',
        'submitted_by_user_id',
        'submitted_at',
        'approved_by_user_id',
        'approved_at',
        'rejected_by_user_id',
        'rejected_at',
        'rejection_reason',
        'applied_at',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
            'applied' => ['color' => 'green', 'label' => 'Applied'],
            default => ['color' => 'gray', 'label' => ucfirst($this->status)],
        };
    }

    public function getTypeBadgeAttribute(): array
    {
        return match($this->type) {
            'add' => ['color' => 'green', 'label' => 'Add Stock'],
            'reduce' => ['color' => 'red', 'label' => 'Reduce Stock'],
            default => ['color' => 'gray', 'label' => ucfirst($this->type)],
        };
    }

    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'damaged' => 'Damaged',
            'expired' => 'Expired',
            'lost' => 'Lost',
            'found' => 'Found',
            'correction' => 'Correction',
            'other' => 'Other',
            default => ucfirst($this->reason),
        };
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft' && Auth::id() === $this->created_by_user_id;
    }

    public function getCanSubmitAttribute(): bool
    {
        return $this->status === 'draft' && Auth::id() === $this->created_by_user_id;
    }

    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'submitted' && Auth::user()->hasRole('Tenant Owner');
    }

    public function getCanRejectAttribute(): bool
    {
        return $this->status === 'submitted' && Auth::user()->hasRole('Tenant Owner');
    }

    public function getCanApplyAttribute(): bool
    {
        return $this->status === 'approved';
    }
}
