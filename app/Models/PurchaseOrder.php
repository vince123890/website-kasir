<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'supplier_id',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'notes',
        'created_by_user_id',
        'submitted_by_user_id',
        'submitted_at',
        'approved_by_user_id',
        'approved_at',
        'rejected_by_user_id',
        'rejected_at',
        'rejection_reason',
        'received_by_user_id',
        'received_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Get the tenant that owns the purchase order.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the store that owns the purchase order.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the supplier for the purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created the purchase order.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the user who submitted the purchase order.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /**
     * Get the user who approved the purchase order.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Get the user who rejected the purchase order.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    /**
     * Get the user who received the purchase order.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    /**
     * Get the items for the purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'draft' => ['color' => 'gray', 'label' => 'Draft'],
            'submitted' => ['color' => 'yellow', 'label' => 'Submitted'],
            'approved' => ['color' => 'blue', 'label' => 'Approved'],
            'received' => ['color' => 'green', 'label' => 'Received'],
            'cancelled' => ['color' => 'red', 'label' => 'Cancelled'],
            default => ['color' => 'gray', 'label' => ucfirst($this->status)],
        };
    }

    /**
     * Check if PO can be edited.
     */
    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if PO can be submitted.
     */
    public function getCanSubmitAttribute(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if PO can be approved.
     */
    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if PO can be received.
     */
    public function getCanReceiveAttribute(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Calculate total amount.
     */
    public function calculateTotal(): void
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->save();
    }
}
