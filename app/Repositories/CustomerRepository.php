<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerRepository
{
    public function getAllPaginated(int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = Customer::with('tenant')
            ->withCount('transactions');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Customer
    {
        return Customer::with(['tenant', 'transactions' => function ($query) {
            $query->completed()->latest('transaction_date')->limit(10);
        }])
        ->withCount('transactions')
        ->find($id);
    }

    public function findByPhone(string $phone): ?Customer
    {
        return Customer::where('phone', $phone)->first();
    }

    public function searchByPhone(string $phone, int $limit = 5): Collection
    {
        return Customer::where('phone', 'LIKE', "%{$phone}%")
            ->active()
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'email', 'loyalty_points']);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }

    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    public function getTransactionHistory(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        $customer = Customer::find($customerId);

        return $customer->transactions()
            ->with(['items.product', 'cashier', 'store'])
            ->latest('transaction_date')
            ->paginate($perPage);
    }

    public function getCustomerStats(int $customerId): array
    {
        $customer = Customer::withCount('transactions')
            ->withSum('transactions as total_spent', 'total_amount')
            ->find($customerId);

        $avgTransaction = $customer->transactions_count > 0
            ? $customer->total_spent / $customer->transactions_count
            : 0;

        return [
            'total_purchases' => $customer->transactions_count,
            'total_spent' => $customer->total_spent ?? 0,
            'average_transaction' => $avgTransaction,
            'loyalty_points' => $customer->loyalty_points,
        ];
    }
}
