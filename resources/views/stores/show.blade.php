@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Store Details</h2>
            <p class="mt-1 text-sm text-gray-600">View store information and statistics</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('stores.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Stores
            </x-button>
            <x-button type="primary" href="{{ route('stores.edit', $store->id) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Store
            </x-button>
            @if(auth()->user()->hasRole('Admin Toko'))
                <x-button type="secondary" href="{{ route('stores.settings', $store->id) }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </x-button>
            @endif
        </div>
    </div>

    <!-- Store Info Card -->
    <x-card>
        <div class="flex items-start space-x-6">
            <!-- Store Logo -->
            <div class="flex-shrink-0">
                @if($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="h-24 w-24 rounded-lg object-cover border">
                @else
                    <div class="h-24 w-24 rounded-lg bg-indigo-500 flex items-center justify-center text-white text-3xl font-bold">
                        {{ substr($store->name, 0, 2) }}
                    </div>
                @endif
            </div>

            <!-- Store Info -->
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $store->name }}</h3>
                        <p class="text-sm text-gray-500">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $store->code }}</code>
                        </p>
                    </div>
                    <x-badge :color="$store->is_active ? 'green' : 'red'" :text="$store->is_active ? 'Active' : 'Inactive'" />
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $store->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">City & Province</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $store->city }}, {{ $store->province }}</dd>
                    </div>
                    @if($store->postal_code)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Postal Code</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $store->postal_code }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $store->phone }}</dd>
                    </div>
                    @if($store->email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $store->email }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $store->timezone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Currency</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $store->currency }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tax Rate</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $store->tax_rate }}%
                            @if($store->tax_included)
                                <span class="text-xs text-gray-500">(included in price)</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $store->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $store->updated_at->format('d M Y H:i') }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card
            title="Total Users"
            :value="$statistics['total_users']"
            icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            color="blue"
        />

        <x-stat-card
            title="Total Products"
            :value="$statistics['total_products']"
            icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
            color="green"
        />

        <x-stat-card
            title="Total Transactions"
            :value="$statistics['total_transactions']"
            icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            color="purple"
        />
    </div>

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

    <!-- Receipt Settings -->
    @if($store->receipt_header || $store->receipt_footer)
        <x-card>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Receipt Settings</h3>
            <div class="space-y-4">
                @if($store->receipt_header)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Receipt Header</dt>
                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $store->receipt_header }}</dd>
                    </div>
                @endif
                @if($store->receipt_footer)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Receipt Footer</dt>
                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $store->receipt_footer }}</dd>
                    </div>
                @endif
            </div>
        </x-card>
    @endif
</div>
@endsection

@php
    $title = 'Store Details - ' . $store->name;
    $breadcrumb = [
        ['label' => 'Stores', 'url' => route('stores.index')],
        ['label' => $store->name, 'url' => route('stores.show', $store->id)]
    ];
@endphp
