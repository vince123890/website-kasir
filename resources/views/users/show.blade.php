@extends(auth()->user()->hasRole('Administrator SaaS') ? 'layouts.admin' : (auth()->user()->hasRole('Tenant Owner') ? 'layouts.tenant' : 'layouts.store'))

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">User Details</h2>
            <p class="mt-1 text-sm text-gray-600">View user information and activity</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('users.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Users
            </x-button>
            @can('users.edit.tenant')
                <x-button type="primary" href="{{ route('users.edit', $user->id) }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
                </x-button>
            @endcan
        </div>
    </div>

    <!-- User Profile Card -->
    <x-card>
        <div class="flex items-start space-x-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                @if($user->avatar_path)
                    <img class="h-24 w-24 rounded-full object-cover" src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->name }}">
                @else
                    <div class="h-24 w-24 rounded-full bg-indigo-500 flex items-center justify-center text-white text-3xl font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                @endif
            </div>

            <!-- User Info -->
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">ID: #{{ $user->id }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-badge :color="$user->is_active ? 'green' : 'red'" :text="$user->is_active ? 'Active' : 'Inactive'" />
                        <x-badge :color="$user->getRoleNames()->first() === 'Administrator SaaS' ? 'purple' : 'blue'" :text="$user->getRoleNames()->first()" />
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tenant</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->tenant->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Store</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->store->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d M Y H:i') }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Account Security -->
    <x-card>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Security</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($user->last_login_at)
                        {{ $user->last_login_at->format('d M Y H:i') }}
                        <span class="text-gray-500">({{ $user->last_login_at->diffForHumans() }})</span>
                    @else
                        Never
                    @endif
                </dd>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Login Count</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->login_count ?? 0 }} times</dd>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Password Expires</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($user->password_expires_at)
                        {{ $user->password_expires_at->format('d M Y') }}
                        @if($user->password_expires_at->isPast())
                            <x-badge color="red" text="Expired" />
                        @elseif($user->password_expires_at->diffInDays(now()) <= 7)
                            <x-badge color="yellow" text="Expiring Soon" />
                        @endif
                    @else
                        -
                    @endif
                </dd>
            </div>
        </div>

        @if($user->last_login_ip || $user->last_login_device)
            <div class="mt-4 border-t pt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Last Login Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($user->last_login_ip)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->last_login_ip }}</dd>
                        </div>
                    @endif
                    @if($user->last_login_device)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Device</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->last_login_device }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($user->must_change_password)
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="ml-3 text-sm text-yellow-700">
                        <span class="font-medium">Password Change Required:</span> This user must change their password on next login.
                    </p>
                </div>
            </div>
        @endif
    </x-card>

    <!-- Quick Actions -->
    <x-card>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            @can('users.edit.tenant')
                @if($user->id !== auth()->id())
                    @if($user->is_active)
                        <form action="{{ route('users.update', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_active" value="0">
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="role" value="{{ $user->getRoleNames()->first() }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                                Deactivate Account
                            </button>
                        </form>
                    @else
                        <form action="{{ route('users.update', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_active" value="1">
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="role" value="{{ $user->getRoleNames()->first() }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Activate Account
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('users.send-activation', $user->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Send Activation Email
                        </button>
                    </form>

                    <form action="{{ route('users.force-password-change', $user->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            Force Password Change
                        </button>
                    </form>

                    <form action="{{ route('users.logout-all', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('This will log out all active sessions for this user. Continue?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout All Sessions
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </x-card>

    <!-- Permissions (if applicable) -->
    @if($user->getAllPermissions()->count() > 0)
        <x-card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                @foreach($user->getAllPermissions() as $permission)
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
@endsection

@php
    $title = 'User Details - ' . $user->name;
    $breadcrumb = [
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => $user->name, 'url' => route('users.show', $user->id)]
    ];
@endphp
