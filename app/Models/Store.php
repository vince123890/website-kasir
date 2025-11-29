<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'slug',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'is_active',
        'timezone',
        'logo_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function storeSettings(): HasOne
    {
        return $this->hasOne(StoreSetting::class);
    }

    /**
     * Accessors
     */
    public function getFullAddressAttribute(): string
    {
        return sprintf(
            '%s, %s, %s %s',
            $this->address,
            $this->city,
            $this->province,
            $this->postal_code
        );
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
