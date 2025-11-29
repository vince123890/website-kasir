<?php

namespace App\Repositories;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TenantRepository
{
    /**
     * Get all tenants with pagination, search, and filters
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = Tenant::withCount(['stores', 'users', 'products']);

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filters
        if (isset($filters['subscription_status']) && $filters['subscription_status'] !== '') {
            $query->where('subscription_status', $filters['subscription_status']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get tenant with statistics
     */
    public function getWithStatistics(int $id): ?Tenant
    {
        return Tenant::withCount(['stores', 'users', 'products'])
            ->with(['stores' => function ($query) {
                $query->withCount('users');
            }])
            ->find($id);
    }

    /**
     * Create a new tenant
     */
    public function create(array $data): Tenant
    {
        // Ensure slug is unique
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Check slug uniqueness and add suffix if needed
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Tenant::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        return Tenant::create($data);
    }

    /**
     * Update tenant
     */
    public function update(int $id, array $data): bool
    {
        $tenant = Tenant::findOrFail($id);

        // If slug is being changed, ensure uniqueness
        if (isset($data['slug']) && $data['slug'] !== $tenant->slug) {
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Tenant::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        return $tenant->update($data);
    }

    /**
     * Soft delete tenant
     */
    public function delete(int $id): bool
    {
        $tenant = Tenant::findOrFail($id);
        return $tenant->delete();
    }

    /**
     * Restore soft deleted tenant
     */
    public function restore(int $id): bool
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        return $tenant->restore();
    }

    /**
     * Activate tenant
     */
    public function activate(int $id): bool
    {
        $tenant = Tenant::findOrFail($id);
        return $tenant->update([
            'is_active' => true,
            'activated_at' => now(),
            'deactivated_at' => null,
        ]);
    }

    /**
     * Deactivate tenant
     */
    public function deactivate(int $id): bool
    {
        $tenant = Tenant::findOrFail($id);
        return $tenant->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Check if slug is available
     */
    public function isSlugAvailable(string $slug, ?int $excludeId = null): bool
    {
        $query = Tenant::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Get tenant by slug
     */
    public function getBySlug(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }

    /**
     * Get active tenants count
     */
    public function getActiveCount(): int
    {
        return Tenant::where('is_active', true)->count();
    }

    /**
     * Get tenants by subscription status
     */
    public function getBySubscriptionStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Tenant::where('subscription_status', $status)
            ->withCount(['stores', 'users', 'products'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get expiring trials (within days)
     */
    public function getExpiringTrials(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return Tenant::where('subscription_status', 'trial')
            ->where('trial_ends_at', '<=', now()->addDays($days))
            ->where('trial_ends_at', '>=', now())
            ->get();
    }
}
