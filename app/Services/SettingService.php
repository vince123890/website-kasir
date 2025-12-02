<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use App\Models\ActivityLog;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

class SettingService extends BaseService
{
    public function __construct(
        protected SettingRepository $settingRepository
    ) {
    }

    /**
     * Get setting by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->settingRepository->get($key, $default);
    }

    /**
     * Get all settings grouped by group
     *
     * @return array
     */
    public function getAllGrouped(): array
    {
        return $this->executeTransaction(function () {
            $settings = $this->settingRepository->getAllGrouped();
            return $this->successResponse('Settings retrieved successfully', $settings);
        });
    }

    /**
     * Get settings by group
     *
     * @param string $group
     * @return array
     */
    public function getByGroup(string $group): array
    {
        return $this->executeTransaction(function () use ($group) {
            $settings = $this->settingRepository->getByGroup($group);
            return $this->successResponse("Settings for group '{$group}' retrieved successfully", $settings);
        });
    }

    /**
     * Update settings
     *
     * @param array $settings
     * @param string $group
     * @return array
     */
    public function updateSettings(array $settings, string $group = 'general'): array
    {
        return $this->executeTransaction(function () use ($settings, $group) {
            // Store old values for activity log
            $oldValues = [];
            foreach ($settings as $key => $value) {
                $oldValues[$key] = $this->settingRepository->get($key);
            }

            // Update settings
            $this->settingRepository->updateMultiple($settings);

            // Log activity
            ActivityLog::log(
                'settings_update',
                "Updated {$group} settings",
                null,
                [
                    'group' => $group,
                    'old_values' => $oldValues,
                    'new_values' => $settings,
                ]
            );

            return $this->successResponse('Settings updated successfully');
        });
    }

    /**
     * Initialize default settings
     *
     * @return array
     */
    public function initializeDefaults(): array
    {
        return $this->executeTransaction(function () {
            $this->settingRepository->initializeDefaults();

            ActivityLog::log(
                'settings_initialize',
                'Initialized default settings'
            );

            return $this->successResponse('Default settings initialized successfully');
        });
    }

    /**
     * Test email configuration
     *
     * @param string $toEmail
     * @return array
     */
    public function testEmailConfiguration(string $toEmail): array
    {
        return $this->executeTransaction(function () use ($toEmail) {
            // Get email settings
            $host = $this->settingRepository->get('smtp_host');
            $port = $this->settingRepository->get('smtp_port');
            $username = $this->settingRepository->get('smtp_username');
            $password = $this->settingRepository->get('smtp_password');
            $encryption = $this->settingRepository->get('smtp_encryption');
            $fromAddress = $this->settingRepository->get('mail_from_address');
            $fromName = $this->settingRepository->get('mail_from_name');

            // Validate settings
            if (!$host || !$port || !$username || !$password) {
                throw new Exception('Email settings are not configured properly');
            }

            // Configure mail settings temporarily
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);

            // Send test email
            try {
                Mail::raw('This is a test email from POS System.', function ($message) use ($toEmail, $fromAddress, $fromName) {
                    $message->to($toEmail)
                        ->from($fromAddress, $fromName)
                        ->subject('Test Email - POS System');
                });

                ActivityLog::log(
                    'email_test',
                    "Test email sent to {$toEmail}"
                );

                return $this->successResponse('Test email sent successfully');
            } catch (Exception $e) {
                throw new Exception('Failed to send test email: ' . $e->getMessage());
            }
        });
    }

    /**
     * Create manual backup
     *
     * @return array
     */
    public function createManualBackup(): array
    {
        return $this->executeTransaction(function () {
            try {
                // Run backup command
                Artisan::call('backup:run');

                ActivityLog::log(
                    'backup_create',
                    'Manual backup created'
                );

                return $this->successResponse('Backup created successfully');
            } catch (Exception $e) {
                throw new Exception('Failed to create backup: ' . $e->getMessage());
            }
        });
    }

    /**
     * Get backup history
     *
     * @return array
     */
    public function getBackupHistory(): array
    {
        return $this->executeTransaction(function () {
            $backupPath = storage_path('app/backups');

            if (!is_dir($backupPath)) {
                return $this->successResponse('Backup history retrieved', []);
            }

            $backups = [];
            $files = scandir($backupPath, SCANDIR_SORT_DESCENDING);

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filepath = $backupPath . '/' . $file;

                if (is_file($filepath)) {
                    $backups[] = [
                        'filename' => $file,
                        'size' => filesize($filepath),
                        'size_formatted' => $this->formatBytes(filesize($filepath)),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filepath)),
                        'path' => $filepath,
                    ];
                }
            }

            return $this->successResponse('Backup history retrieved', $backups);
        });
    }

    /**
     * Download backup file
     *
     * @param string $filename
     * @return string
     */
    public function downloadBackup(string $filename): string
    {
        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            throw new Exception('Backup file not found');
        }

        ActivityLog::log(
            'backup_download',
            "Downloaded backup: {$filename}"
        );

        return $backupPath;
    }

    /**
     * Delete backup file
     *
     * @param string $filename
     * @return array
     */
    public function deleteBackup(string $filename): array
    {
        return $this->executeTransaction(function () use ($filename) {
            $backupPath = storage_path('app/backups/' . $filename);

            if (!file_exists($backupPath)) {
                throw new Exception('Backup file not found');
            }

            unlink($backupPath);

            ActivityLog::log(
                'backup_delete',
                "Deleted backup: {$filename}"
            );

            return $this->successResponse('Backup deleted successfully');
        });
    }

    /**
     * Format bytes to human-readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Validate password against policy
     *
     * @param string $password
     * @return array
     */
    public function validatePassword(string $password): array
    {
        $errors = [];

        // Get password policy settings
        $minLength = (int) $this->settingRepository->get('password_min_length', 8);
        $requireUppercase = (bool) $this->settingRepository->get('password_require_uppercase', true);
        $requireNumbers = (bool) $this->settingRepository->get('password_require_numbers', true);
        $requireSymbols = (bool) $this->settingRepository->get('password_require_symbols', false);

        // Check minimum length
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long";
        }

        // Check uppercase
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        // Check numbers
        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        // Check symbols
        if ($requireSymbols && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password),
        ];
    }

    /**
     * Calculate password strength
     *
     * @param string $password
     * @return string
     */
    protected function calculatePasswordStrength(string $password): string
    {
        $strength = 0;

        // Length
        if (strlen($password) >= 8) {
            $strength++;
        }
        if (strlen($password) >= 12) {
            $strength++;
        }

        // Uppercase
        if (preg_match('/[A-Z]/', $password)) {
            $strength++;
        }

        // Lowercase
        if (preg_match('/[a-z]/', $password)) {
            $strength++;
        }

        // Numbers
        if (preg_match('/[0-9]/', $password)) {
            $strength++;
        }

        // Symbols
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $strength++;
        }

        return match (true) {
            $strength <= 2 => 'weak',
            $strength <= 4 => 'medium',
            default => 'strong',
        };
    }
}
