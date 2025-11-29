<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar_path',
        'is_active',
        'activation_code',
        'activation_code_expires_at',
        'must_change_password',
        'password_expires_at',
        'last_login_at',
        'login_count',
        'last_login_ip',
        'last_login_device',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'activation_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'activation_code_expires_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'created_by_user_id');
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'changed_by_user_id');
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

    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Mutators
     */
    public function setPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }
}
