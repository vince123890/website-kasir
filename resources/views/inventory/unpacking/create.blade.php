<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Unpacking Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Whoops!</strong>
                    <span class="block sm:inline">There were some problems with your input.</span>
                    <ul class="mt-3 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('inventory.unpacking.store') }}" x-data="unpackingForm()">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="store_id" class="block text-sm font-medium text-gray-700 mb-2">Store *</label>
                                <select name="store_id" id="store_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Store</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="unpacking_date" class="block text-sm font-medium text-gray-700 mb-2">Unpacking Date *</label>
                                <input type="date" name="unpacking_date" id="unpacking_date" value="{{ old('unpacking_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                            <h3 class="text-sm font-medium text-blue-900 mb-3">Source Product (To Unpack)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="source_product_id" class="block text-sm font-medium text-gray-700 mb-2">Source Product *</label>
                                    <select name="source_product_id" id="source_product_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Source Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('source_product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="source_quantity" class="block text-sm font-medium text-gray-700 mb-2">Source Quantity *</label>
                                    <input type="number" name="source_quantity" id="source_quantity" x-model.number="sourceQuantity" @input="calculateResult()" value="{{ old('source_quantity') }}" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                            <h3 class="text-sm font-medium text-green-900 mb-3">Result Product (After Unpacking)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="result_product_id" class="block text-sm font-medium text-gray-700 mb-2">Result Product *</label>
                                    <select name="result_product_id" id="result_product_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Result Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('result_product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="conversion_ratio" class="block text-sm font-medium text-gray-700 mb-2">Conversion Ratio *</label>
                                    <input type="number" name="conversion_ratio" id="conversion_ratio" x-model.number="conversionRatio" @input="calculateResult()" value="{{ old('conversion_ratio', 1) }}" min="0.01" step="0.01" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">1 source = ? result items</p>
                                </div>

                                <div>
                                    <label for="result_quantity" class="block text-sm font-medium text-gray-700 mb-2">Result Quantity *</label>
                                    <input type="number" name="result_quantity" id="result_quantity" x-model.number="resultQuantity" value="{{ old('result_quantity') }}" min="1" required readonly class="w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Auto-calculated</p>
                                </div>
                            </div>

                            <div class="mt-3 p-3 bg-white rounded border border-green-300">
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">Calculation:</span>
                                    <span x-text="sourceQuantity"></span> × <span x-text="conversionRatio"></span> = <span class="font-bold text-green-700" x-text="resultQuantity"></span> units
                                </p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('inventory.unpacking.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Create Unpacking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function unpackingForm() {
            return {
                sourceQuantity: {{ old('source_quantity', 1) }},
                conversionRatio: {{ old('conversion_ratio', 1) }},
                resultQuantity: {{ old('result_quantity', 1) }},

                calculateResult() {
                    this.resultQuantity = Math.round(this.sourceQuantity * this.conversionRatio);
                }
            }
        }
    </script>
</x-app-layout>
