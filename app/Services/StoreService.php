<?php

namespace App\Services;

use App\Models\Store;
use App\Repositories\StoreRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StoreService
{
    protected StoreRepository $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Create a new store
     */
    public function createStore(array $data): Store
    {
        DB::beginTransaction();

        try {
            // Generate unique code if not provided
            if (empty($data['code'])) {
                $data['code'] = $this->generateStoreCode($data['tenant_id']);
            }

            // Set default values
            $data['is_active'] = $data['is_active'] ?? true;
            $data['timezone'] = $data['timezone'] ?? 'Asia/Jakarta';
            $data['currency'] = $data['currency'] ?? 'IDR';

            // Create the store
            $store = $this->storeRepository->create($data);

            Log::info('Store created', [
                'store_id' => $store->id,
                'store_code' => $store->code,
                'tenant_id' => $store->tenant_id,
            ]);

            DB::commit();

            return $store;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create store', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Update a store
     */
    public function updateStore(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->storeRepository->update($id, $data);

            if ($result) {
                Log::info('Store updated', [
                    'store_id' => $id,
                    'updated_fields' => array_keys($data),
                ]);
            }

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update store', [
                'store_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a store
     */
    public function deleteStore(int $id): bool
    {
        DB::beginTransaction();

        try {
            $store = $this->storeRepository->find($id);

            if (!$store) {
                return false;
            }

            // Check if store has active transactions
            if ($store->transactions()->count() > 0) {
                throw new Exception('Cannot delete store with existing transactions. Please deactivate instead.');
            }

            // Soft delete the store
            $result = $this->storeRepository->delete($id);

            if ($result) {
                // Deactivate all users associated with this store
                $store->users()->update(['is_active' => false]);

                Log::info('Store deleted', [
                    'store_id' => $id,
                    'store_code' => $store->code,
                ]);
            }

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete store', [
                'store_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore a soft-deleted store
     */
    public function restoreStore(int $id): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->storeRepository->restore($id);

            if ($result) {
                Log::info('Store restored', [
                    'store_id' => $id,
                ]);
            }

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to restore store', [
                'store_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update store settings
     */
    public function updateStoreSettings(int $id, array $settings): bool
    {
        DB::beginTransaction();

        try {
            // Process operating hours if provided
            if (isset($settings['operating_hours']) && is_array($settings['operating_hours'])) {
                $settings['operating_hours'] = json_encode($settings['operating_hours']);
            }

            $result = $this->storeRepository->updateSettings($id, $settings);

            if ($result) {
                Log::info('Store settings updated', [
                    'store_id' => $id,
                    'settings' => array_keys($settings),
                ]);
            }

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update store settings', [
                'store_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Activate a store
     */
    public function activateStore(int $id, bool $activateUsers = false): bool
    {
        DB::beginTransaction();

        try {
            $store = $this->storeRepository->find($id);

            if (!$store) {
                return false;
            }

            // Activate the store
            $store->update([
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Optionally activate all users
            if ($activateUsers) {
                $store->users()->update(['is_active' => true]);
            }

            Log::info('Store activated', [
                'store_id' => $id,
                'activate_users' => $activateUsers,
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to activate store', [
                'store_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Deactivate a store
     */
    public function deactivateStore(int $id, bool $deactivateUsers = false): bool
    {
        DB::beginTransaction();

        try {
            $store = $this->storeRepository->find($id);

            if (!$store) {
                return false;
            }

            // Deactivate the store
            $store->update([
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            // Optionally deactivate all users
            if ($deactivateUsers) {
                $store->users()->update(['is_active' => false]);
            }

            Log::info('Store deactivated', [
                'store_id' => $id,
                'deactivate_users' => $deactivateUsers,
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to deactivate store', [
                'store_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if store code is available
     */
    public function isCodeAvailable(string $code, int $tenantId, ?int $excludeId = null): bool
    {
        return $this->storeRepository->isCodeAvailable($code, $tenantId, $excludeId);
    }

    /**
     * Generate a unique store code
     */
    protected function generateStoreCode(int $tenantId): string
    {
        return $this->storeRepository->generateUniqueCode($tenantId);
    }

    /**
     * Get store statistics
     */
    public function getStoreStatistics(int $id): array
    {
        return $this->storeRepository->getStatistics($id);
    }

    /**
     * Calculate tax amount based on store settings
     */
    public function calculateTax(int $storeId, float $amount): array
    {
        $store = $this->storeRepository->find($storeId);

        if (!$store) {
            return [
                'subtotal' => $amount,
                'tax' => 0,
                'total' => $amount,
            ];
        }

        $taxRate = $store->tax_rate ?? 0;
        $taxIncluded = $store->tax_included ?? false;

        if ($taxIncluded) {
            // Tax is included in the price
            $total = $amount;
            $subtotal = $total / (1 + ($taxRate / 100));
            $tax = $total - $subtotal;
        } else {
            // Tax is added to the price
            $subtotal = $amount;
            $tax = $subtotal * ($taxRate / 100);
            $total = $subtotal + $tax;
        }

        // Apply rounding if configured
        if ($store->rounding_method) {
            $total = $this->applyRounding($total, $store->rounding_method);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'tax_rate' => $taxRate,
            'total' => round($total, 2),
            'tax_included' => $taxIncluded,
        ];
    }

    /**
     * Apply rounding method to amount
     */
    protected function applyRounding(float $amount, string $method): float
    {
        switch ($method) {
            case 'round_up':
                return ceil($amount);
            case 'round_down':
                return floor($amount);
            case 'round_nearest':
                return round($amount);
            case 'round_nearest_5':
                return round($amount / 5) * 5;
            case 'round_nearest_10':
                return round($amount / 10) * 10;
            case 'round_nearest_100':
                return round($amount / 100) * 100;
            case 'round_nearest_1000':
                return round($amount / 1000) * 1000;
            default:
                return $amount;
        }
    }

    /**
     * Upload store logo
     */
    public function uploadLogo(int $storeId, $logoFile): ?string
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if (!$store) {
                return null;
            }

            // Delete old logo if exists
            if ($store->logo && \Storage::disk('public')->exists($store->logo)) {
                \Storage::disk('public')->delete($store->logo);
            }

            // Store new logo
            $path = $logoFile->store('stores/logos', 'public');

            // Update store with new logo path
            $store->update(['logo' => $path]);

            Log::info('Store logo uploaded', [
                'store_id' => $storeId,
                'logo_path' => $path,
            ]);

            return $path;
        } catch (Exception $e) {
            Log::error('Failed to upload store logo', [
                'store_id' => $storeId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete store logo
     */
    public function deleteLogo(int $storeId): bool
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if (!$store || !$store->logo) {
                return false;
            }

            // Delete logo file
            if (\Storage::disk('public')->exists($store->logo)) {
                \Storage::disk('public')->delete($store->logo);
            }

            // Update store
            $store->update(['logo' => null]);

            Log::info('Store logo deleted', [
                'store_id' => $storeId,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete store logo', [
                'store_id' => $storeId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
