<?php

namespace App\Repositories;

use App\Models\StoreSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StoreSessionRepository
{
    public function getAllPaginated(int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = StoreSession::with(['store', 'cashier', 'cashRegister', 'approvedBy'])
            ->withCount('transactions');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('session_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('cashier', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($q) use ($search) {
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

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('session_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('session_date', '<=', $filters['date_to']);
        }

        return $query->latest('session_date')->paginate($perPage);
    }

    public function findById(int $id): ?StoreSession
    {
        return StoreSession::with(['store', 'cashier', 'cashRegister', 'approvedBy', 'transactions'])
            ->withCount('transactions')
            ->find($id);
    }

    public function create(array $data): StoreSession
    {
        return StoreSession::create($data);
    }

    public function update(StoreSession $session, array $data): bool
    {
        return $session->update($data);
    }

    public function delete(StoreSession $session): bool
    {
        return $session->delete();
    }

    public function getOpenSessionForCashier(int $cashierId, int $storeId): ?StoreSession
    {
        return StoreSession::where('cashier_id', $cashierId)
            ->where('store_id', $storeId)
            ->where('status', 'open')
            ->first();
    }

    public function getSessionsForStore(int $storeId, int $perPage = 15): LengthAwarePaginator
    {
        return StoreSession::where('store_id', $storeId)
            ->with(['cashier', 'cashRegister'])
            ->withCount('transactions')
            ->latest('session_date')
            ->paginate($perPage);
    }

    public function getPendingApproval(int $perPage = 15): LengthAwarePaginator
    {
        return StoreSession::where('status', 'pending_approval')
            ->with(['store', 'cashier', 'cashRegister'])
            ->withCount('transactions')
            ->latest('closed_at')
            ->paginate($perPage);
    }

    public function generateSessionNumber(int $storeId): string
    {
        $store = \App\Models\Store::find($storeId);
        $date = now()->format('Ymd');
        $lastSession = StoreSession::where('store_id', $storeId)
            ->whereDate('session_date', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastSession ? (int) substr($lastSession->session_number, -4) + 1 : 1;

        return "SES-{$store->code}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
