@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Tenants Management</h2>
            <p class="mt-1 text-sm text-gray-600">Manage all tenants and their subscriptions</p>
        </div>
        <x-button type="primary" href="{{ route('admin.tenants.create') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Tenant
        </x-button>
    </div>

    <!-- Search and Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.tenants.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, email, slug..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Status</label>
                    <select name="subscription_status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="trial" {{ (($filters['subscription_status'] ?? '') == 'trial') ? 'selected' : '' }}>Trial</option>
                        <option value="active" {{ (($filters['subscription_status'] ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ (($filters['subscription_status'] ?? '') == 'expired') ? 'selected' : '' }}>Expired</option>
                        <option value="cancelled" {{ (($filters['subscription_status'] ?? '') == 'cancelled') ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Active Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="1" {{ (($filters['is_active'] ?? '') === '1') ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ (($filters['is_active'] ?? '') === '0') ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex space-x-2">
                <x-button type="primary" submit>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </x-button>
                <x-button type="secondary" href="{{ route('admin.tenants.index') }}">
                    Reset
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Tenants Table -->
    <x-card>
        @if($tenants->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statistics</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tenants as $tenant)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $tenant->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->slug }}</code>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $tenant->email }}</div>
                                    <div class="text-sm text-gray-500">{{ $tenant->phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <x-badge
                                            :color="$tenant->subscription_status === 'active' ? 'green' : ($tenant->subscription_status === 'trial' ? 'blue' : ($tenant->subscription_status === 'expired' ? 'red' : 'gray'))"
                                            :text="ucfirst($tenant->subscription_status)"
                                        />
                                    </div>
                                    @if($tenant->trial_ends_at)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Trial ends: {{ $tenant->trial_ends_at->format('d M Y') }}
                                        </div>
                                    @endif
                                    @if($tenant->subscription_ends_at)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Ends: {{ $tenant->subscription_ends_at->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>Stores: {{ $tenant->stores_count }}</div>
                                    <div>Users: {{ $tenant->users_count }}</div>
                                    <div>Products: {{ $tenant->products_count }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :color="$tenant->is_active ? 'green' : 'red'" :text="$tenant->is_active ? 'Active' : 'Inactive'" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        <a href="{{ route('admin.tenants.edit', $tenant->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>

                                        @if($tenant->is_active)
                                            <form action="{{ route('admin.tenants.deactivate', $tenant->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-900" onclick="return confirm('Deactivate this tenant?')">Deactivate</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.tenants.activate', $tenant->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">Activate</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.tenants.destroy', $tenant->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure? This will delete all tenant data!')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <x-pagination :paginator="$tenants" />
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No tenants found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new tenant.</p>
                <div class="mt-6">
                    <x-button type="primary" href="{{ route('admin.tenants.create') }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Tenant
                    </x-button>
                </div>
            </div>
        @endif
    </x-card>
</div>
@endsection

@php
    $title = 'Tenants Management';
    $breadcrumb = [
        ['label' => 'Tenants', 'url' => route('admin.tenants.index')]
    ];
@endphp
