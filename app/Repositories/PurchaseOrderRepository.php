<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PurchaseOrderRepository
{
    /**
     * Get purchase orders by store with pagination.
     */
    public function getByStore(int $storeId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = PurchaseOrder::with(['supplier', 'createdBy', 'items'])
            ->where('store_id', $storeId);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'order_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get purchase orders by tenant with pagination.
     */
    public function getByTenant(int $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = PurchaseOrder::with(['supplier', 'store', 'createdBy', 'items'])
            ->where('tenant_id', $tenantId);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by store
        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'order_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get pending purchase orders for a store.
     */
    public function getPending(int $storeId): Collection
    {
        return PurchaseOrder::with(['supplier', 'items'])
            ->where('store_id', $storeId)
            ->where('status', 'submitted')
            ->orderBy('order_date', 'desc')
            ->get();
    }

    /**
     * Find purchase order by ID.
     */
    public function find(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::with([
            'supplier',
            'store',
            'items.product',
            'createdBy',
            'submittedBy',
            'approvedBy',
            'rejectedBy',
            'receivedBy'
        ])->find($id);
    }

    /**
     * Create new purchase order.
     */
    public function create(array $data): PurchaseOrder
    {
        return PurchaseOrder::create($data);
    }

    /**
     * Update purchase order.
     */
    public function update(int $id, array $data): bool
    {
        $purchaseOrder = PurchaseOrder::find($id);
        return $purchaseOrder ? $purchaseOrder->update($data) : false;
    }

    /**
     * Delete purchase order.
     */
    public function delete(int $id): bool
    {
        $purchaseOrder = PurchaseOrder::find($id);
        return $purchaseOrder ? $purchaseOrder->delete() : false;
    }

    /**
     * Generate unique PO number.
     */
    public function generatePONumber(int $tenantId): string
    {
        $date = now()->format('Ymd');
        $prefix = "PO-{$date}-";

        $lastPO = PurchaseOrder::where('tenant_id', $tenantId)
            ->where('po_number', 'like', "{$prefix}%")
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPO) {
            $lastNumber = (int) substr($lastPO->po_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }
}
