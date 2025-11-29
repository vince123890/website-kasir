<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    /**
     * Get all users with pagination, search and filters
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['tenant', 'store', 'roles']);

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if (isset($filters['role']) && $filters['role']) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        // Filter by store
        if (isset($filters['store_id']) && $filters['store_id']) {
            $query->where('store_id', $filters['store_id']);
        }

        // Filter by tenant
        if (isset($filters['tenant_id']) && $filters['tenant_id']) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get users by tenant
     */
    public function getByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['store', 'roles'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get users by store
     */
    public function getByStore(int $storeId, int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['roles'])
            ->where('store_id', $storeId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find user by ID
     */
    public function find(int $id): ?User
    {
        return User::with(['tenant', 'store', 'roles'])->find($id);
    }

    /**
     * Create new user
     */
    public function create(array $data): User
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Generate activation code
        if (!isset($data['activation_code'])) {
            $data['activation_code'] = $this->generateActivationCode();
            $data['activation_code_expires_at'] = now()->addHours(24);
        }

        // Set password expiry (90 days from now)
        if (!isset($data['password_expires_at'])) {
            $data['password_expires_at'] = now()->addDays(90);
        }

        return User::create($data);
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        $user = User::findOrFail($id);

        // Hash password if provided
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
            $data['password_expires_at'] = now()->addDays(90);
        } else {
            unset($data['password']);
        }

        return $user->update($data);
    }

    /**
     * Soft delete user
     */
    public function delete(int $id): bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }

    /**
     * Restore soft deleted user
     */
    public function restore(int $id): bool
    {
        $user = User::withTrashed()->findOrFail($id);
        return $user->restore();
    }

    /**
     * Generate 6-digit activation code
     */
    public function generateActivationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check activation code
     */
    public function checkActivationCode(int $userId, string $code): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        // Check if code matches and not expired
        if ($user->activation_code === $code &&
            $user->activation_code_expires_at &&
            $user->activation_code_expires_at->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Activate user
     */
    public function activate(int $userId): bool
    {
        $user = User::findOrFail($userId);
        return $user->update([
            'email_verified_at' => now(),
            'activation_code' => null,
            'activation_code_expires_at' => null,
        ]);
    }

    /**
     * Force password change
     */
    public function forcePasswordChange(int $userId): bool
    {
        $user = User::findOrFail($userId);
        return $user->update([
            'must_change_password' => true,
        ]);
    }

    /**
     * Track login
     */
    public function trackLogin(int $userId, string $ip, string $device): bool
    {
        $user = User::findOrFail($userId);
        return $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'last_login_device' => $device,
            'login_count' => $user->login_count + 1,
        ]);
    }
}
