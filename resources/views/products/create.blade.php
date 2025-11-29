@extends('layouts.admin')

@section('page-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Product</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new product to your inventory</p>
        </div>
        <x-button type="secondary" href="{{ route('products.index') }}">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Products
        </x-button>
    </div>

    <!-- Create Form -->
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" x-data="productForm()">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <x-card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="space-y-4">
                    <x-form.input
                        name="name"
                        label="Product Name"
                        :value="old('name')"
                        required
                        placeholder="Enter product name"
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            SKU <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-2">
                            <input
                                type="text"
                                name="sku"
                                x-model="sku"
                                required
                                placeholder="PRD-20251130-001"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 uppercase"
                            >
                            <button
                                @click.prevent="generateSKU"
                                type="button"
                                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                            >
                                Generate
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Auto-generated or enter manually</p>
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-form.input
                        name="barcode"
                        label="Barcode"
                        :value="old('barcode')"
                        placeholder="1234567890 (optional)"
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="category_id"
                            x-model="categoryId"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">- Select Category -</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Unit <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="unit"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">- Select Unit -</option>
                            <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pcs (Pieces)</option>
                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                            <option value="carton" {{ old('unit') == 'carton' ? 'selected' : '' }}>Carton</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kg (Kilogram)</option>
                            <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>Gram</option>
                            <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                            <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>ML (Milliliter)</option>
                            <option value="dozen" {{ old('unit') == 'dozen' ? 'selected' : '' }}>Dozen</option>
                            <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                            <option value="bottle" {{ old('unit') == 'bottle' ? 'selected' : '' }}>Bottle</option>
                            <option value="can" {{ old('unit') == 'can' ? 'selected' : '' }}>Can</option>
                            <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>Unit</option>
                        </select>
                        @error('unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            name="description"
                            rows="4"
                            placeholder="Enter product description (optional)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            <!-- Right Column -->
            <div class="space-y-6">
                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing & Stock</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Purchase Price <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input
                                    type="number"
                                    name="purchase_price"
                                    x-model="purchasePrice"
                                    @input="calculateProfitMargin"
                                    required
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                            @error('purchase_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Selling Price <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input
                                    type="number"
                                    name="selling_price"
                                    x-model="sellingPrice"
                                    @input="calculateProfitMargin"
                                    required
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                            @error('selling_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-blue-50 p-3 rounded-md" x-show="profitMargin !== null">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-blue-900">Profit Margin:</span>
                                <span class="text-sm font-bold text-blue-900" x-text="profitMargin + '%'"></span>
                            </div>
                            <div class="mt-1 text-xs text-blue-700" x-show="profitMargin < 0">
                                <span class="text-red-600">⚠️ Selling price must be greater than purchase price!</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Min Stock</label>
                                <input
                                    type="number"
                                    name="min_stock"
                                    value="{{ old('min_stock', 0) }}"
                                    min="0"
                                    placeholder="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                @error('min_stock')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Stock</label>
                                <input
                                    type="number"
                                    name="max_stock"
                                    value="{{ old('max_stock', 0) }}"
                                    min="0"
                                    placeholder="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                @error('max_stock')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Product Image</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Image</label>
                            <div class="flex items-center space-x-4">
                                <div class="shrink-0">
                                    <img
                                        x-show="imagePreview"
                                        :src="imagePreview"
                                        alt="Preview"
                                        class="h-32 w-32 object-cover rounded-lg border-2 border-gray-300"
                                    >
                                    <div x-show="!imagePreview" class="h-32 w-32 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <input
                                        type="file"
                                        name="image"
                                        accept="image/jpeg,image/png,image/webp"
                                        @change="previewImage"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">JPG, PNG, or WebP. Max 5MB.</p>
                                    @error('image')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>
                    <x-form.radio
                        name="is_active"
                        label="Product Status"
                        :options="['1' => 'Active', '0' => 'Inactive']"
                        :selected="old('is_active', '1')"
                    />
                </x-card>
            </div>
        </div>

        <!-- Form Actions -->
        <x-card class="mt-6">
            <div class="flex items-center justify-end space-x-4">
                <x-button type="secondary" href="{{ route('products.index') }}">
                    Cancel
                </x-button>
                <x-button type="primary" submit>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Product
                </x-button>
            </div>
        </x-card>
    </form>
</div>

<script>
function productForm() {
    return {
        sku: '{{ old('sku') }}',
        categoryId: '{{ old('category_id') }}',
        purchasePrice: {{ old('purchase_price', 0) }},
        sellingPrice: {{ old('selling_price', 0) }},
        profitMargin: null,
        imagePreview: null,

        async generateSKU() {
            if (!this.categoryId) {
                alert('Please select a category first');
                return;
            }

            try {
                const response = await fetch('/products/generate-sku?category_id=' + this.categoryId);
                const data = await response.json();

                if (data.success) {
                    this.sku = data.sku;
                }
            } catch (error) {
                console.error('Failed to generate SKU:', error);
            }
        },

        calculateProfitMargin() {
            if (this.purchasePrice > 0 && this.sellingPrice > 0) {
                const margin = ((this.sellingPrice - this.purchasePrice) / this.purchasePrice) * 100;
                this.profitMargin = margin.toFixed(2);
            } else {
                this.profitMargin = null;
            }
        },

        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        init() {
            this.calculateProfitMargin();
        }
    }
}
</script>
@endsection

@php
    $title = 'Create Product';
    $breadcrumb = [
        ['label' => 'Products', 'url' => route('products.index')],
        ['label' => 'Create', 'url' => route('products.create')]
    ];
@endphp
