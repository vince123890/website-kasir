<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
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
                        <a href="{{ route('profile.edit', ['tab' => 'profile']) }}"
                           class="{{ $activeTab === 'profile' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Profile Information
                        </a>
                        <a href="{{ route('profile.edit', ['tab' => 'password']) }}"
                           class="{{ $activeTab === 'password' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Change Password
                        </a>
                        <a href="{{ route('profile.edit', ['tab' => 'activity']) }}"
                           class="{{ $activeTab === 'activity' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Activity Log
                        </a>
                        <a href="{{ route('profile.edit', ['tab' => 'sessions']) }}"
                           class="{{ $activeTab === 'sessions' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Active Sessions
                        </a>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    @if ($activeTab === 'profile')
                        <!-- Profile Information Tab -->
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>

                            <!-- Avatar Upload -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                                <div class="flex items-center space-x-6">
                                    <div class="shrink-0">
                                        @if ($user->avatar)
                                            <img class="h-16 w-16 object-cover rounded-full" src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar">
                                        @else
                                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-xl font-medium text-gray-600">{{ substr($user->name, 0, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('profile.updateAvatar') }}" enctype="multipart/form-data" class="flex items-center space-x-4">
                                        @csrf
                                        <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Upload</button>
                                    </form>
                                </div>
                                @error('avatar')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Profile Form -->
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PATCH')

                                <div class="space-y-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @error('name')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @error('email')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @error('phone')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Read-only fields -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Role</label>
                                        <input type="text" value="{{ $user->roles->first()?->name ?? 'N/A' }}" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                    </div>

                                    @if ($user->tenant)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Tenant</label>
                                            <input type="text" value="{{ $user->tenant->name }}" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                        </div>
                                    @endif

                                    @if ($user->store)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Store</label>
                                            <input type="text" value="{{ $user->store->name }}" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-end">
                                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    @elseif ($activeTab === 'password')
                        <!-- Change Password Tab -->
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>

                            <form method="POST" action="{{ route('profile.updatePassword') }}" x-data="{ password: '', strength: '' }" x-init="
                                $watch('password', value => {
                                    let strength = 0;
                                    if (value.length >= 8) strength++;
                                    if (value.length >= 12) strength++;
                                    if (/[A-Z]/.test(value)) strength++;
                                    if (/[a-z]/.test(value)) strength++;
                                    if (/[0-9]/.test(value)) strength++;
                                    if (/[^A-Za-z0-9]/.test(value)) strength++;

                                    if (strength <= 2) {
                                        this.strength = 'weak';
                                    } else if (strength <= 4) {
                                        this.strength = 'medium';
                                    } else {
                                        this.strength = 'strong';
                                    }
                                })
                            ">
                                @csrf

                                <div class="space-y-6">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                        <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @error('current_password')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                        <input type="password" name="password" id="password" x-model="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @error('password')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror

                                        <!-- Password Strength Indicator -->
                                        <div class="mt-2" x-show="password.length > 0">
                                            <div class="flex items-center space-x-2">
                                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                    <div class="h-2 rounded-full transition-all duration-300"
                                                         :class="{
                                                             'bg-red-500 w-1/3': strength === 'weak',
                                                             'bg-yellow-500 w-2/3': strength === 'medium',
                                                             'bg-green-500 w-full': strength === 'strong'
                                                         }"></div>
                                                </div>
                                                <span class="text-sm font-medium"
                                                      :class="{
                                                          'text-red-600': strength === 'weak',
                                                          'text-yellow-600': strength === 'medium',
                                                          'text-green-600': strength === 'strong'
                                                      }"
                                                      x-text="strength.charAt(0).toUpperCase() + strength.slice(1)"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div class="flex items-center justify-end">
                                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    @elseif ($activeTab === 'activity')
                        <!-- Activity Log Tab -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Log (Last 30 Days)</h3>

                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Action</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Device</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">IP Address</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @forelse ($activityLogs as $log)
                                            <tr>
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                                        @if (str_contains($log->action, 'login')) bg-green-100 text-green-800
                                                        @elseif (str_contains($log->action, 'logout')) bg-gray-100 text-gray-800
                                                        @elseif (str_contains($log->action, 'delete')) bg-red-100 text-red-800
                                                        @elseif (str_contains($log->action, 'update') || str_contains($log->action, 'change')) bg-yellow-100 text-yellow-800
                                                        @else bg-blue-100 text-blue-800
                                                        @endif">
                                                        {{ $log->action }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-4 text-sm text-gray-500">{{ $log->description }}</td>
                                                <td class="px-3 py-4 text-sm text-gray-500">{{ $log->device ?? 'Unknown' }}</td>
                                                <td class="px-3 py-4 text-sm text-gray-500">{{ $log->ip_address }}</td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-4 text-center text-sm text-gray-500">No activity logs found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    @elseif ($activeTab === 'sessions')
                        <!-- Active Sessions Tab -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Active Sessions</h3>
                                <form method="POST" action="{{ route('profile.logoutAllSessions') }}" x-data="{ showPassword: false }">
                                    @csrf
                                    <button type="button" @click="showPassword = true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                        Logout All Other Sessions
                                    </button>

                                    <!-- Password Modal -->
                                    <div x-show="showPassword" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
                                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                                            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Confirm Password</h3>
                                                        <div>
                                                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                                            <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                                                        Logout All
                                                    </button>
                                                    <button type="button" @click="showPassword = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="grid gap-4">
                                @forelse ($activeSessions as $session)
                                    <div class="border rounded-lg p-4 {{ $session['is_current'] ? 'bg-green-50 border-green-200' : 'bg-white' }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $session['device'] }}</p>
                                                        <p class="text-sm text-gray-500">{{ $session['ip_address'] }}</p>
                                                    </div>
                                                </div>
                                                <p class="mt-2 text-xs text-gray-500">Last active: {{ $session['last_activity'] }}</p>
                                            </div>
                                            @if ($session['is_current'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Current Session
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-sm text-gray-500 py-4">No active sessions</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
