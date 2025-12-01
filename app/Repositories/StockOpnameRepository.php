<?php

namespace App\Repositories;

use App\Models\StockOpname;
use App\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockOpnameRepository
{
    public function getByStore(int $storeId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = StockOpname::where('store_id', $storeId)
            ->with(['store', 'createdBy', 'submittedBy', 'approvedBy']);

        // Search
        if (!empty($filters['search'])) {
            $query->where('opname_number', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('opname_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('opname_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('opname_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByTenant(int $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = StockOpname::where('tenant_id', $tenantId)
            ->with(['store', 'createdBy', 'submittedBy', 'approvedBy']);

        // Search
        if (!empty($filters['search'])) {
            $query->where('opname_number', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by store
        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('opname_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('opname_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('opname_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPending(int $storeId)
    {
        return StockOpname::where('store_id', $storeId)
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    public function find(int $id): ?StockOpname
    {
        return StockOpname::with([
            'items.product.category',
            'store',
            'createdBy',
            'submittedBy',
            'approvedBy',
            'rejectedBy'
        ])->find($id);
    }

    public function create(array $data): StockOpname
    {
        return StockOpname::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $stockOpname = StockOpname::find($id);
        if (!$stockOpname) {
            return false;
        }

        return $stockOpname->update($data);
    }

    public function delete(int $id): bool
    {
        $stockOpname = StockOpname::find($id);
        if (!$stockOpname) {
            return false;
        }

        return $stockOpname->delete();
    }

    public function generateOpnameNumber(int $tenantId): string
    {
        $date = now()->format('Ymd');
        $prefix = "SO-{$date}-";

        $lastOpname = StockOpname::where('tenant_id', $tenantId)
            ->where('opname_number', 'like', "{$prefix}%")
            ->orderBy('opname_number', 'desc')
            ->first();

        if ($lastOpname) {
            $lastNumber = (int) substr($lastOpname->opname_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }

    public function getCurrentStockForStore(int $storeId): array
    {
        $stocks = Stock::where('store_id', $storeId)
            ->with('product')
            ->get();

        $result = [];
        foreach ($stocks as $stock) {
            $result[] = [
                'product_id' => $stock->product_id,
                'product_name' => $stock->product->name,
                'sku' => $stock->product->sku,
                'system_quantity' => $stock->quantity,
                'physical_quantity' => 0, // To be filled by user
            ];
        }

        return $result;
    }
}
