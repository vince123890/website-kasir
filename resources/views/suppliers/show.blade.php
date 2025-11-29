@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Supplier Details</h2>
            <p class="mt-1 text-sm text-gray-600">View supplier information and purchase history</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('suppliers.history', $supplier->id) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Purchase History
            </x-button>
            <x-button type="primary" href="{{ route('suppliers.edit', $supplier->id) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Supplier
            </x-button>
            <x-button type="secondary" href="{{ route('suppliers.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Suppliers
            </x-button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Purchase Orders</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">{{ $statistics['total_orders'] ?? 0 }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Purchases</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">Rp {{ number_format($statistics['total_amount'] ?? 0, 0, ',', '.') }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Average Order Value</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">Rp {{ number_format($statistics['average_order'] ?? 0, 0, ',', '.') }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column - Supplier Information -->
        <div class="space-y-6">
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Supplier Information</h3>
                    <x-badge :color="$supplier->is_active ? 'green' : 'red'" :text="$supplier->is_active ? 'Active' : 'Inactive'" />
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Supplier Code</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $supplier->code }}</code>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Supplier Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Contact Person</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->contact_person }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->phone }}</p>
                    </div>
                    @if($supplier->email)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $supplier->email }}</p>
                        </div>
                    @endif
                </div>
            </x-card>

            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Address</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->address }}</p>
                    </div>
                    @if($supplier->city)
                        <div>
                            <label class="text-sm font-medium text-gray-500">City</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $supplier->city }}</p>
                        </div>
                    @endif
                    @if($supplier->province)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Province</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $supplier->province }}</p>
                        </div>
                    @endif
                    @if($supplier->postal_code)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Postal Code</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $supplier->postal_code }}</p>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Right Column - Business Information & Timeline -->
        <div class="space-y-6">
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>
                <div class="space-y-3">
                    @if($supplier->payment_terms)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Payment Terms</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $supplier->payment_terms }}</p>
                        </div>
                    @endif
                    @if($supplier->tax_id)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tax ID / NPWP</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <code class="bg-gray-100 px-2 py-1 rounded">{{ $supplier->tax_id }}</code>
                            </p>
                        </div>
                    @endif
                    <div>
                        <label class="text-sm font-medium text-gray-500">Created At</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supplier->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </x-card>

            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <x-button type="primary" href="{{ route('suppliers.edit', $supplier->id) }}" class="w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Supplier
                    </x-button>
                    <x-button type="secondary" href="{{ route('suppliers.history', $supplier->id) }}" class="w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        View Purchase History
                    </x-button>
                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                        @csrf
                        @method('DELETE')
                        <x-button type="danger" submit class="w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Supplier
                        </x-button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Recent Purchase Orders -->
    <x-card>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Purchase Orders</h3>
        @if($purchaseOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($purchaseOrders->take(10) as $po)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $po->po_number }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $po->order_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'draft' => 'gray',
                                            'pending' => 'yellow',
                                            'approved' => 'blue',
                                            'received' => 'green',
                                            'cancelled' => 'red',
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'pending' => 'Pending',
                                            'approved' => 'Approved',
                                            'received' => 'Received',
                                            'cancelled' => 'Cancelled',
                                        ];
                                    @endphp
                                    <x-badge
                                        :color="$statusColors[$po->status] ?? 'gray'"
                                        :text="$statusLabels[$po->status] ?? ucfirst($po->status)"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($purchaseOrders->count() > 10)
                <div class="mt-4 text-center">
                    <x-button type="secondary" href="{{ route('suppliers.history', $supplier->id) }}">
                        View All Purchase Orders ({{ $purchaseOrders->count() }})
                    </x-button>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No purchase orders yet</p>
            </div>
        @endif
    </x-card>
</div>
@endsection

@php
    $title = 'Supplier Details - ' . $supplier->name;
    $breadcrumb = [
        ['label' => 'Suppliers', 'url' => route('suppliers.index')],
        ['label' => $supplier->name, 'url' => route('suppliers.show', $supplier->id)]
    ];
@endphp
