<?php

namespace App\Repositories;

use App\Models\UnpackingTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UnpackingRepository
{
    public function __construct(
        protected UnpackingTransaction $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->with(['store', 'sourceProduct', 'resultProduct', 'createdBy'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'sourceProduct', 'resultProduct', 'createdBy'])
            ->orderBy('unpacking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByStore(int $storeId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'sourceProduct', 'resultProduct', 'createdBy'])
            ->where('store_id', $storeId)
            ->orderBy('unpacking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'sourceProduct', 'resultProduct', 'createdBy'])
            ->where('tenant_id', $tenantId)
            ->orderBy('unpacking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?UnpackingTransaction
    {
        return $this->model->with([
            'store',
            'sourceProduct',
            'resultProduct',
            'createdBy',
            'submittedBy',
            'approvedBy',
            'rejectedBy'
        ])->find($id);
    }

    public function create(array $data): UnpackingTransaction
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $unpacking = $this->model->find($id);
        return $unpacking ? $unpacking->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $unpacking = $this->model->find($id);
        return $unpacking ? $unpacking->delete() : false;
    }

    public function generateUnpackingNumber(int $tenantId): string
    {
        $date = now()->format('Ymd');
        $prefix = "UP-{$date}-";

        $lastUnpacking = $this->model
            ->where('tenant_id', $tenantId)
            ->where('unpacking_number', 'like', "{$prefix}%")
            ->orderBy('unpacking_number', 'desc')
            ->first();

        if ($lastUnpacking) {
            $lastNumber = (int) substr($lastUnpacking->unpacking_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }

    public function filterByStatus(int $tenantId, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['store', 'sourceProduct', 'resultProduct', 'createdBy'])
            ->where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('unpacking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function filterByDateRange(int $tenantId, ?string $dateFrom = null, ?string $dateTo = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['store', 'sourceProduct', 'resultProduct', 'createdBy'])
            ->where('tenant_id', $tenantId);

        if ($dateFrom) {
            $query->where('unpacking_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('unpacking_date', '<=', $dateTo);
        }

        return $query->orderBy('unpacking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function search(int $tenantId, string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['store', 'sourceProduct', 'resultProduct', 'createdBy'])
            ->where('tenant_id', $tenantId)
            ->where(function($query) use ($search) {
                $query->where('unpacking_number', 'like', "%{$search}%")
                    ->orWhereHas('sourceProduct', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%");
                    })
                    ->orWhereHas('resultProduct', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%");
                    });
            })
            ->orderBy('unpacking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
