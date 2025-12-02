<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingRepository extends BaseRepository
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
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
        return Setting::get($key, $default);
    }

    /**
     * Set setting by key
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @return bool
     */
    public function set(string $key, $value, string $type = 'string', string $group = 'general'): bool
    {
        return Setting::set($key, $value, $type, $group);
    }

    /**
     * Get all settings by group
     *
     * @param string $group
     * @return Collection
     */
    public function getByGroup(string $group): Collection
    {
        return Setting::getByGroup($group);
    }

    /**
     * Get all settings grouped by group
     *
     * @return array
     */
    public function getAllGrouped(): array
    {
        $settings = $this->model->all();

        $grouped = [];
        foreach ($settings as $setting) {
            if (!isset($grouped[$setting->group])) {
                $grouped[$setting->group] = [];
            }

            $grouped[$setting->group][$setting->key] = [
                'value' => $setting->value,
                'type' => $setting->type,
                'label' => $setting->label,
                'description' => $setting->description,
            ];
        }

        return $grouped;
    }

    /**
     * Update multiple settings at once
     *
     * @param array $settings
     * @return bool
     */
    public function updateMultiple(array $settings): bool
    {
        foreach ($settings as $key => $value) {
            $setting = $this->model->where('key', $key)->first();

            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        return true;
    }

    /**
     * Initialize default settings
     *
     * @return void
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'POS System',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Application Name',
                'description' => 'The name of your application',
            ],
            [
                'key' => 'default_timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Default Timezone',
                'description' => 'Default timezone for the application',
            ],
            [
                'key' => 'default_locale',
                'value' => 'id',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Default Locale',
                'description' => 'Default language for the application',
            ],
            [
                'key' => 'date_format',
                'value' => 'd/m/Y',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Date Format',
                'description' => 'Date display format',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Time Format',
                'description' => 'Time display format',
            ],

            // Email Settings
            [
                'key' => 'smtp_host',
                'value' => 'smtp.gmail.com',
                'type' => 'string',
                'group' => 'email',
                'label' => 'SMTP Host',
                'description' => 'SMTP server hostname',
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => 'integer',
                'group' => 'email',
                'label' => 'SMTP Port',
                'description' => 'SMTP server port',
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'type' => 'string',
                'group' => 'email',
                'label' => 'SMTP Username',
                'description' => 'SMTP authentication username',
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'type' => 'string',
                'group' => 'email',
                'label' => 'SMTP Password',
                'description' => 'SMTP authentication password',
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'type' => 'string',
                'group' => 'email',
                'label' => 'SMTP Encryption',
                'description' => 'SMTP encryption method (tls/ssl)',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@possystem.com',
                'type' => 'string',
                'group' => 'email',
                'label' => 'From Email Address',
                'description' => 'Default sender email address',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'POS System',
                'type' => 'string',
                'group' => 'email',
                'label' => 'From Name',
                'description' => 'Default sender name',
            ],

            // Notification Settings
            [
                'key' => 'notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Enable Notifications',
                'description' => 'Enable system notifications',
            ],
            [
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Email Notifications',
                'description' => 'Send notifications via email',
            ],
            [
                'key' => 'inapp_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'In-App Notifications',
                'description' => 'Show in-app notifications',
            ],

            // Security Settings
            [
                'key' => 'session_timeout',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Session Timeout (minutes)',
                'description' => 'User session timeout in minutes',
            ],
            [
                'key' => 'password_min_length',
                'value' => '8',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Minimum Password Length',
                'description' => 'Minimum number of characters for passwords',
            ],
            [
                'key' => 'password_require_uppercase',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'security',
                'label' => 'Require Uppercase',
                'description' => 'Password must contain uppercase letters',
            ],
            [
                'key' => 'password_require_numbers',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'security',
                'label' => 'Require Numbers',
                'description' => 'Password must contain numbers',
            ],
            [
                'key' => 'password_require_symbols',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
                'label' => 'Require Symbols',
                'description' => 'Password must contain special symbols',
            ],
            [
                'key' => 'password_expiry_days',
                'value' => '90',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Password Expiry (days)',
                'description' => 'Days until password expires (0 = never)',
            ],
            [
                'key' => 'two_factor_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
                'label' => 'Two-Factor Authentication',
                'description' => 'Enable two-factor authentication system-wide',
            ],

            // Backup Settings
            [
                'key' => 'backup_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'backups',
                'label' => 'Enable Backups',
                'description' => 'Enable automatic backups',
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'string',
                'group' => 'backups',
                'label' => 'Backup Frequency',
                'description' => 'How often to create backups (daily/weekly/monthly)',
            ],
            [
                'key' => 'backup_time',
                'value' => '02:00',
                'type' => 'string',
                'group' => 'backups',
                'label' => 'Backup Time',
                'description' => 'Time to run automatic backups',
            ],
            [
                'key' => 'backup_retention',
                'value' => '30',
                'type' => 'integer',
                'group' => 'backups',
                'label' => 'Retention Period (days)',
                'description' => 'Number of days to keep backups',
            ],
            [
                'key' => 'backup_storage',
                'value' => 'local',
                'type' => 'string',
                'group' => 'backups',
                'label' => 'Storage Location',
                'description' => 'Where to store backups (local/s3/ftp)',
            ],
        ];

        foreach ($defaults as $setting) {
            if (!Setting::has($setting['key'])) {
                Setting::create($setting);
            }
        }
    }

    /**
     * Check if setting exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Setting::has($key);
    }

    /**
     * Remove setting
     *
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool
    {
        return Setting::remove($key);
    }
}
