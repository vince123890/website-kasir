@extends(auth()->user()->hasRole('Administrator SaaS') ? 'layouts.admin' : (auth()->user()->hasRole('Tenant Owner') ? 'layouts.tenant' : 'layouts.store'))

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New User</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new user to the system</p>
        </div>
        <x-button type="secondary" href="{{ route('users.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Users
        </x-button>
    </div>

    <!-- Create Form -->
    <x-card>
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <!-- Personal Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input
                            name="name"
                            label="Full Name"
                            :value="old('name')"
                            required
                            placeholder="Enter full name"
                        />

                        <x-form.input
                            name="email"
                            type="email"
                            label="Email Address"
                            :value="old('email')"
                            required
                            placeholder="user@example.com"
                        />

                        <x-form.input
                            name="phone"
                            label="Phone Number"
                            :value="old('phone')"
                            placeholder="+62 812-3456-7890"
                        />

                        <x-form.file
                            name="avatar"
                            label="Avatar"
                            accept="image/*"
                        />
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
                            :selected="old('role')"
                            required
                        />

                        @if(auth()->user()->hasRole('Administrator SaaS'))
                            <x-form.select
                                name="tenant_id"
                                label="Tenant"
                                :options="$tenants->pluck('name', 'id')->toArray()"
                                :selected="old('tenant_id')"
                            />
                        @endif

                        <x-form.select
                            name="store_id"
                            label="Store (Optional)"
                            :options="$stores->pluck('name', 'id')->toArray()"
                            :selected="old('store_id')"
                        />
                    </div>
                </div>

                <!-- Password Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Password</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input
                            name="password"
                            type="password"
                            label="Password"
                            required
                            placeholder="Min 8 characters"
                        />

                        <x-form.input
                            name="password_confirmation"
                            type="password"
                            label="Confirm Password"
                            required
                            placeholder="Re-enter password"
                        />
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.
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
                            :selected="old('is_active', '1')"
                        />

                        <div class="space-y-3">
                            <x-form.checkbox
                                name="send_activation_email"
                                label="Send activation email to user"
                                :checked="old('send_activation_email', false)"
                            />

                            <x-form.checkbox
                                name="must_change_password"
                                label="Force password change on first login"
                                :checked="old('must_change_password', false)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t pt-6 flex items-center justify-end space-x-4">
                    <x-button type="secondary" href="{{ route('users.index') }}">
                        Cancel
                    </x-button>
                    <x-button type="primary" submit>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create User
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

@php
    $title = 'Create User';
    $breadcrumb = [
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'Create', 'url' => route('users.create')]
    ];
@endphp
