<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'loyalty_points',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'loyalty_points' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('customers.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPhone($query, string $phone)
    {
        return $query->where('phone', 'LIKE', "%{$phone}%");
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'LIKE', "%{$name}%");
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->transactions()->completed()->count();
    }

    public function getTotalSpentAttribute()
    {
        return $this->transactions()->completed()->sum('total_amount');
    }

    public function getLastPurchaseDateAttribute()
    {
        return $this->transactions()->completed()->latest('transaction_date')->first()?->transaction_date;
    }

    public function addLoyaltyPoints(int $points): void
    {
        $this->increment('loyalty_points', $points);
    }

    public function deductLoyaltyPoints(int $points): void
    {
        $this->decrement('loyalty_points', $points);
    }
}
