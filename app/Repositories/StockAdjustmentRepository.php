<?php

namespace App\Repositories;

use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StockAdjustmentRepository
{
    public function __construct(
        protected StockAdjustment $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->with(['store', 'product', 'createdBy'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'product', 'createdBy'])
            ->orderBy('adjustment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByStore(int $storeId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'product', 'createdBy'])
            ->where('store_id', $storeId)
            ->orderBy('adjustment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'product', 'createdBy'])
            ->where('tenant_id', $tenantId)
            ->orderBy('adjustment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?StockAdjustment
    {
        return $this->model->with([
            'store',
            'product',
            'createdBy',
            'submittedBy',
            'approvedBy',
            'rejectedBy'
        ])->find($id);
    }

    public function create(array $data): StockAdjustment
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $adjustment = $this->model->find($id);
        return $adjustment ? $adjustment->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $adjustment = $this->model->find($id);
        return $adjustment ? $adjustment->delete() : false;
    }

    public function generateAdjustmentNumber(int $tenantId): string
    {
        $date = now()->format('Ymd');
        $prefix = "SA-{$date}-";

        $lastAdjustment = $this->model
            ->where('tenant_id', $tenantId)
            ->where('adjustment_number', 'like', "{$prefix}%")
            ->orderBy('adjustment_number', 'desc')
            ->first();

        if ($lastAdjustment) {
            $lastNumber = (int) substr($lastAdjustment->adjustment_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }

    public function filterByStatus(int $tenantId, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['store', 'product', 'createdBy'])
            ->where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('adjustment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function filterByType(int $tenantId, ?string $type = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['store', 'product', 'createdBy'])
            ->where('tenant_id', $tenantId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('adjustment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function filterByDateRange(int $tenantId, ?string $dateFrom = null, ?string $dateTo = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['store', 'product', 'createdBy'])
            ->where('tenant_id', $tenantId);

        if ($dateFrom) {
            $query->where('adjustment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('adjustment_date', '<=', $dateTo);
        }

        return $query->orderBy('adjustment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function search(int $tenantId, string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'product', 'createdBy'])
            ->where('tenant_id', $tenantId)
            ->where(function($query) use ($search) {
                $query->where('adjustment_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%");
                    });
            })
            ->orderBy('adjustment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
