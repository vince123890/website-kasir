<?php

namespace App\Repositories;

use App\Models\CashRegister;
use Illuminate\Support\Collection;

class CashRegisterRepository
{
    public function getAll(): Collection
    {
        return CashRegister::with('store')->get();
    }

    public function getActiveForStore(int $storeId): Collection
    {
        return CashRegister::where('store_id', $storeId)
            ->where('is_active', true)
            ->get();
    }

    public function findById(int $id): ?CashRegister
    {
        return CashRegister::with('store')->find($id);
    }

    public function create(array $data): CashRegister
    {
        return CashRegister::create($data);
    }

    public function update(CashRegister $register, array $data): bool
    {
        return $register->update($data);
    }

    public function delete(CashRegister $register): bool
    {
        return $register->delete();
    }

    public function findByCode(int $storeId, string $code): ?CashRegister
    {
        return CashRegister::where('store_id', $storeId)
            ->where('register_code', $code)
            ->first();
    }
}
