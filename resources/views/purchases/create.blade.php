@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Purchase Order</h2>
            <p class="mt-1 text-sm text-gray-600">Create a new purchase order for inventory procurement</p>
        </div>
        <x-button type="secondary" href="{{ route('purchases.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to List
        </x-button>
    </div>

    <!-- Create Form -->
    <form action="{{ route('purchases.store') }}" method="POST" x-data="purchaseOrderForm()">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Order Information -->
            <x-card class="lg:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select name="supplier_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">- Select Supplier -</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Order Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="order_date" required value="{{ old('order_date', date('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            @error('order_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Expected Delivery <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="expected_delivery_date" required value="{{ old('expected_delivery_date') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            @error('expected_delivery_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            <!-- Right Column - Summary -->
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-medium" x-text="'Rp ' + subtotal.toLocaleString('id-ID')">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax:</span>
                        <input type="number" name="tax_amount" x-model="tax" step="0.01" min="0" class="w-32 px-2 py-1 text-right border border-gray-300 rounded text-sm">
                    </div>
                    <div class="border-t pt-3 flex justify-between">
                        <span class="font-medium">Total:</span>
                        <span class="text-lg font-bold text-indigo-600" x-text="'Rp ' + total.toLocaleString('id-ID')">Rp 0</span>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Items Section -->
        <x-card class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                <button type="button" @click="addItem" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Add Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-4 py-3">
                                    <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" required class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                        <option value="">- Select Product -</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->purchase_price ?? 0 }}">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" @input="calculateItemSubtotal(index)" min="1" required class="w-24 px-2 py-1 border border-gray-300 rounded text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" @input="calculateItemSubtotal(index)" step="0.01" min="0" required class="w-32 px-2 py-1 border border-gray-300 rounded text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium" x-text="'Rp ' + (item.subtotal || 0).toLocaleString('id-ID')">Rp 0</span>
                                </td>
                                <td class="px-4 py-3">
                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-center text-sm text-gray-500" x-show="items.length === 0">
                No items added. Click "Add Item" to start.
            </div>
        </x-card>

        <!-- Form Actions -->
        <x-card class="mt-6">
            <div class="flex items-center justify-end space-x-4">
                <x-button type="secondary" href="{{ route('purchases.index') }}">
                    Cancel
                </x-button>
                <x-button type="primary" submit>
                    Save as Draft
                </x-button>
                <button type="submit" name="submit_for_approval" value="1" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Save & Submit for Approval
                </button>
            </div>
        </x-card>
    </form>
</div>

<script>
function purchaseOrderForm() {
    return {
        items: [],
        tax: 0,

        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
        },

        get total() {
            return this.subtotal + parseFloat(this.tax || 0);
        },

        addItem() {
            this.items.push({
                product_id: '',
                quantity: 1,
                unit_price: 0,
                subtotal: 0
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        calculateItemSubtotal(index) {
            const item = this.items[index];
            item.subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
        }
    }
}
</script>
@endsection

@php
    $title = 'Create Purchase Order';
    $breadcrumb = [
        ['label' => 'Purchase Orders', 'url' => route('purchases.index')],
        ['label' => 'Create', 'url' => route('purchases.create')]
    ];
@endphp
