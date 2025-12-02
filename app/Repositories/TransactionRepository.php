<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TransactionRepository
{
    public function getAllPaginated(int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::with(['store', 'cashier', 'storeSession', 'items.product'])
            ->withCount('items');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                    ->orWhereHas('cashier', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (!empty($filters['cashier_id'])) {
            $query->where('cashier_id', $filters['cashier_id']);
        }

        if (!empty($filters['session_id'])) {
            $query->where('store_session_id', $filters['session_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        return $query->latest('transaction_date')->latest('id')->paginate($perPage);
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::with(['store', 'cashier', 'storeSession', 'items.product', 'payments', 'voidedBy'])
            ->find($id);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(Transaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function delete(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    public function generateTransactionNumber(int $storeId): string
    {
        $store = \App\Models\Store::find($storeId);
        $date = now()->format('Ymd');
        $lastTransaction = Transaction::where('store_id', $storeId)
            ->whereDate('transaction_date', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? (int) substr($lastTransaction->transaction_number, -4) + 1 : 1;

        return "TRX-{$store->code}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getForSession(int $sessionId): Collection
    {
        return Transaction::where('store_session_id', $sessionId)
            ->with(['items.product', 'payments'])
            ->get();
    }

    public function getTotalSalesBySession(int $sessionId): array
    {
        $transactions = $this->getForSession($sessionId);

        return [
            'total_transactions' => $transactions->count(),
            'total_sales' => $transactions->where('status', 'completed')->sum('total_amount'),
            'cash_sales' => $transactions->where('status', 'completed')
                ->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $transactions->where('status', 'completed')
                ->where('payment_method', 'card')->sum('total_amount'),
            'transfer_sales' => $transactions->where('status', 'completed')
                ->where('payment_method', 'transfer')->sum('total_amount'),
            'ewallet_sales' => $transactions->where('status', 'completed')
                ->where('payment_method', 'ewallet')->sum('total_amount'),
            'split_sales' => $transactions->where('status', 'completed')
                ->where('payment_method', 'split')->sum('total_amount'),
            'voided_count' => $transactions->where('status', 'voided')->count(),
            'voided_amount' => $transactions->where('status', 'voided')->sum('total_amount'),
        ];
    }

    public function getSalesReport(int $storeId, string $startDate, string $endDate): array
    {
        $transactions = Transaction::where('store_id', $storeId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->with(['items.product'])
            ->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_sales' => $transactions->sum('total_amount'),
            'total_items_sold' => $transactions->sum(function ($t) {
                return $t->items->sum('quantity');
            }),
            'average_transaction' => $transactions->count() > 0 ? $transactions->avg('total_amount') : 0,
        ];
    }
}
