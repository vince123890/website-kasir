<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantService
{
    protected TenantRepository $tenantRepository;
    protected UserRepository $userRepository;

    public function __construct(TenantRepository $tenantRepository, UserRepository $userRepository)
    {
        $this->tenantRepository = $tenantRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new tenant
     */
    public function createTenant(array $data): Tenant
    {
        DB::beginTransaction();

        try {
            // Generate unique slug if not provided
            if (!isset($data['slug']) || empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Set default values
            if (!isset($data['is_active'])) {
                $data['is_active'] = true;
            }

            if (!isset($data['subscription_status'])) {
                $data['subscription_status'] = 'trial';
            }

            // Set trial end date if trial status
            if ($data['subscription_status'] === 'trial' && !isset($data['trial_ends_at'])) {
                $data['trial_ends_at'] = now()->addDays(30);
            }

            // Create tenant
            $tenant = $this->tenantRepository->create($data);

            // Auto-create owner account if requested
            if (isset($data['auto_create_owner']) && $data['auto_create_owner']) {
                $this->createOwnerAccount($tenant, $data);
            }

            DB::commit();

            // Log activity
            Log::info('Tenant created', ['tenant_id' => $tenant->id, 'name' => $tenant->name]);

            // TODO: Send welcome email to tenant
            // $this->sendWelcomeEmail($tenant);

            return $tenant;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create tenant', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update tenant
     */
    public function updateTenant(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $tenant = Tenant::findOrFail($id);
            $oldStatus = $tenant->is_active;

            $updated = $this->tenantRepository->update($id, $data);

            // If status changed, notify owner
            if (isset($data['is_active']) && $data['is_active'] != $oldStatus) {
                // TODO: Send status change notification
                // $this->notifyStatusChange($tenant, $data['is_active']);
                Log::info('Tenant status changed', [
                    'tenant_id' => $id,
                    'old_status' => $oldStatus,
                    'new_status' => $data['is_active']
                ]);
            }

            DB::commit();

            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update tenant', ['tenant_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete tenant
     */
    public function deleteTenant(int $id): bool
    {
        DB::beginTransaction();

        try {
            $tenant = Tenant::withCount(['stores', 'users'])->findOrFail($id);

            // Check for active subscriptions or data
            if ($tenant->subscription_status === 'active') {
                throw new \Exception('Cannot delete tenant with active subscription. Please cancel subscription first.');
            }

            // Soft delete tenant (cascade will handle stores and users via model events)
            $deleted = $this->tenantRepository->delete($id);

            DB::commit();

            Log::info('Tenant deleted', ['tenant_id' => $id, 'name' => $tenant->name]);

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete tenant', ['tenant_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Restore deleted tenant
     */
    public function restoreTenant(int $id): bool
    {
        DB::beginTransaction();

        try {
            $restored = $this->tenantRepository->restore($id);

            DB::commit();

            Log::info('Tenant restored', ['tenant_id' => $id]);

            return $restored;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to restore tenant', ['tenant_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Activate tenant
     */
    public function activateTenant(int $id, bool $activateStores = false, bool $activateUsers = false): bool
    {
        DB::beginTransaction();

        try {
            // Activate tenant
            $activated = $this->tenantRepository->activate($id);

            $tenant = Tenant::with(['stores', 'users'])->findOrFail($id);

            // Optionally activate all stores
            if ($activateStores && $tenant->stores) {
                foreach ($tenant->stores as $store) {
                    $store->update(['is_active' => true]);
                }
            }

            // Optionally activate all users
            if ($activateUsers && $tenant->users) {
                foreach ($tenant->users as $user) {
                    $user->update(['is_active' => true]);
                }
            }

            DB::commit();

            Log::info('Tenant activated', [
                'tenant_id' => $id,
                'activate_stores' => $activateStores,
                'activate_users' => $activateUsers
            ]);

            // TODO: Send activation email
            // $this->sendActivationEmail($tenant);

            return $activated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to activate tenant', ['tenant_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Deactivate tenant
     */
    public function deactivateTenant(int $id, bool $deactivateStores = false, bool $deactivateUsers = false): bool
    {
        DB::beginTransaction();

        try {
            // Deactivate tenant
            $deactivated = $this->tenantRepository->deactivate($id);

            $tenant = Tenant::with(['stores', 'users'])->findOrFail($id);

            // Optionally deactivate all stores
            if ($deactivateStores && $tenant->stores) {
                foreach ($tenant->stores as $store) {
                    $store->update(['is_active' => false]);
                }
            }

            // Optionally deactivate all users
            if ($deactivateUsers && $tenant->users) {
                foreach ($tenant->users as $user) {
                    $user->update(['is_active' => false]);
                }
            }

            DB::commit();

            Log::info('Tenant deactivated', [
                'tenant_id' => $id,
                'deactivate_stores' => $deactivateStores,
                'deactivate_users' => $deactivateUsers
            ]);

            // TODO: Send deactivation notification
            // $this->sendDeactivationNotification($tenant);

            return $deactivated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to deactivate tenant', ['tenant_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create owner account for tenant
     */
    protected function createOwnerAccount(Tenant $tenant, array $data): User
    {
        $ownerEmail = $data['owner_email'] ?? $tenant->email;
        $ownerName = $data['owner_name'] ?? 'Owner - ' . $tenant->name;

        // Generate random password
        $password = Str::random(12) . '@' . rand(100, 999);

        // Create user
        $userData = [
            'name' => $ownerName,
            'email' => $ownerEmail,
            'password' => $password,
            'tenant_id' => $tenant->id,
            'store_id' => null,
            'is_active' => true,
            'must_change_password' => true,
        ];

        $user = $this->userRepository->create($userData);

        // Assign Tenant Owner role
        $user->assignRole('Tenant Owner');

        Log::info('Tenant owner account created', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'email' => $ownerEmail
        ]);

        // TODO: Send credentials email
        // $this->sendOwnerCredentials($user, $password);

        return $user;
    }

    /**
     * Check if slug is available
     */
    public function isSlugAvailable(string $slug, ?int $excludeId = null): bool
    {
        return $this->tenantRepository->isSlugAvailable($slug, $excludeId);
    }

    /**
     * Get tenant statistics
     */
    public function getTenantStatistics(int $id): array
    {
        $tenant = $this->tenantRepository->getWithStatistics($id);

        if (!$tenant) {
            throw new \Exception('Tenant not found');
        }

        return [
            'total_stores' => $tenant->stores_count,
            'total_users' => $tenant->users_count,
            'total_products' => $tenant->products_count,
            'is_active' => $tenant->is_active,
            'subscription_status' => $tenant->subscription_status,
            'trial_ends_at' => $tenant->trial_ends_at,
            'subscription_ends_at' => $tenant->subscription_ends_at,
        ];
    }
}
