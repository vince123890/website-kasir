<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock Opname - {{ $stockOpname->opname_number }}
            </h2>
            <span class="px-3 py-1 text-sm font-semibold rounded-full
                @if($stockOpname->statusBadge['color'] === 'gray') bg-gray-100 text-gray-800
                @elseif($stockOpname->statusBadge['color'] === 'yellow') bg-yellow-100 text-yellow-800
                @elseif($stockOpname->statusBadge['color'] === 'blue') bg-blue-100 text-blue-800
                @elseif($stockOpname->statusBadge['color'] === 'green') bg-green-100 text-green-800
                @elseif($stockOpname->statusBadge['color'] === 'red') bg-red-100 text-red-800
                @endif">
                {{ $stockOpname->statusBadge['label'] }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mb-6 flex flex-wrap gap-2">
                @if($stockOpname->canEdit)
                    <a href="{{ route('inventory.opname.edit', $stockOpname->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Edit
                    </a>
                @endif

                @if($stockOpname->canSubmit)
                    <form action="{{ route('inventory.opname.submit', $stockOpname->id) }}" method="POST" class="inline" onsubmit="return confirm('Submit stock opname for approval?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Submit for Approval
                        </button>
                    </form>
                @endif

                @if($stockOpname->canApprove && auth()->user()->hasRole('Tenant Owner'))
                    <form action="{{ route('inventory.opname.approve', $stockOpname->id) }}" method="POST" class="inline" onsubmit="return confirm('Approve this stock opname?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            Approve
                        </button>
                    </form>

                    <button @click="showRejectModal = true" type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Reject
                    </button>
                @endif

                @if($stockOpname->canFinalize)
                    <form action="{{ route('inventory.opname.finalize', $stockOpname->id) }}" method="POST" class="inline" onsubmit="return confirm('Finalize stock opname? This will update all stock quantities!')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                            Finalize & Update Stock
                        </button>
                    </form>
                @endif

                @if($stockOpname->canEdit)
                    <form action="{{ route('inventory.opname.destroy', $stockOpname->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this stock opname?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                @endif

                <a href="{{ route('inventory.opname.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Back to List
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Opname Info Card -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Opname Information</h3>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Opname Number</dt>
                                <dd class="mt-1 text-sm font-medium">{{ $stockOpname->opname_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Opname Date</dt>
                                <dd class="mt-1 text-sm">{{ $stockOpname->opname_date->format('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Store</dt>
                                <dd class="mt-1 text-sm">{{ $stockOpname->store->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Created By</dt>
                                <dd class="mt-1 text-sm">{{ $stockOpname->createdBy->name ?? '-' }}</dd>
                            </div>
                            @if($stockOpname->notes)
                            <div class="col-span-2">
                                <dt class="text-sm text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm">{{ $stockOpname->notes }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Variance Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Variance Summary</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm text-gray-500">Total Variance Value</dt>
                                <dd class="mt-1 text-lg font-bold">Rp {{ number_format($stockOpname->total_variance_value, 0, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Items with Variance</dt>
                                <dd class="mt-1 text-lg font-medium">{{ $stockOpname->itemsWithVarianceCount }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Items with Shortage</dt>
                                <dd class="mt-1 text-lg font-medium text-red-600">{{ $stockOpname->itemsWithShortageCount }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Items with Surplus</dt>
                                <dd class="mt-1 text-lg font-medium text-green-600">{{ $stockOpname->itemsWithSurplusCount }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Stock Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">System</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Physical</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Variance</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">%</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($stockOpname->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">{{ $item->product->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->product->sku }}</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $item->system_quantity }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-medium">{{ $item->physical_quantity }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-medium @if($item->variance > 0) text-green-600 @elseif($item->variance < 0) text-red-600 @endif">
                                            {{ $item->variance }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm @if(abs($item->variance_percentage) > 5) text-red-600 font-bold @endif">
                                            {{ number_format($item->variance_percentage, 1) }}%
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->variance_reason ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Workflow Timeline -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Workflow Timeline</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium">Created</p>
                                <p class="text-sm text-gray-500">{{ $stockOpname->created_at->format('d M Y H:i') }} by {{ $stockOpname->createdBy->name ?? '-' }}</p>
                            </div>
                        </div>

                        @if($stockOpname->submitted_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium">Submitted</p>
                                <p class="text-sm text-gray-500">{{ $stockOpname->submitted_at->format('d M Y H:i') }} by {{ $stockOpname->submittedBy->name ?? '-' }}</p>
                            </div>
                        </div>
                        @endif

                        @if($stockOpname->approved_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium">Approved</p>
                                <p class="text-sm text-gray-500">{{ $stockOpname->approved_at->format('d M Y H:i') }} by {{ $stockOpname->approvedBy->name ?? '-' }}</p>
                            </div>
                        </div>
                        @endif

                        @if($stockOpname->rejected_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium">Rejected</p>
                                <p class="text-sm text-gray-500">{{ $stockOpname->rejected_at->format('d M Y H:i') }} by {{ $stockOpname->rejectedBy->name ?? '-' }}</p>
                                @if($stockOpname->rejection_reason)
                                <p class="text-sm text-red-600 mt-1">Reason: {{ $stockOpname->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($stockOpname->finalized_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium">Finalized</p>
                                <p class="text-sm text-gray-500">{{ $stockOpname->finalized_at->format('d M Y H:i') }}</p>
                                <p class="text-sm text-green-600 mt-1">Stock quantities updated</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-data="{ showRejectModal: false }" x-show="showRejectModal" @click.away="showRejectModal = false" x-cloak class="fixed inset-0 overflow-y-auto z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="relative bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Reject Stock Opname</h3>
                <form action="{{ route('inventory.opname.reject', $stockOpname->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="4" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Reject Stock Opname
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
