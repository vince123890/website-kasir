<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'is_active',
        'activated_at',
        'deactivated_at',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Relationships
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Accessors
     */
    public function getIsActiveAttribute($value): bool
    {
        return (bool) $value;
    }

    public function getIsTrialActiveAttribute(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTrial($query)
    {
        return $query->where('subscription_status', 'trial');
    }

    public function scopeSubscribed($query)
    {
        return $query->where('subscription_status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('subscription_status', 'expired');
    }
}
