<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'operating_hours',
        'tax_enabled',
        'tax_name',
        'tax_rate',
        'tax_calculation',
        'markup_percentage',
        'rounding_rule',
        'max_discount_per_item',
        'max_discount_per_transaction',
        'discount_requires_approval_above',
        'auto_print_receipt',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'tax_enabled' => 'boolean',
        'tax_rate' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'max_discount_per_item' => 'decimal:2',
        'max_discount_per_transaction' => 'decimal:2',
        'discount_requires_approval_above' => 'decimal:2',
        'auto_print_receipt' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Methods
     */
    public function calculateTax(float $amount): float
    {
        if (!$this->tax_enabled) {
            return 0;
        }

        if ($this->tax_calculation === 'inclusive') {
            // Tax is included in the price
            return $amount - ($amount / (1 + ($this->tax_rate / 100)));
        }

        // Tax is exclusive (added to price)
        return $amount * ($this->tax_rate / 100);
    }

    public function applyRounding(float $amount): float
    {
        if ($this->rounding_rule === 'none') {
            return $amount;
        }

        $roundTo = (int) $this->rounding_rule;

        return round($amount / $roundTo) * $roundTo;
    }

    public function discountRequiresApproval(float $discountPercentage): bool
    {
        return $discountPercentage > $this->discount_requires_approval_above;
    }
}
