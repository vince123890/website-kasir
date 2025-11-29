@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Tenant Details</h2>
            <p class="mt-1 text-sm text-gray-600">View tenant information and statistics</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('admin.tenants.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Tenants
            </x-button>
            <x-button type="primary" href="{{ route('admin.tenants.edit', $tenant->id) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Tenant
            </x-button>
        </div>
    </div>

    <!-- Tenant Info Card -->
    <x-card>
        <div class="flex items-start space-x-6">
            <!-- Tenant Icon -->
            <div class="flex-shrink-0">
                <div class="h-24 w-24 rounded-full bg-indigo-500 flex items-center justify-center text-white text-3xl font-bold">
                    {{ substr($tenant->name, 0, 2) }}
                </div>
            </div>

            <!-- Tenant Info -->
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</h3>
                        <p class="text-sm text-gray-500">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->slug }}</code>
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-badge :color="$tenant->is_active ? 'green' : 'red'" :text="$tenant->is_active ? 'Active' : 'Inactive'" />
                        <x-badge
                            :color="$tenant->subscription_status === 'active' ? 'green' : ($tenant->subscription_status === 'trial' ? 'blue' : ($tenant->subscription_status === 'expired' ? 'red' : 'gray'))"
                            :text="ucfirst($tenant->subscription_status)"
                        />
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->phone }}</dd>
                    </div>
                    @if($tenant->trial_ends_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Trial Ends At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $tenant->trial_ends_at->format('d M Y') }}
                                @if($tenant->trial_ends_at->isPast())
                                    <x-badge color="red" text="Expired" />
                                @elseif($tenant->trial_ends_at->diffInDays(now()) <= 7)
                                    <x-badge color="yellow" text="Expiring Soon" />
                                @endif
                            </dd>
                        </div>
                    @endif
                    @if($tenant->subscription_ends_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Subscription Ends At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $tenant->subscription_ends_at->format('d M Y') }}
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->updated_at->format('d M Y H:i') }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card
            title="Total Stores"
            :value="$statistics['total_stores']"
            icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
            color="blue"
        />

        <x-stat-card
            title="Total Users"
            :value="$statistics['total_users']"
            icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            color="green"
        />

        <x-stat-card
            title="Total Products"
            :value="$statistics['total_products']"
            icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
            color="purple"
        />
    </div>

    <!-- Stores Table -->
    @if($tenant->stores->count() > 0)
        <x-card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Stores</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tenant->stores as $store)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $store->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $store->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $store->city }}, {{ $store->province }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :color="$store->is_active ? 'green' : 'red'" :text="$store->is_active ? 'Active' : 'Inactive'" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $store->users_count }} users
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    <!-- Users Breakdown -->
    @if($usersBreakdown->count() > 0)
        <x-card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Users Breakdown by Role</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($usersBreakdown as $roleData)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-gray-500">{{ $roleData->role_name }}</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $roleData->count }}</dd>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <!-- Quick Actions -->
    <x-card>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3" x-data="{ showActivateModal: false, showDeactivateModal: false }">
            @if($tenant->is_active)
                <button
                    @click="showDeactivateModal = true"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                    Deactivate Tenant
                </button>

                <!-- Deactivate Modal -->
                <div x-show="showDeactivateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeactivateModal = false"></div>
                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 shadow-xl max-w-lg w-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Deactivate Tenant</h3>
                            <form action="{{ route('admin.tenants.deactivate', $tenant->id) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <x-form.checkbox
                                        name="deactivate_stores"
                                        label="Also deactivate all stores"
                                    />
                                    <x-form.checkbox
                                        name="deactivate_users"
                                        label="Also deactivate all users"
                                    />
                                </div>
                                <div class="mt-6 flex space-x-3">
                                    <x-button type="secondary" @click="showDeactivateModal = false">Cancel</x-button>
                                    <x-button type="danger" submit>Deactivate</x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <button
                    @click="showActivateModal = true"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Activate Tenant
                </button>

                <!-- Activate Modal -->
                <div x-show="showActivateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showActivateModal = false"></div>
                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 shadow-xl max-w-lg w-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Activate Tenant</h3>
                            <form action="{{ route('admin.tenants.activate', $tenant->id) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <x-form.checkbox
                                        name="activate_stores"
                                        label="Also activate all stores"
                                    />
                                    <x-form.checkbox
                                        name="activate_users"
                                        label="Also activate all users"
                                    />
                                </div>
                                <div class="mt-6 flex space-x-3">
                                    <x-button type="secondary" @click="showActivateModal = false">Cancel</x-button>
                                    <x-button type="primary" submit>Activate</x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection

@php
    $title = 'Tenant Details - ' . $tenant->name;
    $breadcrumb = [
        ['label' => 'Tenants', 'url' => route('admin.tenants.index')],
        ['label' => $tenant->name, 'url' => route('admin.tenants.show', $tenant->id)]
    ];
@endphp
