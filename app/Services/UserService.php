<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create new user
     */
    public function createUser(array $data): User
    {
        DB::beginTransaction();

        try {
            // Create user
            $user = $this->userRepository->create($data);

            // Assign role
            if (isset($data['role'])) {
                $user->assignRole($data['role']);
            }

            // Send activation email if requested
            if (isset($data['send_activation_email']) && $data['send_activation_email']) {
                $this->sendActivationEmail($user->id);
            }

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update user
     */
    public function updateUser(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($id);

            // Update user data
            $updated = $this->userRepository->update($id, $data);

            // Update role if provided
            if (isset($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            DB::commit();

            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete user (soft delete)
     */
    public function deleteUser(int $id): bool
    {
        // Check if user has dependencies (transactions, activities, etc.)
        $user = User::findOrFail($id);

        // You can add checks here for dependencies
        // For now, we'll just soft delete

        return $this->userRepository->delete($id);
    }

    /**
     * Restore deleted user
     */
    public function restoreUser(int $id): bool
    {
        return $this->userRepository->restore($id);
    }

    /**
     * Send activation email
     */
    public function sendActivationEmail(int $userId): bool
    {
        $user = User::findOrFail($userId);

        // Generate new activation code if expired
        if (!$user->activation_code || $user->activation_code_expires_at->isPast()) {
            $user->update([
                'activation_code' => $this->userRepository->generateActivationCode(),
                'activation_code_expires_at' => now()->addHours(24),
            ]);
            $user->refresh();
        }

        // TODO: Send actual email
        // For now, we'll just log it
        \Log::info("Activation code for {$user->email}: {$user->activation_code}");

        return true;
    }

    /**
     * Activate user with code
     */
    public function activateUser(int $userId, string $code): bool
    {
        if ($this->userRepository->checkActivationCode($userId, $code)) {
            return $this->userRepository->activate($userId);
        }

        return false;
    }

    /**
     * Force password change
     */
    public function forcePasswordChange(int $userId): bool
    {
        return $this->userRepository->forcePasswordChange($userId);
    }

    /**
     * Logout all sessions for user
     */
    public function logoutAllSessions(int $userId): bool
    {
        $user = User::findOrFail($userId);

        // Delete all sessions for this user
        DB::table('sessions')
            ->where('user_id', $userId)
            ->delete();

        return true;
    }

    /**
     * Track user login
     */
    public function trackLogin(int $userId, string $ip, string $device): bool
    {
        return $this->userRepository->trackLogin($userId, $ip, $device);
    }

    /**
     * Bulk activate users
     */
    public function bulkActivate(array $userIds): int
    {
        return User::whereIn('id', $userIds)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate users
     */
    public function bulkDeactivate(array $userIds): int
    {
        return User::whereIn('id', $userIds)->update(['is_active' => false]);
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(array $userIds): int
    {
        return User::whereIn('id', $userIds)->delete();
    }
}
