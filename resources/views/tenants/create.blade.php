@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Tenant</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new tenant to the system</p>
        </div>
        <x-button type="secondary" href="{{ route('admin.tenants.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Tenants
        </x-button>
    </div>

    <!-- Create Form -->
    <x-card>
        <form action="{{ route('admin.tenants.store') }}" method="POST" x-data="tenantForm()">
            @csrf

            <div class="space-y-6">
                <!-- Basic Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-form.input
                                name="name"
                                label="Tenant Name"
                                :value="old('name')"
                                required
                                placeholder="Enter tenant name"
                                x-on:input="generateSlug"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Slug <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="slug"
                                x-model="slug"
                                required
                                placeholder="tenant-slug"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <p class="mt-1 text-xs text-gray-500">URL-friendly identifier (lowercase, dashes only)</p>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-form.input
                            name="email"
                            type="email"
                            label="Email Address"
                            :value="old('email')"
                            required
                            placeholder="tenant@example.com"
                        />

                        <x-form.input
                            name="phone"
                            label="Phone Number"
                            :value="old('phone')"
                            required
                            placeholder="+62 812-3456-7890"
                        />
                    </div>
                </div>

                <!-- Subscription Information Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Subscription Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="subscription_status"
                                x-model="subscriptionStatus"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="trial">Trial</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('subscription_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="subscriptionStatus === 'trial'">
                            <x-form.datepicker
                                name="trial_ends_at"
                                label="Trial Ends At"
                                :value="old('trial_ends_at', now()->addDays(30)->format('Y-m-d'))"
                            />
                        </div>

                        <div x-show="subscriptionStatus === 'active'">
                            <x-form.datepicker
                                name="subscription_ends_at"
                                label="Subscription Ends At"
                                :value="old('subscription_ends_at')"
                            />
                        </div>
                    </div>
                </div>

                <!-- Owner Account Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Owner Account</h3>
                    <div class="space-y-4">
                        <x-form.checkbox
                            name="auto_create_owner"
                            label="Auto-create owner account"
                            :checked="old('auto_create_owner', true)"
                            x-model="autoCreateOwner"
                        />

                        <div x-show="autoCreateOwner" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form.input
                                name="owner_email"
                                type="email"
                                label="Owner Email"
                                :value="old('owner_email')"
                                placeholder="owner@example.com"
                            />

                            <x-form.input
                                name="owner_name"
                                label="Owner Name"
                                :value="old('owner_name')"
                                placeholder="Owner Full Name"
                            />
                        </div>

                        <div x-show="autoCreateOwner" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="ml-3 text-sm text-blue-700">
                                    A random password will be generated and sent to the owner's email address.
                                </p>
                            </div>
                        </div>
                    </div>
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
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t pt-6 flex items-center justify-end space-x-4">
                    <x-button type="secondary" href="{{ route('admin.tenants.index') }}">
                        Cancel
                    </x-button>
                    <x-button type="primary" submit>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create Tenant
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>

<script>
function tenantForm() {
    return {
        slug: '{{ old('slug') }}',
        subscriptionStatus: '{{ old('subscription_status', 'trial') }}',
        autoCreateOwner: {{ old('auto_create_owner', 'true') }},

        generateSlug(event) {
            const name = event.target.value;
            this.slug = name
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    }
}
</script>
@endsection

@php
    $title = 'Create Tenant';
    $breadcrumb = [
        ['label' => 'Tenants', 'url' => route('admin.tenants.index')],
        ['label' => 'Create', 'url' => route('admin.tenants.create')]
    ];
@endphp
