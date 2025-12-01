<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Stock Opname') }} - {{ $stockOpname->opname_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('inventory.opname.update', $stockOpname->id) }}" x-data="opnameForm()">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="opname_date" class="block text-sm font-medium text-gray-700 mb-2">Opname Date *</label>
                                <input type="date" name="opname_date" id="opname_date" value="{{ old('opname_date', $stockOpname->opname_date->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea name="notes" id="notes" rows="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $stockOpname->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Stock Items</h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-24">System Qty</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-24">Physical Qty *</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-24">Variance</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-48">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="px-4 py-3 text-sm" x-text="item.product_name"></td>
                                                <td class="px-4 py-3 text-center text-sm">
                                                    <span class="font-medium" x-text="item.system_quantity"></span>
                                                    <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                                                    <input type="hidden" :name="`items[${index}][system_quantity]`" x-model="item.system_quantity">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" :name="`items[${index}][physical_quantity]`" x-model.number="item.physical_quantity" @input="calculateVariance(index)" min="0" required class="w-full text-center rounded border-gray-300">
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="font-medium" :class="item.variance > 0 ? 'text-green-600' : (item.variance < 0 ? 'text-red-600' : '')" x-text="item.variance"></span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="text" :name="`items[${index}][variance_reason]`" x-model="item.variance_reason" :required="Math.abs(item.variance_percentage) > 5" class="w-full rounded border-gray-300">
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('inventory.opname.show', $stockOpname->id) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Update Stock Opname
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function opnameForm() {
            return {
                items: @json($stockOpname->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'system_quantity' => $item->system_quantity,
                    'physical_quantity' => $item->physical_quantity,
                    'variance' => $item->variance,
                    'variance_percentage' => $item->variance_percentage,
                    'variance_reason' => $item->variance_reason,
                ])),

                calculateVariance(index) {
                    const item = this.items[index];
                    item.variance = item.physical_quantity - item.system_quantity;
                    if (item.system_quantity > 0) {
                        item.variance_percentage = (item.variance / item.system_quantity) * 100;
                    } else {
                        item.variance_percentage = 0;
                    }
                }
            }
        }
    </script>
</x-app-layout>
