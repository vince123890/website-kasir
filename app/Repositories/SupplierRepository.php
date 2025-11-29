<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SupplierRepository
{
    /**
     * Get suppliers by tenant with search, filters, and pagination
     */
    public function getByTenant(
        int $tenantId,
        int $perPage = 15,
        ?string $search = null,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Supplier::where('tenant_id', $tenantId)
            ->withCount('purchaseOrders');

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Filter by city
        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get supplier with statistics
     */
    public function getWithStatistics(int $id): ?Supplier
    {
        return Supplier::with(['purchaseOrders' => function ($query) {
            $query->latest()->limit(10);
        }])
        ->withCount('purchaseOrders')
        ->withSum('purchaseOrders as total_purchases', 'total_amount')
        ->find($id);
    }

    /**
     * Get all active suppliers for dropdown
     */
    public function getAllActive(int $tenantId): Collection
    {
        return Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'payment_terms']);
    }

    /**
     * Create a new supplier
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Update a supplier
     */
    public function update(int $id, array $data): bool
    {
        $supplier = $this->find($id);
        if (!$supplier) {
            return false;
        }

        return $supplier->update($data);
    }

    /**
     * Delete a supplier (soft delete)
     */
    public function delete(int $id): bool
    {
        $supplier = $this->find($id);
        if (!$supplier) {
            return false;
        }

        return $supplier->delete();
    }

    /**
     * Find a supplier by ID
     */
    public function find(int $id): ?Supplier
    {
        return Supplier::find($id);
    }

    /**
     * Check if code is duplicate
     */
    public function checkDuplicateCode(string $code, int $tenantId, ?int $excludeId = null): bool
    {
        $query = Supplier::where('code', $code)
            ->where('tenant_id', $tenantId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Generate unique supplier code
     */
    public function generateCode(int $tenantId): string
    {
        // Get last supplier code for tenant
        $lastSupplier = Supplier::where('tenant_id', $tenantId)
            ->where('code', 'like', 'SUP-%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastSupplier) {
            // Extract number from last code (SUP-00001)
            $lastNumber = intval(substr($lastSupplier->code, 4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'SUP-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get purchase history for a supplier
     */
    public function getPurchaseHistory(int $supplierId): Collection
    {
        $supplier = $this->find($supplierId);
        if (!$supplier) {
            return collect([]);
        }

        return $supplier->purchaseOrders()
            ->with(['store', 'createdBy'])
            ->latest()
            ->get();
    }

    /**
     * Get all suppliers for export
     */
    public function getAllForExport(int $tenantId, array $filters = []): Collection
    {
        $query = Supplier::where('tenant_id', $tenantId)
            ->withCount('purchaseOrders')
            ->withSum('purchaseOrders as total_purchases', 'total_amount');

        // Apply search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        // Apply filters
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Check if supplier has active or pending purchase orders
     */
    public function hasActivePurchaseOrders(int $supplierId): bool
    {
        $supplier = $this->find($supplierId);
        if (!$supplier) {
            return false;
        }

        return $supplier->purchaseOrders()
            ->whereIn('status', ['pending', 'approved', 'partial'])
            ->exists();
    }

    /**
     * Get supplier statistics
     */
    public function getStatistics(int $supplierId): array
    {
        $supplier = $this->getWithStatistics($supplierId);
        if (!$supplier) {
            return [];
        }

        return [
            'total_purchase_orders' => $supplier->purchase_orders_count ?? 0,
            'total_purchases' => $supplier->total_purchases ?? 0,
            'average_order_value' => $supplier->purchase_orders_count > 0
                ? ($supplier->total_purchases ?? 0) / $supplier->purchase_orders_count
                : 0,
        ];
    }
}
