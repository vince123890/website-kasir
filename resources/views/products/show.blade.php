@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h2>
            <p class="mt-1 text-sm text-gray-600">Product Details</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('products.edit', $product->id) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Product
            </x-button>
            <x-button type="secondary" href="{{ route('products.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Products
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Product Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Information Card -->
            <x-card>
                <div class="flex items-start space-x-6">
                    <div class="shrink-0">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-48 h-48 object-cover rounded-lg border-2 border-gray-200">
                        @else
                            <div class="w-48 h-48 bg-gray-200 rounded-lg border-2 border-gray-300 flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 space-y-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h3>
                            <div class="mt-2 flex items-center space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    SKU: {{ $product->sku }}
                                </span>
                                @if($product->barcode)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        Barcode: {{ $product->barcode }}
                                    </span>
                                @endif
                                <x-badge :color="$product->is_active ? 'green' : 'red'" :text="$product->is_active ? 'Active' : 'Inactive'" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Category</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $product->category->name ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Unit</span>
                                <p class="mt-1 text-sm text-gray-900">{{ ucfirst($product->unit) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Purchase Price</span>
                                <p class="mt-1 text-lg font-bold text-gray-900">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Selling Price</span>
                                <p class="mt-1 text-lg font-bold text-indigo-600">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Profit Margin</span>
                                <p class="mt-1 text-sm font-bold text-green-600">
                                    @php
                                        $margin = $product->purchase_price > 0
                                            ? (($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100
                                            : 0;
                                    @endphp
                                    {{ number_format($margin, 2) }}%
                                </p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Min / Max Stock</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $product->min_stock ?? 0 }} / {{ $product->max_stock ?? 0 }}</p>
                            </div>
                        </div>

                        @if($product->description)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Description</span>
                                <p class="mt-1 text-sm text-gray-700">{{ $product->description }}</p>
                            </div>
                        @endif

                        <div class="pt-4 border-t">
                            <div class="grid grid-cols-2 gap-4 text-xs text-gray-500">
                                <div>
                                    <span class="font-medium">Created:</span>
                                    <span class="ml-2">{{ $product->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Last Updated:</span>
                                    <span class="ml-2">{{ $product->updated_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Stock Per Store -->
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Stock Per Store</h3>
                </div>

                @if($product->stocks && $product->stocks->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store Price</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->stocks as $stock)
                                    @php
                                        $quantity = $stock->quantity;
                                        $minStock = $product->min_stock ?? 0;
                                        $maxStock = $product->max_stock ?? 0;

                                        if ($quantity == 0) {
                                            $stockBadge = 'gray';
                                            $stockText = 'Out of Stock';
                                        } elseif ($minStock > 0 && $quantity < $minStock) {
                                            $stockBadge = 'red';
                                            $stockText = 'Low Stock';
                                        } elseif ($maxStock > 0 && $quantity > $maxStock) {
                                            $stockBadge = 'orange';
                                            $stockText = 'Overstock';
                                        } else {
                                            $stockBadge = 'green';
                                            $stockText = 'Normal';
                                        }

                                        // Get store-specific price if exists
                                        $storePrice = $product->storePrices->where('store_id', $stock->store_id)->first();
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $stock->store->name ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $stock->store->code ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold text-gray-900">{{ $quantity }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-badge :color="$stockBadge" :text="$stockText" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($storePrice)
                                                <span class="text-sm font-medium text-indigo-600">Rp {{ number_format($storePrice->selling_price, 0, ',', '.') }}</span>
                                                <span class="ml-2 text-xs text-gray-500">(Override)</span>
                                            @else
                                                <span class="text-sm text-gray-500">Default</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        <p class="text-sm">No stock information available</p>
                    </div>
                @endif
            </x-card>

            <!-- Price History -->
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Price History</h3>
                    <a href="{{ route('products.priceHistory', $product->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        View All
                    </a>
                </div>

                @if($priceHistory && $priceHistory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changed By</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($priceHistory as $history)
                                    @php
                                        $change = $history->old_price > 0
                                            ? (($history->new_price - $history->old_price) / $history->old_price) * 100
                                            : 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $history->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $history->store->name ?? 'All Stores' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rp {{ number_format($history->old_price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rp {{ number_format($history->new_price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($change > 0)
                                                <span class="text-sm font-medium text-green-600">+{{ number_format($change, 2) }}%</span>
                                            @elseif($change < 0)
                                                <span class="text-sm font-medium text-red-600">{{ number_format($change, 2) }}%</span>
                                            @else
                                                <span class="text-sm text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $history->user->name ?? 'System' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        <p class="text-sm">No price history available</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Right Column - Statistics -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Statistics</h3>
                <div class="space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-900">Total Stock</span>
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-blue-900">{{ $statistics['total_stock'] ?? 0 }}</p>
                        <p class="mt-1 text-xs text-blue-700">Across all stores</p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-green-900">Profit Margin</span>
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-green-900">{{ $statistics['profit_margin'] ?? 0 }}%</p>
                        <p class="mt-1 text-xs text-green-700">Profit percentage</p>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-purple-900">Stores</span>
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-purple-900">{{ $statistics['stores_count'] ?? 0 }}</p>
                        <p class="mt-1 text-xs text-purple-700">Available in stores</p>
                    </div>

                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-red-900">Low Stock Alerts</span>
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-red-900">{{ $statistics['low_stock_stores'] ?? 0 }}</p>
                        <p class="mt-1 text-xs text-red-700">Stores with low stock</p>
                    </div>
                </div>
            </x-card>

            <!-- Quick Actions -->
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('products.edit', $product->id) }}" class="block w-full px-4 py-2 text-sm font-medium text-center text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">
                        Edit Product
                    </a>
                    <a href="{{ route('products.priceHistory', $product->id) }}" class="block w-full px-4 py-2 text-sm font-medium text-center text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        View Price History
                    </a>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-center text-red-700 bg-white border border-red-300 rounded-md hover:bg-red-50">
                            Delete Product
                        </button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@php
    $title = 'Product Details - ' . $product->name;
    $breadcrumb = [
        ['label' => 'Products', 'url' => route('products.index')],
        ['label' => $product->name, 'url' => route('products.show', $product->id)]
    ];
@endphp
