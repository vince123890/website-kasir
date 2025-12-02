<?php

namespace App\Repositories;

use App\Models\PendingTransaction;
use Illuminate\Support\Collection;

class PendingTransactionRepository
{
    public function getAllForStore(int $storeId): Collection
    {
        return PendingTransaction::where('store_id', $storeId)
            ->with('cashier')
            ->orderBy('held_at', 'desc')
            ->get();
    }

    public function getAllForCashier(int $cashierId, int $storeId): Collection
    {
        return PendingTransaction::where('cashier_id', $cashierId)
            ->where('store_id', $storeId)
            ->orderBy('held_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?PendingTransaction
    {
        return PendingTransaction::with(['cashier', 'store'])->find($id);
    }

    public function create(array $data): PendingTransaction
    {
        return PendingTransaction::create($data);
    }

    public function delete(PendingTransaction $pendingTransaction): bool
    {
        return $pendingTransaction->delete();
    }

    public function generateHoldNumber(int $storeId): string
    {
        $store = \App\Models\Store::find($storeId);
        $date = now()->format('Ymd');
        $lastHold = PendingTransaction::where('store_id', $storeId)
            ->whereDate('held_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastHold ? (int) substr($lastHold->hold_number, -4) + 1 : 1;

        return "HOLD-{$store->code}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function deleteOldPendingTransactions(int $days = 30): int
    {
        return PendingTransaction::where('held_at', '<', now()->subDays($days))->delete();
    }
}
