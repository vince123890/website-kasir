<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UnpackingTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'unpacking_number',
        'unpacking_date',
        'source_product_id',
        'source_quantity',
        'result_product_id',
        'result_quantity',
        'conversion_ratio',
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
        'processed_at',
    ];

    protected $casts = [
        'unpacking_date' => 'date',
        'conversion_ratio' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
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

    public function sourceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }

    public function resultProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'result_product_id');
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
            'processed' => ['color' => 'green', 'label' => 'Processed'],
            default => ['color' => 'gray', 'label' => ucfirst($this->status)],
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

    public function getCanProcessAttribute(): bool
    {
        return $this->status === 'approved';
    }
}
