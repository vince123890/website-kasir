@extends(auth()->user()->hasRole('Administrator SaaS') ? 'layouts.admin' : (auth()->user()->hasRole('Tenant Owner') ? 'layouts.tenant' : 'layouts.store'))

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit User</h2>
            <p class="mt-1 text-sm text-gray-600">Update user information and settings</p>
        </div>
        <x-button type="secondary" href="{{ route('users.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Users
        </x-button>
    </div>

    <!-- Edit Form -->
    <x-card>
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Personal Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input
                            name="name"
                            label="Full Name"
                            :value="old('name', $user->name)"
                            required
                            placeholder="Enter full name"
                        />

                        <x-form.input
                            name="email"
                            type="email"
                            label="Email Address"
                            :value="old('email', $user->email)"
                            required
                            placeholder="user@example.com"
                        />

                        <x-form.input
                            name="phone"
                            label="Phone Number"
                            :value="old('phone', $user->phone)"
                            placeholder="+62 812-3456-7890"
                        />

                        <div>
                            <x-form.file
                                name="avatar"
                                label="Avatar (leave empty to keep current)"
                                accept="image/*"
                            />
                            @if($user->avatar_path)
                                <div class="mt-2 flex items-center">
                                    <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover">
                                    <span class="ml-3 text-sm text-gray-500">Current avatar</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Information Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.select
                            name="role"
                            label="Role"
                            :options="$roles->pluck('name', 'name')->toArray()"
                            :selected="old('role', $user->getRoleNames()->first())"
                            required
                        />

                        @if(auth()->user()->hasRole('Administrator SaaS'))
                            <x-form.select
                                name="tenant_id"
                                label="Tenant"
                                :options="$tenants->pluck('name', 'id')->toArray()"
                                :selected="old('tenant_id', $user->tenant_id)"
                            />
                        @endif

                        <x-form.select
                            name="store_id"
                            label="Store (Optional)"
                            :options="$stores->pluck('name', 'id')->toArray()"
                            :selected="old('store_id', $user->store_id)"
                        />
                    </div>
                </div>

                <!-- Password Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password (Optional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input
                            name="password"
                            type="password"
                            label="New Password"
                            placeholder="Leave empty to keep current password"
                        />

                        <x-form.input
                            name="password_confirmation"
                            type="password"
                            label="Confirm New Password"
                            placeholder="Re-enter new password"
                        />
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Leave empty to keep current password. If changing, password must contain at least 8 characters, including uppercase, lowercase, number, and special character.
                    </p>
                </div>

                <!-- Account Settings Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Settings</h3>
                    <div class="space-y-4">
                        <x-form.radio
                            name="is_active"
                            label="Account Status"
                            :options="['1' => 'Active', '0' => 'Inactive']"
                            :selected="old('is_active', $user->is_active ? '1' : '0')"
                        />

                        <div class="space-y-3">
                            <x-form.checkbox
                                name="send_activation_email"
                                label="Send activation email to user"
                                :checked="old('send_activation_email', false)"
                            />

                            <x-form.checkbox
                                name="must_change_password"
                                label="Force password change on next login"
                                :checked="old('must_change_password', $user->must_change_password)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Account Metadata -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Created:</span>
                                <span class="text-gray-600 ml-2">{{ $user->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Updated:</span>
                                <span class="text-gray-600 ml-2">{{ $user->updated_at->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Login:</span>
                                <span class="text-gray-600 ml-2">{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}</span>
                            </div>
                        </div>
                        @if($user->password_expires_at)
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">Password Expires:</span>
                                <span class="text-gray-600 ml-2">{{ $user->password_expires_at->format('d M Y') }}</span>
                                @if($user->password_expires_at->isPast())
                                    <x-badge color="red" text="Expired" />
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t pt-6 flex items-center justify-between">
                    <div class="flex space-x-3">
                        @can('users.delete.tenant')
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="danger" submit>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete User
                                    </x-button>
                                </form>
                            @endif
                        @endcan
                    </div>
                    <div class="flex items-center space-x-4">
                        <x-button type="secondary" href="{{ route('users.index') }}">
                            Cancel
                        </x-button>
                        <x-button type="primary" submit>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update User
                        </x-button>
                    </div>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

@php
    $title = 'Edit User - ' . $user->name;
    $breadcrumb = [
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => $user->name, 'url' => route('users.show', $user->id)],
        ['label' => 'Edit', 'url' => route('users.edit', $user->id)]
    ];
@endphp
