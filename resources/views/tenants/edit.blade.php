@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Tenant</h2>
            <p class="mt-1 text-sm text-gray-600">Update tenant information and settings</p>
        </div>
        <x-button type="secondary" href="{{ route('admin.tenants.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Tenants
        </x-button>
    </div>

    <!-- Edit Form -->
    <x-card>
        <form action="{{ route('admin.tenants.update', $tenant->id) }}" method="POST" x-data="tenantForm()">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Basic Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-form.input
                                name="name"
                                label="Tenant Name"
                                :value="old('name', $tenant->name)"
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
                            <p class="mt-1 text-xs text-yellow-600">⚠️ Warning: Changing slug will affect all URLs!</p>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-form.input
                            name="email"
                            type="email"
                            label="Email Address"
                            :value="old('email', $tenant->email)"
                            required
                            placeholder="tenant@example.com"
                        />

                        <x-form.input
                            name="phone"
                            label="Phone Number"
                            :value="old('phone', $tenant->phone)"
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
                                :value="old('trial_ends_at', $tenant->trial_ends_at ? $tenant->trial_ends_at->format('Y-m-d') : '')"
                            />
                        </div>

                        <div x-show="subscriptionStatus === 'active'">
                            <x-form.datepicker
                                name="subscription_ends_at"
                                label="Subscription Ends At"
                                :value="old('subscription_ends_at', $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('Y-m-d') : '')"
                            />
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
                            :selected="old('is_active', $tenant->is_active ? '1' : '0')"
                        />
                    </div>
                </div>

                <!-- Account Metadata -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Created:</span>
                                <span class="text-gray-600 ml-2">{{ $tenant->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Updated:</span>
                                <span class="text-gray-600 ml-2">{{ $tenant->updated_at->format('d M Y H:i') }}</span>
                            </div>
                            @if($tenant->activated_at)
                                <div>
                                    <span class="font-medium text-gray-700">Activated At:</span>
                                    <span class="text-gray-600 ml-2">{{ $tenant->activated_at->format('d M Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Total Stores:</span>
                                <span class="text-gray-600 ml-2">{{ $tenant->stores_count }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Total Users:</span>
                                <span class="text-gray-600 ml-2">{{ $tenant->users_count }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Total Products:</span>
                                <span class="text-gray-600 ml-2">{{ $tenant->products_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t pt-6 flex items-center justify-between">
                    <div class="flex space-x-3">
                        <form action="{{ route('admin.tenants.destroy', $tenant->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tenant? All data will be lost!');">
                            @csrf
                            @method('DELETE')
                            <x-button type="danger" submit>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Tenant
                            </x-button>
                        </form>
                    </div>
                    <div class="flex items-center space-x-4">
                        <x-button type="secondary" href="{{ route('admin.tenants.index') }}">
                            Cancel
                        </x-button>
                        <x-button type="primary" submit>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Tenant
                        </x-button>
                    </div>
                </div>
            </div>
        </form>
    </x-card>
</div>

<script>
function tenantForm() {
    return {
        slug: '{{ old('slug', $tenant->slug) }}',
        subscriptionStatus: '{{ old('subscription_status', $tenant->subscription_status) }}',

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
    $title = 'Edit Tenant - ' . $tenant->name;
    $breadcrumb = [
        ['label' => 'Tenants', 'url' => route('admin.tenants.index')],
        ['label' => $tenant->name, 'url' => route('admin.tenants.show', $tenant->id)],
        ['label' => 'Edit', 'url' => route('admin.tenants.edit', $tenant->id')]
    ];
@endphp
