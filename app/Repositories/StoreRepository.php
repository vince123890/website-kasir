<?php

namespace App\Repositories;

use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StoreRepository
{
    /**
     * Get all stores with optional pagination and filters
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = Store::with(['tenant', 'users'])
            ->withCount(['users', 'products', 'transactions']);

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        // Apply filters
        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'LIKE', "%{$filters['city']}%");
        }

        if (!empty($filters['province'])) {
            $query->where('province', 'LIKE', "%{$filters['province']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get stores by tenant ID
     */
    public function getByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Store::where('tenant_id', $tenantId)
            ->with(['users'])
            ->withCount(['users', 'products', 'transactions'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single store by ID with statistics
     */
    public function getWithStatistics(int $id): ?Store
    {
        return Store::with(['tenant', 'users'])
            ->withCount(['users', 'products', 'transactions'])
            ->find($id);
    }

    /**
     * Get a single store by ID
     */
    public function find(int $id): ?Store
    {
        return Store::find($id);
    }

    /**
     * Create a new store
     */
    public function create(array $data): Store
    {
        // Generate unique code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->generateUniqueCode($data['tenant_id']);
        } else {
            // Ensure code is unique
            $data['code'] = $this->ensureUniqueCode($data['code'], $data['tenant_id']);
        }

        return Store::create($data);
    }

    /**
     * Update a store
     */
    public function update(int $id, array $data): bool
    {
        $store = $this->find($id);

        if (!$store) {
            return false;
        }

        // If code is being updated, ensure uniqueness
        if (isset($data['code']) && $data['code'] !== $store->code) {
            $data['code'] = $this->ensureUniqueCode($data['code'], $store->tenant_id, $id);
        }

        return $store->update($data);
    }

    /**
     * Delete a store (soft delete)
     */
    public function delete(int $id): bool
    {
        $store = $this->find($id);

        if (!$store) {
            return false;
        }

        return $store->delete();
    }

    /**
     * Restore a soft-deleted store
     */
    public function restore(int $id): bool
    {
        $store = Store::withTrashed()->find($id);

        if (!$store) {
            return false;
        }

        return $store->restore();
    }

    /**
     * Update store settings
     */
    public function updateSettings(int $id, array $settings): bool
    {
        $store = $this->find($id);

        if (!$store) {
            return false;
        }

        // Update only settings-related fields
        $allowedSettings = [
            'timezone',
            'currency',
            'tax_rate',
            'tax_included',
            'rounding_method',
            'receipt_header',
            'receipt_footer',
            'operating_hours'
        ];

        $dataToUpdate = array_intersect_key($settings, array_flip($allowedSettings));

        return $store->update($dataToUpdate);
    }

    /**
     * Check if store code is available
     */
    public function isCodeAvailable(string $code, int $tenantId, ?int $excludeId = null): bool
    {
        $query = Store::where('code', $code)
            ->where('tenant_id', $tenantId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Generate a unique store code
     */
    protected function generateUniqueCode(int $tenantId): string
    {
        $prefix = 'STORE';
        $randomNumber = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $code = "{$prefix}-{$randomNumber}";

        // If code exists, try again
        if (!$this->isCodeAvailable($code, $tenantId)) {
            return $this->generateUniqueCode($tenantId);
        }

        return $code;
    }

    /**
     * Ensure code is unique by appending a number if needed
     */
    protected function ensureUniqueCode(string $code, int $tenantId, ?int $excludeId = null): string
    {
        $originalCode = $code;
        $counter = 1;

        while (!$this->isCodeAvailable($code, $tenantId, $excludeId)) {
            $code = "{$originalCode}-{$counter}";
            $counter++;
        }

        return $code;
    }

    /**
     * Get store by code
     */
    public function getByCode(string $code, int $tenantId): ?Store
    {
        return Store::where('code', $code)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Get active stores count
     */
    public function getActiveCount(int $tenantId): int
    {
        return Store::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get all active stores for a tenant
     */
    public function getActiveStores(int $tenantId): Collection
    {
        return Store::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get stores by city
     */
    public function getByCity(string $city, int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Store::where('tenant_id', $tenantId)
            ->where('city', 'LIKE', "%{$city}%")
            ->withCount(['users', 'products', 'transactions'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get stores by province
     */
    public function getByProvince(string $province, int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Store::where('tenant_id', $tenantId)
            ->where('province', 'LIKE', "%{$province}%")
            ->withCount(['users', 'products', 'transactions'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get store statistics
     */
    public function getStatistics(int $id): array
    {
        $store = $this->getWithStatistics($id);

        if (!$store) {
            return [];
        }

        return [
            'total_users' => $store->users_count ?? 0,
            'total_products' => $store->products_count ?? 0,
            'total_transactions' => $store->transactions_count ?? 0,
        ];
    }
}
