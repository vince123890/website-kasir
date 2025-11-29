@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Stores Management</h2>
            <p class="mt-1 text-sm text-gray-600">Manage all your stores</p>
        </div>
        <x-button type="primary" href="{{ route('stores.create') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Store
        </x-button>
    </div>

    <!-- Search and Filters -->
    <x-card>
        <form method="GET" action="{{ route('stores.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, code, email..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" placeholder="Filter by city" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                    <input type="text" name="province" value="{{ $filters['province'] ?? '' }}" placeholder="Filter by province" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
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
                <x-button type="secondary" href="{{ route('stores.index') }}">
                    Reset
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Stores Grid -->
    @if($stores->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($stores as $store)
                <x-card>
                    <div class="space-y-4">
                        <!-- Store Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                @if($store->logo)
                                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="h-12 w-12 rounded-lg object-cover">
                                @else
                                    <div class="h-12 w-12 rounded-lg bg-indigo-500 flex items-center justify-center text-white text-lg font-bold">
                                        {{ substr($store->name, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $store->name }}</h3>
                                    <p class="text-xs text-gray-500">
                                        <code class="bg-gray-100 px-2 py-1 rounded">{{ $store->code }}</code>
                                    </p>
                                </div>
                            </div>
                            <x-badge :color="$store->is_active ? 'green' : 'red'" :text="$store->is_active ? 'Active' : 'Inactive'" />
                        </div>

                        <!-- Store Info -->
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $store->city }}, {{ $store->province }}
                            </div>
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $store->phone }}
                            </div>
                            @if($store->email)
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $store->email }}
                                </div>
                            @endif
                        </div>

                        <!-- Statistics -->
                        <div class="grid grid-cols-3 gap-3 pt-3 border-t">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-indigo-600">{{ $store->users_count }}</div>
                                <div class="text-xs text-gray-500">Users</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $store->products_count }}</div>
                                <div class="text-xs text-gray-500">Products</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $store->transactions_count }}</div>
                                <div class="text-xs text-gray-500">Transactions</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-3 border-t">
                            <div class="flex space-x-2">
                                <a href="{{ route('stores.show', $store->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('stores.edit', $store->id) }}" class="text-sm text-blue-600 hover:text-blue-900">Edit</a>
                                @if(auth()->user()->hasRole('Admin Toko'))
                                    <a href="{{ route('stores.settings', $store->id) }}" class="text-sm text-gray-600 hover:text-gray-900">Settings</a>
                                @endif
                            </div>
                            <form action="{{ route('stores.destroy', $store->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this store?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <x-pagination :paginator="$stores" />
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No stores found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new store.</p>
                <div class="mt-6">
                    <x-button type="primary" href="{{ route('stores.create') }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Store
                    </x-button>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

@php
    $title = 'Stores Management';
    $breadcrumb = [
        ['label' => 'Stores', 'url' => route('stores.index')]
    ];
@endphp
