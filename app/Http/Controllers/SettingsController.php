<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Request;
use Exception;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {
        $this->middleware('auth');
        $this->middleware('role:Administrator SaaS');
    }

    /**
     * Display system settings
     */
    public function index(Request $request)
    {
        try {
            $result = $this->settingService->getAllGrouped();

            $activeTab = $request->get('tab', 'general');

            return view('admin.settings.index', [
                'settings' => $result['data'],
                'activeTab' => $activeTab,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        try {
            $request->validate([
                'app_name' => 'required|string|max:255',
                'default_timezone' => 'required|string',
                'default_locale' => 'required|string',
                'date_format' => 'required|string',
                'time_format' => 'required|string',
            ]);

            $settings = $request->only([
                'app_name',
                'default_timezone',
                'default_locale',
                'date_format',
                'time_format',
            ]);

            $this->settingService->updateSettings($settings, 'general');

            return redirect()->route('admin.settings.index', ['tab' => 'general'])
                ->with('success', 'General settings updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        try {
            $request->validate([
                'smtp_host' => 'required|string',
                'smtp_port' => 'required|integer',
                'smtp_username' => 'required|string',
                'smtp_password' => 'nullable|string',
                'smtp_encryption' => 'required|in:tls,ssl',
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string',
            ]);

            $settings = $request->only([
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_encryption',
                'mail_from_address',
                'mail_from_name',
            ]);

            // Only update password if provided
            if ($request->filled('smtp_password')) {
                $settings['smtp_password'] = $request->smtp_password;
            }

            $this->settingService->updateSettings($settings, 'email');

            return redirect()->route('admin.settings.index', ['tab' => 'email'])
                ->with('success', 'Email settings updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        try {
            $request->validate([
                'test_email' => 'required|email',
            ]);

            $this->settingService->testEmailConfiguration($request->test_email);

            return back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        try {
            $settings = [
                'notifications_enabled' => $request->has('notifications_enabled') ? '1' : '0',
                'email_notifications' => $request->has('email_notifications') ? '1' : '0',
                'inapp_notifications' => $request->has('inapp_notifications') ? '1' : '0',
            ];

            $this->settingService->updateSettings($settings, 'notifications');

            return redirect()->route('admin.settings.index', ['tab' => 'notifications'])
                ->with('success', 'Notification settings updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request)
    {
        try {
            $request->validate([
                'session_timeout' => 'required|integer|min:5|max:1440',
                'password_min_length' => 'required|integer|min:6|max:50',
                'password_expiry_days' => 'required|integer|min:0|max:365',
            ]);

            $settings = [
                'session_timeout' => $request->session_timeout,
                'password_min_length' => $request->password_min_length,
                'password_require_uppercase' => $request->has('password_require_uppercase') ? '1' : '0',
                'password_require_numbers' => $request->has('password_require_numbers') ? '1' : '0',
                'password_require_symbols' => $request->has('password_require_symbols') ? '1' : '0',
                'password_expiry_days' => $request->password_expiry_days,
                'two_factor_enabled' => $request->has('two_factor_enabled') ? '1' : '0',
            ];

            $this->settingService->updateSettings($settings, 'security');

            return redirect()->route('admin.settings.index', ['tab' => 'security'])
                ->with('success', 'Security settings updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Update backup settings
     */
    public function updateBackups(Request $request)
    {
        try {
            $request->validate([
                'backup_frequency' => 'required|in:daily,weekly,monthly',
                'backup_time' => 'required|string',
                'backup_retention' => 'required|integer|min:1|max:365',
                'backup_storage' => 'required|in:local,s3,ftp',
            ]);

            $settings = [
                'backup_enabled' => $request->has('backup_enabled') ? '1' : '0',
                'backup_frequency' => $request->backup_frequency,
                'backup_time' => $request->backup_time,
                'backup_retention' => $request->backup_retention,
                'backup_storage' => $request->backup_storage,
            ];

            $this->settingService->updateSettings($settings, 'backups');

            return redirect()->route('admin.settings.index', ['tab' => 'backups'])
                ->with('success', 'Backup settings updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Create manual backup
     */
    public function createBackup()
    {
        try {
            $this->settingService->createManualBackup();

            return back()->with('success', 'Backup created successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get backup history
     */
    public function backupHistory()
    {
        try {
            $result = $this->settingService->getBackupHistory();

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download backup
     */
    public function downloadBackup(Request $request)
    {
        try {
            $request->validate([
                'filename' => 'required|string',
            ]);

            $filepath = $this->settingService->downloadBackup($request->filename);

            return response()->download($filepath);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete backup
     */
    public function deleteBackup(Request $request)
    {
        try {
            $request->validate([
                'filename' => 'required|string',
            ]);

            $this->settingService->deleteBackup($request->filename);

            return back()->with('success', 'Backup deleted successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults()
    {
        try {
            $this->settingService->initializeDefaults();

            return back()->with('success', 'Default settings initialized successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
