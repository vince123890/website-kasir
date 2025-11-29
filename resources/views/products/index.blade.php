@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Products Management</h2>
            <p class="mt-1 text-sm text-gray-600">Manage your product inventory</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-button type="secondary" href="{{ route('products.downloadTemplate') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                Download Template
            </x-button>
            <button @click="showBulkImportModal = true" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Bulk Import
            </button>
            <button @click="showBulkPriceModal = true" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Bulk Price Update
            </button>
            <x-button type="secondary" href="{{ route('products.export', request()->query()) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export to CSV
            </x-button>
            <x-button type="primary" href="{{ route('products.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Product
            </x-button>
        </div>
    </div>

    <!-- Search and Filters -->
    <x-card>
        <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, SKU, Barcode..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (($filters['category_id'] ?? '') == $category->id) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Level</label>
                    <select name="stock_level" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="out" {{ (($filters['stock_level'] ?? '') === 'out') ? 'selected' : '' }}>Out of Stock</option>
                        <option value="low" {{ (($filters['stock_level'] ?? '') === 'low') ? 'selected' : '' }}>Low Stock</option>
                        <option value="normal" {{ (($filters['stock_level'] ?? '') === 'normal') ? 'selected' : '' }}>Normal</option>
                        <option value="over" {{ (($filters['stock_level'] ?? '') === 'over') ? 'selected' : '' }}>Overstock</option>
                    </select>
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
                <x-button type="secondary" href="{{ route('products.index') }}">
                    Reset
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Products Table -->
    <x-card>
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . str_replace('products/', 'products/thumbnails/', $product->image_path)) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded">
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        <span class="font-mono">{{ $product->sku }}</span>
                                        @if($product->barcode)
                                            <span class="ml-2">| {{ $product->barcode }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $product->category->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucfirst($product->unit) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $totalStock = $product->total_stock ?? 0;
                                        $minStock = $product->min_stock ?? 0;
                                        $maxStock = $product->max_stock ?? 0;

                                        if ($totalStock == 0) {
                                            $stockBadge = 'gray';
                                            $stockText = 'Out';
                                        } elseif ($minStock > 0 && $totalStock < $minStock) {
                                            $stockBadge = 'red';
                                            $stockText = 'Low';
                                        } elseif ($maxStock > 0 && $totalStock > $maxStock) {
                                            $stockBadge = 'orange';
                                            $stockText = 'Over';
                                        } else {
                                            $stockBadge = 'green';
                                            $stockText = 'Normal';
                                        }
                                    @endphp
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $totalStock }}</span>
                                        <x-badge :color="$stockBadge" :text="$stockText" />
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :color="$product->is_active ? 'green' : 'red'" :text="$product->is_active ? 'Active' : 'Inactive'" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('products.show', $product->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Hapus produk ini?')">Delete</button>
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
                <x-pagination :paginator="$products" />
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new product.</p>
                <div class="mt-6">
                    <x-button type="primary" href="{{ route('products.create') }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Product
                    </x-button>
                </div>
            </div>
        @endif
    </x-card>
</div>

<!-- Bulk Import Modal -->
<div x-data="{ showBulkImportModal: false, showBulkPriceModal: false }" x-show="showBulkImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBulkImportModal = false"></div>
        <div class="relative bg-white rounded-lg px-6 pt-5 pb-6 shadow-xl max-w-lg w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Import Products</h3>
            <form action="{{ route('products.bulkImport') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Upload an Excel or CSV file to import multiple products at once.
                    </p>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('products.downloadTemplate') }}" class="text-sm text-blue-600 hover:text-blue-900">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Download Template
                        </a>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Choose File</label>
                        <input type="file" name="file" accept=".xlsx,.csv" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <x-button type="secondary" @click="showBulkImportModal = false">Cancel</x-button>
                    <x-button type="primary" submit>Import</x-button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Price Update Modal -->
<div x-show="showBulkPriceModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBulkPriceModal = false"></div>
        <div class="relative bg-white rounded-lg px-6 pt-5 pb-6 shadow-xl max-w-lg w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Price Update</h3>
            <form action="{{ route('products.bulkPriceUpdate') }}" method="POST" x-data="bulkPriceForm()">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apply to Category (Optional)</label>
                        <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Products</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Change Type</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="change_type" value="increase_percent" x-model="changeType" class="mr-2">
                                <span class="text-sm">Increase by percentage (%)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="change_type" value="decrease_percent" x-model="changeType" class="mr-2">
                                <span class="text-sm">Decrease by percentage (%)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="change_type" value="increase_fixed" x-model="changeType" class="mr-2">
                                <span class="text-sm">Increase by fixed amount (Rp)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="change_type" value="decrease_fixed" x-model="changeType" class="mr-2">
                                <span class="text-sm">Decrease by fixed amount (Rp)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                        <input type="number" name="value" x-model="value" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="bg-blue-50 p-3 rounded-md" x-show="changeType && value">
                        <p class="text-sm text-blue-800">
                            <strong>Preview:</strong>
                            <span x-show="changeType === 'increase_percent'">Rp 100,000 → Rp <span x-text="(100000 * (1 + value/100)).toLocaleString('id-ID')"></span></span>
                            <span x-show="changeType === 'decrease_percent'">Rp 100,000 → Rp <span x-text="(100000 * (1 - value/100)).toLocaleString('id-ID')"></span></span>
                            <span x-show="changeType === 'increase_fixed'">Rp 100,000 → Rp <span x-text="(100000 + parseFloat(value)).toLocaleString('id-ID')"></span></span>
                            <span x-show="changeType === 'decrease_fixed'">Rp 100,000 → Rp <span x-text="(100000 - parseFloat(value)).toLocaleString('id-ID')"></span></span>
                        </p>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <x-button type="secondary" @click="showBulkPriceModal = false">Cancel</x-button>
                    <x-button type="primary" submit>Update Prices</x-button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bulkPriceForm() {
    return {
        changeType: 'increase_percent',
        value: 0
    }
}
</script>
@endsection

@php
    $title = 'Products Management';
    $breadcrumb = [
        ['label' => 'Products', 'url' => route('products.index')]
    ];
@endphp
