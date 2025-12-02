<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <a href="{{ route('admin.settings.index', ['tab' => 'general']) }}"
                           class="{{ $activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            General
                        </a>
                        <a href="{{ route('admin.settings.index', ['tab' => 'email']) }}"
                           class="{{ $activeTab === 'email' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Email
                        </a>
                        <a href="{{ route('admin.settings.index', ['tab' => 'notifications']) }}"
                           class="{{ $activeTab === 'notifications' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Notifications
                        </a>
                        <a href="{{ route('admin.settings.index', ['tab' => 'security']) }}"
                           class="{{ $activeTab === 'security' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Security
                        </a>
                        <a href="{{ route('admin.settings.index', ['tab' => 'backups']) }}"
                           class="{{ $activeTab === 'backups' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Backups
                        </a>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    @if ($activeTab === 'general')
                        <!-- General Settings Tab -->
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>

                            <form method="POST" action="{{ route('admin.settings.updateGeneral') }}">
                                @csrf

                                <div class="space-y-6">
                                    <div>
                                        <label for="app_name" class="block text-sm font-medium text-gray-700">Application Name</label>
                                        <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $settings['general']['app_name']['value'] ?? 'POS System') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @error('app_name')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="default_timezone" class="block text-sm font-medium text-gray-700">Default Timezone</label>
                                        <select name="default_timezone" id="default_timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="Asia/Jakarta" {{ ($settings['general']['default_timezone']['value'] ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                                            <option value="Asia/Makassar" {{ ($settings['general']['default_timezone']['value'] ?? '') === 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                                            <option value="Asia/Jayapura" {{ ($settings['general']['default_timezone']['value'] ?? '') === 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="default_locale" class="block text-sm font-medium text-gray-700">Default Language</label>
                                        <select name="default_locale" id="default_locale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="id" {{ ($settings['general']['default_locale']['value'] ?? 'id') === 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                                            <option value="en" {{ ($settings['general']['default_locale']['value'] ?? '') === 'en' ? 'selected' : '' }}>English</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="date_format" class="block text-sm font-medium text-gray-700">Date Format</label>
                                        <select name="date_format" id="date_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="d/m/Y" {{ ($settings['general']['date_format']['value'] ?? 'd/m/Y') === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                            <option value="Y-m-d" {{ ($settings['general']['date_format']['value'] ?? '') === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                            <option value="m/d/Y" {{ ($settings['general']['date_format']['value'] ?? '') === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="time_format" class="block text-sm font-medium text-gray-700">Time Format</label>
                                        <select name="time_format" id="time_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="H:i" {{ ($settings['general']['time_format']['value'] ?? 'H:i') === 'H:i' ? 'selected' : '' }}>24-hour (HH:MM)</option>
                                            <option value="h:i A" {{ ($settings['general']['time_format']['value'] ?? '') === 'h:i A' ? 'selected' : '' }}>12-hour (hh:MM AM/PM)</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center justify-end">
                                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    @elseif ($activeTab === 'email')
                        <!-- Email Settings Tab -->
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Email Settings</h3>

                            <form method="POST" action="{{ route('admin.settings.updateEmail') }}">
                                @csrf

                                <div class="space-y-6">
                                    <div>
                                        <label for="smtp_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                        <input type="text" name="smtp_host" id="smtp_host" value="{{ old('smtp_host', $settings['email']['smtp_host']['value'] ?? 'smtp.gmail.com') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="smtp_port" class="block text-sm font-medium text-gray-700">SMTP Port</label>
                                        <input type="number" name="smtp_port" id="smtp_port" value="{{ old('smtp_port', $settings['email']['smtp_port']['value'] ?? '587') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="smtp_username" class="block text-sm font-medium text-gray-700">SMTP Username</label>
                                        <input type="text" name="smtp_username" id="smtp_username" value="{{ old('smtp_username', $settings['email']['smtp_username']['value'] ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="smtp_password" class="block text-sm font-medium text-gray-700">SMTP Password</label>
                                        <input type="password" name="smtp_password" id="smtp_password" placeholder="Leave blank to keep current password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-sm text-gray-500">Leave blank if you don't want to change the password</p>
                                    </div>

                                    <div>
                                        <label for="smtp_encryption" class="block text-sm font-medium text-gray-700">SMTP Encryption</label>
                                        <select name="smtp_encryption" id="smtp_encryption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="tls" {{ ($settings['email']['smtp_encryption']['value'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ ($settings['email']['smtp_encryption']['value'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="mail_from_address" class="block text-sm font-medium text-gray-700">From Email Address</label>
                                        <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $settings['email']['mail_from_address']['value'] ?? 'noreply@possystem.com') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="mail_from_name" class="block text-sm font-medium text-gray-700">From Name</label>
                                        <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $settings['email']['mail_from_name']['value'] ?? 'POS System') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div class="flex items-center justify-between border-t pt-6">
                                        <form method="POST" action="{{ route('admin.settings.testEmail') }}" class="flex items-end space-x-4">
                                            @csrf
                                            <div>
                                                <label for="test_email" class="block text-sm font-medium text-gray-700">Test Email</label>
                                                <input type="email" name="test_email" id="test_email" placeholder="your@email.com" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </div>
                                            <button type="submit" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                                Send Test Email
                                            </button>
                                        </form>

                                        <button type="submit" form="emailForm" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    @elseif ($activeTab === 'notifications')
                        <!-- Notifications Settings Tab -->
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Settings</h3>

                            <form method="POST" action="{{ route('admin.settings.updateNotifications') }}">
                                @csrf

                                <div class="space-y-6">
                                    <div class="flex items-start">
                                        <div class="flex h-5 items-center">
                                            <input id="notifications_enabled" name="notifications_enabled" type="checkbox" {{ ($settings['notifications']['notifications_enabled']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="notifications_enabled" class="font-medium text-gray-700">Enable Notifications</label>
                                            <p class="text-gray-500">Enable or disable all system notifications</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="flex h-5 items-center">
                                            <input id="email_notifications" name="email_notifications" type="checkbox" {{ ($settings['notifications']['email_notifications']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="email_notifications" class="font-medium text-gray-700">Email Notifications</label>
                                            <p class="text-gray-500">Send notifications via email</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="flex h-5 items-center">
                                            <input id="inapp_notifications" name="inapp_notifications" type="checkbox" {{ ($settings['notifications']['inapp_notifications']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="inapp_notifications" class="font-medium text-gray-700">In-App Notifications</label>
                                            <p class="text-gray-500">Show notifications in the application</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end border-t pt-6">
                                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    @elseif ($activeTab === 'security')
                        <!-- Security Settings Tab -->
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Security Settings</h3>

                            <form method="POST" action="{{ route('admin.settings.updateSecurity') }}">
                                @csrf

                                <div class="space-y-6">
                                    <div>
                                        <label for="session_timeout" class="block text-sm font-medium text-gray-700">Session Timeout (minutes)</label>
                                        <input type="number" name="session_timeout" id="session_timeout" value="{{ old('session_timeout', $settings['security']['session_timeout']['value'] ?? '120') }}" min="5" max="1440" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-sm text-gray-500">How long (in minutes) before a user is automatically logged out due to inactivity</p>
                                    </div>

                                    <div class="border-t pt-6">
                                        <h4 class="text-sm font-medium text-gray-900 mb-4">Password Policy</h4>

                                        <div class="space-y-4">
                                            <div>
                                                <label for="password_min_length" class="block text-sm font-medium text-gray-700">Minimum Password Length</label>
                                                <input type="number" name="password_min_length" id="password_min_length" value="{{ old('password_min_length', $settings['security']['password_min_length']['value'] ?? '8') }}" min="6" max="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex h-5 items-center">
                                                    <input id="password_require_uppercase" name="password_require_uppercase" type="checkbox" {{ ($settings['security']['password_require_uppercase']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="password_require_uppercase" class="font-medium text-gray-700">Require Uppercase Letters</label>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex h-5 items-center">
                                                    <input id="password_require_numbers" name="password_require_numbers" type="checkbox" {{ ($settings['security']['password_require_numbers']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="password_require_numbers" class="font-medium text-gray-700">Require Numbers</label>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex h-5 items-center">
                                                    <input id="password_require_symbols" name="password_require_symbols" type="checkbox" {{ ($settings['security']['password_require_symbols']['value'] ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="password_require_symbols" class="font-medium text-gray-700">Require Special Characters</label>
                                                </div>
                                            </div>

                                            <div>
                                                <label for="password_expiry_days" class="block text-sm font-medium text-gray-700">Password Expiry (days)</label>
                                                <input type="number" name="password_expiry_days" id="password_expiry_days" value="{{ old('password_expiry_days', $settings['security']['password_expiry_days']['value'] ?? '90') }}" min="0" max="365" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <p class="mt-1 text-sm text-gray-500">Set to 0 for passwords that never expire</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t pt-6">
                                        <div class="flex items-start">
                                            <div class="flex h-5 items-center">
                                                <input id="two_factor_enabled" name="two_factor_enabled" type="checkbox" {{ ($settings['security']['two_factor_enabled']['value'] ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="two_factor_enabled" class="font-medium text-gray-700">Enable Two-Factor Authentication</label>
                                                <p class="text-gray-500">Require users to verify their identity with a second factor</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end border-t pt-6">
                                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    @elseif ($activeTab === 'backups')
                        <!-- Backups Settings Tab -->
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Backup Settings</h3>

                            <div class="mb-6">
                                <form method="POST" action="{{ route('admin.settings.createBackup') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        Create Manual Backup Now
                                    </button>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.updateBackups') }}">
                                @csrf

                                <div class="space-y-6">
                                    <div class="flex items-start">
                                        <div class="flex h-5 items-center">
                                            <input id="backup_enabled" name="backup_enabled" type="checkbox" {{ ($settings['backups']['backup_enabled']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="backup_enabled" class="font-medium text-gray-700">Enable Automatic Backups</label>
                                            <p class="text-gray-500">Automatically create backups on a schedule</p>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="backup_frequency" class="block text-sm font-medium text-gray-700">Backup Frequency</label>
                                        <select name="backup_frequency" id="backup_frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="daily" {{ ($settings['backups']['backup_frequency']['value'] ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ ($settings['backups']['backup_frequency']['value'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ ($settings['backups']['backup_frequency']['value'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="backup_time" class="block text-sm font-medium text-gray-700">Backup Time</label>
                                        <input type="time" name="backup_time" id="backup_time" value="{{ old('backup_time', $settings['backups']['backup_time']['value'] ?? '02:00') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="backup_retention" class="block text-sm font-medium text-gray-700">Retention Period (days)</label>
                                        <input type="number" name="backup_retention" id="backup_retention" value="{{ old('backup_retention', $settings['backups']['backup_retention']['value'] ?? '30') }}" min="1" max="365" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-sm text-gray-500">How many days to keep old backups before deleting them</p>
                                    </div>

                                    <div>
                                        <label for="backup_storage" class="block text-sm font-medium text-gray-700">Storage Location</label>
                                        <select name="backup_storage" id="backup_storage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="local" {{ ($settings['backups']['backup_storage']['value'] ?? 'local') === 'local' ? 'selected' : '' }}>Local Storage</option>
                                            <option value="s3" {{ ($settings['backups']['backup_storage']['value'] ?? '') === 's3' ? 'selected' : '' }}>Amazon S3</option>
                                            <option value="ftp" {{ ($settings['backups']['backup_storage']['value'] ?? '') === 'ftp' ? 'selected' : '' }}>FTP Server</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center justify-end border-t pt-6">
                                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
